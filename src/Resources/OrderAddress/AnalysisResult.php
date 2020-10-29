<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Resources\OrderAddress;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class AnalysisResult extends Entity implements AnalysisResultInterface
{
    /**
     * @var string
     */
    protected $orderAddressId;

    /**
     * @var string
     */
    protected $statusCodes;

    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var string
     */
    protected $city;

    /**
     * @var string
     */
    protected $postalCode;

    /**
     * @var string
     */
    protected $street;

    /**
     * @var string|null
     */
    protected $streetNumber;

    public function getOrderAddressId(): string
    {
        return $this->orderAddressId;
    }

    public function setOrderAddressId(string $orderAddressId): void
    {
        $this->orderAddressId = $orderAddressId;
    }

    /**
     * @param string[] $statusCodes
     */
    public function setStatusCodes(array $statusCodes): void
    {
        $this->statusCodes = implode(',', $statusCodes);
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function setPostalCode(string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    public function setStreetNumber(string $streetNumber): void
    {
        $this->streetNumber = $streetNumber;
    }

    /**
     * @return string[]
     */
    public function getStatusCodes(): array
    {
        return explode(',', $this->statusCodes);
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getStreetNumber(): string
    {
        return $this->streetNumber ?? '';
    }
}
