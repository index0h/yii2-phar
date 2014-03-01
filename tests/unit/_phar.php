<?php
/**
 * @link      https://github.com/index0h/yii2-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii2-phar/master/LICENSE
 */

return [
    'compress' => false,
    'signature' => false,
    'stub' => false,
    'path' => '@app/tests/_runtime/app.phar',
    'ignore' => ['/.*\.phar$/s', '/\.(git|svn|hg)/s'],
    'folders' => ['@app/src', '@app/vendor'],
    'files' => ['.test.php'],
    'components' => [
        'fixer' => [
            'class' => 'index0h\\phar\\components\\php\\Fixer',
            'match' => '/.*\.php/s'
        ]
    ]
];
