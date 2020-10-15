<?php

namespace app\controllers;

use app\models\SumoInfluencer;

class SumoInfluencersController extends \yii\web\Controller
{
    public $_url = 'http://headreach.info/web/buzzsumo/influencers.json';

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
            $row['person_type'] = serialize($row['person_type']);
            $row['highlight'] = !is_null($row['highlight']) ? serialize($row['highlight']) : NULL;
            unset($row['id']);
            //var_dump($row); exit();
            $influencer = new SumoInfluencer();
            foreach($row as $attr => $value) {
                $influencer->$attr = $value;
            }
            $influencer->save();
        }
        return count($data) . ' rows inserted';
    }
}
