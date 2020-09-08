# NRLEJPostDirektAddressfactory - Shopware 6 Integration for Deutsche Post Direkt ADDRESSFACTORY

The ADDRESSFACTORY plugin for Shopware 6 allows you to automatically analyze and correct shipping addresses
in your shop system using the service of Deutsche Post Direkt.

## Requirements

* Shopware 6.1.0 or newer
* PHP >= 7.2
* Contract with Deutsche Post Direkt GmbH for using the ADDRESSFACTORY DIRECT API

## Installation

### With Composer (Recommended)

Run the following commands from your shop's root directory:

```shell script
composer require netresearch/postdirekt-addressfactory
(cd vendor/netresearch/postdirekt-addressfactory/src/Resources/app/storefront && npm install)
composer dump
bin/console plugin:refresh
bin/console plugin:install --activate --clearCache NRLEJPostDirektAddressfactory
bin/console bundle:dump
PROJECT_ROOT=/app/  npm --prefix vendor/shopware/platform/src/Storefront/Resources/app/storefront/ run production
bin/console assets:install
bin/console theme:compile
```

### With .zip or `git clone`

Either extract the `NRLEJPostDirektAddressfactory` directory from the `.zip` file into your `custom/plugins` directory
or run the following command from your shop's root directory:

```shell script
git clone git@github.com:netresearch/postdirekt-addressfactory-sw custom/plugins/NRLEJPostDirektAddressfactory
```

Then, install the composer dependencies and activate the plugin:

```shell script
composer require netresearch/postdirekt-addressfactory
composer dump
bin/console plugin:refresh
bin/console plugin:install --activate --clearCache NRLEJPostDirektAddressfactory
bin/console bundle:dump
PROJECT_ROOT=/app/  npm --prefix vendor/shopware/platform/src/Storefront/Resources/app/storefront/ run production
bin/console assets:install
bin/console theme:compile
```

## Configuration

* Navigate to `Administration > Settings > System > Plugins`
* Select **'…'** in the "Deutsche Post Direkt ADDRESSFACTORY" row and select **"Config"**
* Check **"Active"**
* Enter your credentials in the **"API User"** and **"API Password"** fields

## Testing

### Administration Unit Tests

Run the following commands from the repository root, replacing `[Shopware Project]`
with the absolute path to your Shopware installation:

```bash
cd src/Resources/app/administration/
npm install
ADMIN_PATH='[Shopware Project]vendor//platform/src/Administration/Resources/app/administration' npm test
```

## Author

[Netresearch DTT GmbH](https://www.netresearch.de)

## License

See LICENSE.md.
