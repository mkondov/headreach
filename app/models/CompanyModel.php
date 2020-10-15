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

class CompanyModel extends ActiveRecord {
	
	/**
	 * @inheritdoc
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 *
	 * @return string the name of the table associated with this ActiveRecord class.
	 */
	public static function tableName() {
		return 'hr_companies';
	}

	public function getDBCompany( $domain ) {
		$company_model = new CompanyModel();

		$args = array (
			'domain' => $domain,
		);

		$result = $company_model->findOne( $args );

		if ( empty($result) ) {
			return false;
		}

		return json_decode( $result['json'] );
	}

	public function insertCompany( $data ) {

		$company_model = new CompanyModel();

		$domain = $data->website;
		$domain = parse_url( $domain );

		if ( isset($domain['host']) ) {
			$domain = $domain['host'];
		} else {
			$domain = $domain['path'];
		}

		$company_model->name = $data->name;
		$company_model->domain = $domain;
		$company_model->json = json_encode($data);

		$args = array (
			'name' => $data->name,
			'domain' => $domain,
		);

		$result = $company_model->findAll( $args );

		// Company found
		if ( !empty($result) ) {
			return array(
				'id' => $result[0]->id,
				'company' => $result[0]->name,
				'domain' => $result[0]->domain,
			);
		}

		$company_model->insert();

		$id = Yii::$app->db->getLastInsertID();

		return array(
			'id' => $id,
			'company' => $data->name,
			'domain' => $domain,
		);
	}

	public static function getFormattedCompanyData( $company_json ) {

		$company_data = json_decode( $company_json );

		$return = array();

		$fields = array(
			'social_profiles', 'email_addresses', 'tel_numbers',
		);

		foreach ($fields as $field) {
			if ( isset($company_data->$field) AND !empty($company_data->$field) ) {
				$return[$field] = implode(',', $company_data->$field);
			} else {
				$return[$field] = '';
			}
		}

		return $return;
	}

}