<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Service;

use PostDirekt\Addressfactory\NRLEJPostDirektAddressfactory;
use PostDirekt\Addressfactory\Resources\OrderAddress\AnalysisResultInterface;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class AddressUpdater
{
    public function __construct(private readonly EntityRepository $orderAddressRepository)
    {
    }

    /**
     * Overwrite an Order Address with data from an ADDRESSFACTORY DIRECT deliverability analyis.
     *
     * The only address fields that will be modified are:
     * - first name
     * - last name
     * - street
     * - city
     * - postal code
     *
     * Note: Do not use this method directly when operating on Order scope,
     * use PostDirekt\Addressfactory\Model\OrderAnalysis::updateShippingAddress instead
     * to keep the Order's analysis status in sync.
     *
     * @return bool If the Address update was successfull
     */
    public function update(
        AnalysisResultInterface $analysisResult,
        Context $context
    ): bool {
        $orderAddress = $this->orderAddressRepository->search(
            new Criteria([$analysisResult->getOrderAddressId()]),
            $context
        )->first();
        if (!$orderAddress instanceof OrderAddressEntity || !$this->addressesAreDifferent(
                $analysisResult,
                $orderAddress
            )) {
            return false;
        }

        $event = $this->orderAddressRepository->update(
            [
                [
                    'id' => $analysisResult->getOrderAddressId(),
                    'street' => implode(' ', [$analysisResult->getStreet(), $analysisResult->getStreetNumber()]),
                    'firstName' => $analysisResult->getFirstName(),
                    'lastName' => $analysisResult->getLastName(),
                    'zipcode' => $analysisResult->getPostalCode(),
                    'city' => $analysisResult->getCity(),
                    'customFields' => [
                        NRLEJPostDirektAddressfactory::CUSTOM_FIELD_STATUS_ON_ADDRESS => AnalysisStatusUpdater::ADDRESS_CORRECTED
                    ],
                ],
            ],
            $context
        );
        if ($event->getErrors()) {
            return false;
        }

        return true;
    }

    public function addressesAreDifferent(
        AnalysisResultInterface $analysisResult,
        OrderAddressEntity $orderAddress
    ): bool {
        $street = trim(implode(' ', [$analysisResult->getStreet(), $analysisResult->getStreetNumber()]));
        $orderStreet = trim((string)$orderAddress->getStreet());

        return $orderAddress->getFirstName() !== $analysisResult->getFirstName()
            || $orderAddress->getLastName() !== $analysisResult->getLastName()
            || $orderAddress->getCity() !== $analysisResult->getCity()
            || $orderAddress->getZipcode() !== $analysisResult->getPostalCode()
            || $street !== $orderStreet;
    }
}
