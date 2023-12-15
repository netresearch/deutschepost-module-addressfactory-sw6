<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\ScheduledTask;

use PostDirekt\Addressfactory\Resources\OrderAddress\AnalysisResultInterface;
use PostDirekt\Addressfactory\Resources\OrderAddress\AnalysisStatus;
use PostDirekt\Addressfactory\Resources\OrderAddress\AnalysisStatusCollection;
use PostDirekt\Addressfactory\Service\AnalysisStatusUpdater;
use PostDirekt\Addressfactory\Service\ModuleConfig;
use PostDirekt\Addressfactory\Service\OrderAnalysis;
use PostDirekt\Addressfactory\Service\OrderUpdater;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskCollection;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: AutoProcess::class)]
class AutoProcessHandler extends ScheduledTaskHandler
{
    /**
     * @param EntityRepository<AnalysisStatusCollection> $analysisStatusRepo
     * @param EntityRepository<ScheduledTaskCollection> $scheduledTaskRepository
     */
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        private readonly ModuleConfig $config,
        private readonly OrderAnalysis $orderAnalysisService,
        private readonly EntityRepository $analysisStatusRepo,
        private readonly OrderUpdater $orderUpdater,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct(
            $scheduledTaskRepository,
        );
    }

    /**
     * Analyse and process all new Orders that have been put into analysis status "pending"
     */
    public function run(): void
    {
        $context = Context::createDefaultContext();

        if (!$this->config->isCronAnalysis()) {
            return;
        }
        $orders = $this->loadPendingOrders($context);
        $analysisResults = $this->orderAnalysisService->analyse($orders, $context);

        foreach ($orders as $order) {
            $analysisResult = $analysisResults[$order->getId()];
            if ($analysisResult) {
                $this->process($order, $analysisResult, $context);
            } else {
                $this->logger->error(
                    sprintf('ADDRESSFACTORY DIRECT: Order %s could not be analysed', (string) $order->getOrderNumber())
                );
            }
        }
    }

    /**
     * Process given order according to the module configuration:
     *
     * - put it on hold,
     * - cancel it or
     * - update the shipping address
     */
    private function process(OrderEntity $order, AnalysisResultInterface $analysisResult, Context $context): void
    {
        $isCanceled = false;
        $this->logger->info(
            sprintf('ADDRESSFACTORY DIRECT: Processing Order %s ...', (string) $order->getOrderNumber())
        );

        if ($this->config->isAutoCancelNonDeliverableOrders($order->getSalesChannelId())) {
            $isCanceled = $this->orderUpdater->cancelIfUndeliverable($order, $analysisResult, $context);
            if ($isCanceled) {
                $this->logger->info(
                    sprintf(
                        'ADDRESSFACTORY DIRECT: Undeliverable Order "%s" cancelled',
                        (string) $order->getOrderNumber()
                    )
                );
            }
        }
        if (!$isCanceled && $this->config->isAutoUpdateShippingAddress($order->getSalesChannelId())) {
            $isUpdated = $this->orderAnalysisService->updateShippingAddress($order->getId(), $analysisResult, $context);
            if ($isUpdated) {
                $this->logger->info(
                    sprintf(
                        'ADDRESSFACTORY DIRECT: Order "%s" address updated',
                        (string) $order->getOrderNumber()
                    )
                );
            }
        }
    }

    /**
     * Fetch all orders with analysis status "pending" from the database.
     *
     * @return OrderEntity[]
     */
    private function loadPendingOrders(Context $context): array
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('status', AnalysisStatusUpdater::PENDING))
            ->addAssociations(['order', 'order.deliveries', 'order.deliveries.shippingOrderAddress.country']);
        /** @var AnalysisStatusCollection $statuses */
        $statuses = $this->analysisStatusRepo->search($criteria, $context)->getEntities();

        return $statuses->fmap(
            static fn(AnalysisStatus $item): ?OrderEntity => $item->getOrder()
        );
    }
}
