<?php
/**
 * @link      https://github.com/index0h/yii2-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii2-phar/master/LICENSE
 */

namespace Codeception\Module;

use index0h\phar\base\FileEvent;

/**
 * Methods for checks in index0h\phar\components\php\***.
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
class ComponentHelper extends \Codeception\Module
{

    public function createFileEvent($relativePathAsArray)
    {
        $relativePath = implode(DIRECTORY_SEPARATOR, $relativePathAsArray);
        $realPath = \Yii::getAlias('@tests/_data/') . $relativePath;
        return new FileEvent(['realPath' => $realPath, 'relativePath' => $relativePath]);
    }

    public function processFileEventByComponent($object, $relativePathAsArray)
    {
        $file = $this->createFileEvent($relativePathAsArray);
        $object->onProcessFile($file);

        return $file;
    }

    public function removeTemporaryFile($file)
    {
        if ($file->isTemporary === true) {
            @unlink($file->realPath);
        }
    }

    public function seeFileRealPathChanged($newFile, $relativePathAsArray)
    {
        $oldFile = $this->createFileEvent($relativePathAsArray);

        $this->assertEquals($oldFile->relativePath, $newFile->relativePath);
        $this->assertNotEquals($oldFile->realPath, $newFile->realPath);
        $this->assertTrue($newFile->isTemporary);
    }

    public function seeFixedFile($file)
    {
        $expected = "<?php\necho \\index0h\\phar\\helpers\\FileHelper::realPath(__DIR__);";
        $actual = file_get_contents($file->realPath);

        $this->assertEquals($expected, $actual);
    }

    public function seeMinimizedFile($file, $relativePathAsArray)
    {
        $realFile = $this->createFileEvent($relativePathAsArray);

        $expected = php_strip_whitespace($realFile->realPath);
        $actual = file_get_contents($file->realPath);

        $this->assertEquals($expected, $actual);
    }
}
