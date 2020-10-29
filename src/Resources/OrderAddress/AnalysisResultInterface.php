<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Resources\OrderAddress;

interface AnalysisResultInterface extends \JsonSerializable
{
    public function getOrderAddressId(): string;

    /**
     * @return string[]
     */
    public function getStatusCodes(): array;

    public function getFirstName(): string;

    public function getLastName(): string;

    public function getCity(): string;

    public function getPostalCode(): string;

    public function getStreet(): string;

    public function getStreetNumber(): string;
}
