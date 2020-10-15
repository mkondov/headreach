<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "jobs".
 *
 * @property integer $id
 * @property integer $started_at
 * @property integer $finished_at
 *
 * @property JobsTasks[] $jobsTasks
 */
class Job extends \yii\db\ActiveRecord
{

    public $job_id;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'hr_jobs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['started_at'], 'required'],
            [['started_at', 'finished_at'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'search_term' => 'Search Term',
            'started_at' => 'Started At',
            'finished_at' => 'Finished At',
        ];
    }

    /**
     * @param $method string - method to call
     * @param $params array - array with parameters
     * @param $taskName - title of the task
     */
    public function doTask($method, $params, $taskName) {

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJobsTasks() {
        $connection = Yii::$app->db;

        $sql = "SELECT hr_job_tasks.* FROM hr_job_tasks
                INNER JOIN hr_jobs
                ON hr_job_tasks.job_id = hr_jobs.id
                WHERE hr_jobs.id = :id";
        
        $command = $connection->createCommand ( $sql );
        
        $args = array(
            ':id' => $this->job_id,
        );

        $command->bindValues ( $args );
        
        $tasks = $command->queryAll();

        if ( empty($tasks) ) {
            return array();
        }

        return $tasks;
    }

    public function getTaskInfluencers( $task_id, $page = 1, $wp_user_id, $use_paging = true ) {
        $connection = Yii::$app->db;

        $per_page = 20;
        $offset = ($page - 1) * $per_page;

        $sql = "SELECT SQL_CALC_FOUND_ROWS hr_job_task_meta.*,
                hr_influencers.*
                FROM hr_influencers
                INNER JOIN hr_job_task_meta
                ON hr_influencers.id = hr_job_task_meta.influencer_id
                WHERE hr_job_task_meta.job_task_id = :task_id
                GROUP BY hr_influencers.id
                # ORDER BY (hr_influencers.email LIKE '@') DESC, hr_job_task_meta.id
                ORDER BY hr_job_task_meta.id";

        if ( $use_paging ) {
            $sql .= " LIMIT $offset, $per_page";
        }
        
        $command = $connection->createCommand ( $sql );
        
        $args = array(
            ':task_id' => $task_id,
        );

        $command->bindValues ( $args );
        
        $influencers = $command->queryAll();

        if ( empty($influencers) ) {
            return array();
        }

        $count = $connection
                    ->createCommand('SELECT FOUND_ROWS()')
                    ->queryScalar();

        return array(
            'influencers' => $influencers,
            'count' => $count
        );
    }

    public function getInfluencersForExport( $task_id ) {
        $connection = Yii::$app->db;

        $sql = "SELECT SQL_CALC_FOUND_ROWS hr_job_task_meta.*,
                hr_influencers.*,
                hr_companies.name as company_name,
                hr_companies.domain as company_domain,
                hr_companies.json as company_json
                FROM hr_influencers
                INNER JOIN hr_job_task_meta
                    ON hr_influencers.id = hr_job_task_meta.influencer_id
                LEFT OUTER JOIN hr_companies_meta as com_meta
                    ON hr_influencers.id = com_meta.influencer_id
                LEFT OUTER JOIN hr_companies
                    ON hr_companies.id = com_meta.company_id
                WHERE hr_job_task_meta.job_task_id = :task_id
                GROUP BY hr_influencers.id
                # ORDER BY (hr_influencers.email LIKE '@') DESC, hr_job_task_meta.id
                ORDER BY hr_job_task_meta.id";
        
        $command = $connection->createCommand ( $sql );
        
        $args = array(
            ':task_id' => $task_id,
        );

        $command->bindValues( $args );
        
        $influencers = $command->queryAll();

        return $influencers;
    }

    public function getContacts( $page = 1, $wp_user_id, $use_paging = true ) {
        $connection = Yii::$app->db;

        $per_page = 20;
        $offset = ($page - 1) * $per_page;

        $sql = "SELECT SQL_CALC_FOUND_ROWS hr_job_task_meta.*,
                hr_influencers.*,
                hr_social_searches.is_charged,
                hr_social_searches.email_found,
                hr_social_searches.socials_found,
                hr_social_searches.timestamp as timestamp_searched
                FROM hr_influencers
                INNER JOIN hr_job_task_meta
                    ON hr_influencers.id = hr_job_task_meta.influencer_id
                INNER JOIN hr_social_searches
                    ON hr_social_searches.influencer_id = hr_job_task_meta.influencer_id
                WHERE hr_social_searches.wp_user_id = :wp_user_id
                GROUP BY hr_influencers.id
                ORDER BY hr_social_searches.timestamp DESC";

        if ( $use_paging ) {
            $sql .= " LIMIT $offset, $per_page";
        }
        
        $command = $connection->createCommand ( $sql );
        
        $args = array(
            ':wp_user_id' => $wp_user_id,
        );

        $command->bindValues ( $args );
        
        $contacts = $command->queryAll();

        if ( empty($contacts) ) {
            return array();
        }

        $count = $connection
                    ->createCommand('SELECT FOUND_ROWS()')
                    ->queryScalar();

        return array(
            'contacts' => $contacts,
            'count' => $count
        );
    }

    // Note - this is a quite heavy query
    public function getContactsWithCompany( $page = 1, $wp_user_id, $use_paging = true ) {
        $connection = Yii::$app->db;

        $per_page = 20;
        $offset = ($page - 1) * $per_page;

        $sql = "SELECT SQL_CALC_FOUND_ROWS hr_job_task_meta.*,
                hr_influencers.*,
                hr_social_searches.is_charged,
                hr_social_searches.email_found,
                hr_social_searches.socials_found,
                hr_social_searches.timestamp as timestamp_searched,
                hr_companies.name as company_name,
                hr_companies.domain as company_domain,
                hr_companies.json as company_json
                FROM hr_influencers
                INNER JOIN hr_job_task_meta
                    ON hr_influencers.id = hr_job_task_meta.influencer_id
                INNER JOIN hr_social_searches
                    ON hr_social_searches.influencer_id = hr_job_task_meta.influencer_id
                LEFT OUTER JOIN hr_companies_meta as com_meta
                    ON hr_influencers.id = com_meta.influencer_id
                LEFT OUTER JOIN hr_companies
                    ON hr_companies.id = com_meta.company_id
                WHERE hr_social_searches.wp_user_id = :wp_user_id
                # AND com_meta.influencer_id = hr_influencers.id
                GROUP BY hr_influencers.id
                ORDER BY hr_social_searches.timestamp DESC";

        if ( $use_paging ) {
            $sql .= " LIMIT $offset, $per_page";
        }
        
        $command = $connection->createCommand ( $sql );
        
        $args = array(
            ':wp_user_id' => $wp_user_id,
        );

        $command->bindValues ( $args );
        
        $contacts = $command->queryAll();

        if ( empty($contacts) ) {
            return array();
        }

        $count = $connection
                    ->createCommand('SELECT FOUND_ROWS()')
                    ->queryScalar();

        return array(
            'contacts' => $contacts,
            'count' => $count
        );
    }

    public function getJobsData( $wp_user_id, $page ) {
        $connection = Yii::$app->db;

        $per_page = 20;
        $offset = ($page - 1) * $per_page;

        $sql = "SELECT SQL_CALC_FOUND_ROWS hr_jobs.*, hr_job_tasks.id as task_id
                FROM hr_jobs
                LEFT OUTER JOIN hr_job_tasks
                ON hr_jobs.id = hr_job_tasks.job_id
                WHERE hr_jobs.wp_user_id = :wp_user_id
                AND hr_jobs.total_results != 0
                GROUP BY hr_jobs.id
                ORDER BY hr_jobs.id DESC
                LIMIT $offset, $per_page";
        
        $command = $connection->createCommand ( $sql );
        
        $args = array(
            ':wp_user_id' => $wp_user_id,
        );

        $command->bindValues ( $args );
        
        $jobs = $command->queryAll();

        if ( empty($jobs) ) {
            return array();
        }

        $count_with_results = $connection
                    ->createCommand('SELECT FOUND_ROWS()')
                    ->queryScalar();

        // Get the real count
        $sql_count = "SELECT SQL_CALC_FOUND_ROWS hr_jobs.*
                FROM hr_jobs
                WHERE hr_jobs.wp_user_id = :wp_user_id";
        
        $command = $connection->createCommand ( $sql_count );
        
        $args = array(
            ':wp_user_id' => $wp_user_id,
        );

        $command->bindValues ( $args );
        $command->queryAll();

        $count_real = $connection
                    ->createCommand('SELECT FOUND_ROWS()')
                    ->queryScalar();

        return array(
            'jobs' => $jobs,
            'count_with_results' => $count_with_results,
            'count' => $count_real
        );
    }

    public function startTask($name) {
        $task = new JobTask();
        $task->job_id = $this->id;
        $task->step = $name;
        $task->started_at = time();
        $task->save(false);
        return $task->id;
    }

    public function finishTask() {
        $task = JobTask::find()->where(['job_id' => $this->id, 'finished_at' => null])->one();

        if ( empty($task) ) {
            return false;
        }

        $task->finished_at = time();
        $task->save(false);
    }

}