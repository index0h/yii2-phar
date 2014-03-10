yii2-phar
========

[![Build Status](https://travis-ci.org/index0h/yii2-phar.png?branch=master)](https://travis-ci.org/index0h/yii2-phar) [![Latest Stable Version](https://poser.pugx.org/index0h/yii2-phar/v/stable.png)](https://packagist.org/packages/index0h/yii2-phar) [![Dependency Status](https://gemnasium.com/index0h/yii2-phar.png)](https://gemnasium.com/index0h/yii2-phar) [![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/index0h/yii2-phar/badges/quality-score.png?s=646499f8cfca4630130df1b879f36d4be735cb71)](https://scrutinizer-ci.com/g/index0h/yii2-phar/) [![Code Coverage](https://scrutinizer-ci.com/g/index0h/yii2-phar/badges/coverage.png?s=53d2290e629cdc0a7f65e67e8c979cec9f94cfa2)](https://scrutinizer-ci.com/g/index0h/yii2-phar/) [![Total Downloads](https://poser.pugx.org/index0h/yii2-phar/downloads.png)](https://packagist.org/packages/index0h/yii2-phar) [![License](https://poser.pugx.org/index0h/yii2-phar/license.png)](https://packagist.org/packages/index0h/yii2-phar)

Модуль, создающий консольный интерфейс для сборки PHAR архивов в приложениях Yii2.

## Установка

Рекомендуемый способ установки через [composer](http://getcomposer.org/download/).

```sh
php composer.phar require --prefer-dist index0h/yii2-phar "*"
```

или добавьте строку в require секцию файла `composer.json`

```json
"index0h/yii2-phar": "*"
```

## Автономное использование

* Установка

```sh
php composer.phar global require index0h/yii2-phar:*
```

* Выполнение

```sh
yii2-phar
# Or with external configuration
yii2-phar phar/build myConfiguration.php
```

## Использование

Установив модуль, его необходимо настроить в конфигурации приложения, пример минимальной настройки ниже:

```php
return [
    'modules' => [
        'phar' => 'index0h\\phar\\Module',
        ...
    ],
    ...
];
```

Теперь вы можете использовать yii2-phar через консоль:

```sh
yii phar/build
```

## Опции

* **compress** - Список алгоритмов сжатия, \Phar::GZ, \Phar::BZ2. Создаст сжатые копии основного phar архива.
* **files** - Список псевдонимов файлов, для включения в архив.
* **folders** - Список псевдонимов директорий, для включения в архив.
* **ignore** - Список регулярных выражений, определяющих файлы, которые не должны попасть в сборку. Это значит, если
    файл соответствует хотя бы одному шаблону - он будет проигнорирован.
* **path** - Путь, по которому будет создан phar.
* **pharName** - Название архива.
* **signature** - Один из [алгогритмов подписей Phar](http://www.php.net/manual/en/phar.setsignaturealgorithm.php). В
    случае Phar::OPENSSL - опция **openSSLPrivateKeyAlias** обязательна.
* **openSSLPrivateKeyAlias** - Псевдоним пути до сертификата OpenSSL, используется в случае \Phar::OPENSSL подписи.
* **stub** - Псевдоним пути к stub файлу, если false - добавлен не будет.

## Компоненты

Компоненты - это php классы, предназначенные для изменения данных файлов перед вставкой в phar архив. Например:
удаление всех комментариев и не функциональных пробельных символов. Настройка компонентов yii2-phar такая же, как и
настройка компонентов yii2 приложения, например:


```php
return [
    'modules' => [
        'phar' => [
            'class' => 'index0h\\phar\\Module',
            'components' => [
                'fixer' => [
                    'class' => 'index0h\\phar\\components\\php\\Fixer',
                    'match' => '/.*\.php/'
                ]
            ]
        ]
        ...
    ],
    ...
];
```

### Доступные компоненты

#### Fixer

Fixer исправляет функции realpath и тому подобные, которые не работают внутри phar архива.

* **match** - Список шаблонов регулярных выражений, для файлов, в которых будут проводиться изменения.
* **replace** - Список регулярных выражений [`from` => `to`] по которым будут выполняться замены.

#### Minimize

Удаляет все пробельные символы и комментарии с помощью функции php_strip_whitespace.

* **match** - Список шаблонов регулярных выражений, для файлов, в которых будут проводиться изменения.

### Написание собственных компонентов.

Создайте дочерний класс для index0h\phar\base\Component и имплементируйте метод processFile.

Например компонент minimize:

```php
namespace index0h\phar\components\php;

use index0h\phar\base\Component;
use index0h\phar\base\FileEvent;

/**
 * Удаляет все пробельные символы и комментарии.
 */
class Minimize extends Component
{
    /**
     * Используется для всех php файлов, кроме контролеров, так как Yii берет справочную информацию из них.
     */
    protected $match = ['/(?<!Controller)\.php/us'];

    /**
     * Изменение файла.
     *
     * @param FileEvent $event Событие, содержащее данные о файле.
     */
    public function processFile(FileEvent $event)
    {
        file_put_contents($event->realPath, php_strip_whitespace($event->realPath));
    }
}
```

#### Структура FileEvent

* realPath - путь к временному файлу.
* relativePath - путь в phar архиве.

## Тесты

#### Выполнение тестов в IDE (пример для PhpStorm)

- Выберите Run/Debug Configuration -> Edit Configurations
- Выберите Add New Configuration -> PHP Script
- Введите:
    * File: /path/to/yii2-phar/.test.php
    * Arguments run: run --coverage --html
- OK

#### Выполнение тестов в IDE (example for PhpStorm) `внутри phar архива`

- Выберите Run/Debug Configuration -> Edit Configurations
- Выберите Add New Configuration -> PHP Script
- Введите:
    * File: /path/to/yii2-phar/.test.phar.php
    * Arguments run: run --no-exit
- OK

#### Выполнение тестов в консоли

```sh
make test-all
```
