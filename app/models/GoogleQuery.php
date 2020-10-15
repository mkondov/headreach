<?php

namespace app\models;

use yii\db\ActiveRecord;

class GoogleQuery extends ActiveRecord
{
    
    /**
     * @inheritdoc
     */
    public function getId()
    {
    	return $this->id;
    }
    
    /**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
    	return 'hr_google_queries';
    }

    public function getCachedData( $url ) {
        $model = new GoogleQuery();

        $md5_string = md5( $url );

        $db_record = $model->findOne(array(
                'md5_string' => $md5_string
            ));

        if ( $db_record ) {
            $data = $db_record->api_response;

            if ( $data != false OR !empty($data) ) {
                return array(
                    'found' => true,
                    'results' => json_decode( $data, true ),
                    'rerun' => false,
                );
            } else {
                return array(
                    'found' => true,
                    'results' => false,
                    'rerun' => false,
                );
            }

        }

        return array(
            'found' => false,
            'results' => false,
            'rerun' => false,
        );
    }

    public function storeData( $url, $data ) {
        $model = new GoogleQuery();

        $model->url = $url;
        $model->api_response = json_encode( $data );
        $model->last_updated = time();
        $model->md5_string = md5( $url );
        $model->insert();

        return true;
    }
    
}