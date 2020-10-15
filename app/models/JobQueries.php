<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class JobQueries extends ActiveRecord
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
        return 'hr_job_queries';
    }

    public function getJobQueries( $job_id ) {
        $model = new JobQueries();

        $args = array(
            'job_id' => $job_id
        );

        $queries = $model->findAll( $args );

        if ( $queries ) {
            return $queries;
        }

        return false;
    }
    
    
}