<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Subscriber;

use PostDirekt\Addressfactory\Service\AnalysisStatusUpdater;
use PostDirekt\Addressfactory\Service\ModuleConfig;
use PostDirekt\Addressfactory\Service\OrderAnalysis;
use PostDirekt\Addressfactory\Service\OrderUpdater;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NewOrderSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ModuleConfig $config,
        private readonly AnalysisStatusUpdater $analysisStatus,
        private readonly OrderAnalysis $analyseService,
        private readonly OrderUpdater $orderUpdater,
        private readonly LoggerInterface $logger
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutOrderPlacedEvent::class => 'onOrderPlace',
        ];
    }

    public function onOrderPlace(CheckoutOrderPlacedEvent $event): void
    {
        $order = $event->getOrder();

        $deliveries = $order->getDeliveries();
        if ($deliveries === null) {
            // Order is virtual or broken
            return;
        }

        $shippingAddress = $deliveries->getShippingAddress()->first();

        if ($shippingAddress === null
            || ($country = $shippingAddress->getCountry()) === null
            || $country->getIso() !== 'DE') {
            // Only process german shipping addresses
            return;
        }

        $channelId = $event->getSalesChannelId();

        $orderId = $order->getId();
        $status = $this->analysisStatus->getStatus($orderId, $event->getContext());
        if ($status !== AnalysisStatusUpdater::NOT_ANALYSED) {
            // The order already has been analysed
            return;
        }

        if ($this->config->isManualAnalysis($channelId)) {
            $this->analysisStatus->setStatusNotAnalyzed($orderId, $event->getContext());
        }

        if ($this->config->isCronAnalysis($channelId)) {
            // Pending status means the cron will pick up the order
            $this->analysisStatus->setStatusPending($orderId, $event->getContext());
        }

        if ($this->config->isSynchronousAnalysis($channelId)) {
            $analysisResults = $this->analyseService->analyse([$order], $event->getContext());
            $analysisResult = $analysisResults[$orderId];
            if (!$analysisResult) {
                $this->logger->error(
                    sprintf('ADDRESSFACTORY DIRECT: Order %s could not be analysed', (string) $order->getOrderNumber())
                );

                return;
            }
            if ($this->config->isAutoCancelNonDeliverableOrders($channelId)) {
                $this->orderUpdater->cancelIfUndeliverable($order, $analysisResult, $event->getContext());
            }
            if ($this->config->isAutoUpdateShippingAddress($channelId)) {
                $this->analyseService->updateShippingAddress($orderId, $analysisResult, $event->getContext());
            }
        }
    }
}
