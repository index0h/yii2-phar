<?php
/**
 * @link      https://github.com/index0h/yii-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii-phar/master/LICENSE
 */

namespace index0h\yii\phar\iterators;

use index0h\yii\phar\helpers\FileHelper;

/**
 * Scans all subdirectories.
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
class FolderIterator implements \Iterator
{
    /** @type string Root path to directory. */
    protected $initPath;

    /** @type \RecursiveIteratorIterator Main iterator. */
    protected $iterator;

    /**
     * @param string $path Root path to directory.
     */
    public function __construct($path)
    {
        $this->initPath = FileHelper::realPath($path . DIRECTORY_SEPARATOR . '..');

        $this->iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $path,
                \FilesystemIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return $this->iterator->current()->getPathName();
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->iterator->key();
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        $this->iterator->next();
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->iterator->rewind();
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return $this->iterator->valid();
    }

    /**
     * @return string
     */
    public function getRelativePath()
    {
        $path = substr($this->iterator->current(), strlen($this->initPath));
        if ($path[0] === '/') {
            $path = substr($path, 1);
        }
        return $path;
    }
}
