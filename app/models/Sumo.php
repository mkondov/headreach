<?php

namespace app\models;

use Yii;

class Sumo {

    public static function getArticleUrl($keyword) {
        $api_key = Yii::$app->params['buzzsumo_api_key'];
        return 'http://api.buzzsumo.com/search/influencers.json?q='.urlencode($keyword).'&result_type=relevancy&api_key='.$api_key;
    }
    
}