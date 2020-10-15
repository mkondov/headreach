<?php

namespace app\controllers;

use Yii;
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
use yii\db\IntegrityException;

class AjaxController extends Controller {
	public function behaviors() {
		return [ 
				'access' => [ 
						'class' => AccessControl::className (),
						'only' => [ 
								'logout' 
						],
						'rules' => [ 
								[ 
										'actions' => [ 
												'logout' 
										],
										'allow' => true,
										'roles' => [ 
												'@' 
										] 
								] 
						] 
				],
				'verbs' => [ 
						'class' => VerbFilter::className (),
						'actions' => [ 
								'logout' => [ 
										'post' 
								] 
						] 
				] 
		];
	}
	public function actions() {
		return [ 
				'error' => [ 
						'class' => 'yii\web\ErrorAction' 
				],
				'captcha' => [ 
						'class' => 'yii\captcha\CaptchaAction',
						'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null 
				] 
		];
	}
	public function actionSubmittwitter() {
		$json = \Yii::$app->request->getRawBody ();
		$decoded = json_decode ( $json, true );
		$keyword = $decoded ['keyword'];
		
		$twitterApi = new TwitterApi ();
		$json_twitter_data = $twitterApi->getPopularProfiles ( $keyword );
		
		$twit_profiles_info = json_decode ( $json_twitter_data );
		
		$imgWorker = new ImageWorker ();
		for($p = 0; $p < count ( $twit_profiles_info->results ); $p ++) {
			$twit_profiles_info->results [$p]->photo_path = $imgWorker->savePhotoFromUrl ( $twit_profiles_info->results [$p]->photo );
		}
		
		$fullContact = new FullContactApi ();
			
		$fc_people = array ();
		for($e = 0; $e < 10 && $e < count ( $twit_profiles_info ); $e ++) {
			$twit_handle = $twit_profiles_info[$e]->twitter_handle;
			$json_person_info = $fullContact->getPopularProfiles( $twit_handle );
		
			$fc_person_info = json_decode ( $json_person_info );
			$fc_person_info = $fc_person_info [0];
		
			$imgWorker = new ImageWorker ();
			$fc_person_info->photo_path = $imgWorker->savePhotoFromUrl ( $fc_person_info->photo );
		
			$fc_people [] = $fc_person_info;
		}

		for($p = 0; $p < count ( $twit_profiles_info->results ); $p ++) {
			
			$twit_person = $li_people_info->results [$p];
			if (! isset ( $twit_person->email )) {
				$twit_person->email = "";
			}
			
			
				
				$influencers [] = array (
						"first_name" => $twit_person->first_name,
						"last_name" => $twit_person->last_name,
						"time_zone" => $twit_person->time_zone,
						"location" => $twit_person->location,
						"twitter_id" => $twit_person->twitter_id,
						"twitter_handle" => $twit_person->twitter_handle,
						"bio" => "",
						"contact_info" => array (),
						"company_social" => array (),
						"person_social" => array (),
						"photo_path" => $twit_person->photo_path,
						"keyword" => $keyword 
				);
		}
	}
	public function actionSubmiturl() {
		$json_url = \Yii::$app->request->getRawBody ();
		$decoded_url = json_decode ( $json_url, true );
		$post_url = $decoded_url ['url'];
		
		// $url_pattern = "@(https?|ftp)://(-\.)?([^\s/?\.#-]+\.?)+(/[^\s]*)?$@iS";
		// $url_pattern = "/(?:http|https)?(?:\:\/\/)?(?:www.)?(([A-Za-z0-9-]+\.)*[A-Za-z0-9-]+\.[A-Za-z]+)(?:\/.*)?/igm";
		
		/*
		 * if (! preg_match ( $url_pattern, $post_url )) {
		 * \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
		 *
		 * $result = json_encode ( array (
		 * 'error' => 'true',
		 * 'status' => 'no valid url specified'
		 * ) );
		 *
		 * return $result;
		 * }
		 */
		
		/*
		 * $keyword_pattern = "/^(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,6}$/";
		 *
		 * if (! preg_match ( $keyword_pattern, $keyword->keyword )) {
		 * \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
		 *
		 * $result = json_encode ( array (
		 * 'error' => 'true',
		 * 'status' => 'no keyword specified'
		 * ) );
		 *
		 * return $result;
		 * }
		 */
		
		$ch = curl_init ();
		$timeout = 5;
		curl_setopt ( $ch, CURLOPT_URL, $post_url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true );
		$html_data = curl_exec ( $ch );
		curl_close ( $ch );
		
		// $url = "http://www.example.net/somepage.html";
		// $input = @file_get_contents($url) or die("Could not access file: $url");
		$regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
		if (preg_match_all ( "/$regexp/siU", $html_data, $matches )) {
			
			$domains = $matches [2];
			// $matches[3] = array of link text - including HTML code
		}
		
		$connection = Yii::$app->db;
		$influencers = array ();
		
		$uq_domains = array ();
		for($dom = 0; $dom < count ( $domains ); $dom ++) {
			if (isset ( parse_url ( $domains [$dom] ) ['host'] )) {
				$uq_domains [] = parse_url ( $domains [$dom] ) ['host'];
			}
		}
		$uq_domains = array_unique ( $uq_domains );
		$uq_domains = array_values ( $uq_domains );
		
		for($dom = 0; $dom < count ( $uq_domains ); $dom ++) {
			$sql = "SELECT * FROM tbl_influencers WHERE domain=:domain";
			$command = $connection->createCommand ( $sql );
			$command->bindParam ( ":domain", $uq_domains [$dom] );
			
			$dataReader = $command->query ();
			$rows = $dataReader->readAll ();
			
			if (! empty ( $rows )) {
				$influencers [] = array (
						"title" => $row ['title'],
						"first_name" => $row ['first_name'],
						"last_name" => $row ['last_name'],
						"company" => $row ['company'],
						"email" => $row ['email'],
						"bio" => $row ['bio'],
						"contact_info" => json_decode ( $row ['contact_info'] ),
						"company_social" => json_decode ( $row ['company_social'] ),
						"person_social" => json_decode ( $row ['person_social'] ),
						"photo_path" => $row ['photo_path'],
						"domain" => $uq_domains [$dom] 
				);
				continue;
			}
			
			$fullContact = new FullContactApi ();
			$json_company_data = $fullContact->getCompanyInfo ( $uq_domains [$dom] );
			
			$fc_company_info = json_decode ( $json_company_data );
			
			$pythonScraper = new LinkedinScraper ();
			
			if ($fc_company_info->name != "") {
				$json_output = $pythonScraper->scrape ( $fc_company_info->name );
			} else {
				$json_output = $pythonScraper->scrape ( $uq_domains [$dom] );
			}
			
			$li_people_info = json_decode ( $json_output );
			
			$imgWorker = new ImageWorker ();
			for($p = 0; $p < count ( $li_people_info->results ); $p ++) {
				$li_people_info->results [$p]->photo_path = $imgWorker->savePhotoFromUrl ( $li_people_info->results [$p]->photo );
			}
			
			$emailHunter = new EmailHunterApi ();
			
			if ($fc_company_info->website != "") {
				$json_emails = $emailHunter->getEmail ( $fc_company_info->website );
			} else {
				$json_emails = $emailHunter->getEmail ( $uq_domains [$dom] );
			}
			
			$eh_emails = json_decode ( $json_emails );
			
			$fullContact = new FullContactApi ();
			
			$fc_people = array ();
			for($e = 0; $e < 10 && $e < count ( $eh_emails ); $e ++) {
				$fc_person_email = $eh_emails [$e];
				$json_person_info = $fullContact->getPopularProfiles ( $fc_person_email );
				
				$fc_person_info = json_decode ( $json_person_info );
				$fc_person_info = $fc_person_info [0];
				
				if ($fc_person_info->first_name == "" && $fc_company_info->email_addresses [0] != null) {
					$json_person_info = $fullContact->getPopularProfiles ( $fc_company_info->email_addresses [0] );
					$fc_person_info = json_decode ( $json_person_info );
					$fc_person_info = $fc_person_info [0];
				}
				
				$imgWorker = new ImageWorker ();
				$fc_person_info->photo_path = $imgWorker->savePhotoFromUrl ( $fc_person_info->photo );
				
				$fc_people [] = $fc_person_info;
			}
			
			for($p = 0; $p < count ( $li_people_info->results ); $p ++) {
				
				$li_person = $li_people_info->results [$p];
				if (! isset ( $li_person->email )) {
					$li_person->email = "";
				}
				
				
					
					$influencers [] = array (
							"title" => $li_person->title,
							"first_name" => $li_person->first_name,
							"last_name" => $li_person->last_name,
							"company" => $li_person->company,
							"email" => $li_person->email,
							"bio" => "",
							"contact_info" => array (),
							"company_social" => array (),
							"person_social" => array (),
							"photo_path" => $li_person->photo_path,
							"domain" => $uq_domains [$dom] 
					);
				}
			}
			
			for($f = 0; $f < count ( $fc_people ); $f ++) {
				$fc_person_info = $fc_people [$f];
				if ($fc_person_info->first_name != "") {
					
					if (! isset ( $fc_person_info->json_response )) {
						$fc_person_info->json_response = "";
					}
					if (! isset ( $fc_company_info->json_response )) {
						$fc_company_info->json_response = "";
					}
					if (! isset ( $email_hunter->json_response )) {
						$email_hunter->json_response = "";
					}
					if (! isset ( $fc_person_info->contact_info )) {
						$fc_person_info->contact_info = array ();
					}
					if (! isset ( $fc_company_info->social_profiles )) {
						$fc_company_info->social_profiles = array ();
					}
					if (! isset ( $fc_person_info->social )) {
						$fc_person_info->social = array ();
					}
					
					try {
						$sql = 'INSERT INTO tbl_influencers (title,first_name,last_name,company,email,bio,contact_info,company_social,person_social,full_contact_person_json,full_contact_company_json,email_hunter_json,photo_path,domain,time_updated) VALUES (:title,:first_name,:last_name,:company,:email,:bio,:contact_info,:company_social,:person_social,:full_contact_person_json,:full_contact_company_json,:email_hunter_json,:photo_path,:domain,:time_updated)';
						$command = $connection->createCommand ( $sql );
						$command->bindValue ( ':title', $fc_person_info->title );
						$command->bindValue ( ':first_name', $fc_person_info->first_name );
						$command->bindValue ( ':last_name', $fc_person_info->last_name );
						$command->bindValue ( ':company', $fc_person_info->company );
						$command->bindValue ( ':email', $fc_person_email );
						$command->bindValue ( ':bio', $fc_person_info->bio );
						
						$command->bindValue ( ':contact_info', json_encode ( $fc_person_info->contact_info ) );
						$command->bindValue ( ':company_social', json_encode ( $fc_company_info->social_profiles ) );
						$command->bindValue ( ':person_social', json_encode ( $fc_person_info->social ) );
						$command->bindValue ( ':full_contact_person_json', $fc_person_info->json_response );
						$command->bindValue ( ':full_contact_company_json', $fc_company_info->json_response );
						$command->bindValue ( ':email_hunter_json', $email_hunter->json_response );
						$command->bindValue ( ':photo_path', $fc_person_info->photo_path );
						$command->bindValue ( ':domain', $uq_domains [$dom] );
						$command->bindValue ( ':time_updated', time () );
						$command->execute ();
					} catch ( IntegrityException $e ) {
						
						$sql = 'UPDATE tbl_influencers SET title=:title,company=:company,email=:email,bio=:bio,contact_info=:contact_info,company_social=:company_social,person_social=:person_social,full_contact_person_json=:full_contact_person_json,full_contact_company_json=:full_contact_company_json,email_hunter_json=:email_hunter_json,photo_path=:photo_path,domain=:domain,time_updated=:time_updated WHERE first_name=:first_name and last_name=:last_name';
						$command = $connection->createCommand ( $sql );
						$command->bindValue ( ':title', $fc_person_info->title );
						$command->bindValue ( ':first_name', $fc_person_info->first_name );
						$command->bindValue ( ':last_name', $fc_person_info->last_name );
						$command->bindValue ( ':company', $fc_person_info->company );
						$command->bindValue ( ':email', $fc_person_email );
						$command->bindValue ( ':bio', $fc_person_info->bio );
						
						$command->bindValue ( ':contact_info', json_encode ( $fc_person_info->contact_info ) );
						$command->bindValue ( ':company_social', json_encode ( $fc_company_info->social_profiles ) );
						$command->bindValue ( ':person_social', json_encode ( $fc_person_info->social ) );
						$command->bindValue ( ':full_contact_person_json', $fc_person_info->json_response );
						$command->bindValue ( ':full_contact_company_json', $fc_company_info->json_response );
						$command->bindValue ( ':email_hunter_json', $email_hunter->json_response );
						$command->bindValue ( ':photo_path', $fc_person_info->photo_path );
						$command->bindValue ( ':domain', $uq_domains [$dom] );
						$command->bindValue ( ':time_updated', time () );
						$command->execute ();
					}
					
					$influencers [] = array (
							"title" => $fc_person_info->title,
							"first_name" => $fc_person_info->first_name,
							"last_name" => $fc_person_info->last_name,
							"company" => $fc_person_info->company,
							"email" => $fc_person_email,
							"bio" => $fc_person_info->bio,
							"contact_info" => $fc_person_info->contact_info,
							"company_social" => $fc_company_info->social_profiles,
							"person_social" => $fc_person_info->social,
							"photo_path" => $fc_person_info->photo_path,
							"domain" => $uq_domains [$dom] 
					);
				}
			}
		}
		
