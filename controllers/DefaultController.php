<?php
/**
 * @link      https://github.com/index0h/yii-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii-phar/master/LICENSE
 */

namespace index0h\yii\phar\controllers;

use index0h\yii\phar\base\Builder;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\helpers\FileHelper;

/**
 * Phar builder controller.
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
class DefaultController extends Controller
{
    /** @type \index0h\yii\phar\Module Link to main module object. */
    public $module;

    /** @type \Phar Phar object. */
    public $phar;

    /**
     * Action of building phar archive.
     *
     * @param string|bool $configFile Path to external file configuration.
     *
     * @throws \Exception Probably wrong configuration or no rights.
     */
    public function actionIndex($configFile = false)
    {

        echo "Start building PHAR package...\n";

        $this->loadConfiguration($configFile);

        $this->clean(false);

        $phar = new \Phar(\Yii::getAlias($this->module->path), 0, $this->module->pharName);

        Builder::addFilesFromIterator($phar, $this->module);
        Builder::addStub($phar, $this->module->stub);
        Builder::addCompress($phar, $this->module->compress);
        Builder::addSignature($phar, $this->module->signature, $this->module->openSSLPrivateKeyAlias);

        echo "\n\nFinish\n";
    }

    /**
     * Clean yii-phar runtime directory.
     *
     * @param bool $runtimeOnly On false - remove phar archive.
     */
    protected function clean($runtimeOnly = true)
    {
        $runtime = \Yii::getAlias('@runtime/yii-phar');
        if (file_exists($runtime) === true) {
            FileHelper::removeDirectory($runtime);
        }
        mkdir($runtime, 0777);

        if ($runtimeOnly === true) {
            return;
        }

        $path = \Yii::getAlias($this->module->path);

        foreach (['', '.gz', '.bz2'] as $extension) {
            $fullPath = $path . DIRECTORY_SEPARATOR . $extension;
            if (file_exists($fullPath) === true) {
                \Phar::unlinkArchive($fullPath);
            }
        }
    }

    /**
     * Loads and update module default configuration.
     *
     * @param string|bool $configFile Path to external file configuration.
     *
     * @throws \yii\base\InvalidConfigException On unkniwn option in configuration file.
     */
    protected function loadConfiguration($configFile = false)
    {
        if ($configFile === false) {
            return;
        }
        echo '\nLoad configuration';

        $configuration = require $configFile;

        foreach ($configuration as $name => $value) {
            if ((property_exists($this->module, $name) === true) || ($this->module->canSetProperty($name) == true)) {
                $this->$name = $value;
            } else {
                throw new InvalidConfigException("Invalid configuration. Unknown configuration option '{$name}'");
            }
        }
    }
}
