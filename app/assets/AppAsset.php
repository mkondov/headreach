<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css',
        'css/foundation.css',
        'css/app.css',
    ];
    public $js = [
        'js/lib/jquery-ui.min.js',
        'js/lib/what-input.js',
        'js/lib/jquery.stickytableheaders.min.js',
        'js/lib/jquery.autocomplete.js',
        'js/foundation.min.js',
        'js/app.js',
        'js/main.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        // 'yii\bootstrap\BootstrapAsset',
    ];
}