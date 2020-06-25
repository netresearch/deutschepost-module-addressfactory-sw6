<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Service;

use PostDirekt\Addressfactory\Resources\OrderAddress\AnalysisResultInterface;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

class AddressUpdater
{
    /**
     * @var EntityRepositoryInterface
     */
    private $orderAddressRepository;

    public function __construct(EntityRepositoryInterface $orderAddressRepository)
    {
        $this->orderAddressRepository = $orderAddressRepository;
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
        $event = $this->orderAddressRepository->update(
            [
                [
                    'id' => $analysisResult->getOrderAddressId(),
                    'street' => implode(' ', [$analysisResult->getStreet(), $analysisResult->getStreetNumber()]),
                    'firstName' => $analysisResult->getFirstName(),
                    'lastName' => $analysisResult->getLastName(),
                    'zipcode' => $analysisResult->getPostalCode(),
                    'city' => $analysisResult->getCity(),
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
        $orderStreet = trim($orderAddress->getStreet());

        return $orderAddress->getFirstName() !== $analysisResult->getFirstName()
            || $orderAddress->getLastName() !== $analysisResult->getLastName()
            || $orderAddress->getCity() !== $analysisResult->getCity()
            || $orderAddress->getZipcode() !== $analysisResult->getPostalCode()
            || $street !== $orderStreet;
    }
}
