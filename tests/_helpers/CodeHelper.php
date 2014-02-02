<?php
/**
 * @link      https://github.com/index0h/yii-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii-phar/master/LICENSE
 */

namespace Codeception\Module;

use index0h\yii\phar\iterators\FolderIterator;
use yii\console\Application;

/**
 * Methods for emulating console application with Phar module call.
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
class CodeHelper extends \Codeception\Module
{
    /** @type array Configuration of phar module. */
    protected $moduleConfiguration = [
        'class' => 'index0h\\yii\\phar\\Module',
        'compress' => false,
        'signature' => false,
        'stub' => false,
        'path' => '@tests/_runtime/yii-phar/app.phar',
        'ignore' => [],
        'folders' => ['@tests/_data'],
        'components' => [
            'fixer' => [
                'class' => 'index0h\\yii\\phar\\components\\php\\Fixer',
                'match' => '/.*\.php/'
            ],
            'minimize' => [
                'class' => 'index0h\\yii\\phar\\components\\php\\Minimize',
                'match' => ['/.*\.php/']
            ]
        ]
    ];

    public function extractPharFile()
    {
        $phar = new \Phar(\Yii::getAlias('@tests/_runtime/yii-phar/app.phar'));
        $phar->extractTo(\Yii::getAlias('@tests/_runtime/yii-phar/extract'));
    }

    public function runPharCommand()
    {
        $configuration = require \Yii::getAlias('@tests/unit/_config.php');

        $configuration['modules']['phar'] = $this->moduleConfiguration;

        $application = new Application($configuration);

        $application->requestedRoute = 'phar';
        $application->runAction('phar', []);
    }

    public function seeAllFilesCompiled()
    {
        $expected = $this->getAllFiles(\Yii::getAlias('@tests/_data'));
        $actual = $this->getAllFiles(\Yii::getAlias('@tests/_runtime/yii-phar/extract/_data'));

        $this->assertEquals($expected, $actual);
    }

    protected function getAllFiles($path)
    {
        $iterator = new FolderIterator($path);

        $result = [];

        foreach ($iterator as $value) {
            $result[] = substr($value, strlen($path));
        }

        sort($result);

        return $result;
    }
}
