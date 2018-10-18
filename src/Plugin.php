<?php

namespace timkelty\craft\sitesync;

use craft\base\Element;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;
use craft\events\ModelEvent;
use yii\base\Event;
use timkelty\craft\sitesync\Field as SiteSyncField;
use timkelty\craft\sitesync\models\Syncable;

class Plugin extends \craft\base\Plugin
{
    public function init()
    {
        parent::init();

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = SiteSyncField::class;
            }
        );

        ModelEvent::on(
            Element::class,
            Element::EVENT_BEFORE_SAVE,
            [Syncable::class, 'beforeElementSaveHandler']
        );
    }
}
