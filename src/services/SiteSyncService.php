<?php
/**
 * Site Sync plugin for Craft CMS 3.x
 *
 * Sync content to other sites on element save.
 *
 * @link      https://github.com/timkelty
 * @copyright Copyright (c) 2018 Tim Kelty
 */

namespace timkelty\sitesync\services;

use timkelty\sitesync\SiteSync;

use Craft;
use craft\base\Component;

/**
 * SiteSyncService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Tim Kelty
 * @package   SiteSync
 * @since     1.0.0
 */
class SiteSyncService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     SiteSync::$plugin->siteSyncService->exampleService()
     *
     * @return mixed
     */
    public function exampleService()
    {
        $result = 'something';
        // Check our Plugin's settings for `someAttribute`
        if (SiteSync::$plugin->getSettings()->someAttribute) {
        }

        return $result;
    }
}
