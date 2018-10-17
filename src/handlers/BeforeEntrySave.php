<?php
namespace timkelty\craft\sitesync\handlers;

class BeforeEntrySave
{
    public function handle($event)
    {
        $element = $event->sender;

        if ($element->propagating) {
            return;
        }

        $instance = \timkelty\craft\sitesync\models\SiteSyncSettings::getSettingsFromElement($element);
        $type = get_class($element);
        xdebug_break();
        if (!$instance) {
            return false;
        }

        $instance->syncToSites();
    }
}
