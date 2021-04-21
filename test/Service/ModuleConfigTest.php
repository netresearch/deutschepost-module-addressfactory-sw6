<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Test\Service;

use PHPUnit\Framework\TestCase;
use PostDirekt\Addressfactory\Service\ModuleConfig;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ModuleConfigTest extends TestCase
{
    /**
     * @var ModuleConfig
     */
    private $subject;

    protected function setUp(): void
    {
        $systemConfigMock = $this->getMockBuilder(SystemConfigService::class)
                                 ->disableOriginalConstructor()
                                 ->onlyMethods(['get'])
                                 ->getMock();
        $systemConfigMock->method('get')
                         ->willReturnArgument(0);
        /* @var SystemConfigService $systemConfigMock */
        $this->subject = new ModuleConfig($systemConfigMock);
        parent::setUp();
    }

    public function testGetApiUser(): void
    {
        static::assertEquals('NRLEJPostDirektAddressfactory.config.apiUser', $this->subject->getApiUser());
    }

    public function testGetApiPassword(): void
    {
        static::assertEquals('NRLEJPostDirektAddressfactory.config.apiPassword', $this->subject->getApiPassword());
    }
}
