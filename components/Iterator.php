<?php
/**
 * @link      https://github.com/index0h/yii-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii-phar/master/LICENSE
 */

namespace index0h\yii\phar\components;

/**
 * Phar iterator.
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
class Iterator implements \Iterator
{
    /** @type int Used to cat relative path. */
    protected $basePathLength = 0;

    /** @type array[] List of found files [[relativePath, realPath]]. */
    protected $files = array();

    /** @type int Current iterator index. */
    protected $index = 0;

    /** @type \index0h\yii\phar\Module Link to main module object. */
    protected $module;

    /**
     * @param string                   $path   Path ot scanning directory.
     * @param \index0h\yii\phar\Module $module Link to main module object.
     */
    public function __construct($path, &$module)
    {
        $this->module = $module;
        $basePath = realpath($path);
        $this->basePathLength = strlen(realpath($basePath . '/../'));
        $this->scan($basePath);
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return $this->files[$this->index][1];
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->files[$this->index][0];
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        $this->index++;
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->index = 0;
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return isset($this->files[$this->index]);
    }

    /**
     * Check if file is ignored by (index0h\yii\phar\Module::ignore).
     *
     * @param string $file Path to found file or folder.
     *
     * @return bool
     */
    protected function isIgnored($file)
    {
        foreach ($this->module->ignore as $pattern) {
            if (preg_match("/{$pattern}/us", $file) > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Compress php files through php_strip_whitespace.
     *
     * @param string $file Real path to file.
     *
     * @return string
     */
    protected function minimizePHP($file) {
        foreach ($this->module->minimizePHP as $pattern) {
            if (preg_match("/{$pattern}/us", $file) > 0) {
                $path = tempnam(\Yii::getAlias('@runtime/yii-phar'), 'minimizePHP');
                file_put_contents($path, php_strip_whitespace($file));
                return $path;
            }
        }
        return $file;
    }

    /**
     * Recursively found files in folder.
     *
     * @param string $path Path to folder.
     */
    protected function scan($path)
    {
        foreach (glob($path . '/*') as $file) {
            if ($this->isIgnored($file) === true) {
                continue;
            }
            $relative = substr($file, $this->basePathLength);
            $file = $this->minimizePHP($file);
            if (is_dir($file) == true) {
                $this->scan($file);
            } else {
                $this->files[] = array($relative, $file);
            }
        }
    }
}
