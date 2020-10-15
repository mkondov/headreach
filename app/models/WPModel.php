<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\filters\AccessControl;
use yii\web\Controller;

class WPModel extends ActiveRecord {

	public static function getDb() {
		return Yii::$app->get('db_wp'); // second database
	}

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
		return 'wp_ext_credits';
	}

	public function getCredits( $wp_user_id ) {

		$connection = WPModel::getDb();

		$sql = "SELECT wp_ext_credits.*,
				wp_ext_credits_log.credit_amount_earned
				FROM wp_ext_credits
				INNER JOIN wp_ext_credits_log
				ON wp_ext_credits_log.credit_entry_id = wp_ext_credits.id
				WHERE wp_ext_credits.wp_user_id = :wp_user_id
				ORDER BY wp_ext_credits_log.id DESC
				";
		
		$command = $connection->createCommand ( $sql );
		
		$args = array(
			':wp_user_id' => $wp_user_id,
		);

		$command->bindValues ( $args );
		
		$credits = $command->queryAll();

		return $credits;
	}

}