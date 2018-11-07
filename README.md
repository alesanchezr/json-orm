# JSON PDO

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/alesanchezr/json-orm/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/emmanuelroecker/php-linkchecker/?branch=master)
[![Build Status](https://travis-ci.org/alesanchezr/json-orm.svg?branch=master)](https://travis-ci.org/emmanuelroecker/php-linkchecker)
[![Coverage Status](https://coveralls.io/repos/github/alesanchezr/json-orm/badge.svg?branch=master)](https://coveralls.io/github/emmanuelroecker/php-linkchecker?branch=master)

Very simple JSON file based database manager.

## Installation

This library can be found on [Packagist](https://packagist.org/packages/alesanchezr/json-orm).

The recommended way to install is through [composer](http://getcomposer.org).

Edit your `composer.json` and add :

```json
{
    "require": {
       "alesanchezr/json-orm": "dev-master"
    }
}
```

Install dependencies :

```bash
php composer.phar install
```

## How to check links in html / json files ?

```php
require 'vendor/autoload.php';

use JsonPDO\JsonPDO;

```

## Running Tests

Launch from command line :

```console
./vendor/bin/phpunit --bootstrap vendor/autoload.php tests/*
```

## License MIT

## Contact

Authors : Alejandro Sanchez