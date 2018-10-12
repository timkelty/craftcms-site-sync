<?php
namespace timkelty\craft\sitesync\services;

use Craft;
use craft\base\Element;
use craft\helpers\ElementHelper;
use craft\base\Component;
use timkelty\craft\sitesync\SiteSync;

class Content extends Component
{
    public function syncToSites(Element $element, array $siteIds = null)
    {
        // TODO: Check if element is even multisite enabled first
        $supportedSites = ElementHelper::supportedSitesForElement($element);
        $siteIds = $siteIds ?? array_map(function($siteInfo) {
            return (int) $siteInfo['siteId'];
        }, $supportedSites);

        foreach ($siteIds as $siteId) {
            $this->syncToSite($element, $siteId);
        }
    }

    public function syncToSite(Element $element, int $siteId)
    {
        // TODO: Check if element is even multisite enabled first
        if ($element->siteId === $siteId) {
            return;
        }

        $siteElement = Craft::$app->getElements()->getElementById($element->id, get_class($element), $siteId);
        $siteElement->title = $element->title;
        $siteElement->slug = $element->slug;
        $siteElement->setFieldValues($element->getFieldValues());
        Craft::$app->getElements()->updateElementSlugAndUri($siteElement, false, false);
        Craft::$app->getContent()->saveContent($siteElement);
    }
}
