<?php
namespace timkelty\craft\sitesync\models;

use Craft;
use craft\events\ModelEvent;
use craft\base\Model;
use craft\base\Element;
use craft\base\Field;
use craft\helpers\ElementHelper;

use timkelty\craft\sitesync\fields\SiteSyncSettingsField;

class SiteSyncSettings extends Model
{
    public $syncEnabled;
    public $overwrite;
    private $element;

    // public static function beforeElementSaveHandler(ModelEvent $event)
    // {
    //     $element = $event->sender;
    //
    //     if ($element->propagating) {
    //         return;
    //     }
    //
    //     $instance = self::getSettingsFromElement($element);
    //
    //     if (!$instance) {
    //         return false;
    //     }
    //
    //     return $instance->syncToSites();
    // }

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

        $savedElement = Craft::$app->getElements()->getElementById($this->element->id, get_class($this->element), $this->element->siteId);
        $siteElement = Craft::$app->getElements()->getElementById($this->element->id, get_class($this->element), $siteId);

        if ($this->overwrite) {
            $siteElement->title = $this->element->title;
            $siteElement->slug = $this->element->slug;
            $siteElement->setFieldValues($this->element->getFieldValues());
        } else {
            $attributesToUpdate = array_merge($this->getTranslatableFieldHandles(), [
                'title',
                'slug'
            ]);

            foreach ($attributesToUpdate as $handle) {

                // Compare serialized values so we can make a strict comparison
                $siteVal = $siteElement->getSerializedFieldValues([$handle]);
                $savedVal = $savedElement->getSerializedFieldValues([$handle]);

                // Values matched before change
                if ($savedVal === $siteVal) {
                    $siteElement->{$handle} = $this->element->{$handle};
                }
            }
        }

        $siteElement->propagating = true;
        Craft::$app->elements->saveElement($siteElement, true, false);
    }

    public function getTranslatableFieldHandles()
    {
        // TODO: getIsTranslatable?
        // $element::isLocalized()
        return array_map(function(Field $field) {
            return $field->handle;
        }, array_filter($this->element->getFieldLayout()->getFields(), function(Field $field) {
            // TODO: support more?
            // xdebug_break();
            return $field->getIsTranslatable();
            // return $field->translationMethod === $field::TRANSLATION_METHOD_SITE;
        }));
    }

    private function getSupportedSiteIds()
    {
        // TODO: should we use editableSiteIdsForElement instead?
        $supportedSites = ElementHelper::supportedSitesForElement($this->element);

        return array_map(function($siteInfo) {
            return (int) $siteInfo['siteId'];
        }, $supportedSites);
    }

    // TODO: rename to create
    public static function getSettingsFromElement(Element $element): ?SiteSyncSettings
    {
        $field = self::getSettingsField($element);

        // Matrix
        if (!$field && method_exists($element, 'getOwner')) {
            $owner = $element->getOwner();
            $field = self::getSettingsField($owner);



            if ($field) {
                $instance = $owner->getFieldValue($field->handle);
                $instance->element = $element;

                return $instance;
                // return new SiteSyncSettings($element, [
                //     'syncEnabled' => true,
                //     'overwrite' => true,
                // ]);
            }
        }

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
