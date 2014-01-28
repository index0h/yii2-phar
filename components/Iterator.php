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
        $basePath = self::realPath($path);
        $this->basePathLength = strlen(self::realPath($basePath . DIRECTORY_SEPARATOR . '..'));
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
     * @param string $file     Current path to file (could be tmp).
     * @param string $basePath First path to file.
     *
     * @return string
     */
    protected function fixPHP($file, $basePath)
    {
        if ($this->module->fixPHP === false) {
            return $file;
        }
        foreach ($this->module->fixPHP as $pattern) {
            if (preg_match("/{$pattern}/us", $basePath) > 0) {
                list($content, $isUpdated) = $this->fixPHPContent($file);

                if ($isUpdated === false) {
                    return $file;
                }

                $path = tempnam(\Yii::getAlias('@runtime/yii-phar'), 'fixPHP');

                file_put_contents($path, $content);
                return $path;
            }
        }
        return $file;
    }

    /**
     * @param string $file Current path to file.
     *
     * @return array
     */
    protected function fixPHPContent($file)
    {
        $content = file_get_contents($file);
        $patterns = [];
        $replacements = [];

        foreach ($this->module->fixPHPRules as $from => $to) {
            $patterns[] = "/{$from}/us";
            $replacements[] = $to;
        }

        $newContent = preg_replace($patterns, $replacements, $content);
        return [$newContent, ($newContent !== $content)];
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
        if ($this->module->ignore === false) {
            return $file;
        }
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
     * @param string $file     Current path to file (could be tmp).
     * @param string $basePath First path to file.
     *
     * @return string
     */
    protected function minimizePHP($file, $basePath)
    {
        if ($this->module->minimizePHP === false) {
            return $file;
        }
        foreach ($this->module->minimizePHP as $pattern) {
            if (preg_match("/{$pattern}/us", $basePath) > 0) {
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
        foreach (glob($path . DIRECTORY_SEPARATOR .  '*') as $file) {
            if ($this->isIgnored($file) === true) {
                continue;
            }
            $basePath = $file;
            $relative = substr($file, $this->basePathLength);
            $file = $this->minimizePHP($file, $basePath);
            $file = $this->fixPHP($file, $basePath);

            if (is_dir($file) == true) {
                $this->scan($file);
            } else {
                $this->files[] = array($relative, $file);
            }
        }
    }

    /**
     * @param string $path Path to convert.
     *
     * @return mixed|string
     */
    public static function realPath($path)
    {
        $isUnixPath = ((strlen($path) === 0) || ($path[0] !== '/'));

        // Checks if path is relative.
        if ((strpos($path, ':') === false) && ($isUnixPath === true)) {
            $path = getcwd() . DIRECTORY_SEPARATOR . $path;
        }

        // Resolve path parts (single dot, double dot and double delimiters).
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) {
                continue;
            }
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        $path = implode(DIRECTORY_SEPARATOR, $absolutes);

        // Resolve any symlinks.
        if ((file_exists($path) === true) && (linkinfo($path) > 0)) {
            $path = readlink($path);
        }

        if ($isUnixPath === false) {
            $path = '/' . $path;
        }

        return $path;
    }
}
