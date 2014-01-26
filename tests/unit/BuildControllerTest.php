<?php
/**
 * @link      https://github.com/index0h/yii-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii-phar/master/LICENSE
 */

namespace index0h\yii\phar\tests\unit;

use index0h\yii\phar\controllers\BuildController;
use index0h\yii\phar\Module;
use yii\base\InvalidConfigException;
use yii\codeception\TestCase;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use Yii;

/**
 * Tests for BuildController.
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
class BuildControllerTest extends TestCase
{

    /** @type \index0h\yii\phar\controllers\BuildController Testing object. */
    public $object;

    /** @type array array of basic test configuration. */
    protected $configuration = [
        'compress' => [\Phar::GZ, \Phar::BZ2],
        'files' => ['@tests/_data/include.php', '@tests/_data/notInclude.md', '@tests/_data/minimize.php'],
        'folders' => ['@tests/_data/include/', '@tests/_data/notInclude/'],
        'ignore' => ['.*notInclude.*'],
        'openSSLPrivateKeyAlias' => '@tests/_data/private.pem',
        'path' => '@tests/_runtime/phar/test.phar',
        'pharName' => 'test',
        'signature' => \Phar::OPENSSL,
        'stub' => '@tests/_data/stub.php',
        'minimizePHP' => ['\.php']
    ];

    /** @type string Path to phar runtime directory. */
    protected $pathPrefix;

    /**
     * Checks that all files that must be in phar - compiled to it.
     */
    public function testAllFilesExist()
    {
        $expected = [
            'include/subdir/file.php',
            'include/include.php',
            'include/minimize.php',
            'include.php',
            'notInclude.md',
            'minimize.php'
        ];
        $prefix = $this->getPathPrefix() . '/extract';
        foreach ($expected as $file) {
            $this->assertFileExists("{$prefix}/{$file}");
        }
    }

    /**
     * Checks files that must be minimized.
     */
    public function testMinimize()
    {
        $expected = php_strip_whitespace(\Yii::getAlias('@tests/_data/minimize.php'));
        $actual = file_get_contents(\Yii::getAlias('@tests/_runtime/phar/extract/minimize.php'));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Checks that all phar files are created.
     */
    public function testPharCreated()
    {
        $prefix = $this->getPathPrefix();
        foreach (['', '.gz', '.bz2'] as $extension) {
            $this->assertFileExists("{$prefix}/test.phar{$extension}");
        }
    }

    /**
     * Checks that stub file is set.
     */
    public function testStub()
    {
        $prefix = $this->getPathPrefix();
        $phar = new \Phar("{$prefix}/test.phar");

        $expected = trim(preg_replace('/\s+/s', ' ', file_get_contents(\Yii::getAlias('@tests/_data/stub.php'))));
        $actual = trim(preg_replace('/\s+/s', ' ', $phar->getStub()));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Many checks of wrong configuration.
     */
    public function testWrongConfiguration()
    {
        $wrongConfiguration = [
            ['compress' => ['WrongCompress'], 'signature' => false],
            ['files' => '/-----/-----/-----/', 'signature' => false],
            ['folders' => '/-----/-----/-----/', 'signature' => false],
            ['stub' => '/-----/-----/-----/', 'signature' => false],
            ['signature' => 'WrongSignature'],
            ['openSSLPrivateKeyAlias' => '@tests/_data/unknown.pem'],
        ];

        foreach ($wrongConfiguration as $step) {
            $configuration = ArrayHelper::merge($this->configuration, $step);
            try {
                $this->createPhar($configuration);
                $this->fail();
            } catch (InvalidConfigException $error) {
                // There must be exception.
            }
            $this->cleanRuntime();
        }
    }

    /**
     * Clean runtime directory.
     */
    protected function cleanRuntime()
    {
        $prefix = $this->getPathPrefix();
        foreach (['', '.gz', '.bz2'] as $extension) {
            if (file_exists("{$prefix}/test.phar{$extension}") === true) {
                \Phar::unlinkArchive("{$prefix}/test.phar{$extension}");
            }
        }

        if (file_exists($prefix)) {
            FileHelper::removeDirectory($prefix);
        }
        mkdir($prefix, 0777);
        copy(\Yii::getAlias('@tests/_data/public.pem'), "{$prefix}/test.phar.pubkey");
    }

    /**
     * @param array $configuration Configuration of phar generator.
     */
    protected function createPhar($configuration)
    {
        $this->cleanRuntime();
        $module = new Module('phar', null, $configuration);
        $module->runAction('build');
    }

    /**
     * Extract phar archive.
     */
    protected function extract()
    {
        $prefix = $this->getPathPrefix();
        $phar = new \Phar("{$prefix}/test.phar");
        $phar->extractTo("$prefix/extract/");
    }

    /**
     * Path to phar's runtime directory.
     *
     * @return bool|string
     */
    protected function getPathPrefix()
    {
        if ($this->pathPrefix === null) {
            $this->pathPrefix = \Yii::getAlias('@tests/_runtime/phar');
        }
        return $this->pathPrefix;
    }

    /**
     * Start method.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->createPhar($this->configuration);
        $this->extract();
    }

    /**
     * Ending method.
     */
    protected function tearDown()
    {
        $this->cleanRuntime();
        parent::tearDown();
    }
}
