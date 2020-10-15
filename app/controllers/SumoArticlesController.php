<?php

namespace app\controllers;

use app\models\SumoArticle;

class SumoArticlesController extends \yii\web\Controller
{
    public $_url = 'http://headreach.info/web/buzzsumo/articles.json';

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
            $row['article_amplifier_images'] = serialize($row['article_amplifier_images']);
            $row['article_amplifiers'] = serialize($row['article_amplifiers']);
            $row['highlight'] = !is_null($row['highlight']) ? serialize($row['highlight']) : NULL;
            unset($row['id']);
            //var_dump($row); exit();
            $article = new SumoArticle();
            foreach($row as $attr => $value) {
                $article->$attr = $value;
            }
            $article->save();
        }
        return count($data) . ' rows inserted';
    }

}
