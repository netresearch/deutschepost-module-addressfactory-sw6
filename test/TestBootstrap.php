<?php

declare(strict_types=1);

if (is_readable('/opt/share/shopware/tests/TestBootstrapper.php')) {
    // For Docker image: ghcr.io/friendsofshopware/platform-plugin-dev
    $testBootstrapper = require_once '/opt/share/shopware/tests/TestBootstrapper.php';
} else {
    $projectRoot = getenv('PROJECT_ROOT') ?: __DIR__ . '/../../../..';
    $testBootstrapper = require_once $projectRoot . '/vendor/shopware/core/TestBootstrapper.php';
}
require_once __DIR__ . '/../src/NRLEJPostDirektAddressfactory.php';

return $testBootstrapper
    ->setLoadEnvFile(true)
    ->setForceInstallPlugins(true)
    ->addActivePlugins('NRLEJPostDirektAddressfactory')
    ->bootstrap()
    ->getClassLoader();
