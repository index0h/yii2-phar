<?php
/**
 * @link      https://github.com/index0h/yii-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii-phar/master/LICENSE
 */

use index0h\yii\phar\components\php\Fixer;
use index0h\yii\phar\components\php\Minimize;

/**
 * Check index0h\yii\phar\components\php\***.
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
class ComponentsCests
{
    public function testPHPFixer(\CodeGuy $I)
    {
        $file = $I->processFileEventByComponent(new Fixer(), ['include', 'realpath.php']);
        $I->seeFixedFile($file);
        $I->removeTemporaryFile($file);
    }

    public function testPHPMinimize(\CodeGuy $I)
    {
        $file = $I->processFileEventByComponent(new Minimize(), ['include', 'realpath.php']);
        $I->seeMinimizedFile($file, ['include', 'realpath.php']);
        $I->removeTemporaryFile($file);
    }
}
