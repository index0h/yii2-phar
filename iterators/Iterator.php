<?php
/**
 * @link      https://github.com/index0h/yii-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii-phar/master/LICENSE
 */

namespace index0h\yii\phar\iterators;

use index0h\yii\phar\base\FileEvent;
use index0h\yii\phar\Module;

/**
 * Phar builder controller.
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
class Iterator implements \Iterator
{
    /** @type \index0h\yii\phar\Module Link to main module object. */
    public $module;

    /** @type FileEvent Current processing file. */
    protected $file;

    /** @type string[] List of regexp. */
    protected $ignore;

    /** @type \CallbackFilterIterator Main iterator. */
    protected $iterator;

    /**
     * @param \index0h\yii\phar\Module $module Link to main module object.
     */
    public function __construct($module)
    {
        $this->module = $module;
        $this->ignore = $module->ignore;

        $append = new \AppendIterator();
        $this->appendFilesIterator($module->files, $append);
        $this->appendFoldersIterator($module->folders, $append);

        $this->iterator = new \CallbackFilterIterator(
            $append,
            function ($current) {
                if ($this->isIgnored($current) === false) {
                    return false;
                }

                return true;
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        if ($this->file === null) {
            return null;
        }

        return $this->file->realPath;
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        if ($this->file === null) {
            return null;
        }

        return $this->file->relativePath;
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        $this->iterator->next();

        $this->triggerFileEvent();
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->iterator->rewind();

        $this->triggerFileEvent();
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return $this->iterator->valid();
    }

    /**
     * @param string[]        $files    List of files.
     * @param \AppendIterator $iterator Appending iterator.
     */
    protected function appendFilesIterator($files, \AppendIterator $iterator)
    {
        foreach ($files as &$file) {
            $file = \Yii::getAlias($file);
        }
        $iterator->append(new FileIterator($files));
    }

    /**
     * @param string[]        $folders  List of folders to scan.
     * @param \AppendIterator $iterator Appending iterator.
     */
    protected function appendFoldersIterator(array $folders, \AppendIterator $iterator)
    {
        foreach ($folders as $folder) {
            $iterator->append(new FolderIterator(\Yii::getAlias($folder)));
        }
    }

    /**
     * Create file event and trigger in from module.
     */
    protected function createFile()
    {
        $event = new FileEvent([
            'realPath' => $this->iterator->current(),
            'relativePath' => $this->iterator->getRelativePath()
        ]);

        $this->module->trigger(Module::EVENT_PROCESS_FILE, $event);

        $this->file = $event;
    }

    /**
     * Remove file after next/rewind call.
     */
    protected function dropFile()
    {
        if (($this->file !== null) && ($this->file->isTemporary === true)) {
            @unlink($this->file->realPath);
        }

        $this->file = null;
    }

    /**
     * @param string $current Path to file/folder.
     *
     * @return bool
     */
    protected function isIgnored($current)
    {
        if (is_dir($current) === true) {
            return false;
        }

        foreach ($this->ignore as $pattern) {
            if (preg_match($pattern, $current) > 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * Clean old files, creates new and trigger event in module.
     */
    protected function triggerFileEvent()
    {
        $this->dropFile();

        if ($this->valid() === false) {
            return;
        }

        $this->createFile();
    }
}