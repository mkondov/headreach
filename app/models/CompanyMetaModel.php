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

class CompanyMetaModel extends ActiveRecord {
	
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
		return 'hr_companies_meta';
	}

	public function getUserCompany( $influencer_id ) {
		$connection = Yii::$app->db;

        $sql = "SELECT hr_companies.*,
                hr_companies_meta.influencer_id
                FROM hr_companies
                INNER JOIN hr_companies_meta
                ON hr_companies.id = hr_companies_meta.company_id
                WHERE hr_companies_meta.influencer_id = :influencer_id";
        
        $command = $connection->createCommand ( $sql );
        
        $args = array(
            ':influencer_id' => $influencer_id,
        );

        $command->bindValues ( $args );
        
        $company = $command->queryAll();

        if ( empty($company) ) {
            return false;
        }

        $company = $company[0];

		return array(
			'full_data' => json_decode( $company['json'] ),
			'company' => $company['name'],
			'domain' => $company['domain'],
		);
	}

	public function addAssociation( $company_id, $influencer_id ) {
		$companyMetaModel = new CompanyMetaModel();

		$args = array (
			'influencer_id' => $influencer_id,
		);

		$result = $companyMetaModel->findAll( $args );

		// Company found
		if ( !empty($result) ) {
			return $result[0]->id;
		}

		$companyMetaModel->company_id = $company_id;
		$companyMetaModel->influencer_id = $influencer_id;
		$companyMetaModel->insert();

		return true;
	}

}