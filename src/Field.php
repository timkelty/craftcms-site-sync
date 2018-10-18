<?php

namespace timkelty\craft\sitesync;

use Craft;
use craft\base\ElementInterface;
use timkelty\craft\sitesync\models\Syncable;

class Field extends \craft\base\Field
{
    public static function displayName(): string
    {
        return Craft::t('site-sync', 'Site-Sync Settings');
    }

    public static function hasContentColumn(): bool
    {
        return false;
    }

    public function beforeSave(bool $isNew): bool
    {
        // TODO: disallow multiple fields in the same layout
        return parent::beforeSave($isNew);
    }

    public function serializeValue($value, ElementInterface $element = null)
    {
        unset($value['element']);

        return parent::serializeValue($value, $element);
    }

    public function normalizeValue($value, ElementInterface $element = null)
    {
        if ($value instanceof Syncable) {
            return $value;
        }

        $config = array_merge($value ?? [], [
            'element' => $element,
        ]);

        // TODO: load defaults from field settings
        // TODO: validate before returning

        return new Syncable($config);
    }

    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $id = Craft::$app->getView()->formatInputId($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        return Craft::$app->getView()->renderTemplate(
            'site-sync/_field/input.twig',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'id' => $id,
                'namespacedId' => $namespacedId,
            ]
        );
    }
}
