<?php

namespace app\models;

class FullContactApi extends \yii\base\Object
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
    public function getPersonInfo($email) {
    	$ret = exec("python2.7 ".ASSETS_PATH."full_contact_person_api.py ". $email,$output,$return_var);
    	
    	return ( isset($output[0]) ? $output[0] : false );
       // return $this->keyword === $keyword;
    }
    
    /**
     * scrapes :D
     *
     * @param  string  $keyword email to validate
     * @return boolean if keyword provided is valid
     */
    public function getCompanyInfo( $domain ) {

        $configs = parse_ini_file( '/home/dogostz/webapps/headreach/app/config.ini' );
        // $configs = parse_ini_file( 'C:\xampp\htdocs\headreach-app\config.ini' );
        $api_key = $configs['full_contact_api'];

        $url = 'https://api.fullcontact.com/v2/company/lookup.json?domain=' . urlencode($domain) . '&apiKey=' . $api_key;
        $data = @file_get_contents( $url );
        
        if ( empty($data) ) {
            return false;
        }

        $contents = json_decode( $data, true );

        $social_profiles = array();
        $email_addresses = array();
        $tel_numbers = array();
        $name = '';
        $website = '';

        if ( isset($contents['organization']['name']) ) {
            $name = $contents['organization']['name'];
        }

        if ( isset($contents['website']) ) {
            $website = $contents['website'];
        }

        if ( isset($contents['socialProfiles']) ) {
            foreach ($contents['socialProfiles'] as $social_profile) {
                if ( isset($social_profile['url']) ) {
                    $social_profiles[] = $social_profile['url'];
                }
            }
        }

        if ( isset($contents['organization']['contactInfo']['emailAddresses']) ) {
            foreach ($contents['organization']['contactInfo']['emailAddresses'] as $email_address) {
                if ( isset($email_address['value']) ) {
                    $email_addresses[] = $email_address['value'];
                }
            }   
        }

        if ( isset($contents['organization']['contactInfo']['phoneNumbers']) ) {
            foreach ($contents['organization']['contactInfo']['phoneNumbers'] as $phone_number) {
                if ( isset($phone_number['value']) ) {
                    $tel_numbers[] = $phone_number['number'];
                }
            }   
        }

        $return = array(
            'social_profiles' => $social_profiles,
            'name' => $name,
            'website' => $website,
            'email_addresses' => $email_addresses,
            'tel_numbers' => $tel_numbers,
            'json_response' => $data,
        );

        return json_encode($return);
        
        /*
    	$ret = exec("python2.7 ".ASSETS_PATH."full_contact_company_api.py ". $domain,$output,$return_var);
    	 
    	return ( isset($output[0]) ? $output[0] : false );
    	// return $this->keyword === $keyword;
        */
    }
    
    public function getTwitterInfo( $twitter_handle ) {
    	$ret = exec("python2.7 ".ASSETS_PATH."full_contact_twitter_api.py ". $twitter_handle,$output,$return_var);
    
    	return ( isset($output[0]) ? $output[0] : false );
    	// return $this->keyword === $keyword;
    }

}