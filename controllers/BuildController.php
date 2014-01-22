<?php
/**
 * @link      https://github.com/index0h/yii-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii-phar/master/LICENSE
 */

namespace index0h\yii\phar\controllers;

use index0h\yii\phar\components\Iterator;
use yii\base\InvalidConfigException;
use yii\console\Controller;

/**
 * Phar builder command.
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
class BuildController extends Controller
{
    /** @type \index0h\yii\phar\Module Link to main module object. */
    public $module;

    /**
     * Action of building phar archive.
     */
    public function actionIndex()
    {
        echo "Start building PHAR package...\n";

        if (is_file($this->module->path)) {
            unlink($this->module->path);
        }

        $phar = new \Phar($this->module->path, 0, $this->module->pharName);

        $this->addFiles($phar);
        $this->addFolders($phar);
        $this->addStub($phar);
        $this->addCompress($phar);
        $this->addSignature($phar);

        echo "Finish\n";
    }

    /**
     * Create compressed copies of phar file.
     *
     * @param \Phar $phar Phar object.
     *
     * @throws \yii\base\InvalidConfigException On wrong compress type set (index0h\yii\phar\Module::compress).
     */
    protected function addCompress($phar)
    {
        foreach ($this->module->compress as $compress) {
            if (\Phar::canCompress($compress)) {
                if (in_array($compress, [\Phar::NONE, \Phar::GZ, \Phar::BZ2]) === false) {
                    throw new InvalidConfigException("Invalid configuration. Unknown compress type '{$compress}'.");
                }
                $phar->compress($compress);
            }
        }
    }

    /**
     * Add files to phar file.
     *
     * @param \Phar $phar Phar object.
     *
     * @throws \yii\base\InvalidConfigException On wrong files list set (index0h\yii\phar\Module::files).
     */
    protected function addFiles($phar)
    {
        if (count($this->module->files) > 0) {
            foreach ($this->module->files as $file) {
                $path = \Yii::getAlias($file);
                if (file_exists($path) === false) {
                    throw new InvalidConfigException("Invalid configuration. File '{$path}' does not exists.");
                }
                $phar->addFile($path, basename($path));
            }
        }
    }

    /**
     * Add folders to phar file.
     *
     * @param \Phar $phar Phar object.
     *
     * @throws \yii\base\InvalidConfigException On wrong folders list set (index0h\yii\phar\Module::folders).
     */
    protected function addFolders($phar)
    {
        if (count($this->module->folders) > 0) {
            foreach ($this->module->folders as $folder) {
                $path = \Yii::getAlias($folder);
                if (file_exists($path) === false) {
                    throw new InvalidConfigException("Invalid configuration. Folder '{$path}' does not exists.");
                }
                $phar->buildFromIterator(new Iterator($path, $this->module));
            }
        }
    }

    /**
     * Add signature to phar file.
     *
     * If signature type is \Phar::OPENSSL - there must be configured openSSLPrivateKeyAlias.
     *
     * @param \Phar $phar Phar object.
     *
     * @throws \yii\base\InvalidConfigException On wrong signature type set (index0h\yii\phar\Module::signature).
     *     On wrong OpenSSL certificate file set (index0h\yii\phar\Module::openSSLPrivateKeyAlias).
     */
    protected function addSignature($phar)
    {
        $signature = $this->module->signature;
        if ($signature === false) {
            return;
        }
        if (in_array($signature, array(\Phar::MD5, \Phar::SHA1, \Phar::SHA256, \Phar::SHA512))) {
            $phar->setSignatureAlgorithm($signature);
        } elseif ($signature === \Phar::OPENSSL) {
            $path = \Yii::getAlias($this->module->openSSLPrivateKeyAlias);
            if (file_exists($path) === false) {
                throw new InvalidConfigException("Invalid configuration. Private key '{$path}' does not exists.");
            }
            $OpenSSLPrivateKey = openssl_get_privatekey(file_get_contents($path));
            $OpenSSLPKey = '';
            openssl_pkey_export($OpenSSLPrivateKey, $OpenSSLPKey);
            $phar->setSignatureAlgorithm(\Phar::OPENSSL, $OpenSSLPKey);
        } else {
            throw new InvalidConfigException("Invalid configuration. Unknown signature type '{$signature}'.");
        }
    }

    /**
     * Add stub from file to phar file.
     *
     * @param \Phar $phar Phar object.
     *
     * @throws \yii\base\InvalidConfigException On wrong stub file set (index0h\yii\phar\Module::stub).
     */
    protected function addStub($phar)
    {
        if ($this->module->stub !== false) {
            $path = \Yii::getAlias($this->module->stub);
            if (file_exists($path) === false) {
                throw new InvalidConfigException("Invalid configuration. Stub file '{$path}' does not exists.");
            }
            $phar->setStub($path);
        }
    }
}