<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

if (\file_exists(__DIR__ . '/../vendor/autoload.php')) {
    $autoloadPath = __DIR__ . '/../vendor/autoload.php';
}
if (\file_exists(__DIR__ . '/../vendor/scoper-autoload.php')) {
    $autoloadPath = __DIR__ . '/../vendor/scoper-autoload.php';
}

if (!\defined('__NR_POSTDIREKT_ADDRESSFACTORY_MANAGED_BY_COMPOSER') && isset($autoloadPath)) {
    require_once $autoloadPath;
}

class NRLEJPostDirektAddressfactory extends Plugin
{
    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            parent::uninstall($uninstallContext);

            return;
        }

        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        $connection->executeUpdate('DROP TABLE IF EXISTS `postdirekt_addressfactory_analysis_status`;');
        $connection->executeUpdate('DROP TABLE IF EXISTS `postdirekt_addressfactory_analysis_result`;');
    }
}
