<?php
/**
 * Asset for form widget in ImageBehavior
 *
 * @link https://github.com/inblank/yii2-image
 * @copyright Copyright (c) 2017 Pavel Aleksandrov <inblank@yandex.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace inblank\image;

use yii\web\AssetBundle;

class ImageAsset extends AssetBundle{
    public $sourcePath = '@inblank/image/assets';
    public $css = [
        'styles.css',
    ];
    public $js = [
        'script.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
