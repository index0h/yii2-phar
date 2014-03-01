<?php
/**
 * @link      https://github.com/index0h/yii2-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii2-phar/master/LICENSE
 */

namespace index0h\phar\components\php;

use index0h\phar\base\Component;
use index0h\phar\base\FileEvent;

/**
 * Removes whitespace and comments from php files.
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
class Minimize extends Component
{
    /**
     * @inheritdoc
     */
    protected $match = ['/(?<!Controller)\.php/us'];

    /**
     * @inheritdoc
     */
    public function processFile(FileEvent $event)
    {
        file_put_contents($event->realPath, php_strip_whitespace($event->realPath));
    }
}
