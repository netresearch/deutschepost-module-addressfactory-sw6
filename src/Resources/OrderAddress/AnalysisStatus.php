<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Resources\OrderAddress;

use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class AnalysisStatus extends Entity implements AnalysisStatusInterface
{
    use EntityIdTrait;

    /**
     * @var string
     */
    protected $orderId;

    /**
     * @var string
     */
    protected $status;

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getOrder(): ?OrderEntity
    {
        try {
            return $this->get('order');
        } catch (\InvalidArgumentException $exception) {
            return null;
        }
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}
