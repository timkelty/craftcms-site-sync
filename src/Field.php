<?php

namespace timkelty\craft\sitesync;

use Craft;
use craft\base\ElementInterface;
use timkelty\craft\sitesync\models\Syncable;

class Field extends \craft\base\Field
{
    public $defaults;

    public static function displayName(): string
    {
        return Craft::t('site-sync', 'Site-Sync Settings');
    }

    public static function hasContentColumn(): bool
    {
        return false;
    }

    public function serializeValue($value, ElementInterface $element = null)
    {
        return parent::serializeValue(null, $element);
    }

    public function normalizeValue($value, ElementInterface $element = null): Syncable
    {
        if ($value instanceof Syncable) {
            return $value;
        }

        $syncable = new Syncable($value);
        $syncable->element = $element;

        if (!$syncable->validate()) {
            Craft::error(
                Craft::t('site-sync', 'Syncable failed validation: ') . print_r($syncable->getErrors(), true),
                __METHOD__
            );
        }

        return $syncable;
    }

    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $syncable = new Syncable($this->defaults);
        $syncable->element = $element;

        return $this->getFieldHtml($this->handle, $syncable);
    }

    public function getSettingsHtml(): string
    {
        $syncable = new Syncable($this->defaults);
        $defaultsHtml = $this->getFieldHtml('defaults', $syncable, false);

        return Craft::$app->getView()->renderTemplate('site-sync/_field/settings.twig', [
            'defaultsHtml' => $defaultsHtml,
        ]);
    }

    private function getFieldHtml(string $name, Syncable $value): string
    {
        $id = Craft::$app->getView()->formatInputId($name);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        Craft::$app->getView()->registerAssetBundle(FieldAssets::class);
        Craft::$app->getView()->registerJs("$('#{$namespacedId}').siteSyncField();");

        return Craft::$app->getView()->renderTemplate('site-sync/_field/input.twig', [
            'name' => $name,
            'id' => $id,
            'value' => $value,
        ]);
    }
}
