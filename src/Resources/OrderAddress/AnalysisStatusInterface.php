<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Resources\OrderAddress;

use Shopware\Core\Checkout\Order\OrderEntity;

interface AnalysisStatusInterface extends \JsonSerializable
{
    public function getId(): string;

    public function getOrderId(): string;

    public function getStatus(): string;

    public function getOrder(): ?OrderEntity;
}
