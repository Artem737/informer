<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Class BootstrapFourAsset
 * @package app\assets
 */
class BootstrapFourAsset extends AssetBundle
{
    public $sourcePath = '@vendor/twbs/bootstrap/dist/';
    public $css = [
        'css/bootstrap.css',
    ];
    public $js = [
        'js/bootstrap.js',
    ];
    public $depends = [
    ];
}