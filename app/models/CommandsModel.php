<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\KeywordInputForm;
use app\models\Keyword;
use app\models\LinkedinScraper;
use app\models\EmailHunterApi;
use app\models\FullContactApi;
use app\models\TwitterApi;
use app\models\Influencer;
use app\models\ImageWorker;
use app\models\FakeData;
use yii\db\IntegrityException;

class CommandsModel extends ActiveRecord {
	
	/**
	 * @inheritdoc
	 */
	public function getId() {
		return $this->_id;
	}
	
	/**
	 *
	 * @return string the name of the table associated with this ActiveRecord class.
	 */
	public static function tableName() {
		return 'hr_commands';
	}

}