<?php

namespace app\models;

use yii\db\ActiveRecord;

class SocialSearches extends ActiveRecord
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
    	return 'hr_social_searches';
    }
    
}