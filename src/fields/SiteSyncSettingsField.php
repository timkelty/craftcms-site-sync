<?php
namespace timkelty\craft\sitesync\fields;

use timkelty\craft\sitesync\SiteSync;
use timkelty\craft\sitesync\assetbundles\FieldAssets;
use timkelty\craft\sitesync\models\SiteSyncSettings;
use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Db;
use yii\db\Schema;
use craft\helpers\Json;

class SiteSyncSettingsField extends Field
{
    // public $syncEnabledByDefault = true;
    // public $syncTitle = true;
    // public $syncSlug = true;
    // public $syncFields = true;

    public static function displayName(): string
    {
        return Craft::t('site-sync', 'Site-Sync Settings');
    }

    // public static function hasContentColumn(): bool
    // {
    //     return false;
    // }
    //
    // public static function supportedTranslationMethods(): array
    // {
    //     return [];
    // }

    public function beforeSave(bool $isNew): bool
    {
        // TODO: disallow multiple fields in the same layout
        return parent::beforeSave($isNew);
    }

    public function serializeValue($value, ElementInterface $element = null)
    {
        $serialized = parent::serializeValue($value, $element);

        // Never persist the overwrite setting to the DB
        unset($serialized['overwrite']);

        return $serialized;
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        if ($value instanceof SiteSyncSettings) {
            return $value;
        }

        $config = [];

        // From DB
        if (is_string($value)) {
            $config = Json::decodeIfJson($value);
        // From form submit
        } elseif (is_array($value)) {
            $config = $value;
        } else {
            // load defaults from settings?
        }

        return new SiteSyncSettings($element, $config);
    }

    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $id = Craft::$app->getView()->formatInputId($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        return Craft::$app->getView()->renderTemplate(
            'site-sync/_fields/site-sync-settings/input.twig',
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
