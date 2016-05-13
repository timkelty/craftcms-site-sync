<?php
namespace Craft;

class LocaleSyncVariable
{
	public function getLocaleInputOptions($locales = null, $exclude = [])
	{
		return craft()->localeSync->getLocaleInputOptions($locales, $exclude);
	}
}
