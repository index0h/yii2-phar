<?php
/**
 * @link      https://github.com/index0h/yii2-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii2-phar/master/LICENSE
 */

/**
 * Check index0h\phar\Module.
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
class ModuleCest
{
    public function testRunYiiApplication(CodeGuy $I)
    {
        $I->runPharCommand();
        $I->extractPharFile();
        $I->seeAllFilesCompiled();
    }
}