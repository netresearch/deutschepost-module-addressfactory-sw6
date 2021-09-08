<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

if (file_exists(__DIR__ . '/../vendor/scoper-autoload.php')) {
    require_once __DIR__ . '/../vendor/scoper-autoload.php';
}

class NRLEJPostDirektAddressfactory extends Plugin
{
    /**
     * @throws Exception
     */
    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            parent::uninstall($uninstallContext);

            return;
        }

        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        $connection->executeStatement('DROP TABLE IF EXISTS `postdirekt_addressfactory_analysis_status`;');
        $connection->executeStatement('DROP TABLE IF EXISTS `postdirekt_addressfactory_analysis_result`;');
    }

    public function executeComposerCommands(): bool
    {
        return true;
    }
}
