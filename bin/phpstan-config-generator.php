<?php declare(strict_types=1);

use Composer\InstalledVersions;
use PostDirekt\Addressfactory\NRLEJPostDirektAddressfactory;
use Shopware\Core\DevOps\StaticAnalyze\StaticAnalyzeKernel;
use Shopware\Core\Framework\Plugin\KernelPluginLoader\StaticKernelPluginLoader;
use Symfony\Component\Dotenv\Dotenv;

$sw6Path = getenv('PROJECT_ROOT') . '/' ?: __DIR__ . '/../../../../';

$classLoader = require $sw6Path . 'vendor/autoload.php';
(new Dotenv(true))->load($sw6Path . '.env');

$shopwareVersion = InstalledVersions::getVersion('shopware/core');

$pluginRootPath = \dirname(__DIR__);
$composerJson = json_decode((string) file_get_contents($pluginRootPath . '/composer.json'), true);

$nrlejAddressfactory = [
    'autoload' => $composerJson['autoload'],
    'baseClass' => NRLEJPostDirektAddressfactory::class,
    'managedByComposer' => false,
    'name' => 'NRLEJPostDirektAddressfactory',
    'version' => $composerJson['version'],
    'active' => true,
    'path' => $pluginRootPath,
];
$pluginLoader = new StaticKernelPluginLoader($classLoader, null, [$nrlejAddressfactory]);

$kernel = new StaticAnalyzeKernel('dev', true, $pluginLoader, $shopwareVersion);
$kernel->boot();
$projectDir = $kernel->getProjectDir();
$cacheDir = $kernel->getCacheDir();

$relativeCacheDir = str_replace($projectDir, '', $cacheDir);

$phpStanConfigDist = file_get_contents($pluginRootPath . '/phpstan.neon.dist');
if ($phpStanConfigDist === false) {
    throw new RuntimeException('phpstan.neon.dist file not found');
}

// because the cache dir is hashed by Shopware, we need to set the PHPStan config dynamically
$phpStanConfig = str_replace(
    [
        "\n        # the placeholder \"%ShopwareHashedCacheDir%\" will be replaced on execution by bin/phpstan-config-generator.php script",
        '%ShopwareHashedCacheDir%',
    ],
    [
        '',
        $relativeCacheDir,
    ],
    $phpStanConfigDist
);

file_put_contents($pluginRootPath . '/phpstan.neon', $phpStanConfig);
