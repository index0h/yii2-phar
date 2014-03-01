<?php
/**
 * Codeception PHP script runner.
 *
 * @link      https://github.com/index0h/yii2-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii2-phar/master/LICENSE
 */

defined('ROOT_PATH') || define('ROOT_PATH', __DIR__);

require_once implode(DIRECTORY_SEPARATOR, [ROOT_PATH, 'vendor', 'codeception', 'codeception', 'autoload.php']);

use Symfony\Component\Console\Application;

$app = new Application('Codeception', Codeception\Codecept::VERSION);
$app->setAutoExit(false);
$app->add(new Codeception\Command\Run('run'));

$app->run();
