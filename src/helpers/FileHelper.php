<?php
/**
 * @link      https://github.com/index0h/yii2-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii2-phar/master/LICENSE
 */

namespace index0h\phar\helpers;

use yii\helpers;

/**
 * Fixed realpath function.
 *
 * @see    https://bugs.php.net/bug.php?id=52769
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
class FileHelper extends helpers\BaseFileHelper
{
    const  PHAR_PREFIX = 'phar://';

    /**
     * @param string $path Path to convert.
     *
     * @return string
     */
    public static function realPath($path)
    {
        if (self::isPhar($path) === false) {
            return realpath($path);
        }

        preg_match('/^(phar:\/{0,2})?(.*)?/u', $path, $matches);

        $path = self::resolveRelativeToGlobal($matches[2]);
        $path = self::resolveDots($path);

        return self::PHAR_PREFIX . $path;
    }

    /**
     * @param string $path Path to file/folder.
     *
     * @return int
     */
    public static function fileMakeTime($path)
    {
        if (self::isPhar($path) === false) {
            return filemtime($path);
        }

        return filemtime(self::getPharPath($path));
    }

    /**
     * @param string $path Path to folder.
     *
     * @return bool
     */
    public static function isDir($path)
    {
        return (self::isPhar($path) === true) ? file_exists($path) : is_dir($path);
    }

    /**
     * @param string $path Path to file/folder.
     *
     * @return bool
     */
    public static function isPhar($path)
    {
        return (strpos($path, 'phar:') === 0);
    }

    /**
     * @param string $path Path to file/folder.
     *
     * @return string
     */
    protected static function getPharPath($path)
    {
        $path = self::realPath($path);
        return str_replace(self::PHAR_PREFIX, '', $path);
    }

    /**
     * @param string $path Path without stream prefix.
     *
     * @return string
     */
    protected static function resolveRelativeToGlobal($path)
    {
        // Checks if path is relative.
        if ((strpos($path, ':') === false) && ((strlen($path) === 0) || ($path[0] !== '/'))) {
            $path = getcwd() . DIRECTORY_SEPARATOR . $path;
        }
        return $path;
    }

    /**
     * Resolve path parts (single dot, double dot and double delimiters).
     *
     * @param string $path Path to resolve.
     *
     * @return string
     */
    protected static function resolveDots($path)
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = [];

        if ($path[0] === DIRECTORY_SEPARATOR) {
            $absolutes[] = '';
        }

        foreach ($parts as $part) {
            if (('.' === $part) || ('' === $part)) {
                continue;
            } elseif ('..' === $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return implode(DIRECTORY_SEPARATOR, $absolutes);
    }
}
