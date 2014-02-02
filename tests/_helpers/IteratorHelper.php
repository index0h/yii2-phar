<?php
/**
 * @link      https://github.com/index0h/yii-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii-phar/master/LICENSE
 */

namespace Codeception\Module;

use index0h\yii\phar\iterators\Iterator;
use index0h\yii\phar\Module;

/**
 * Methods for checks index0h\yii\phar\iterators\***.
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
class IteratorHelper extends \Codeception\Module
{
    public function createModuleForIterator()
    {
        $configuration = [
            'files' => [
                '@tests/_data/include.php',
                '@tests/_data/notInclude.md',
                '@tests/_data/minimize.php',
                '@tests/_data/realpath.php'
            ],
            'folders' => ['@tests/_data/'],
            'ignore' => ['/.*notInclude.*/u']
        ];

        return new Module('phar', null, $configuration);
    }

    public function getAllFilesFromIteratorByModule($module)
    {
        $iterator = new Iterator($module);
        $actual = [];

        foreach ($iterator as $relativePath => $realPath) {
            $actual[$relativePath] = $realPath;
        }

        return $actual;
    }

    public function seeAllFilesFoundByIterator($actual)
    {
        $actual = $actual->__value();

        $expected = [
            // From folders.
            implode(DIRECTORY_SEPARATOR, ['_data', 'include', 'subdir', 'file.php']) => false,
            implode(DIRECTORY_SEPARATOR, ['_data', 'include', 'include.php']) => false,
            implode(DIRECTORY_SEPARATOR, ['_data', 'include', 'minimize.php']) => false,
            implode(DIRECTORY_SEPARATOR, ['_data', 'include', 'realpath.php']) => false,
            implode(DIRECTORY_SEPARATOR, ['_data', 'include.php']) => false,
            implode(DIRECTORY_SEPARATOR, ['_data', 'minimize.php']) => false,
            implode(DIRECTORY_SEPARATOR, ['_data', 'realpath.php']) => false,
            implode(DIRECTORY_SEPARATOR, ['_data', 'private.pem']) => false,
            implode(DIRECTORY_SEPARATOR, ['_data', 'public.pem']) => false,
            implode(DIRECTORY_SEPARATOR, ['_data', 'stub.php']) => false,
            // From files.
            'include.php' => true,
            'minimize.php' => true,
            'realpath.php' => true,
        ];

        foreach ($expected as $relativePath => &$realPath) {
            if ($realPath === true) {
                $realPath = \Yii::getAlias('@tests/_data/') . $relativePath;
            } else {
                $realPath = \Yii::getAlias('@tests/') . $relativePath;
            }
        }

        ksort($expected);
        ksort($actual);

        $this->assertEquals($expected, $actual);
    }
}
