<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\GoogleAPI;
use app\models\KeywordInputForm;
use app\models\Keyword;
use app\models\EmailHunterApi;
use app\models\FullContactApi;
use app\models\TwitterApi;
use app\models\Influencer;
use app\models\ImageWorker;
use app\models\CompanyModel;
use app\models\HrEmails;
use app\models\JobQueries;
use yii\db\IntegrityException;
use app\common\components\Helpers;
use app\common\components\TLDFinder;

class Influencer extends ActiveRecord {

	public $job_id;
	public $job_task_id;
	public $emails_data;
	public $wp_user_id;

	
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
		return 'hr_influencers';
	}

	public function findExistingInfluencers( $param, $context = 'url' ) {

		$connection = Yii::$app->db;

		$where_clause = 'WHERE hr_job_task_meta.url LIKE :param';
		if ( $context == 'keyword' ) {
			$where_clause = 'WHERE hr_job_task_meta.keyword LIKE :param';
		}

		$sql = "SELECT * FROM hr_influencers
				INNER JOIN hr_job_task_meta
				ON hr_influencers.id = hr_job_task_meta.influencer_id
				$where_clause
				GROUP BY influencer_id
				";
		
		$command = $connection->createCommand ( $sql );
		
		$args = array(
			':param' => "%$param%",
		);

		$command->bindValues ( $args );

		// error_reporting(-1);
		// ini_set('display_errors', true);
		
		$influencers = $command->queryAll();

		if ( empty($influencers) ) {
			return array();
		}

		return $influencers;
	}

	/*
	* Get a Company name by Domain name
	* We assume that the first result is the most probable one
	*/
	public function getCompanyNameByDomain( $domain_name ) {
		$url = 'https://autocomplete.clearbit.com/v1/companies/suggest?query=' . urlencode( $domain_name );

		$json = @file_get_contents($url);

		if ( empty($json) ) {
			return false;
		}

		$data = json_decode( $json, true );

		if ( isset($data[0]['name']) ) {
			return $data[0]['name'];
		} else {
			return $domain_name;
		}
	}

	/*
	* Get a Domain name by Company name
	* We assume that the first result is the most probable one
	*/
	public function getDomainByCompanyName( $company_name ) {
		$url = 'https://autocomplete.clearbit.com/v1/companies/suggest?query=' . urlencode( $company_name );

		$json = @file_get_contents($url);

		if ( empty($json) ) {
			return false;
		}

		$data = json_decode( $json, true );

		if ( isset($data[0]['domain']) ) {
			return $data[0]['domain'];
		}
		
		return false;
	}

	public function saveMentionsData( $url ) {
		
		if ( !isset( $url ) OR empty( $url ) ) {
			return false;
		}

		$domain = $url;

		// APIs
		$fullContact = new FullContactApi();

		// Find some essential company information
		$json_company_data = $fullContact->getCompanyInfo ( $domain );		
		$fc_company_info = json_decode ( $json_company_data );

		if ( !empty($fc_company_info->name) ) {
			$company = $fc_company_info->name;
		} else {
			$company = $domain;
		}

		$this->savePeopleByCompany( $company, $domain, $search_type = 'domain' );

		return true;
	}

	public function savePeople( $name, $max_results = 4 ) {

		$imgWorker = new ImageWorker ();
		$gAPI = new GoogleAPI();

		$query_url = 'allintitle:'. $name .' site:linkedin.com/in/ OR site:linkedin.com/pub/ -site:linkedin.com/pub/dir/';
		$api_data = $gAPI->getAPIResults( $query_url, $max_results );

		$this->addJobQuery( $api_data['fullurl'], $api_data['raw_data'] );

		$api_results = $api_data['results_total'];

		if ( empty($api_results) ) {
			return false;
		}

		foreach ($api_results as $i => $result) {
			if ( isset($result->photo) ) {
				// $api_results[$i]->photo_path = $imgWorker->savePhotoFromUrl ( $result->photo );
				$api_results[$i]->photo_path = $result->photo;
			}
		}

		// Typecast before passing
		$tmp_results = array();
		foreach ($api_results as $r) {
			$tmp_results[] = (array) $r;
		}

		// Additional - always add LinkedIn as a Social profile
		$main_results = $this->mapLinkedIn( $tmp_results );

		$this->updateTotalJobResults( $api_data['total_count'] );

		// Store all results to DB
		$this->saveInfluencers( $main_results, '', $name );

		return true;
	}

	public function addJobQuery( $query_url, $api_data, $job_id = false ) {
        $model = new JobQueries();

        $insert_job_id = ( $job_id ? $job_id : $this->job_id );

        $model->job_id = $insert_job_id;
        $model->query_url = $query_url;

        if ( isset($api_data['queries']['nextPage']) ) {
            $model->has_next_page = 1;
        } else {
            $model->has_next_page = 0;
        }

        $model->insert();

        return true;
	}

	/*
	* Find and Save people base on their Company name
	* Note: This method would be working with a Domain name as well, although would probably return less results
	* If search_type is equal to "domain", than $domain should be required
	*/
	public function savePeopleByCompany( $company, $domain = false, $search_type = 'company', $pages = 3 ) {

		if ( empty($company) ) {
			return false;
		}
		
		// APIs
		$gAPI = new GoogleAPI();

		$query_url = '(' . $company .') AND "current" site:linkedin.com/in/ OR site:linkedin.com/pub/ -site:linkedin.com/pub/dir/ -intitle:"'. $company .'"';
		$api_data = $gAPI->getAPIResults( $query_url, $pages );

		$this->addJobQuery( $api_data['fullurl'], $api_data['raw_data'] );

		$api_results = $api_data['results_total'];

		if ( empty($api_results) ) {
			// $this->sendMailLog( $company );
			return false;
		}
		
		foreach ($api_results as $i => $result) {
			if ( isset($result->photo) ) {
				$api_results[$i]->photo_path = $result->photo;
			}
		}

		// Combine all results
		$mentionProfiles = $this->getUniqueResults( $api_results, array() );

		// No profiles found :(
		if ( empty($mentionProfiles) ) {
			return false;
		}

		// Additional - always add LinkedIn as a Social profile
		$mentionProfiles = $this->mapLinkedIn( $mentionProfiles );

		$this->updateTotalJobResults( $api_data['total_count'] );

		// Store all results to DB
		if ( $search_type == 'company' ) {
			$this->saveInfluencers( $mentionProfiles, '', $company );
		} else if ( $search_type == 'domain' ) {
			$this->saveInfluencers( $mentionProfiles, $domain, '' );
		}

		return true;
	}

	/**
	* Advanced search functionality
	* This method receives a serialize array of the needed search parameters
	*/
	public function advancedSearch( $parameters, $pages = 1 ) {

		if ( empty($parameters) ) {
			return false;
		}

		$param_data = @unserialize($parameters);

		// If the unserialization fails
		if ( empty($param_data) ) {
			$fixed_data = preg_replace_callback ( '!s:(\d+):"(.*?)";!', function($match) {      
			    return ($match[1] == strlen($match[2])) ? $match[0] : 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
			}, $param_data);

			$param_data = $fixed_data;
		}

		// We should be not getting here .. but still
		if ( empty($param_data) ) {
			$to = 'dogostz@gmail.com';
			$subject = 'Serialization failed';
			$txt = $parameters;
			$headers = "From: webmaster@headreach.com" . "\r\n" .
			"CC: support@headreach.com";

			mail($to, $subject, $txt, $headers);

			return false;
		}

		$query = '';

		foreach ($param_data as $param => $data) {
			$ct = count($data);
			
			switch ($param) {
				case 'company':

					if ( isset($param_data['current_only']) AND !empty($param_data['current_only']) ) {
						$query .= 'more:pagemap:person-org:';
						
						foreach ($data as $i => $d) {
							$query .= $this->getQueryString( $d );
							if ( $ct > ($i+1) )
								$query .= ',';
						}
						
						$query .= ' ';
					} else {

						$query .= '(';

						foreach ($data as $i => $d) {
							$query .= '"' . $d . '"';
							if ( $ct > ($i+1) )
								$query .= ' OR ';
						}

						$query .= ')';

					}

					// Exclude from -intitle
					foreach ($data as $exc_d) {
						$query .= ' -intitle:"'. $d .'" ';
					}

					break;

				case 'name':
					$query .= ' AND (';
					foreach ($data as $i => $d) {
						$query .= 'intitle:"'. $d .'"';
						if ( $ct > ($i+1) )
							$query .= ' OR ';
					}
					$query .= ') ';

					break;
				
				case 'position':
					$query .= ' AND ';
					$query .= 'more:pagemap:person-role:';
					
					foreach ($data as $i => $d) {
						$query .= $this->getQueryString( $d );
						if ( $ct > ($i+1) )
							$query .= ',';
					}

					$query .= ' ';
					break;

				case 'industry':
				case 'keywords':
				case 'degree':
					$query .= ' AND (';
					
					foreach ($data as $i => $d) {

						$query .= '"' . $d . '"';
						// if ( $param == 'start_date' OR $param == 'degree' ) {
						// 	$query .= '"' . $d . '"';
						// } else {
						// 	$query .= '"' . str_replace(' ', '*', $d) . '"';
						// }

						if ( $ct > ($i+1) )
							$query .= ' OR ';
					}

					$query .= ') ';
					break;

				case 'school':
					$query .= ' AND (';
					
					foreach ($data as $i => $d) {
						$query .= '"' . $d . '"';
						if ( $ct > ($i+1) )
							$query .= ' OR ';
					}

					$query .= ') ';
					break;

				case 'start_date':
					$query .= ' AND (';
					
					foreach ($data as $i => $d) {
						$query .= '"' . $d . ' * present' . '"';
						if ( $ct > ($i+1) )
							$query .= ' OR ';
					}

					$query .= ') ';
					break;

				case 'location':
					$query .= ' AND ';
					$query .= 'more:pagemap:person-location:';
					
					foreach ($data as $i => $d) {
						$query .= $this->getQueryString( $d );
						if ( $ct > ($i+1) )
							$query .= ',';
					}

					$query .= ' ';
					break;

			}

			unset($ct);
			unset($d);
		}

		if ( isset($param_data['country']) AND !empty($param_data['country']) ) {
			$countries = $param_data['country'];
			$total_c = count($countries);
			$list = Helpers::countriesMap();

			if ( $total_c > 1 ) {
				$query .= '(';
			}

			foreach ($countries as $j => $country) {
				$subdomain = array_search($country, $list);

				$query .= 'site:'. $subdomain .'/in/ OR site:'. $subdomain .'/pub/';

				if ( $total_c > ($j+1) )
					$query .= ' OR ';
			}

			if ( $total_c > 1 ) {
				$query .= ')';
			}
			
		} else {
			$query .= ' site:linkedin.com/in/ OR site:linkedin.com/pub/';
		}

		$query .= ' -site:linkedin.com/pub/dir/';

		$query = str_replace('  ', ' ', $query);
		$query = preg_replace('~^ AND ~', '', $query);

		$gAPI = new GoogleAPI();

		$api_data = $gAPI->getAPIResults( $query, $pages );

		$this->addJobQuery( $api_data['fullurl'], $api_data['raw_data'] );

		$api_results = $api_data['results_total'];

		if ( empty($api_results) ) {
			// $this->sendMailLog( $query );
			return false;
		}
		
		foreach ($api_results as $i => $result) {
			if ( isset($result->photo) ) {
				$api_results[$i]->photo_path = $result->photo;
			}
		}

		// Combine all results
		$mentionProfiles = $this->getUniqueResults( $api_results, array() );

		// Additional - always add LinkedIn as a Social profile
		$mentionProfiles = $this->mapLinkedIn( $mentionProfiles );

		$this->updateTotalJobResults( $api_data['total_count'] );

		$this->saveInfluencers( $mentionProfiles, '', $query );

		return true;
	}

	public function getQueryString( $string ) {
		$trim_chars = array( '.', '|', ',', '+', '-', '!', '—', '–', '&', '^', '!', '@', '$', '(', ')', '<', '>', ';', ';', '~', '`', ' ' );
		return str_replace($trim_chars, '*', $string);
	}

	public function updateTotalJobResults( $count ) {
		
		if ( empty($count) ) {
			return false;
		}

		$jobs_model = new Job();
		$job = $jobs_model->findOne( $this->job_id );
		$job->total_results = $count;
		$job->update();

		return true;
	}

	public function findPeopleByEmail( $domain ) {

		$imgWorker = new ImageWorker ();
		$emailHunter = new EmailHunterApi();
		$fullContact = new FullContactApi();

		$json_emails = $emailHunter->getEmail ( $domain );

		if ( empty($json_emails) ) {
			return false;
		}
		
		$eh_emails = json_decode ( $json_emails );

		$results = array();
		
		foreach ($eh_emails as $fc_person_email) {
			$json_person_info = $fullContact->getPersonInfo ( $fc_person_email );

			if ( empty($json_person_info) ) {
				continue;
			}
			
			$fc_person_info = json_decode ( $json_person_info );
			$fc_person_info = $fc_person_info [0];
			$response = json_decode( $fc_person_info->json_response );

			// Email found, but no results from Fullcontact
			if ( $response->status == '404' ) {
				continue;
			}

			$fc_person_info->email = $fc_person_email;

			if ( $fc_person_info->photo ) {
				$fc_person_info->photo_path = $imgWorker->savePhotoFromUrl ( $fc_person_info->photo );
			} else {
				$fc_person_info->photo_path = '';
			}
			
			$results[] = $fc_person_info;
		}

		return $results;
	}

	public function saveInfluencers( $profiles, $domain, $keyword ) {

		$jobTask = JobTask::findOne ( [ 
			'id' => $this->job_task_id
		] );

		// Failsafe - we shouldn't have such cases
		if ( empty($jobTask) ) {
			die( 'This job doesn\'t exist' );
		}

		$infModel = new Influencer();

		if ( !is_array($profiles) ) {
			$profiles = (array) $profiles;
		}

		foreach ($profiles as $i => $profile) {

			if ( !is_array($profile) ) {
				$profile = (array) $profile;
			}
			
			$args = array (
				'name' => $profile['name'],
				'title' => $profile['title'],
			);

			$result = $infModel->findOne( $args );

			// Insert it
			if ( empty($result) ) {
				$influencer_id = $infModel->insertToDb( $profile );
			} else {
				$influencer_id = $result->id;
			}

			if ( !empty($influencer_id) ) {
				// Should populate domain/keyword here
				$meta_id = $jobTask->addMeta( $influencer_id, $domain, $keyword );
			}

			$profiles[$i]['id'] = $influencer_id;
		}

		return $profiles;
	}

	public function getEmailViaAPI( $domain, $company, $first_name, $last_name, $use_all_sources = true ) {

		// If subdomain, use the MAIN domain
		if ( $domain ) {
			$tld_domain = TLDFinder::getTld( $domain, false, true );
			if ( $tld_domain ) {
				$domain = $tld_domain;
			}

			$domain = str_replace('www.', '', $domain);
		}

		// Do not attempt a search
		if ( empty($domain) AND empty($company) ) {
			return false;
		}

		// if ( $this->wp_user_id == 1 ) {
		// 	$email = $this->getAnymailfinderEmail( $domain, $company, $first_name, $last_name );
		// 	return $email;
		// }

		$email = $this->getMailhunterEmail( $domain, $company, $first_name, $last_name );

		// if ( empty($email) AND $use_all_sources ) {
		// 	$email = $this->getNorbertEmail( $domain, $company, $first_name, $last_name );
		// }

		return $email;
	}

	public function getNorbertEmail( $domain, $company, $first_name, $last_name ) {
		$email = '';
		$api_key = '777ac779-1b10-46c1-a854-5e40bfc13000';

		$login = 'wooguru'; // norbert ignores the username
		$password = $api_key; // uses API KEY as password
		$url_search = 'http://api.voilanorbert.com/2016-01-04/search/name';

		$data = array(
			'name' => $first_name . ' ' . $last_name,
		);

		if ( $domain ) {
			$data['domain'] = $domain;
		}

		if ( $company ) {
			$data['company'] = $company;
		}

		$process = curl_init();
		curl_setopt($process, CURLOPT_URL,$url_search);
		curl_setopt($process, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($process, CURLOPT_POST, 1);
		curl_setopt($process, CURLOPT_POSTFIELDS, $data);
		curl_setopt($process, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($process, CURLOPT_USERPWD, "$login:$password");
		$result = curl_exec($process);
		curl_close($process);
	
		if ( empty($result) ) {
			return false;
		}

		$result = json_decode( $result );

		if ( !is_object($result) OR !isset($result->id) ) {
			return false;
		}

		$url_get = 'http://api.voilanorbert.com/2016-01-04/contacts/' . $result->id;

		while (true) {
			$cget = curl_init();
			curl_setopt($cget, CURLOPT_URL,$url_get);
			curl_setopt($cget, CURLOPT_RETURNTRANSFER,1);
			// curl_setopt($cget, CURLOPT_POST, 1);
			curl_setopt($cget, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($cget, CURLOPT_USERPWD, "$login:$password");
			$mail_data = curl_exec($cget);
			curl_close($cget);

			if ( empty($mail_data) ) {
				break;
			}

			// Mail Data return several other fields, we could leverage them at some point
			$mail_data = json_decode($mail_data, true);
			if ( $mail_data['searching'] == false ) {
				
				if ( $mail_data['email'] ) {
					// $email = $mail_data->email->email;
					$mail_data['source'] = 'voilanorbert';
					$email = $mail_data;
				}

				break;
			}

		}

		return $email;
	}

	public function getMailhunterEmail( $domain, $company, $first_name, $last_name ) {
		// $api_key = '7c03901d9fe9ca20d6ffe155fad8200aca183987';
		// $api_key = '4fdec8e38908366936ec70b3a3fbce7c89478a8c';
		$api_key = '229da7bf07fe3b3a313f575a48930d165cdae194';

		$args = '';

		$params = array(
			'domain' => $domain,
			'company' => $company,
			'first_name' => $first_name,
			'last_name' => $last_name,
		);

		$i = 0;

		foreach ($params as $key => $value) {
			if ( !empty($value) ) {
				$i++;
				$div = ( $i > 1 ? '&amp;' : '?' );
				$args .= $div . $key . '=' . $value;
			}
		}

		// $url = 'https://api.emailhunter.co/v1/generate'. $args .'&amp;api_key=' . $api_key;
		$url = 'https://api.hunter.io/v2/email-finder'. $args .'&amp;api_key=' . $api_key;

		$url = str_replace(' ', '%20', $url);

		$json = @file_get_contents($url);

		if ( empty($json) ) {
			return false;
		}

		$data = json_decode( $json, true );

		if ( isset($data['data']['email']) AND !empty($data['data']['email']) ) {
			$data['source'] = 'emailhunter';
			return $data;
		}

		// if ( isset($data['email']) AND !empty($data['email']) ) {
		// 	return $data['email'];
		// }

		return false;
	}

	public function getAnymailfinderEmail( $domain, $company, $first_name, $last_name ) {

		if ( empty($domain) ) {
			return false;
		}

		$api_key = '9665565549022082356c025611754cb714ea2c7a';
		$url_search = 'https://api.anymailfinder.com/v3.0/search/person.json';

		$headers = array(
			'X-Api-Key: '. $api_key
		);

		$data['name'] = $first_name . ' ' .  $last_name;
		$data['domain'] = $domain;
		$data['include_pattern'] = true;

		$process = curl_init();
		curl_setopt($process, CURLOPT_URL,$url_search);
		curl_setopt($process, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($process, CURLOPT_POST, 1);
		curl_setopt($process, CURLOPT_POSTFIELDS, $data);
		curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($process, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		# curl_setopt($process, CURLOPT_USERPWD, "$login:$password");
		$json = curl_exec($process);
		curl_close($process);

		if ( empty($json) ) {
			return false;
		}

		$data = json_decode( $json, true );

		if ( isset($data['status']) AND $data['status'] == 'success' ) {
			$data['source'] = 'anymailfinder';
			return $data;
		}

		return false;
	}

	public function checkName( $name ) {
		$pieces = explode(' ', $name);
		$first_name = $pieces[0];

		$key = 'da200505';
		$url = 'http://www.behindthename.com/api/lookup.php?name='. $first_name .'&key=' . $key;
		$xml = simplexml_load_file( $url );

		if ( $xml->error ) {
			return false;
		} else {
			return true;
		}
	}

	public function associateInfluencers( $influencers, $domain, $keyword ) {
		$jobTask = JobTask::findOne ( [ 
			'id' => $this->job_task_id 
		] );

		if ( empty($jobTask) ) {
			die( 'This job doesn\'t exist' );
		}

		foreach ($influencers as $influencer) {
			$influencer_id = $influencer['id'];
			$jobTask->addMeta( $influencer_id, $domain, $keyword );
		}

		return true;
	}

	public function getUniqueResults( $api_results, $fullcontact_results ) {

		$tmp_results = array();
		$ref_results = array();
		$unq_results = array();

		if ( $api_results ) {
			foreach ($api_results as $api_person) {
				$tmp_results[] = array(
					'title' => ( isset($api_person->title) ? $api_person->title : '' ),
					'company' => ( isset($api_person->company) ? $api_person->company : '' ),
					'name' => $api_person->name,
					'description' => $api_person->snippet,
					// 'linkedin_json' => ( isset($api_person->json_response) ? $api_person->json_response : '' ),
					'photo_path' => ( isset($api_person->photo_path) ? $api_person->photo_path : '' ),
					'profile_link' => $api_person->profile_link,
					'location' => $api_person->location,
					// 'industry' => $api_person->industry,
				);
			}
		}

		if ( $fullcontact_results ) {
			foreach ($fullcontact_results as $fc_person) {
				$tmp_results[] = array(
					'title' => $fc_person->title,
					'company' => $fc_person->company,
					'name' => $fc_person->first_name . ' ' . $fc_person->last_name,
					'bio' => $fc_person->bio,
					'contact_info' => $fc_person->contact_info,
					'person_social' => $fc_person->social,
					'full_contact_person_json' => ( isset($fc_person->json_response) ? $fc_person->json_response : '' ),
					'photo_path' => $fc_person->photo_path,
					'email' => $fc_person->email,
				);
			}
		}

		if ( empty($tmp_results) ) {
			return array();
		}

		/*
		foreach ($tmp_results as $result) {
			$name = $result['name'];
			$ref_results[$name][] = $result;
		}

		foreach ($ref_results as $key => $data) {
			
			$count = count($data);
			if ( $count > 1 ) {
				$data = array_reverse( $data ); // LinkedIn results would always take priority
				$func = array( $this, 'array_merge_retain' );
				$args = array( $data[0], $data[1] );
				$unq_results[] = call_user_func_array( $func, $args );
			} else {
				$unq_results[] = $data[0];
			}

		}
		*/

		return $tmp_results;
	}

	public function updateRecords( $records ) {

		if ( empty($records) ) {
			return false;
		}

		foreach ($records as $record) {
			$this->updateSingleRecord( $record );
		}

		return true;
	}

	public function updateSingleRecord( $record ) {
			
		if ( !empty($record['email']) ) {
			return false;
		}

		$influencersModel = new Influencer();

		$first_name = $record['first_name'];
		$last_name = $record['last_name'];
		$company = $record['company'];

		$email = $this->getEmailViaAPI( false, $company, $first_name, $last_name );

		if ( empty($email) ) {
			return false;
		}

		$db_record = $influencersModel->findOne(array(
				'_id' => $record['id']
			));

		if ( !is_object($db_record) ) {
			return false;
		}

		$ret = exec("python2.7 ". __DIR__ ."/../assets/full_contact_person_api.py ". $email,$output,$return_var);

		if ( isset($output[0]) ) {
			$extra_data = $output[0];

			if ( $extra_data ) {
				$fc_person_info = json_decode ( $extra_data );
				$fc_person_info = $fc_person_info [0];
				$response = json_decode( $fc_person_info->json_response );

				// Email found, but no results from Fullcontact
				if ( $response->status != '404' ) {
					$db_record->bio = $fc_person_info->bio;
					$db_record->contact_info = json_encode( $fc_person_info->contact_info );
					$db_record->person_social = $this->mapLinkedInSingle( $fc_person_info->social, $db_record );
					$db_record->full_contact_person_json = json_encode( $fc_person_info->json_response );
				}
			}
		}

		$db_record->email = $email;
		$db_record->update();
			
		return true;
	}

	/*
	* @array $socials
	*/
	public function mapLinkedInSingle( $socials, $profile ) {

		if ( empty($socials) ) {
			return false;
		}

		$links = $profile->person_social;

		if ( !empty($links) ) {
			$links = json_decode( $links, true );
		} else {
			$links = array();
		}

		$new_links = array();
		foreach ($socials as $social) {
			if ( !preg_match('~linkedin~', $social) ) {
				$new_links[] = $social;
			}
		}

		$links = array_merge( $links, $new_links );

		return json_encode($links);
	}

	public function mapLinkedIn( $profiles ) {

		foreach ($profiles as $i => $profile) {

			// Failsafe
			if ( !isset($profile['profile_link']) ) {
				continue;
			}

			if ( isset($profile['person_social']) AND !empty($profile['person_social']) ) {
				$links = $profile['person_social'];

				$matches  = preg_grep('/linkedin/i', $links);
				if ( empty($matches) ) {
					$links[] = $profile['profile_link'];
				}

				$profiles[$i]['person_social'] = $links;
			} else {
				$linkedin_url = array( $profile['profile_link'] ); // keep the format
				$profiles[$i]['person_social'] = $linkedin_url;
			}
		}

		return $profiles;
	}

	public function insertToDb( $person ) {

		$fields = $this->getFields();

		$infModel = new Influencer();

		foreach ($fields as $field) {
			if ( isset($person[$field]) AND !empty($person[$field]) ) {
				$value = ( is_array($person[$field]) ? json_encode($person[$field]) : $person[$field] );
				$infModel->{$field} = $value;
			}
		}

		$infModel->insert();

		$lastInsertID = Yii::$app->db->getLastInsertID();

		return $lastInsertID;
	}

	public function getFields() {
		return array(
			'title',
			'company',
			'name',
			'description',
			'linkedin_json',
			'photo_path',
			'bio',
			'contact_info',
			'person_social',
			'full_contact_person_json',
			'email',
			'location',
			// 'industry',
		);
	}

	/*
	* Merge an array and keep its data
	*/
	public function array_merge_retain( $array_a = array(), $array_b = array() ) {
		
		$array_merge= array();

		if ( empty($array_a) OR empty($array_b) ) {
			return array();
		}

		foreach ($array_a as $field => $value) {
			$array_merge[$field]= $value;
		}

		foreach ($array_b as $field=>$value) {

			if (!empty($value)) {
				$array_merge[$field]= $value;
			} elseif (!array_key_exists($field, $array_a)) {
				$array_merge[$field]= $value;
			}

		}

		return $array_merge;
	}

	public function sendMailLog( $param ) {
		$to = 'dogostz@gmail.com';
		$subject = 'Search failed';
		$txt = 'Our Google API failed for the following Parameter: ' . $param;
		$headers = "From: webmaster@headreach.com" . "\r\n" .
		"CC: support@headreach.com";

		mail($to, $subject, $txt, $headers);
	}

	public function loadMoreResults( $job_query_id ) {
		
		// APIs
		$gAPI = new GoogleAPI();
		$jobs_model = new Job();
		$job_task_model = new JobTask();
		$job_task_meta_model = new JobTaskMeta();
		$queries_model = new JobQueries();

		// Get the latest recorded query
		// $job_query_id is the ID of the query, which is inserted last in the DB
		$query = $queries_model->findOne( $job_query_id );

		if ( empty($query) OR !$query->has_next_page ) {
			return false;
		}

		$job = $jobs_model->findOne( $query->job_id );

		$job_task = $job_task_model->findOne(array(
			'job_id' => $query->job_id
		));

		if ( empty($job_task) ) {
			return false;
		}

		$this->job_task_id = $job_task->id;

		$job_task_meta = $job_task_meta_model->findOne(array(
			'job_task_id' => $job_task->id
		));

		if ( empty($job_task_meta) ) {
			return false;
		}
		
		$query_url = $this->getQueryURL( $query->query_url );
		$api_data = $gAPI->getDirectResults( $query_url );

		$this->addJobQuery( $api_data['fullurl'], $api_data['raw_data'], $query->job_id );

		$api_results = $api_data['results_total'];
		
		foreach ($api_results as $i => $result) {
			if ( isset($result->photo) ) {
				$api_results[$i]->photo_path = $result->photo;
			}
		}

		// Combine all results
		$data = $this->getUniqueResults( $api_results, array() );

		// No profiles found :(
		if ( empty($data) ) {
			return false;
		}

		// Additional - always add LinkedIn as a Social profile
		$data = $this->mapLinkedIn( $data );

		$profiles = $this->saveInfluencers( $data, $job_task_meta->url, $job_task_meta->keyword );

		return $profiles;
	}

	public function getQueryURL( $query_url ) {
		$pieces = explode('&', $query_url);
		$pieces_new = array();

		foreach ($pieces as $piece) {
			if ( preg_match('~start=~', $piece) ) {
				$curr_page = explode('=', $piece);
				if ( isset($curr_page[1]) ) {
					$num = $curr_page[1];
					// $num = $num + 10;
					$start = $num + 10;
					$pieces_new[] = 'start=' . $start;
				}
			} else {
				$pieces_new[] = $piece;
			}
		}

		return implode('&', $pieces_new);
	}

}