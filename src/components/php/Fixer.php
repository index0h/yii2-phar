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
 * Fixes realpath function in phar files.
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
class Fixer extends Component
{
    /** @type array RegExp patterns to replace `from` => `to`. */
    public $replace = ['/(\s)realpath\(/us' => '\1ltrim('];

    /**
     * @inheritdoc
     */
    protected $match = ['/.*\.php/s'];

    /**
     * @inheritdoc
     */
    public function processFile(FileEvent $event)
    {
        $content = file_get_contents($event->realPath);
        $patterns = [];
        $replacements = [];

        foreach ($this->replace as $from => $to) {
            $patterns[] = $from;
            $replacements[] = $to;
        }

        $content = preg_replace($patterns, $replacements, $content);

        file_put_contents($event->realPath, $content);
    }
}
