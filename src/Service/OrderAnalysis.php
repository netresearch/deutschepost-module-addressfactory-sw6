<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Service;

use PostDirekt\Addressfactory\Resources\OrderAddress\AnalysisResultInterface;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;

class OrderAnalysis
{
    public function __construct(private readonly AddressAnalysis $addressAnalysis, private readonly DeliverabilityCodes $deliverabilityScore, private readonly AnalysisStatusUpdater $deliverabilityStatus, private readonly AddressUpdater $addressUpdater)
    {
    }

    /**
     * Get ADDRESSFACTORY DIRECT Deliverability analysis objects
     * for the Shipping Address of every given Order.
     *
     * @param OrderEntity[] $orders
     *
     * @return array<string, AnalysisResultInterface|null>
     */
    public function analyse(array $orders, Context $context): array
    {
        $addresses = [];
        foreach ($orders as $order) {
            $deliveries = $order->getDeliveries();
            if ($deliveries) {
                $addresses[] = $deliveries->getShippingAddress()->first();
            }
        }
        /** @var OrderAddressEntity[] $addresses */
        $addresses = array_filter($addresses);

        try {
            $analysisResults = $this->addressAnalysis->analyse($addresses, $context);
        } catch (\RuntimeException) {
            $analysisResults = [];
        }
        $result = [];
        foreach ($addresses as $address) {
            $analysisResult = $analysisResults[$address->getId()] ?? null;
            $this->updateDeliverabilityStatus($address->getOrderId(), $analysisResult, $context);
            $result[$address->getOrderId()] = $analysisResult;
        }

        return $result;
    }

    public function updateShippingAddress(
        string $orderId,
        AnalysisResultInterface $analysisResult,
        Context $context
    ): bool {
        $wasUpdated = $this->addressUpdater->update($analysisResult, $context);
        if ($wasUpdated) {
            $this->deliverabilityStatus->setStatusAddressCorrected($orderId, $context);
        }

        return $wasUpdated;
    }

    private function updateDeliverabilityStatus(
        string $orderId,
        ?AnalysisResultInterface $analysisResult,
        Context $context
    ): void {
        if (!$analysisResult) {
            $this->deliverabilityStatus->setStatusAnalysisFailed($orderId, $context);

            return;
        }

        $currentStatus = $this->deliverabilityStatus->getStatus($orderId, $context);
        $statusCode = $this->deliverabilityScore->computeScore(
            $analysisResult->getStatusCodes(),
            $currentStatus === AnalysisStatusUpdater::ADDRESS_CORRECTED
        );
        switch ($statusCode) {
            case DeliverabilityCodes::DELIVERABLE:
                $this->deliverabilityStatus->setStatusDeliverable($orderId, $context);

                break;
            case DeliverabilityCodes::POSSIBLY_DELIVERABLE:
                $this->deliverabilityStatus->setStatusPossiblyDeliverable($orderId, $context);

                break;
            case DeliverabilityCodes::UNDELIVERABLE:
                $this->deliverabilityStatus->setStatusUndeliverable($orderId, $context);

                break;
            case DeliverabilityCodes::CORRECTION_REQUIRED:
                $this->deliverabilityStatus->setStatusCorrectionRequired($orderId, $context);
        }
    }
}
