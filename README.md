# elgentos Magento 2 Vurbis extension

This is an unofficial version of the [original Magento 2 Vurbis extension](https://gitlab.com/vurbis/vurbis-interactive-magento-2.3.x-punch-out-extension).

This version contains some bugfixes, some new features and a lot of refactoring.

It:
- Is based on the original 2.1.12 version
- Uses Guzzle instead of plain cURL
- Uses Magento's query builder instead of raw SQL
- Uses PHP 8 style constructor promotion
- Declares strict types in all PHP files
- Adds a Hyvä compatibility configuration option (default disabled)
- Adds an option to control sending all your module information to Vurbis with the newly introduced Status endpoint (default disabled)
- Adds an option to use the original customer account instead of the random generated customers Vurbis' middleware creates (default disabled)
- Adds a cron to clean up the random generated Vurbis customers
- Adds a test mode for testing on local environments
- Adheres to Magento's Coding Standards (phpcs and PhpStan level 4)

## Installation

```
composer require elgentos/magento2-vurbis-punchout
bin/magento setup:upgrade
```

## How to use the test mode?

Vurbis will supply you with a login URL in this format;

```
https://your-store.com/punchout/customer/login?username=johndoe%40example.com&password=abcdefghijklmnopqrstuvwxyz&HOOK_URL=https%3A%2F%2Fioedeveloper.com%2Fapi%2Fintegrations%2Fpunchoutparser%2Foci.php
```

If you will use this format on your local installation, the callback will be done to the production URL. This callback will create a customer which you would normally be logged into. However, since you are working on a different environment, that customer account isn't created in your instance. 

To be able to test, you can add `&test=your%40emailaddress.com` to the URL. If your Magento store is in Developer mode, it will then log you in into that given customer account.

## What is the option to use the original customer account?

In some instances, we need to use the original customer account to log into instead of the newly generated one. This has to do with certain customer attributes that are set on the original customer account which won't be available on the generated one.

The main downside of enabling this setting is that the cart will be shared across multiple OCI buyers. Only use this if there is only one OCI buyer! Otherwise, carts might be 'randomly' cleared.

## What does the Hyvä / headless compatibility setting do?

This will disable setting the quote's customer ID value to null to avoid a GraphQL validation error. Hyvä and headless setups use GraphQL to update the cart. Magento has added extra validation on the GraphQL to check whether the cart that is being updated actually belongs to the currently logged in user. Setting the Customer ID field on the quote to null makes this validation fail, so we disable it when you enable this setting.
