<?php

namespace app\models;
use Yii;

class LinkedinScraper extends \yii\base\Object
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
    public function scrape( $keyword ) {
        // $ret = exec("python2.7 " . ASSETS_PATH . "linkedin_scraper_2.py '" . $keyword . "'", $output, $return_var);
        $ret = exec("python2.7 " . __DIR__.'/../assets/' . "linkedin_scraper_2.py '" . $keyword . "'", $output, $return_var);
        return ( isset($output[0]) ? $output[0] : false );
    }

    public function scrapeName( $first_name, $last_name, $max_results ) {
        // $ret = exec('python2.7 ' . ASSETS_PATH . 'linkedin_scraper_2_name.py "' . $first_name . ' ' . $last_name . '"', $output, $return_var);
        $ret = exec('python2.7 ' . ASSETS_PATH . 'linkedin_scraper_2_name.py ' . $first_name . ' ' . $last_name . ' ' . $max_results, $output, $return_var);
    	return ( isset($output[0]) ? $output[0] : false );
    }

    public function extractNames( $url ) {
        $ret = exec('python2.7 ' . ASSETS_PATH . 'name_extractor.py ' . $url, $output, $return_var);
        return ( isset($output[0]) ? $output[0] : false );
    }

}