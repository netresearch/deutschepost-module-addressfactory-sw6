<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace PostDirekt\Addressfactory;

use Shopware\Core\Framework\Plugin;

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!defined('__NR_POSTDIREKT_ADDRESSFACTORY_MANAGED_BY_COMPOSER') && file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

class NRLEJPostDirektAddressfactory extends Plugin
{
}