		/*
		 * $report_id = substr ( uniqid (), 20 );
		 * $sql = 'INSERT INTO tbl_reports (_id,url,time_created) VALUES (:_id,:url,:time_created)';
		 * $command = $connection->createCommand ( $sql );
		 * $command->bindValue ( ':_id', $li_person->title );
		 * $command->bindValue ( ':url', $post_url );
		 * $command->bindValue ( ':time_created', time () );
		 * $affected_rows = $command->execute ();
		 */
		
		$return_json = array (
				"code" => 200,
				"report_id" => $report_id,
				"results" => $influencers 
		);
	}
	public function formatResults($rows) {
		$influencers = array ();
		
		foreach ( $rows as $row ) {
			$influencers [] = array (
					"title" => $row ['title'],
					"first_name" => $row ['first_name'],
					"last_name" => $row ['last_name'],
					"company" => $row ['company'],
					"email" => $row ['email'],
					"bio" => $row ['bio'],
					"contact_info" => json_decode ( $row ['contact_info'] ),
					"company_social" => json_decode ( $row ['company_social'] ),
					"person_social" => json_decode ( $row ['person_social'] ),
					"photo_path" => $row ['photo_path'] 
			);
		}
		
		$return_json = array (
				"code" => 200,
				"results" => $influencers 
		);
		
		return json_encode ( $return_json );
	}
}