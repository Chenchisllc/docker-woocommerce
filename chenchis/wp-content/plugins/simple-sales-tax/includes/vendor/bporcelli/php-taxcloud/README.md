[![Latest Stable Version](https://poser.pugx.org/bporcelli/php-taxcloud/v/stable.png)](https://packagist.org/packages/bporcelli/php-taxcloud)
[![Total Downloads](https://poser.pugx.org/bporcelli/php-taxcloud/downloads.png)](https://packagist.org/packages/bporcelli/php-taxcloud)

This library is based on the PHP TaxCloud library by [VMDoh](https://github.com/VMdoh/php-taxcloud). It introduces
support for tax exemptions and brings the original library up-to-date with the most recent version of the [TaxCloud API](https://dev.taxcloud.com/docs/versions/1.0/).

At this point, all of the functionality needed to complete an order has been
implemented. The only API endpoint left to implement and test is [AddTransactions](https://dev.taxcloud.com/docs/versions/1.0/resources/taxcloud-us-advanced-apis/endpoints/list-taxcloud-us-advanced-apis-35962eeb-4ada-43bb-87a4-cfa5cdf0be08#endpoint_443977448363656300).

A smoketest is provided that connects to the TaxCloud API using credentials
stored in environment variables. It is intended for quick tests to ensure that
the core of the library works, but it is not a thorough test. **DO NOT RUN THE
SMOKETEST WITH CREDENTIALS FOR A LIVE SITE. IT WILL CREATE TRANSACTIONS.**

The smoketest also provides an excellent set of examples on how to use this
library.

About
----------------
PHP library to facilitate the ability of your PHP web application to
communicate with TaxCloud.

Compatibility
----------------
php-taxcloud is tested with PHP 5.5 and later.

Contributions
----------------
If you'd like to help with php-taxcloud, your efforts are appreciated!

However, your code should at least somewhat closely follow PSR-2 guidelines, and
API changes should be accompanied by tests.

Speaking of tests, we're lacking in that department. If you could help to increase
test coverage that would be great.

Getting Started
----------------
This library requires that you have API credentials for [TaxCloud](https://taxcloud.net).

To obtain TaxCloud API keys, you will need to first sign up for an account
with TaxCloud, [verify your website](https://taxcloud.net/account/websites/), and then obtain your **API ID** and **API KEY**
for your specific website.

Examples
----------------
The smoketest is a great resource for a working example that goes through the
entire process in a basic and straightforward manner. The unit tests are a much
better resource if you need to see how specific functionality works. The unit
tests use stubs to mock the API, and these stubs can show you what sort of data
to expect.

Testing
----------------
php-taxcloud includes thorough unit tests that do not require a live connection
to the API. If you are contributing to php-taxcloud, please include unit tests
for your contributions.

[Travis-ci](https://travis-ci.org/bporcelli/php-taxcloud) runs unit tests for the repository. However, you can run them locally
with [PHPUnit](http://phpunit.de/manual/current/en/index.html).

A smoketest is also included that connects to the API and is intended only for
a very quick check that basic functionality has not been broken. To use the
smoketest, you will need to set the following environment variables:
* TaxCloud_apiLoginID
* TaxCloud_apiKey

**DO NOT RUN THE SMOKETEST WITH CREDENTIALS FOR A LIVE SITE. IT WILL CREATE
TRANSACTIONS**
