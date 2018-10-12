<?php
namespace timkelty\craft\sitesync\listeners;

use Craft;
use craft\base\Element;
use craft\helpers\ElementHelper;
use craft\helpers\ArrayHelper;
use timkelty\craft\sitesync\SiteSync;

class BeforeElementSave
{
    public function handle(\craft\events\ModelEvent $event)
    {
        $element = $event->sender;

        SiteSync::getInstance()->content->syncToSites($element);
    }
}
