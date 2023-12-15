<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Service;

use PostDirekt\Addressfactory\Resources\OrderAddress\AnalysisStatus;
use PostDirekt\Addressfactory\Resources\OrderAddress\AnalysisStatusCollection;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class AnalysisStatusUpdater
{
    final public const NOT_ANALYSED = 'not_analysed';
    final public const PENDING = 'pending';
    final public const UNDELIVERABLE = 'undeliverable';
    final public const CORRECTION_REQUIRED = 'correction_required';
    final public const POSSIBLY_DELIVERABLE = 'possibly_deliverable';
    final public const DELIVERABLE = 'deliverable';
    final public const ADDRESS_CORRECTED = 'address_corrected';
    final public const ANALYSIS_FAILED = 'analysis_failed';
    final public const MANUALLY_EDITED = 'manually_edited';


    /**
     * @param EntityRepository<AnalysisStatusCollection> $statusRepository
     */
    public function __construct(
        private readonly EntityRepository $statusRepository,
        private readonly LoggerInterface $logger
    ) {
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
        $deliverabilityStatus = $this->statusRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('orderId', $orderId)),
            $context
        )->first();
        if (!$deliverabilityStatus instanceof AnalysisStatus) {
            return self::NOT_ANALYSED;
        }

        return $deliverabilityStatus->getStatus();
    }

    /**
     * @param array<string,string> $data
     */
    private function updateStatus(array $data, Context $context): bool
    {
        /** @var EntitySearchResult<AnalysisStatusCollection> $statusCollection */
        $statusCollection = $this->statusRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('orderId', $data['orderId'])),
            $context
        );

        if ($statusCollection->getTotal() > 0) {
            // add ID if a status for the order does already exist
            $firstStatus = $statusCollection->first();
            if ($firstStatus instanceof AnalysisStatus) {
                $data['id'] = $firstStatus->getId();
            }
        }

        $event = $this->statusRepository->upsert([$data], $context);
        if ($event->getErrors()) {
            foreach ($event->getErrors() as $error) {
                $this->logger->error($error);
            }

            return false;
        }

        return true;
    }
}
