<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Subscriber;

use PostDirekt\Addressfactory\NRLEJPostDirektAddressfactory;
use PostDirekt\Addressfactory\Service\AnalysisStatusUpdater;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressDefinition;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderAddressChangeSubscriber implements EventSubscriberInterface
{
    private readonly EntityRepository $analysisResultRepo;

    private readonly EntityRepository $orderRepo;

    private readonly LoggerInterface $logger;

    public function __construct(
        EntityRepository $analysisResultRepo,
        EntityRepository $orderRepo,
        private readonly AnalysisStatusUpdater $analysisStatusUpdater,
        LoggerInterface $logger
    ) {
        $this->analysisResultRepo = $analysisResultRepo;
        $this->orderRepo = $orderRepo;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntityWrittenContainerEvent::class => 'onEntityWrittenContainerEvent',
        ];
    }

    public function onEntityWrittenContainerEvent(EntityWrittenContainerEvent $event): void
    {
        $orderEvent = $event->getEventByEntityName(OrderDefinition::ENTITY_NAME);
        if (!$orderEvent) {
            return;
        }
        $orderId = current($orderEvent->getIds());
        $orderAddressEvent = $event->getEventByEntityName(OrderAddressDefinition::ENTITY_NAME);

        if ($orderAddressEvent) {
            $this->onOrderAddressChange($orderAddressEvent, $orderId);
        }
    }

    public function onOrderAddressChange(EntityWrittenEvent $event, string $orderId): void
    {
        $writes = $event->getWriteResults();
        foreach ($writes as $write) {
            $payload = $write->getPayload();
            if (!$payload) {
                continue;
            }
            if ($write->getOperation() !== 'update') {
                continue;
            }
            if ($payload['orderVersionId'] !== Defaults::LIVE_VERSION) {
                continue;
            }
            if (isset($payload['customFields'][NRLEJPostDirektAddressfactory::CUSTOM_FIELD_STATUS_ON_ADDRESS]) &&
                $payload['customFields'][NRLEJPostDirektAddressfactory::CUSTOM_FIELD_STATUS_ON_ADDRESS] === AnalysisStatusUpdater::ADDRESS_CORRECTED) {
                continue;
            }

            $context = $event->getContext();
            $addressId = $write->getPrimaryKey();
            $previous = $this->analysisStatusUpdater->getStatus($orderId, $event->getContext());
            $shippingAddressId = $this->getShippingAddressId($orderId, $event->getContext());

            if ($addressId !== $shippingAddressId || $previous === AnalysisStatusUpdater::MANUALLY_EDITED) {
                continue;
            }
            $correctedCollection = $this->analysisResultRepo->search(
                (new Criteria())->addFilter(new EqualsFilter('orderAddressId', $addressId)),
                $context
            );

            if ($previous !== AnalysisStatusUpdater::ADDRESS_CORRECTED || $correctedCollection->count() === 0) {
                $this->update($orderId, $shippingAddressId, $context);
                return;
            }

            $correctedVars = $correctedCollection->first()->getVars();

            if ($this->isAddressChanged($correctedVars, $payload)) {
                $this->update($orderId, $shippingAddressId, $context);
            }
        }
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

    private function getAnalysisResultIdsByAddressId(string $addressId, Context $context): array
    {
        $result = $this->analysisResultRepo->searchIds(
            (new Criteria())->addFilter(new EqualsFilter('orderAddressId', $addressId)),
            $context
        );

        return $result->getIds();
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

    private function isAddressChanged(array $correctedVars, array $payload): bool
    {
        return (isset($payload['firstName']) &&
                $correctedVars['firstName'] !== $payload['firstName'])

            || (isset($payload['lastName']) &&
                $correctedVars['lastName'] !== $payload['lastName'])

            || (isset($payload['street']) &&
                $correctedVars['street'] . ' ' . $correctedVars['streetNumber'] !== $payload['street'])

            || (isset($payload['zipcode']) &&
                $correctedVars['postalCode'] !== $payload['zipcode'])

            || (isset($payload['city']) &&
                $correctedVars['city'] !== $payload['city']);
    }
}
