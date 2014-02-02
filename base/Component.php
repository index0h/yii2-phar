<?php
/**
 * @link      https://github.com/index0h/yii-phar
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii-phar/master/LICENSE
 */

namespace index0h\yii\phar\base;

use index0h\yii\phar\Module;
use yii\base\ActionEvent;
use yii\base;
use yii\base\InvalidConfigException;

/**
 * Base component for creating file modifications.
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
abstract class Component extends base\Component
{
    /** @type \index0h\yii\phar\Module Link to Phar module. */
    public $module;

    /** @type string System name for Phar module. */
    public $pharId = 'phar';

    /** @type string|string[]|bool Array of regexp, for which file to make modifications. */
    protected $match = [];

    /**
     * Modification of file.
     *
     * @param FileEvent $event Event with file information.
     */
    abstract public function processFile(FileEvent $event);

    /**
     * @param ActionEvent $event Build action.
     */
    public function afterAction(ActionEvent $event)
    {
    }

    /**
     * @param ActionEvent $event Build action.
     */
    public function beforeAction(ActionEvent $event)
    {
    }

    /**
     * If module and matches ok - connect events to Phar module.
     */
    public function init()
    {
        parent::init();

        if (($this->module === null) || ($this->match === false)) {
            return;
        }

        $this->module->on(
            Module::EVENT_BEFORE_ACTION,
            function (ActionEvent $event) {
                $this->beforeAction($event);
            }
        );

        $this->module->on(
            Module::EVENT_AFTER_ACTION,
            function (ActionEvent $event) {
                $this->afterAction($event);
            }
        );

        $this->module->on(
            Module::EVENT_PROCESS_FILE,
            function ($event) {
                $this->onProcessFile($event);
            }
        );
    }

    /**
     * Check if file apply to any match.
     *
     * @param FileEvent $event File event.
     *
     * @return bool
     */
    public function isAppropriate(FileEvent $event)
    {
        foreach ($this->match as $pattern) {
            if (preg_match($pattern, $event->realPath) > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Creates tmp file for modificators.
     *
     * @param FileEvent $event File event.
     */
    public function makeTemporary(FileEvent $event)
    {
        if ($event->isTemporary === true) {
            return;
        }
        $temporaryPath = tempnam(\Yii::getAlias('@runtime/yii-phar'), basename($event->realPath));
        copy($event->realPath, $temporaryPath);

        $event->isTemporary = true;
        $event->realPath = $temporaryPath;
    }

    /**
     * Main function that called on Module::EVENT_PROCESS_FILE event.
     *
     * @param FileEvent $event File event.
     */
    public function onProcessFile(FileEvent $event)
    {
        if ($this->isAppropriate($event) === false) {
            return;
        }
        $this->makeTemporary($event);

        $this->processFile($event);
    }

    /**
     * Changes match to one format.
     *
     * @param mixed $value New match.
     *
     * @throws \yii\base\InvalidConfigException On wrong match type set.
     */
    public function setMatch($value)
    {
        switch (gettype($value)) {
            case 'array':
                $this->match = $value;
                break;
            case 'string':
                $this->match = [$value];
                break;
            case 'boolean':
                if ($value === false) {
                    $this->match = false;
                    break;
                }
            default:
                throw new InvalidConfigException("Invalid configuration. Wrong march type '{$value}'.");
        }
    }
}