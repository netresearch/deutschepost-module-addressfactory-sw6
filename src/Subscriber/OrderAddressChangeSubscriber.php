<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Subscriber;

use PostDirekt\Addressfactory\Service\AnalysisStatusUpdater;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Order\OrderEvents;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderAddressChangeSubscriber implements EventSubscriberInterface
{
    private EntityRepositoryInterface $analysisResultRepo;

    private EntityRepositoryInterface $orderRepo;

    private AnalysisStatusUpdater $analysisStatusUpdater;

    private LoggerInterface $logger;

    public function __construct(
        EntityRepositoryInterface $analysisResultRepo,
        EntityRepositoryInterface $orderRepo,
        AnalysisStatusUpdater $analysisStatusUpdater,
        LoggerInterface $logger
    ) {
        $this->analysisResultRepo = $analysisResultRepo;
        $this->orderRepo = $orderRepo;
        $this->analysisStatusUpdater = $analysisStatusUpdater;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OrderEvents::ORDER_ADDRESS_WRITTEN_EVENT => 'onOrderAddressChange',
        ];
    }

    public function onOrderAddressChange(EntityWrittenEvent $event): void
    {
        $writes = $event->getWriteResults();
        foreach ($writes as $write) {
            if ($write->getPayload()) {
                if ($write->getOperation() === 'update' && \array_key_exists('orderId', $write->getPayload())) {
                    $context = $event->getContext();
                    $addressId = $write->getPrimaryKey();
                    $orderId = $write->getPayload()['orderId'];
                    $previous = $this->analysisStatusUpdater->getStatus($orderId, $event->getContext());
                    $shippingAddressId = $this->getShippingAddressId($orderId, $event->getContext());
                    if (($addressId === $shippingAddressId)
                        && ($previous !== AnalysisStatusUpdater::MANUALLY_EDITED)) {
                        $this->update($orderId, $shippingAddressId, $context);
                    }
                }
            }
        }
    }

    private function update(string $orderId, string $shippingAddressId, Context $context): void
    {
        $isManuallyEdited = $this->analysisStatusUpdater->setStatusManuallyEdited($orderId, $context);
        if ($isManuallyEdited) {
            $statusIds = $this->getAnalysisResultIdsByAddressId($shippingAddressId, $context);
            if (!empty($statusIds)) {
                foreach ($statusIds as $id) {
                    $this->deleteOldResult($id, $context);
                }
            }
        }
    }

    private function deleteOldResult(string $statusId, Context $context): void
    {
        $event = $this->analysisResultRepo->delete([['id' => $statusId]], $context);
        if ($event->getErrors()) {
            foreach ($event->getErrors() as $error) {
                $this->logger->error($error);
            }
        }
    }

    private function getAnalysisResultIdsByAddressId(string $addressId, Context $context): array
    {
        $result = $this->analysisResultRepo->searchIds(
            (new Criteria())->addFilter(new EqualsFilter('orderAddressId', $addressId)),
            $context
        );

        return $result->getIds();
    }

    private function getShippingAddressId(string $orderId, Context $context): string
    {
        /** @var OrderEntity $order */
        $order = $this->orderRepo->search(
            (new Criteria([$orderId]))->addAssociation('deliveries'),
            $context
        )->first();

        $deliveries = $order->getDeliveries();
        if (!$deliveries) {
            return '';
        }
        $shippingAddress = $deliveries->getShippingAddress()->first();
        if (!$shippingAddress) {
            return '';
        }

        return $shippingAddress->getId();
    }
}
