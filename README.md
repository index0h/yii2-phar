yii-phar
========

[![Build Status](https://travis-ci.org/index0h/yii-phar.png?branch=master)](https://travis-ci.org/index0h/yii-phar) [![Dependency Status](https://gemnasium.com/index0h/yii-phar.png)](https://gemnasium.com/index0h/yii-phar) [![Coverage Status](https://coveralls.io/repos/index0h/yii-phar/badge.png?branch=master)](https://coveralls.io/r/index0h/yii-phar?branch=master) [![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/index0h/yii-phar/badges/quality-score.png?s=9459b0c09b0ba38b0943a9f14b666d3777c224bb)](https://scrutinizer-ci.com/g/index0h/yii-phar/)

This module provides console interface for building PHAR archives for Yii2 applications.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

```sh
php composer.phar require --prefer-dist index0h/yii-phar "0.0.2"
```

or add line to require section of `composer.json`

```json
"index0h/yii-phar": "*"
```

## Standalone usage

* Installation

```sh
php composer.phar global require index0h/yii-phar:0.0.2
```

* Running

```sh
yii-phar
# Or with external configuration
yii-phar phar/build myConfiguraion.php
```

## Usage

Once module is installed, modify your application configuration as follows:

```php
return [
    'modules' => [
        'phar' => 'index0h\\yii\\phar\\Module',
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
* **path** - Path to phar file save.
* **pharName** - Phar name.
* **signature** - One of [Phar signature algorithms](http://www.php.net/manual/en/phar.setsignaturealgorithm.php). If
    it is Phar::OPENSSL - **openSSLPrivateKeyAlias** is required.
* **openSSLPrivateKeyAlias** - Alias to OpenSSL certificate, should be on \Phar::OPENSSL signature set.
* **stub** - Alias to stub file, if false - will not be set.

## Components

Components - php classes for files modifications in phar archives. For example: remove all whitespaces from php code.
Components configuration is just like yii Application components, for example:

```php
return [
    'modules' => [
        'phar' => [
            'class' => 'index0h\\yii\\phar\\Module',
            'components' => [
                'fixer' => [
                    'class' => 'index0h\\yii\\phar\\components\\php\\Fixer',
                    'match' => '/.*\.php/'
                ]
            ]
        ]
        ...
    ],
    ...
];
```

### Available components

#### Fixer

Fixer changes realpath functions in files that doesn't work in phar.

* **match** - List of regexp for files that must be modified.
* **replace** - Array of regexp for [`from` => `to`] for modifications in files.

#### Minimize

Removes all whitespaces form php files by php_strip_whitespace.

* **match** - List of regexp for files that must be modified.

### Writing own component

Simply create class that extends index0h\yii\phar\base\Component and implement processFile method.

For example minimize component:

```php
namespace index0h\yii\phar\components\php;

use index0h\yii\phar\base\Component;
use index0h\yii\phar\base\FileEvent;

/**
 * Removes whitespace and comments from php files.
 */
class Minimize extends Component
{
    /**
     * For all php files without suffix Controller (because help command parses comments).
     */
    protected $match = ['/(?<!Controller)\.php/us'];

    /**
     * Modification of file.
     *
     * @param FileEvent $event Event with file information.
     */
    public function processFile(FileEvent $event)
    {
        file_put_contents($event->realPath, php_strip_whitespace($event->realPath));
    }
}
```

#### FileEvent structure

* realPath - path to temporary file.
* relativePath - path in phar file.

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
