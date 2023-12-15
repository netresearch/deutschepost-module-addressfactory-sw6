<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Test\ScheduledTask;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PostDirekt\Addressfactory\Resources\OrderAddress\AnalysisResult;
use PostDirekt\Addressfactory\Resources\OrderAddress\AnalysisStatus;
use PostDirekt\Addressfactory\Resources\OrderAddress\AnalysisStatusDefinition;
use PostDirekt\Addressfactory\ScheduledTask\AutoProcess;
use PostDirekt\Addressfactory\ScheduledTask\AutoProcessHandler;
use PostDirekt\Addressfactory\Service\AnalysisStatusUpdater;
use PostDirekt\Addressfactory\Service\ModuleConfig;
use PostDirekt\Addressfactory\Service\OrderAnalysis;
use PostDirekt\Addressfactory\Service\OrderUpdater;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Uuid\Uuid;

class AutoProcessHandlerTest extends TestCase
{
    /**
     * @var ModuleConfig|MockObject
     */
    private $config;

    /**
     * @var OrderAnalysis|MockObject
     */
    private $orderAnalysis;

    /**
     * @var EntityRepository|MockObject
     */
    private $analysisStatusRepo;

    /**
     * @var OrderUpdater|MockObject
     */
    private $orderUpdater;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var OrderEntity|MockObject
     */
    private $order;

    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(ModuleConfig::class)->disableOriginalConstructor()->getMock();
        $this->orderAnalysis = $this->getMockBuilder(OrderAnalysis::class)->disableOriginalConstructor()->getMock();
        $this->order = $this->getMockBuilder(OrderEntity::class)->getMock();
        $this->order->method('getId')->willReturn(Uuid::randomHex());
        $analysisStatus = $this->getMockBuilder(AnalysisStatus::class)->getMock();
        $analysisStatus
            ->method('getStatus')
            ->willReturn(AnalysisStatusUpdater::PENDING);
        $analysisStatus
            ->method('getOrder')
            ->willReturn($this->order);
        $this->analysisStatusRepo = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor(
        )->getMock();
        $this->analysisStatusRepo
            ->method('search')
            ->willReturn(
                new EntitySearchResult(
                    AnalysisStatusDefinition::ENTITY_NAME,
                    1,
                    new EntityCollection([$analysisStatus]),
                    null,
                    new Criteria(),
                    $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock()
                )
            );
        $this->orderUpdater = $this->getMockBuilder(OrderUpdater::class)->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
    }

    public function testRunAndCancel(): void
    {
        $this->config->method('isCronAnalysis')->willReturn(true);
        $this->config->method('isAutoCancelNonDeliverableOrders')->willReturn(true);
        $this->orderAnalysis
            ->expects(static::once())
            ->method('analyse')
            ->with(['' => $this->order])
            ->willReturn([$this->order->getId() => new AnalysisResult()]);
        $this->orderUpdater
            ->expects(static::once())
            ->method('cancelIfUndeliverable');
        $this->logger->expects(static::never())->method('error');
        $subject = new AutoProcessHandler(
            $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor(
            )->getMock(),
            $this->config,
            $this->orderAnalysis,
            $this->analysisStatusRepo,
            $this->orderUpdater,
            $this->logger
        );

        $subject(new AutoProcess());
    }

    public function testRunAndUpdate(): void
    {
        $this->config->method('isCronAnalysis')->willReturn(true);
        $this->config->method('isAutoUpdateShippingAddress')->willReturn(true);
        $this->orderAnalysis
            ->expects(static::once())
            ->method('analyse')
            ->with(['' => $this->order])
            ->willReturn([$this->order->getId() => new AnalysisResult()]);
        $this->orderAnalysis
            ->expects(static::once())
            ->method('updateShippingAddress');
        $this->logger->expects(static::never())->method('error');
        $subject = new AutoProcessHandler(
            $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor(
            )->getMock(),
            $this->config,
            $this->orderAnalysis,
            $this->analysisStatusRepo,
            $this->orderUpdater,
            $this->logger
        );

        $subject(new AutoProcess());
    }

    public function testRunAndDoNothing(): void
    {
        $this->config->method('isCronAnalysis')->willReturn(false);
        $this->orderAnalysis
            ->expects(static::never())
            ->method('analyse');
        $this->orderAnalysis
            ->expects(static::never())
            ->method('updateShippingAddress');
        $this->logger->expects(static::never())->method('error');
        $subject = new AutoProcessHandler(
            $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor(
            )->getMock(),
            $this->config,
            $this->orderAnalysis,
            $this->analysisStatusRepo,
            $this->orderUpdater,
            $this->logger
        );

        $subject(new AutoProcess());
    }
}
