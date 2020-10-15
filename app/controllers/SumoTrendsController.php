<?php

namespace app\controllers;

use app\models\SumoTrend;

class SumoTrendsController extends \yii\web\Controller
{
    public $_url = 'http://headreach.info/web/buzzsumo/trends.json';

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionFetch() {
        $json = file_get_contents($this->_url);
        $data = json_decode($json, true);
        $data = $data['results'];
        foreach($data as &$row) {
            $row['sumo_id'] = $row['id'];
            $row['article_types'] = serialize($row['article_types']);
            $row['highlight'] = !is_null($row['highlight']) ? serialize($row['highlight']) : NULL;
            $row['article_amplifiers'] = !is_null($row['article_amplifiers']) ? serialize($row['article_amplifiers']) : NULL;
            unset($row['id']);
            //var_dump($row); exit();
            $trend = new SumoTrend();
            foreach($row as $attr => $value) {
                $trend->$attr = $value;
            }
            $trend->save();
        }
        return count($data) . ' rows inserted';
    }
}
