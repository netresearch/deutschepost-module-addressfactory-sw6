<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Service;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class AnalysisStatusUpdater
{
    public const NOT_ANALYSED = 'not_analysed';
    public const PENDING = 'pending';
    public const UNDELIVERABLE = 'undeliverable';
    public const CORRECTION_REQUIRED = 'correction_required';
    public const POSSIBLY_DELIVERABLE = 'possibly_deliverable';
    public const DELIVERABLE = 'deliverable';
    public const ADDRESS_CORRECTED = 'address_corrected';
    public const ANALYSIS_FAILED = 'analysis_failed';
    public const MANUALLY_EDITED = 'manually_edited';

    private EntityRepository $repository;

    private LoggerInterface $logger;

    public function __construct(
        EntityRepository $repository,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->logger = $logger;
    }

    public function setStatusPending(string $orderId, Context $context): bool
    {
        return $this->updateStatus([
            'orderId' => $orderId,
            'status' => self::PENDING,
        ], $context);
    }

    public function setStatusUndeliverable(string $orderId, Context $context): bool
    {
        return $this->updateStatus([
            'orderId' => $orderId,
            'status' => self::UNDELIVERABLE,
        ], $context);
    }

    public function setStatusCorrectionRequired(string $orderId, Context $context): bool
    {
        return $this->updateStatus([
            'orderId' => $orderId,
            'status' => self::CORRECTION_REQUIRED,
        ], $context);
    }

    public function setStatusNotAnalyzed(string $orderId, Context $context): bool
    {
        return $this->updateStatus([
            'orderId' => $orderId,
            'status' => self::NOT_ANALYSED
        ], $context);
    }

    public function setStatusPossiblyDeliverable(string $orderId, Context $context): bool
    {
        return $this->updateStatus([
            'orderId' => $orderId,
            'status' => self::POSSIBLY_DELIVERABLE,
        ], $context);
    }

    public function setStatusDeliverable(string $orderId, Context $context): bool
    {
        return $this->updateStatus([
            'orderId' => $orderId,
            'status' => self::DELIVERABLE,
        ], $context);
    }

    public function setStatusAddressCorrected(string $orderId, Context $context): bool
    {
        return $this->updateStatus([
            'orderId' => $orderId,
            'status' => self::ADDRESS_CORRECTED,
        ], $context);
    }

    public function setStatusAnalysisFailed(string $orderId, Context $context): bool
    {
        return $this->updateStatus([
            'orderId' => $orderId,
            'status' => self::ANALYSIS_FAILED,
        ], $context);
    }

    public function setStatusManuallyEdited(string $orderId, Context $context): bool
    {
        return $this->updateStatus([
            'orderId' => $orderId,
            'status' => self::MANUALLY_EDITED,
        ], $context);
    }

    public function getStatus(string $orderId, Context $context): string
    {
        $deliverabilityStatus = $this->repository->search(
            (new Criteria())->addFilter(new EqualsFilter('orderId', $orderId)),
            $context
        )->first();
        if (!$deliverabilityStatus) {
            return self::NOT_ANALYSED;
        }

        return $deliverabilityStatus->getStatus();
    }

    /**
     * @param array<string,string> $data
     */
    private function updateStatus(array $data, Context $context): bool
    {
        $statusesCollection = $this->repository->search(
            (new Criteria())->addFilter(new EqualsFilter('orderId', $data['orderId'])),
            $context
        );

        if ($statusesCollection->getTotal()) {
            // add ID if a status for the order does already exists
            $data['id'] = $statusesCollection->first()->getId();
        }

        $event = $this->repository->upsert([$data], $context);
        if ($event->getErrors()) {
            foreach ($event->getErrors() as $error) {
                $this->logger->error($error);
            }

            return false;
        }

        return true;
    }
}
