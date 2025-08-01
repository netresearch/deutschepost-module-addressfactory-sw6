# NRLEJPostDirektAddressfactory - Shopware 6 Integration for Deutsche Post Direkt ADDRESSFACTORY

The ADDRESSFACTORY plugin for Shopware 6 allows you to automatically analyze and correct shipping addresses
in your shop system using the service of Deutsche Post Direkt.

## Requirements

* Shopware 6.7.0 or newer
* PHP >= 8.2
* Contract with Deutsche Post Direkt GmbH for using the ADDRESSFACTORY DIRECT API

## Installation

### From [Community Store](https://store.shopware.com/)

You have bought the plugin in the Shopware community store. You manage your plugins from within your shop.

Please follow the
corresponding [documentation](https://docs.shopware.com/en/shopware-6-en/extensions/myextensions#installing-extensions).

### From [Community Store via Composer](https://store.shopware.com/) (recommended)

You have bought the plugin in the Shopware community store and want to manage your shop's plugins and dependencies with
composer.

Follow the [Shopware Instruction Video](https://www.youtube.com/watch?v=OcaTiOhum2k) to set up your shop for using
composer.

```shell script
composer require store.shopware.com/nrlejpostdirektaddressfactory
bin/console plugin:refresh
bin/console plugin:install --activate --clearCache NRLEJPostDirektAddressfactory

```

### From [Packagist](https://packagist.org/)

You are fine with having to install dependencies of the plugin yourself.

Run the following commands from your shop's root directory:

```shell script
composer require netresearch/postdirekt-addressfactory
composer dump
bin/console plugin:refresh
bin/console plugin:install --activate --clearCache NRLEJPostDirektAddressfactory
bin/console bundle:dump
npm --prefix vendor/shopware/administration/Resources/app/administration/ run build
bin/console assets:install
```

### From [Github](https://github.com/netresearch/deutschepost-module-addressfactory-sw6)

You are fine with having to install dependencies of the plugin yourself. You want to extend or adapt the extension to your own needs.

Run the following command from your shop's root directory:

```shell script
git clone git@github.com:netresearch/deutschepost-module-addressfactory-sw6 custom/plugins/NRLEJPostDirektAddressfactory

```

Then, continue as described in [From Packagist](#from-packagist).

## Configuration

* Navigate to `Administration > Settings > System > Plugins`
* Select **'…'** in the "Deutsche Post Direkt ADDRESSFACTORY" row and select **"Config"**
* Check **"Active"**
* Enter your credentials in the **"API User"** and **"API Password"** fields

## Testing

### Administration Unit Tests

Run the following commands from the repository root, replacing `[Shopware Project]`
with the absolute path to your Shopware installation and `[Plugin Directory]` with the path where the plugin was installed to (depends on installation method):

```bash
cd [Plugin Directory]/src/Resources/app/administration/
npm install
ADMIN_PATH='[Shopware Project]vendor/shopware/administration/Resources/app/administration' npm test
```

## Author

[Netresearch DTT GmbH](https://www.netresearch.de)

## License

See LICENSE.md.
