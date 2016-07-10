<?php

namespace app\assets;

use yii\web\AssetBundle;

class JqtreeAsset extends AssetBundle {
    public $sourcePath = '@bower/jqtree';
    public $css = [
        'jqtree.css',
    ];
    public $js = [
        'tree.jquery.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
