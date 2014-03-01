<?php
/**
 * @link      https://github.com/index0h/yii2-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii2-phar/master/LICENSE
 */

use yii\base\InvalidConfigException;

/**
 * Check index0h\phar\base\Builder.
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
class BuilderCest
{
    public function testAddRightCompress(CodeGuy $I)
    {
        $I->createExamplePhar(\Yii::getAlias('@tests/_runtime/yii2-phar'));
        $I->addRightCompress();
        $I->seeCompressed();
    }

    public function testAddRightSignature(CodeGuy $I)
    {
        $I->createExamplePhar(\Yii::getAlias('@tests/_runtime/yii2-phar'));

        $I->addRightSignature();
        $I->seeRightSignature();
    }

    public function testAddRightStub(CodeGuy $I)
    {
        $I->createExamplePhar(\Yii::getAlias('@tests/_runtime/yii2-phar'));

        $I->addRightStub();
        $I->seeRightStub();
    }

    public function testAddOpenSSLSignature(CodeGuy $I)
    {
        $I->wantToTest('add OpenSSL signature');

        $I->createExamplePhar(\Yii::getAlias('@tests/_runtime/yii2-phar'));

        $I->addOpenSSLSignature();
        $I->seeOpenSSLSignature();
    }

    public function testAddWrongOpenSSLSignature(CodeGuy $I)
    {
        $I->wantToTest('add OpenSSL signature with wrong private key');
        $I->createExamplePhar(\Yii::getAlias('@tests/_runtime/yii2-phar'));
        try {
            $I->addWrongOpenSSLSignature();
            $I->fail("signature with wrong params didn't fire exception");
        } catch (InvalidConfigException $error) {
            // Exception must fire.
        }
    }

    public function testAddWrongCompress(CodeGuy $I)
    {
        $I->createExamplePhar(\Yii::getAlias('@tests/_runtime/yii2-phar'));
        try {
            $I->addWrongCompress();
            $I->fail("compress with wrong params didn't fire exception");
        } catch (InvalidConfigException $error) {
            // Exception must fire.
        }
    }

    public function testAddWrongStub(CodeGuy $I)
    {
        $I->createExamplePhar(\Yii::getAlias('@tests/_runtime/yii2-phar'));
        try {
            $I->addWrongStub();
            $I->fail("stub with wrong params didn't fire exception");
        } catch (InvalidConfigException $error) {
            // Exception must fire.
        }
    }

    public function testAddWrongSignature(CodeGuy $I)
    {
        $I->createExamplePhar(\Yii::getAlias('@tests/_runtime/yii2-phar'));
        try {
            $I->addWrongSignature();
            $I->fail("signature with wrong params didn't fire exception");
        } catch (InvalidConfigException $error) {
            // Exception must fire.
        }
    }
}
