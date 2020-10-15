<?php

namespace app\controllers;

use app\models\SumoSharedLink;

class SumoSharedLinksController extends \yii\web\Controller
{
    public $_url = 'http://headreach.info/web/buzzsumo/shared_links.json';

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionFetch() {
        $json = file_get_contents($this->_url);
        $data = json_decode($json, true);
        //$data = $data['results'];
        foreach($data as &$row) {
            $row['sumo_id'] = $row['id'];
            $row['highlight'] = !is_null($row['highlight']) ? serialize($row['highlight']) : NULL;
            unset($row['id']);
            //var_dump($row); exit();
            $link = new SumoSharedLink();
            foreach($row as $attr => $value) {
                $link->$attr = $value;
            }
            $link->save();
        }
        return count($data) . ' rows inserted';
    }

}
