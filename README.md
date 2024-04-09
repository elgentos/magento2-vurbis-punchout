# elgentos Magento 2 Vurbis extension

This is an unofficial version of the [original Magento 2 Vurbis extension](https://gitlab.com/vurbis/vurbis-interactive-magento-2.3.x-punch-out-extension).

This version contains some bugfixes, some new features and a lot of refactoring.

It:
- Is based on the original 2.1.12 version
- Uses Guzzle instead of plain cURL
- Uses PHP 8 style constructor promotion
- Declares strict types in all PHP files
- Adds a Hyvä compatibility configuration option
- Adds an option to avoid sending all your module information to Vurbis with the newly introduced Status endpoint
- Adds an option to use the original customer account instead of the random generated customers Vurbis' middleware creates
- Adds a cron to clean up the random generated Vurbis customers
- Adds a test mode for testing on local environments
- Adheres to Magento's Coding Standards (phpcs and PhpStan level 4)

# Installation

```
composer require elgentos/magento2-vurbis-punchout
php bin/magento setup:upgrade
```
