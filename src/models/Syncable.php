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
    public $sources = self::SOURCE_ALL;
    public $element;

    const SOURCE_ALL = '*';
    const SOURCE_TITLE = 'title';
    const SOURCE_SLUG = 'slug';
    const SOURCE_FIELDS = 'fields';

    public static function supportedSources(): array
    {
        return [
            self::SOURCE_ALL,
            self::SOURCE_TITLE,
            self::SOURCE_SLUG,
            self::SOURCE_FIELDS,
        ];
    }

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

        $siteElement = Craft::$app->getElements()->getElementById($this->element->id, get_class($this->element), $siteId);
        $updates = $this->getUpdatesForElement($siteElement);

        if (!$updates) {
            return false;
        }

        Craft::configure($siteElement, $updates);

        // Don't bother validating custom fields for other sites
        $siteElement->setScenario(Element::SCENARIO_ESSENTIALS);
        $siteElement->propagating = true;

        return Craft::$app->elements->saveElement($siteElement, true, false);
    }

    public function rules()
    {
        $rules = [
            [['enabled', 'overwrite', 'element', 'sources'], 'required'],
            [
                'sources',
                'in',
                'range' => self::supportedSources(),
            ],
        ];

        return $rules;
    }

    private function hasSource(string $source): bool
    {
        return $this->sources === self::SOURCE_ALL || in_array($source, $this->sources);
    }

    private function getUpdatesForElement(Element $siteElement): array
    {
        $savedElement = Craft::$app->getElements()->getElementById($this->element->id, get_class($this->element), $this->element->siteId);
        $updates = [];

        if ($this->hasSource(self::SOURCE_FIELDS)) {
            if ($this->overwrite) {
                $updates = $this->element->getFieldValues();
            } else {
                foreach ($this->getTranslatableFieldHandles() as $handle) {
                    if ($savedElement->getSerializedFieldValues([$handle]) === $siteElement->getSerializedFieldValues([$handle])) {
                        $updates[$handle] = $this->element->{$handle};
                    }
                }
            }
        }

        if ($this->hasSource(self::SOURCE_TITLE)) {
            if ($this->overwrite || $savedElement->title === $siteElement->title) {
                $updates['title'] = $this->element->title;
            }
        }

        if ($this->hasSource(self::SOURCE_SLUG)) {
            if ($this->overwrite || $savedElement->slug === $siteElement->slug) {
                $updates['slug'] = $this->element->slug;
            }
        }

        return $updates;
    }

    private function getTranslatableFieldHandles()
    {
        return array_map(function(Field $field) {
            return $field->handle;
        }, array_filter($this->element->getFieldLayout()->getFields(), function(Field $field) {
            return $field->translationMethod === $field::TRANSLATION_METHOD_SITE;

            // TODO: does this make more sense?
            // return $field->getIsTranslatable();
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
