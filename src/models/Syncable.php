<?php
namespace timkelty\craft\sitesync\models;

use Craft;
use craft\events\ModelEvent;
use craft\base\Element;
use craft\base\Field;
use craft\helpers\ElementHelper;
use timkelty\craft\sitesync\Field as SiteSyncField;

class Syncable extends \craft\base\Model
{
    public $enabled = false;
    public $overwrite = false;
    public $element;

    public static function beforeElementSaveHandler(ModelEvent $event)
    {
        $element = $event->sender;

        if (!$element->isLocalized() || $element->propagating) {
            return;
        }

        $syncable = self::findFieldData($element);

        if (!$syncable || !$syncable->enabled) {
            return;
        }

        // Set element explicily here for when we get field data from an owner
        // element (Entry), but are syncing a child element (Matrix Block)
        $syncable->element = $element;

        $syncable->propagateToSites();
    }

    private static function findFieldData(Element $element): ?Syncable
    {
        $layout = $element->getFieldLayout();
        $fields = array_filter($layout->getFields(), function($field) {
            return $field instanceof SiteSyncField;
        });
        $field = array_shift($fields);

        // Do owners have a field? (e.g. Matrix blocks)
        if (!$field && method_exists($element, 'getOwner')) {
            return self::findFieldData($element->getOwner());
        }

        // No syncable fields
        if (!$field) {
            return null;
        }

        return $element->getFieldValue($field->handle);
    }

    public function propagateToSites()
    {
        foreach ($this->getSupportedSiteIds() as $siteId) {
            $this->propagateToSite($siteId);
        }
    }

    public function propagateToSite(int $siteId): bool
    {
        if (!$this->enabled || $this->element->siteId === $siteId) {
            return false;
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

        // TODO: set scenario?
        // $siteElement->setScenario(Element::SCENARIO_ESSENTIALS);

        $siteElement->propagating = true;
        return Craft::$app->elements->saveElement($siteElement, true, false);
    }

    private function getTranslatableFieldHandles()
    {
        return array_map(function(Field $field) {
            return $field->handle;
        }, array_filter($this->element->getFieldLayout()->getFields(), function(Field $field) {
            return $field->getIsTranslatable();
            // TODO: does this make more sense?
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
}
