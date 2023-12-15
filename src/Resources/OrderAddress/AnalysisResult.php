<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Resources\OrderAddress;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class AnalysisResult extends Entity implements AnalysisResultInterface
{
    use EntityIdTrait;

    protected string $orderAddressId;

    protected string $statusCodes;

    protected string $firstName = '';

    protected string $lastName = '';

    protected string $city;

    protected string $postalCode;

    protected string $street;

    protected ?string $streetNumber = null;

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
