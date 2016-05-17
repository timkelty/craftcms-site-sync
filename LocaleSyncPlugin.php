<?php
namespace Craft;

/**
 * Locale Sync Plugin for Craft CMS.
 *
 * Sync content to locales on element save.
 *
 * @author    Tim Kelty
 * @copyright Copyright (c) 2016 Tim Kelty
 *
 * @link      http://fusionary.com/
 * @since     1.0.0
 */
class LocaleSyncPlugin extends BasePlugin
{

    /**
     * @return mixed
     */
    public function init()
    {
        craft()->templates->hook('cp.entries.edit.right-pane', function (&$context) {
            return craft()->localeSync->getElementOptionsHtml($context['entry']);
        });

        craft()->on('elements.onBeforeSaveElement', function (Event $event) {
            return craft()->localeSync->syncElementContent($event, craft()->request->getPost('localeSync'));
        });
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return Craft::t('Locale Sync');
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return Craft::t('Sync content to locales on element save.');
    }

    /**
     * @return string
     */
    public function getDocumentationUrl()
    {
        return 'https://github.com/timkelty/localesync/blob/master/README.md';
    }

    /**
     * @return string
     */
    public function getReleaseFeedUrl()
    {
        return 'https://raw.githubusercontent.com/timkelty/localesync/master/releases.json';
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0.0';
    }

    /**
     * @return string
     */
    public function getSchemaVersion()
    {
        return '1.0.0';
    }

    /**
     * @return string
     */
    public function getDeveloper()
    {
        return 'Tim Kelty';
    }

    /**
     * @return string
     */
    public function getDeveloperUrl()
    {
        return 'http://fusionary.com/';
    }

    /**
     * @return bool
     */
    public function hasCpSection()
    {
        return false;
    }

    public function onBeforeInstall()
    {
    }

    public function onAfterInstall()
    {
    }

    public function onBeforeUninstall()
    {
    }

    public function onAfterUninstall()
    {
    }

    /**
     * @return array
     */
    protected function defineSettings()
    {
        $locales = craft()->i18n->getSiteLocales();
        $localeDefaults = [
            'enabled' => true,
            'overwrite' => false,
            'targets' => [],
        ];
        $settings = [
            'localeDefaults' => [
                'type' => AttributeType::Mixed,
            ],
        ];

        foreach ($locales as $locale) {
            $settings['localeDefaults']['default'][$locale->id] = $localeDefaults;
        }

        return $settings;
    }

    /**
     * @return mixed
     */
    public function getSettingsHtml()
    {
        return craft()->templates->render('localesync/_cp/settings', [
            'settings' => $this->getSettings(),
            'localeInputOptions' => craft()->localeSync->getLocaleInputOptions(),
        ]);
    }
}
