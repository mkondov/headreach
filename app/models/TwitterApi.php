<?php

namespace app\models;

class TwitterApi extends \yii\base\Object
{
    public $id;
    public $keyword;
    public $name;
    public $company;
    public $title;
    public $email;


    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * scrapes :D
     *
     * @param  string  $keyword email to validate
     * @return boolean if keyword provided is valid
     */
    public function getPopularProfiles($keyword) {
    	$ret = exec("python2.7 ".ASSETS_PATH."twitter_api.py ". $keyword, $output, $return_var);    	
    	return ( isset($output[0]) ? $output[0] : false );
       // return $this->keyword === $keyword;
    }

}