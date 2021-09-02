<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory\Service;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class ModuleConfig
{
    private const PREFIX = 'NRLEJPostDirektAddressfactory.config.';

    private SystemConfigService $systemConfig;

    public function __construct(SystemConfigService $systemConfig)
    {
        $this->systemConfig = $systemConfig;
    }

    /**
     * Check wether logging is enabled
     */
    public function isLoggingEnabled(?string $salesChannelId = null): bool
    {
        return $this->systemConfig->getBool(self::PREFIX . 'logging', $salesChannelId);
    }

    /**
     * Fetch API user
     */
    public function getApiUser(?string $salesChannelId = null): string
    {
        return $this->systemConfig->getString(self::PREFIX . 'apiUser', $salesChannelId);
    }

    /**
     * Fetch API password
     */
    public function getApiPassword(?string $salesChannelId = null): string
    {
        return $this->systemConfig->getString(self::PREFIX . 'apiPassword', $salesChannelId);
    }

    public function getConfigurationName(?string $salesChannelId = null): string
    {
        return $this->systemConfig->getString(self::PREFIX . 'configurationName', $salesChannelId);
    }

    public function getClientId(?string $salesChannelId = null): string
    {
        return $this->systemConfig->getString(self::PREFIX . 'clientId', $salesChannelId);
    }

    public function isCronAnalysis(?string $salesChannelId = null): bool
    {
        return $this->systemConfig->get(self::PREFIX . 'automaticAnalysis', $salesChannelId) === 'cron';
    }

    public function isSynchronousAnalysis(?string $salesChannelId = null): bool
    {
        return $this->systemConfig->get(self::PREFIX . 'automaticAnalysis', $salesChannelId) === 'synchrounous';
    }

    public function isAutoCancelNonDeliverableOrders(?string $salesChannelId = null): bool
    {
        return $this->systemConfig->getBool(self::PREFIX . 'autoCancelOrder', $salesChannelId);
    }

    public function isAutoUpdateShippingAddress(?string $salesChannelId = null): bool
    {
        return $this->systemConfig->getBool(self::PREFIX . 'autoUpdateOrder', $salesChannelId);
    }
}
