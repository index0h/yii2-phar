<?php
/**
 * @link      https://github.com/index0h/yii2-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii2-phar/master/LICENSE
 */

namespace index0h\phar;

use Yii;
use yii\base\ActionEvent;

/**
 * This is main class of yii2-phar module.
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
class Module extends \yii\base\Module
{
    /** @type string After action event name. */
    const EVENT_AFTER_ACTION = 'afterAction';

    /** @type string Before action event name. */
    const EVENT_BEFORE_ACTION = 'beforeAction';


    const EVENT_PROCESS_FILE = 'processFile';

    /** @type int[] Array of compress algorithms, \Phar::GZ, \Phar::BZ2. */
    public $compress = [];

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'index0h\\phar\\controllers';

    /** @type string[] List of aliases to files to compile. */
    public $files = [];

    /** @type string[] List of aliases to directories to compile. */
    public $folders = ['@app'];

    /** @type bool|string[] List of regexp patterns that must be ignored on build. */
    public $ignore = ['/\.(git|gitignore|svn|hg|hgignore)/us', '/\.(travis|coveralls)\.yml/us'];

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
        $event = new ActionEvent($action, ['result' => $result]);
        $this->trigger(self::EVENT_AFTER_ACTION, $event);
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

    public function init()
    {
        parent::init();

        $components = $this->getComponents();

        foreach ($components as $component) {
            $component['module'] = $this;
            \Yii::createObject($component);
        }
    }
}
