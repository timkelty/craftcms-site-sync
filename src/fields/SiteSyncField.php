<?php
/**
 * Site Sync plugin for Craft CMS 3.x
 *
 * Sync content to other sites on element save.
 *
 * @link      https://github.com/timkelty
 * @copyright Copyright (c) 2018 Tim Kelty
 */

namespace timkelty\craft\sitesync\fields;

use timkelty\craft\sitesync\SiteSync;
use timkelty\craft\sitesync\assetbundles\FieldAssets;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Db;
use yii\db\Schema;
use craft\helpers\Json;

class SiteSyncField extends Field
{
    public $syncOptions = [
        [
            'label' => 'Do Not Sync',
            'value' => null
        ],
        // [
        //     'label' => 'Sync Identical Content',
        //     'value' => 'identical'
        // ],
        [
            'label' => 'Sync All Content',
            'value' => 'all'
        ]
    ];

    public static function displayName(): string
    {
        return Craft::t('site-sync', 'Site-Sync Settings');
    }

    public function getInputHtml($value, ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate('_includes/forms/select', [
            'name' => $this->handle,
            'value' => $value,
            'options' => $this->syncOptions,
        ]);
    }
}
