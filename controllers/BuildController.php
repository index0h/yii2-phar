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
use yii\helpers\FileHelper;

/**
 * Phar builder command.
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
class BuildController extends Controller
{
    /** @type \index0h\yii\phar\Module Link to main module object. */
    public $module;

    /** @type \Phar Phar object. */
    public $phar;

    /**
     * Action of building phar archive.
     */
    public function actionIndex()
    {
        echo "Start building PHAR package...\n";

        $this->clean(false);

        $this->phar = new \Phar(\Yii::getAlias($this->module->path), 0, $this->module->pharName);

        try {
            $this->addFiles();
            $this->addFolders();
            $this->addStub();
            $this->addCompress();
            $this->addSignature();
        } catch (\Exception $error) {
            unset($this->phar);
            throw $error;
        }

        unset($this->phar);
        $this->clean();

        echo "Finish\n";
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

        foreach (['', '.gz', '.bz2'] as $extension) {
            if (file_exists($this->module->path . $extension) === true) {
                \Phar::unlinkArchive($this->module->path . $extension);
            }
        }
    }

    /**
     * Create compressed copies of phar file.
     *
     * @throws \yii\base\InvalidConfigException On wrong compress type set (index0h\yii\phar\Module::compress).
     */
    protected function addCompress()
    {
        $configuration = $this->stringToArray($this->module->compress);
        foreach ($configuration as $compress) {
            if (in_array($compress, [\Phar::NONE, \Phar::GZ, \Phar::BZ2], true) === false) {
                throw new InvalidConfigException("Invalid configuration. Unknown compress type '{$compress}'.");
            }
            if (\Phar::canCompress($compress)) {
                $this->phar->compress($compress);
            }
        }
    }

    /**
     * Add files to phar file.
     *
     * @throws \yii\base\InvalidConfigException On wrong files list set (index0h\yii\phar\Module::files).
     */
    protected function addFiles()
    {
        $configuration = $this->stringToArray($this->module->files);
        if (count($configuration) > 0) {
            foreach ($configuration as $file) {
                $path = \Yii::getAlias($file);
                if (file_exists($path) === false) {
                    throw new InvalidConfigException("Invalid configuration. File '{$path}' does not exists.");
                }
                $relative = basename($path);
                if ($this->module->minimizePHP === false) {
                    $this->phar->addFile($path, $relative);
                } else {
                    $content = $this->minimizePHP($path);
                    $this->phar->addFromString($relative, $content);
                }
            }
        }
    }

    /**
     * Add folders to phar file.
     *
     * @throws \yii\base\InvalidConfigException On wrong folders list set (index0h\yii\phar\Module::folders).
     */
    protected function addFolders()
    {
        $configuration = $this->stringToArray($this->module->folders);
        if (count($configuration) > 0) {
            foreach ($configuration as $folder) {
                $path = \Yii::getAlias($folder);
                if (file_exists($path) === false) {
                    throw new InvalidConfigException("Invalid configuration. Folder '{$path}' does not exists.");
                }
                $this->phar->buildFromIterator(new Iterator($path, $this->module));
            }
        }
    }

    /**
     * Return minimized content of file though php_strip_whitespace.
     *
     * @return string
     */
    protected function minimizePHP($path)
    {
        $configuration = $this->stringToArray($this->module->minimizePHP);
        foreach ($configuration as $pattern) {
            if (preg_match("/{$pattern}/us", $path) > 0) {
                return php_strip_whitespace($path);
            }
        }
        return file_get_contents($path);
    }

    /**
     * Add signature to phar file.
     *
     * If signature type is \Phar::OPENSSL - there must be configured openSSLPrivateKeyAlias.
     *
     * @throws \yii\base\InvalidConfigException On wrong signature type set (index0h\yii\phar\Module::signature).
     */
    protected function addSignature()
    {
        $signature = $this->module->signature;
        if ($signature === false) {
            return;
        }
        if (in_array($signature, [\Phar::MD5, \Phar::SHA1, \Phar::SHA256, \Phar::SHA512], true) === true) {
            $this->phar->setSignatureAlgorithm($signature);
        } elseif ($signature === \Phar::OPENSSL) {
            $this->setOpenSSLSignature();
        } else {
            throw new InvalidConfigException("Invalid configuration. Unknown signature type '{$signature}'.");
        }
    }

    /**
     * Add stub from file to phar file.
     *
     * @throws \yii\base\InvalidConfigException On wrong stub file set (index0h\yii\phar\Module::stub).
     */
    protected function addStub()
    {
        if ($this->module->stub !== false) {
            $path = \Yii::getAlias($this->module->stub);
            if (file_exists($path) === false) {
                throw new InvalidConfigException("Invalid configuration. Stub file '{$path}' does not exists.");
            }
            $this->phar->setStub(file_get_contents($path));
        }
    }

    /**
     * Set OpenSSL signature and creating public key file.
     *
     * @throws \yii\base\InvalidConfigException
     *     On wrong OpenSSL certificate file set (index0h\yii\phar\Module::openSSLPrivateKeyAlias).
     */
    protected function setOpenSSLSignature()
    {
        $path = \Yii::getAlias($this->module->openSSLPrivateKeyAlias);
        if (file_exists($path) === false) {
            throw new InvalidConfigException("Invalid configuration. Private key '{$path}' does not exists.");
        }

        $pemFile = file_get_contents($path);

        $resource = openssl_get_privatekey($pemFile);
        $privateKey = '';
        openssl_pkey_export($resource, $privateKey);
        $this->phar->setSignatureAlgorithm(\Phar::OPENSSL, $privateKey);
    }

    /**
     * Converts string configurations to array.
     *
     * @return array
     */
    protected function stringToArray($config)
    {
        if (gettype($config) === 'string') {
            return [$config];
        }
        return $config;
    }
}
