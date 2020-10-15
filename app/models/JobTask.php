<?php

namespace app\models;

use Yii;
use app\models\JobTaskMeta;

/**
 * This is the model class for table "jobs_tasks".
 *
 * @property integer $id
 * @property string $step
 * @property integer $started_at
 * @property integer $finished_at
 * @property integer $job_id
 *
 * @property Jobs $job
 */
class JobTask extends \yii\db\ActiveRecord {
	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return 'hr_job_tasks';
	}
	
	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [ 
				[ 
						[ 
								'step',
								'started_at',
								'job_id' 
						],
						'required' 
				],
				[ 
						[ 
								'started_at',
								'finished_at',
								'job_id' 
						],
						'integer' 
				],
				[ 
						[ 
								'step' 
						],
						'string',
						'max' => 255 
				],
				[ 
						[ 
								'job_id' 
						],
						'exist',
						'skipOnError' => true,
						'targetClass' => Job::className (),
						'targetAttribute' => [ 
								'job_id' => 'id' 
						] 
				] 
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [ 
				'id' => 'ID',
				'step' => 'Step',
				'started_at' => 'Started At',
				'finished_at' => 'Finished At',
				'job_id' => 'Job ID' 
		];
	}

	public function addMeta( $influencer_id, $url = '', $keyword = '' ) {
		
		$meta = new JobTaskMeta();
		$meta->job_task_id = $this->id;
		$meta->influencer_id = $influencer_id;
		$meta->url = $url;
		$meta->keyword = $keyword;
		$meta->insert();

		$lastInsertID = Yii::$app->db->getLastInsertID();

		return $lastInsertID;
	}

	public function addInfluencerMap( $data ) {
		
		$meta = new InfluencerMap();
		$meta->influencer_id = '';
		$meta->job_task_meta_id = '';
		$meta->domain = '';
		// $meta->keyword = '';
		$meta->insert();

		$lastInsertID = Yii::$app->db->getLastInsertID();

		return $lastInsertID;
	}
	
	/**
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getJob() {
		return $this->hasOne ( Job::className (), [ 
				'id' => 'job_id' 
		] );
	}
}