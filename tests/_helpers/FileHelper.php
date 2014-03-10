<?php
/**
 * @link      https://github.com/index0h/yii2-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii2-phar/master/LICENSE
 */

namespace Codeception\Module;

use index0h\phar\helpers;

/**
 * Methods for FileHelper tests.
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
class FileHelper extends \Codeception\Module
{
    public function seeRightPathConvert()
    {
        $data = [
            'phar:///test\data\\.\\..\\..\\data/.////./Windows/\\..\\../.\\data1.phar' => 'phar:///data1.phar',
            'phar://' => 'phar://' . getcwd(),
            'phar://C:\\test\\./data\\.\\..\\..\\data/.////./Windows/\\../.\\data.phar' => 'phar://C:/data/data.phar',
            __DIR__ . '/../../.' => realpath(__DIR__ . '/../../.'),
        ];

        foreach ($data as $path => $expected) {
            $this->assertEquals($expected, helpers\FileHelper::realPath($path));
        }
    }
}
