<?php
/**
 * @link      https://github.com/index0h/yii2-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii2-phar/master/LICENSE
 */

namespace Codeception\Module;

use index0h\phar\base\Builder;
use yii\helpers\FileHelper;

/**
 * Methods for builder tests.
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
class BuilderHelper extends \Codeception\Module
{
    /** @type \Phar Current PHAR object. */
    protected $phar;

    /** @type string Path to runtime directory. */
    protected $runtime;

    public function addOpenSSLSignature()
    {
        Builder::addSignature($this->phar, \Phar::OPENSSL, '@tests/_data/private.pem');
    }

    public function addRightCompress()
    {
        Builder::addCompress($this->phar, [\Phar::BZ2, \Phar::GZ]);
    }

    public function addRightSignature()
    {
        Builder::addSignature($this->phar, \Phar::MD5);
    }

    public function addRightStub()
    {
        Builder::addStub($this->phar, '@tests/_data/stub.php');
    }

    public function addWrongCompress()
    {
        Builder::addCompress($this->phar, ['WRONG_COMPRESS']);
    }

    public function addWrongOpenSSLSignature()
    {
        Builder::addSignature($this->phar, \Phar::OPENSSL, 'WRONG_FILE');
    }

    public function addWrongSignature()
    {
        Builder::addSignature($this->phar, 'WRONG_SIGNATURE');
    }

    public function addWrongStub()
    {
        Builder::addStub($this->phar, 'WRONG_STUB');
    }

    public function createExamplePhar($runtimePath = null)
    {
        if ($runtimePath !== null) {
            $this->setRuntimeDirectory($runtimePath);
        }

        $this->unlinkArchives();

        $this->phar = new \Phar($this->runtime . DIRECTORY_SEPARATOR . 'example.phar', 0, 'example');
        $this->phar->addFromString('example.php', '// example.');
    }

    public function seeCompressed()
    {
        $prefix = $this->runtime . DIRECTORY_SEPARATOR . 'example.phar';
        $this->assertTrue(file_exists($prefix . '.bz2'));
        $this->assertTrue(file_exists($prefix . '.gz'));
    }

    public function seeOpenSSLSignature()
    {
        $signature = $this->phar->getSignature();
        $this->assertEquals($signature['hash_type'], 'OpenSSL');
    }

    public function seeRightSignature()
    {
        $signature = $this->phar->getSignature();
        $this->assertEquals($signature['hash_type'], 'MD5');
    }

    public function seeRightStub()
    {
        $expected = file_get_contents(\Yii::getAlias('@tests/_data/stub.php'));
        $actual = $this->phar->getStub();

        $expected = preg_replace('/\s+/s', ' ', $expected);
        $actual = preg_replace('/\s+/s', ' ', $actual);

        $this->assertEquals(trim($expected), trim($actual));
    }

    public function setRuntimeDirectory($path)
    {
        $this->runtime = $path;
    }

    protected function unlinkArchives()
    {
        unset($this->phar);

        $prefix = $this->runtime . DIRECTORY_SEPARATOR . 'example.phar';
        foreach (['', '.bz2', '.gz'] as $extension) {
            $path = $prefix . $extension;
            if (file_exists($path) === true) {
                \Phar::unlinkArchive($path);
            }
        }

        if (file_exists($this->runtime) === true) {
            FileHelper::removeDirectory($this->runtime);
        }
        mkdir($this->runtime, 0777);
    }
}
