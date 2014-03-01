<?php
/**
 * @link      https://github.com/index0h/yii2-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii2-phar/master/LICENSE
 */

namespace index0h\phar\base;

use index0h\phar\iterators\Iterator;
use yii\base\InvalidConfigException;

/**
 * Main class for operating with Phar objects.
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
class Builder
{
    /**
     * Add compress to phar file.
     *
     * @param \Phar $phar          PHAR object to update.
     * @param int[] $configuration List of compress types.
     *
     * @throws \yii\base\InvalidConfigException On wrong compress type set (index0h\phar\Module::compress).
     */
    public static function addCompress(\Phar $phar, $configuration)
    {
        if ($configuration === false) {
            return;
        }
        echo "\nAdd compress";
        foreach ($configuration as $compress) {
            if (in_array($compress, [\Phar::NONE, \Phar::GZ, \Phar::BZ2], true) === false) {
                throw new InvalidConfigException("Invalid configuration. Unknown compress type '{$compress}'.");
            }
            if (\Phar::canCompress($compress) === true) {
                $phar->compress($compress);
            }
        }
    }

    /**
     * Add Files form folders.
     *
     * @param \Phar                    $phar   PHAR object to update.
     * @param \index0h\phar\Module $module Link to phar-module for getting configs and fire events.
     */
    public static function addFilesFromIterator(\Phar $phar, $module)
    {
        echo "\nAdd files";
        $phar->buildFromIterator(new Iterator($module));
    }

    /**
     * Add signature to phar file.
     *
     * @param \Phar       $phar            PHAR object to update.
     * @param integer     $signature       Signature type.
     * @param null|string $privateKeyAlias Path alias to private key, on signature type is \Phar::OPENSSL.
     *
     * @throws \yii\base\InvalidConfigException On wrong signature type set (index0h\phar\Module::signature).
     */
    public static function addSignature(\Phar $phar, $signature, $privateKeyAlias = null)
    {
        if ($signature === false) {
            return;
        }
        echo "\nAdd signature";
        if (in_array($signature, [\Phar::MD5, \Phar::SHA1, \Phar::SHA256, \Phar::SHA512], true) === true) {
            $phar->setSignatureAlgorithm($signature);
        } elseif ($signature === \Phar::OPENSSL) {
            self::setOpenSSLSignature($phar, $privateKeyAlias);
        } else {
            throw new InvalidConfigException("Invalid configuration. Unknown signature type '{$signature}'.");
        }
    }

    /**
     * Add stub from file to phar file.
     *
     * @param \Phar       $phar      PHAR object to update.
     * @param string|bool $stubAlias Path alias to stub file.
     *
     * @throws \yii\base\InvalidConfigException On wrong stub file set (index0h\phar\Module::stub).
     */
    public static function addStub(\Phar $phar, $stubAlias)
    {
        if ($stubAlias === false) {
            return;
        }
        echo "\nAdd stub";
        $path = \Yii::getAlias($stubAlias);
        if (file_exists($path) === false) {
            throw new InvalidConfigException("Invalid configuration. Stub file '{$path}' does not exists.");
        }
        $phar->setStub(file_get_contents($path));
    }

    /**
     * Set OpenSSL signature and creating public key file.
     *
     * @param \Phar  $phar      PHAR object to update.
     * @param string $pathAlias Path alias to private key file.
     *
     * @throws \yii\base\InvalidConfigException
     *     On wrong OpenSSL certificate file set (index0h\phar\Module::openSSLPrivateKeyAlias).
     */
    protected static function setOpenSSLSignature(\Phar $phar, $pathAlias)
    {
        $path = \Yii::getAlias($pathAlias);
        if (file_exists($path) === false) {
            throw new InvalidConfigException("Invalid configuration. Private key '{$path}' does not exists.");
        }

        $pemFile = file_get_contents($path);

        $resource = openssl_get_privatekey($pemFile);
        $privateKey = '';
        openssl_pkey_export($resource, $privateKey);
        $phar->setSignatureAlgorithm(\Phar::OPENSSL, $privateKey);
    }
}
