<?php

namespace timkelty\craft\sitesync;

use craft\web\assets\cp\CpAsset;

class FieldAssets extends \craft\web\AssetBundle
{
    public function init()
    {
        $this->sourcePath = "@timkelty/craft/sitesync/resources/web";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = ['field.js'];
        $this->css = ['field.css'];

        parent::init();
    }
}
