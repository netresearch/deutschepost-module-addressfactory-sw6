<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Test;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use PostDirekt\Addressfactory\Resources\OrderAddress\AnalysisStatus;
use PostDirekt\Addressfactory\Resources\OrderAddress\AnalysisStatusInterface;
use PostDirekt\Addressfactory\Test\Fixture\Order;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\StateMachine\StateMachineRegistry;

class AnalysisStatusRepositoryTest extends TestCase
{
    use IntegrationTestBehaviour;

    private const TESTSTATUS = 'aTestStatus';

    /**
     * @var EntityRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var string
     */
    private $statusId;

    /**
     * @var EntityRepositoryInterface
     */
    private $subject;

    protected function setUp(): void
    {
        $this->context = Context::createDefaultContext();
        $this->orderRepository = $this->getContainer()->get('order.repository');
        /** @var EntityRepositoryInterface $subject */
        $subject = $this->getContainer()->get('postdirekt_addressfactory_analysis_status.repository');
        $this->subject = $subject;
        $this->statusId = Uuid::randomHex();

        $orderFixture = new Order(
            $this->context,
            $this->orderRepository,
            $this->getContainer()->get(StateMachineRegistry::class),
            $this->getContainer()
        );
        $orderFixture->create();

        parent::setUp();
    }

    public function testCreate(): void
    {
        $order = $this->orderRepository->search(new Criteria(), $this->context)->first();
        static::assertNotNull($order);

        $result = $this->subject->create(
            [
                [
                    'status' => self::TESTSTATUS,
                    'orderId' => $order->getId(),
                ],
            ],
            $this->context
        );

        static::assertEmpty($result->getErrors(), implode(', ', $result->getErrors()));
        $count = $this->getContainer()->get(Connection::class)->fetchAll(
            'SELECT * FROM `postdirekt_addressfactory_analysis_status` WHERE status = :status',
            ['status' => self::TESTSTATUS]
        );
        static::assertCount(1, $count);
    }

    public function testSearch(): void
    {
        $order = $this->orderRepository->search(new Criteria(), $this->context)->first();

        static::assertNotNull($order);

        $this->subject->create(
            [
                [
                    'status' => self::TESTSTATUS,
                    'orderId' => $order->getId(),
                ],
            ],
            $this->context
        );

        $result = $this->subject->search(
            (new Criteria())->addFilter(new EqualsFilter('status', self::TESTSTATUS)),
            $this->context
        );

        static::assertCount(1, $result->getElements());
        /** @var AnalysisStatusInterface $entity */
        $entity = $result->first();
        static::assertSame(AnalysisStatus::class, get_class($entity));
        static::assertSame(self::TESTSTATUS, $entity->getStatus());
        static::assertSame($order->getId(), $entity->getOrderId());
        static::assertNull($entity->getOrder());
    }

    public function testOrderAssociation(): void
    {
        /** @var OrderEntity|null $order */
        $order = $this->orderRepository->search(new Criteria(), $this->context)->first();

        static::assertNotNull($order);

        $this->subject->create(
            [
                [
                    'status' => self::TESTSTATUS,
                    'orderId' => $order->getId(),
                ],
            ],
            $this->context
        );

        $result = $this->subject->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('status', self::TESTSTATUS))
                ->addAssociation('order'),
            $this->context
        );

        static::assertCount(1, $result->getElements());
        /** @var AnalysisStatusInterface $entity */
        $entity = $result->first();
        $orderObject = $entity->getOrder();
        static::assertNotNull($orderObject);
        static::assertSame(OrderEntity::class, get_class($orderObject));
        static::assertSame($order->getId(), $orderObject->getId());
    }
}
