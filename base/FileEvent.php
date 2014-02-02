<?php
/**
 * @link      https://github.com/index0h/yii-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii-phar/master/LICENSE
 */

namespace index0h\yii\phar\base;

use yii\base\Event;

/**
 * File structure.
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
class FileEvent extends Event
{
    /** @type bool Was file modified. */
    public $isTemporary = false;

    /** @type string Path to file. */
    public $realPath;

    /** @type string Path, that will be in phar file. */
    public $relativePath;
}