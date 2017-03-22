<?php

namespace app\assets;

use yii\web\AssetBundle;

class MaskmoneyAsset extends AssetBundle {
    public $js = [
        'https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
