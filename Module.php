<?php
/**
 * @link      https://github.com/index0h/yii-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii-phar/master/LICENSE
 */

namespace index0h\yii\phar;

use Yii;
use yii\base\ActionEvent;

/**
 * This is main class of Yii-Phar module.
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
class Module extends \yii\base\Module
{
    /** @type After action event name. */
    const EVENT_AFTER_ACTION = 'afterAction';

    /** @type Before action event name. */
    const EVENT_BEFORE_ACTION = 'beforeAction';

    /** @type int[] Array of compress algorithms, \Phar::GZ, \Phar::BZ2. */
    public $compress = [];

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'index0h\yii\phar\controllers';

    /** @type string[] List of aliases to files to compile. */
    public $files = ['@app/yii'];

    /** @type string[] List of aliases to directories to compile. */
    public $folders = ['@app'];

    /** @type string[] List of regexp patterns that must be ignored on build. */
    public $ignore = ['.*app.phar'];

    /** @var bool|string[] Array of regexp patterns of files that files must be included after php_strip_whitespace. */
    public $minimizePHP = false;

    /** @type string Alias to OpenSSL certificate, should be on \Phar::OPENSSL signature set. */
    public $openSSLPrivateKeyAlias = '@app/data/cert.pem';

    /** @type string Path to phar file save. */
    public $path = '@app/app.phar';

    /** @type string Phar name. */
    public $pharName = 'app';

    /** @type int|bool Signature of result phar file. */
    public $signature = \Phar::MD5;

    /** @type string|bool Alias to stub file, if false - will not be set. */
    public $stub = false;

    /**
     * @param \yii\base\Action $action Build action.
     * @param mixed            $result Output of action.
     */
    public function afterAction($action, &$result)
    {
        $event = new ActionEvent($action);
        $event->result = $result;
        $this->trigger(self::EVENT_BEFORE_ACTION, $event);

        unset($action->controller->phar);
    }

    /**
     * @param \yii\base\Action $action Build action.
     *
     * @return bool
     */
    public function beforeAction($action)
    {
        $event = new ActionEvent($action);
        $this->trigger(self::EVENT_BEFORE_ACTION, $event);

        return true;
    }
}
