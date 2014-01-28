yii-phar
========

[![Build Status](https://travis-ci.org/index0h/yii-phar.png?branch=master)](https://travis-ci.org/index0h/yii-phar) [![Dependency Status](https://gemnasium.com/index0h/yii-phar.png)](https://gemnasium.com/index0h/yii-phar) [![Coverage Status](https://coveralls.io/repos/index0h/yii-phar/badge.png?branch=master)](https://coveralls.io/r/index0h/yii-phar?branch=master)

This module provides console interface for building PHAR archives for Yii2 applications.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

```sh
php composer.phar require --prefer-dist index0h/yii-phar "*"
```

or add line to require section of `composer.json`

```json
"index0h/yii-phar": "*"
```

## Usage

Once module is installed, modify your application configuration as follows:

```php
return [
    'modules' => [
        'phar' => 'index0h/yii/phar/Module',
        ...
    ],
    ...
];
```

You can access to yii-phar module though console:

```sh
yii phar/build
```

## Options

* **compress** - Array of compress algorithms, \Phar::GZ, \Phar::BZ2. Creates compressed files of main phar.
* **files** - List of files to compile.
* **folders** - List of directories to compile.
* **ignore** - List of regexp patterns that must be ignored on build. That means if any file will match to any of
    patterns - it will be ignored.
* **minimizePHP** - Array of regexp patterns of files that files must be included after php_strip_whitespace.
* **path** - Path to phar file save.
* **pharName** - Phar name.
* **signature** - One of [Phar signature algorytms](http://www.php.net/manual/en/phar.setsignaturealgorithm.php). If
    it is Phar::OPENSSL - **openSSLPrivateKeyAlias** is required.
* **openSSLPrivateKeyAlias** - Alias to OpenSSL certificate, should be on \Phar::OPENSSL signature set.
* **stub** - Alias to stub file, if false - will not be set.

## Testing

#### Run tests from IDE (example for PhpStorm)

- Select Run/Debug Configuration -> Edit Configurations
- Select Add New Configuration -> PHP Script
- Type:
    * File: /path/to/yii-phar/runTests.php
    * Arguments run: run  --coverage --html
- OK

#### Run tests from console

```sh
make test
```