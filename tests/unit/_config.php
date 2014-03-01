<?php
/**
 * @link      https://github.com/index0h/yii2-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii2-phar/master/LICENSE
 */

return [
    'id' => 'basic',
    'basePath' => \Yii::getAlias('@tests'),
    'runtimePath' => \Yii::getAlias('@tests/_runtime'),
    'modules' => [
        'phar' => ['class' => 'index0h\\yii\\phar\\Module']
    ]
];
