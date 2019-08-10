# JSON PDO

[![Build Status](https://travis-ci.org/alesanchezr/json-orm.svg?branch=master)](https://travis-ci.org/alesanchezr/json-orm)
[![Coverage Status](https://coveralls.io/repos/github/alesanchezr/json-orm/badge.svg?branch=master)](https://coveralls.io/github/alesanchezr/json-orm?branch=master)

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

## How use it?

```php
require 'vendor/autoload.php';

use JsonPDO\JsonPDO;

//create a database pointing to a file or folder
$orm = new JsonPDO('./tests/data/');

//get any file from the data folder
$content = $orm->getJsonByName('countries');

//save some data into a json file
$someData = [ "ve" => "venezuela" ];
$file = $orm->toNewFile('countries');
$file->save($content);

//check if a json file exists
$exists = $orm->jsonExists('countries');

//if there are several json files, you can list them all
$allFiles = $orm->getAllFiles();

//delete a json file
$orm->deleteFile('countries');

```

## Running Tests

Launch from command line :

```console
./vendor/bin/phpunit --bootstrap vendor/autoload.php tests/*
```

## License MIT

## Contact

Authors : Alejandro Sanchez
