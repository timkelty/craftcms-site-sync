<?php
/**
 * Site Sync plugin for Craft CMS 3.x
 *
 * Sync content to other sites on element save.
 *
 * @link      https://github.com/timkelty
 * @copyright Copyright (c) 2018 Tim Kelty
 */

namespace timkelty\craft\sitesync;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\console\Application as ConsoleApplication;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;
use craft\events\ModelEvent;
use craft\base\Element;
use yii\base\Event;

class SiteSync extends Plugin
{
    public function init()
    {
        parent::init();

        // $this->setComponents([
        //     'content' => services\Content::class,
        // ]);

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = \timkelty\craft\sitesync\fields\SiteSyncSettingsField::class;
            }
        );

        ModelEvent::on(
            Element::class,
            Element::EVENT_BEFORE_SAVE,
            [\timkelty\craft\sitesync\models\SiteSyncSettings::class, 'beforeElementSaveHandler']
            // function(ModelEvent $event) {
            //     (new \timkelty\craft\sitesync\models\SiteSyncElement($event->sender))->syncToSites();
            // }
        );
    }
}
