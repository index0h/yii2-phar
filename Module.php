<?php
/**
 * @link      https://github.com/index0h/yii-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii-phar/master/LICENSE
 */

namespace index0h\yii\phar;

use Yii;

/**
 * This is main class of Yii-Phar module.
 *
 * To use Yii-Phar, include as module to console application configuration like an example:
 *
 * ```php
 * return [
 * .....
 *     'modules' => [
 *         'phar' => ['class' => 'index0h\yii\phar\Module']
 *     ]
 * .....
 * ];
 * ```
 *
 * After installation you can run it from shell:
 *
 * ```sh
 * yii phar/build
 * ```
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
class Module extends \yii\base\Module
{
    /** @type int[] Array of compress algorithms, \Phar::GZ, \Phar::BZ2. */
    public $compress = [];

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'index0h\yii\phar\controllers';

    /** @type string[] List of files to compile. */
    public $files = ['@app/yii'];

    /** @type string[] List of directories to compile. */
    public $folders = ['@app'];

    /** @type string[] List of regexp patterns that must be ignored on build. */
    public $ignore = ['.*app.phar'];

    /** @type string OpenSSL certificate, should be on \Phar::OPENSSL signature set. */
    public $openSSLPrivateKeyAlias = '@app/data/certificate.pem';

    /** @type string Path to phar file save. */
    public $path = '@app/app.phar';

    /** @type string Phar name. */
    public $pharName = 'app';

    /** @type int|bool Signature of result phar file. */
    public $signature = \Phar::MD5;

    /** @type string|bool Path alias to stub file, if false - will not be set. */
    public $stub = false;
}