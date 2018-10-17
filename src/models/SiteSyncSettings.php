<?php
namespace timkelty\craft\sitesync\models;

use Craft;
use craft\events\ModelEvent;
use craft\base\Model;
use craft\base\Element;
use craft\helpers\ElementHelper;
use timkelty\craft\sitesync\fields\SiteSyncSettingsField;

class SiteSyncSettings extends Model
{
    public $syncEnabled;
    public $overwrite;
    private $element;

    public static function beforeElementSaveHandler(ModelEvent $event)
    {
        $element = $event->sender;

        return self::getSettingsFromElement($element)->syncToSites();
    }

    public function __construct(Element $element, $config = [])
    {
        $this->element = $element;

        parent::__construct($config);
    }

    public function syncToSites(array $siteIds = null)
    {
        $siteIds = $siteIds ?? $this->getSupportedSiteIds();

        foreach ($siteIds as $siteId) {
            $this->syncToSite($siteId);
        }
    }

    public function syncToSite(int $siteId)
    {
        // TODO: Check if element is even multisite enabled first
        if ($this->element->siteId === $siteId) {
            return;
        }

        if (!$this->syncEnabled) {
            return;
        }

        $siteElement = Craft::$app->getElements()->getElementById($this->element->id, get_class($this->element), $siteId);

        if ($this->overwrite) {
            $siteElement->title = $this->element->title;
            $siteElement->slug = $this->element->slug;
            $siteElement->setFieldValues($this->element->getFieldValues());
        } else {
            // TODO: update matching content only
        }

        Craft::$app->getElements()->updateElementSlugAndUri($siteElement, false, false);
        Craft::$app->getContent()->saveContent($siteElement);
    }

    private function getSupportedSiteIds()
    {
        // TODO: should we use editableSiteIdsForElement instead?
        $supportedSites = ElementHelper::supportedSitesForElement($this->element);

        return array_map(function($siteInfo) {
            return (int) $siteInfo['siteId'];
        }, $supportedSites);
    }

    private static function getSettingsFromElement(Element $element): SiteSyncSettings
    {
        $field = self::getSettingsField($element);

        if (!$field) {
            return null;
        }

        return $element->getFieldValue($field->handle);
    }

    private static function getSettingsField(Element $element)
    {
        $layout = $element->getFieldLayout();
        $fields = array_filter($layout->getFields(), function($field) {
            return $field instanceof SiteSyncSettingsField;
        });

        // TODO: throw error if more that 1?
        return array_shift($fields);
    }
}
