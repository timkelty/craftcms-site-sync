<?php
namespace Craft;

class LocaleSyncService extends BaseApplicationComponent
{
	public function getElementOptionsHtml(BaseElementModel $element)
	{
		$isNew = $element->id === null;

		if ($isNew) {
			return;
		}

		$locales = array_keys($element->getLocales());
		$settings = craft()->plugins->getPlugin('localeSync')->getSettings();

		$targets = [
			'options' => craft()->localeSync->getLocaleInputOptions($locales),
			'values' => isset($settings->defaultTargets[$element->locale]) ? $settings->defaultTargets[$element->locale] : [],
		];

		return craft()->templates->render('localesync/_cp/entriesEditRightPane', [
			'targets' => $targets,
			'enabled' => (bool) count($targets['values']),
		]);
	}

	public function getLocaleInputOptions($locales = null)
	{
		$locales = $locales ?: craft()->i18n->getSiteLocales();

		return array_map(function($locale) {
			if (!$locale instanceof LocaleModel) {
				$locale = craft()->i18n->getLocaleById($locale);
			}

			if ($locale instanceof LocaleModel) {
				$locale = [
					'label' => $locale->name,
					'value' => $locale->id,
				];
			}

			return $locale;
		}, $locales);
	}

	public function syncElementContent(Event $event, $elementSettings)
	{
		$pluginSettings = craft()->plugins->getPlugin('localeSync')->getSettings();
		$element = $event->params['element'];

		if ($event->params['isNewElement'] || empty($elementSettings['enabled'])) {
			return;
		}

		$elementBeforeSave = craft()->elements->getElementById($element->id, $element->elementType, $element->locale);
		$locales = $elementBeforeSave->getLocales();
		$targets = [];

		if ($elementSettings === null && isset($pluginSettings->defaultTargets[$element->locale])) {
			$targets = $pluginSettings->defaultTargets[$element->locale];
		} elseif (!empty($elementSettings['targets'])) {
			$targets = $elementSettings['targets'];
		};

		foreach ($locales as $localeId => $localeInfo)
		{
			$localizedElement = craft()->elements->getElementById($element->id, $element->elementType, $localeId);

			if (
				!$localizedElement ||
				$element->locale === $localeId ||
				($targets !== '*' && !in_array($localeId, $targets))
			) {
				continue;
			}

			foreach ($localizedElement->getFieldLayout()->getFields() as $fieldLayoutField)
			{
				$field = $fieldLayoutField->getField();
				$matches = $elementBeforeSave->content->{$field->handle} === $localizedElement->content->{$field->handle};

				if (
					!$field->translatable ||
					($elementSettings !== null && $elementSettings['content'] === 'matching' && !$matches)
				) {
					continue;
				}

				$localizedElement->content->{$field->handle} = $element->content->{$field->handle};
			}

			craft()->content->saveContent($localizedElement, false, false);
		}
	}
}
