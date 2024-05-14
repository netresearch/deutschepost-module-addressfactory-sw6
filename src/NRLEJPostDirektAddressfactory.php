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
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

if (file_exists(__DIR__ . '/../vendor/scoper-autoload.php')) {
    require_once __DIR__ . '/../vendor/scoper-autoload.php';
}

class NRLEJPostDirektAddressfactory extends Plugin
{
    final public const CUSTOM_FIELD_STATUS_ON_ADDRESS = 'postdirektAddressfactory';
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

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $locator = new FileLocator('Resources/config');

        $resolver = new LoaderResolver([
            new YamlFileLoader($container, $locator),
            new GlobFileLoader($container, $locator),
            new DirectoryLoader($container, $locator),
        ]);

        $configLoader = new DelegatingLoader($resolver);

        $confDir = \rtrim($this->getPath(), '/') . '/Resources/config';

        $configLoader->load($confDir . '/{packages}/*.yaml', 'glob');
    }
}
