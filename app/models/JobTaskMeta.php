<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class JobTaskMeta extends ActiveRecord
{

    public $task_id;
    
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
    	return 'hr_job_task_meta';
    }

    public function getEntries( $type = 'all' ) {
        $connection = Yii::$app->db;

        $sql = "SELECT hr_job_task_meta.* FROM hr_job_task_meta
                WHERE hr_job_task_meta.job_task_id = :id";
        
        $command = $connection->createCommand ( $sql );
        
        $args = array(
            ':id' => $this->task_id,
        );

        $command->bindValues ( $args );
        
        $tasks = $command->queryAll();

        if ( empty($tasks) ) {
            return false;
        }

        if ( $type == 'single' ) {
            return $tasks[0];
        }

        return $tasks;
    }
    
}