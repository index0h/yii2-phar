<?php
/**
 * @link      https://github.com/index0h/yii-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii-phar/master/LICENSE
 */

namespace index0h\yii\phar\helpers;

use yii\helpers;

/**
 * Fixed realpath function.
 *
 * @see https://bugs.php.net/bug.php?id=52769
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
class FileHelper extends helpers\BaseFileHelper
{
    /**
     * @param string $path Path to convert.
     *
     * @return string
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
            if (('.' === $part) || ('' === $part)) {
                continue;
            }
            if ('..' === $part) {
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
