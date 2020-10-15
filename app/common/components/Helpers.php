<?php

namespace app\common\components;
use app\models\WPModel;
use app\models\Job;

class Helpers {
    
	public static function getSessionData( $session_name = 'headreach_auth', $session_save_handler = 'files' ) {
	    $session_data = array();

	    /*
		$arr['current-user'] = array(
            'ID' => 1,
            'user_login' => 'admin',
            'user_first_name' => 'Daniel',
            'avatar' => 'http://1.gravatar.com/avatar/44cacbe2cfa02ba639f2efd498fa523f?s=96&d=mm&r=g',
            'role' => 'administrator',
        );

        return $arr;
        */

		# did we get told what the old session id was? we can't continue it without that info
		if (array_key_exists($session_name, $_COOKIE)) {
			# save current session id
			$session_id = $_COOKIE[$session_name];
			$old_session_id = session_id();

			# write and close current session
			session_write_close();

			# grab old save handler, and switch to files
			$old_session_save_handler = ini_get('session.save_handler');
			ini_set('session.save_handler', $session_save_handler);

			# now we can switch the session over, capturing the old session name
			$old_session_name = session_name($session_name);
			session_id($session_id);
			session_start();

			# get the desired session data
			$session_data = $_SESSION;

			# close this session, switch back to the original handler, then restart the old session
			session_write_close();
			ini_set('session.save_handler', $old_session_save_handler);
			session_name($old_session_name);
			session_id($old_session_id);
			session_start();
		}

		// if ( $session_data['current-user']['ID'] == 1 ) {
		// 	return false;
		// }

		# now return the data we just retrieved
		return $session_data;
	}

	public static function getCredits() {
		$session = Helpers::getSessionData();
		$wp_user_id = $session['current-user']['ID'];

		$WPModel = new WPModel();
		$credits = $WPModel->getCredits( $wp_user_id );

		if ( empty($credits) ) {
			return array(
				'left' => 0,
				'used' => 0,
				'total' => 0,
			);
		}

		$active_subscription = $credits[0];

		$total = $active_subscription['credit_amount_earned'];
		$current = $active_subscription['credits'];

		$step = (($total - ($total-1) )/$total) * 100;
		$percentage_used = (($total - $current )/$total) * 100;

		return array(
			'left' => $current,
			'used' => $total - $current,
			'total' => $total,
			'percentage_used' => number_format( $percentage_used, 2 ),
			'step' => number_format( $step, 2 ),
		);
	}

	public static function getSearches() {
		$session = Helpers::getSessionData();
		$wp_user_id = $session['current-user']['ID'];

		$jobsModel = new Job();

		$args = array(
			'wp_user_id' => $wp_user_id,
		);
		
		$data = $jobsModel->findAll( $args );
		$used = count( $data );

		// Normalize
		$used = ( $used > 15 ? 15 : $used );

		$total = 15;
		$left = $total - $used;

		$step = (($total - ($total-1) )/$total) * 100;
		$percentage_used = (($total - $left )/$total) * 100;
		
		return array(
			'left' => $left,
			'used' => $total - $left,
			'total' => $total,
			'percentage_used' => number_format( $percentage_used, 2 ),
			'step' => number_format( $step, 2 ),
		);
	}

	public static function getPaging( $count, $per_page = 20 ) {

		if ( $count < $per_page ) {
			return false;
		}

		$show_ellipses = false;
		$class = '';

		$page = ( isset($_GET['page']) ? $_GET['page'] : 1 );
		$num_pages = round( $count / $per_page );
		$last = ceil( $count / $per_page );

		if ( $num_pages > 10 ) {
			$show_ellipses = true;
		}

	    $start = ( ( $page - $num_pages ) > 0 ) ? $page - $num_pages : 1;
	    $end  = ( ( $page + $num_pages ) < $last ) ? $page + $num_pages : $last;

		$html       = '<ul class="pagination" role="navigation" aria-label="Pagination">';

	    if ( $page == 1 ) {
	    	$html .= '<li class="pagination-previous disabled">Previous <span class="show-for-sr">page</span></li>';
	    } else {
	    	$html .= '<li class="pagination-previous"><a href="'. URLHelpers::add_query_arg( 'page', $page - 1 ) .'">Previous <span class="show-for-sr">page</span></a></li>';
	    }
	 
	    if ( $start > 1 ) {
	        $html .= '<li><a href="'. URLHelpers::add_query_arg( 'page', 1 ) .'">1</a></li>';
	        $html .= '<li class="disabled"><span>...</span></li>';
	    }
	 
		for ( $i = $start; $i <= $end; $i++ ) {

			if ( $page == $i ) {
				$html .= '<li class="current"><span class="show-for-sr">You\'re on page</span> '. $page .'</li>';
			} else {
				$html .= '<li class="' . $class . '"><a href="'. URLHelpers::add_query_arg( 'page', $i ) .'">' . $i . '</a></li>';
			}				

		}
	 
	    if ( $end < $last ) {
	        $html .= '<li class="disabled"><span>...</span></li>';
	        $html .= '<li><a href="'. URLHelpers::add_query_arg( 'page', $last ) .'">' . $last . '</a></li>';
	    }

	    if ( $page == $last ) {
	    	$html .= '<li class="pagination-next disabled">Next <span class="show-for-sr">page</span></li>';
	    } else {
	    	$html .= '<li class="pagination-next"><a href="'. URLHelpers::add_query_arg( 'page', $page + 1 ) .'" aria-label="Next page">Next <span class="show-for-sr">page</span></a></li>';
	    }
	 
	    $html .= '</ul>';

	    echo $html;
	}

	public static function downloadFile( $filename ) {
		// disable caching
		$now = gmdate("D, d M Y H:i:s");
		header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		header("Last-Modified: {$now} GMT");

		// force download  
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");

		// disposition / encoding on response body
		header("Content-Disposition: attachment;filename={$filename}");
		header("Content-Transfer-Encoding: binary");
	}

	public static function array2csv(array &$array) {
		if (count($array) == 0) {
			return null;
		}
		
		ob_start();
		$df = fopen("php://output", 'w');
		
		fputcsv($df, array_keys(reset($array)));
		
		foreach ($array as $row) {
			fputcsv($df, $row);
		}

		fclose($df);

		return ob_get_clean();
	}

	public static function getName( $name ) {

		// Remove brackets, if any
		$name = preg_replace('/\([^)]+\)/', '', $name);

		// Remove extra white spaces
		$name = preg_replace('~  ~', ' ', $name);

		// Remove everything, which occurs after a comma
		$name = preg_replace('/^([^,]*).*$/', '$1', $name);

		// Remove name titles
		$name = preg_replace('~^(\w+\. )?~', '', $name);

		// Get the name without any description
		$actual_name_pieces = explode(' - ', $name);
		$actual_name = $actual_name_pieces[0];
		$actual_name = rtrim( $actual_name, ' ' );

		$pieces = explode( ' ', $actual_name );

		$result['first_name'] = $pieces[0];

		if ( isset($pieces[1]) ) {
			array_shift( $pieces );
			$result['last_name'] = implode(' ', $pieces);
		} else {
			$result['last_name'] = '';
		}

		return $result;
	}

	public static function countriesMap() {
		return array(
			'af.linkedin.com' => 'Afghanistan',
			'al.linkedin.com' => 'Albania',
			'dz.linkedin.com' => 'Algeria',
			'ar.linkedin.com' => 'Argentina',
			'au.linkedin.com' => 'Australia',
			'at.linkedin.com' => 'Austria',
			'bh.linkedin.com' => 'Bahrain',
			'bd.linkedin.com' => 'Bangladesh',
			'be.linkedin.com' => 'Belgium',
			'bo.linkedin.com' => 'Bolivia',
			'ba.linkedin.com' => 'Bosnia and Herzegovina',
			'br.linkedin.com' => 'Brazil',
			'bg.linkedin.com' => 'Bulgaria',
			'ca.linkedin.com' => 'Canada',
			'cl.linkedin.com' => 'Chile',
			'cn.linkedin.com' => 'China',
			'co.linkedin.com' => 'Colombia',
			'cr.linkedin.com' => 'Costa Rica',
			'hr.linkedin.com' => 'Croatia',
			'cy.linkedin.com' => 'Cyprus',
			'cz.linkedin.com' => 'Czech Republic',
			'dk.linkedin.com' => 'Denmark',
			'do.linkedin.com' => 'Dominican Republic',
			'ec.linkedin.com' => 'Ecuador',
			'eg.linkedin.com' => 'Egypt',
			'sv.linkedin.com' => 'El Salvador',
			'ee.linkedin.com' => 'Estonia',
			'fi.linkedin.com' => 'Finland',
			'fr.linkedin.com' => 'France',
			'de.linkedin.com' => 'Germany',
			'gh.linkedin.com' => 'Ghana',
			'gr.linkedin.com' => 'Greece',
			'gt.linkedin.com' => 'Guatemala',
			'hk.linkedin.com' => 'Hong Kong',
			'hu.linkedin.com' => 'Hungary',
			'is.linkedin.com' => 'Iceland',
			'in.linkedin.com' => 'India',
			'id.linkedin.com' => 'Indonesia',
			'ir.linkedin.com' => 'Iran',
			'ie.linkedin.com' => 'Ireland',
			'il.linkedin.com' => 'Israel',
			'it.linkedin.com' => 'Italy',
			'jm.linkedin.com' => 'Jamaica',
			'jp.linkedin.com' => 'Japan',
			'jo.linkedin.com' => 'Jordan',
			'kz.linkedin.com' => 'Kazakhstan',
			'ke.linkedin.com' => 'Kenya',
			'kr.linkedin.com' => 'Korea',
			'kw.linkedin.com' => 'Kuwait',
			'lv.linkedin.com' => 'Latvia',
			'lb.linkedin.com' => 'Lebanon',
			'lt.linkedin.com' => 'Lithuania',
			'lu.linkedin.com' => 'Luxembourg',
			'mk.linkedin.com' => 'Macedonia',
			'my.linkedin.com' => 'Malaysia',
			'mt.linkedin.com' => 'Malta',
			'mu.linkedin.com' => 'Mauritius',
			'mx.linkedin.com' => 'Mexico',
			'ma.linkedin.com' => 'Morocco',
			'np.linkedin.com' => 'Nepal',
			'nl.linkedin.com' => 'Netherlands',
			'nz.linkedin.com' => 'New Zealand',
			'ng.linkedin.com' => 'Nigeria',
			'no.linkedin.com' => 'Norway',
			'om.linkedin.com' => 'Oman',
			'pk.linkedin.com' => 'Pakistan',
			'pa.linkedin.com' => 'Panama',
			'pe.linkedin.com' => 'Peru',
			'ph.linkedin.com' => 'Philippines',
			'pl.linkedin.com' => 'Poland',
			'pt.linkedin.com' => 'Portugal',
			'pr.linkedin.com' => 'Puerto Rico',
			'qa.linkedin.com' => 'Qatar',
			'ro.linkedin.com' => 'Romania',
			'ru.linkedin.com' => 'Russian Federation',
			'sa.linkedin.com' => 'Saudi Arabia',
			'sg.linkedin.com' => 'Singapore',
			'sk.linkedin.com' => 'Slovak Republic',
			'si.linkedin.com' => 'Slovenia',
			'za.linkedin.com' => 'South Africa',
			'es.linkedin.com' => 'Spain',
			'lk.linkedin.com' => 'Sri Lanka',
			'se.linkedin.com' => 'Sweden',
			'ch.linkedin.com' => 'Switzerland',
			'tw.linkedin.com' => 'Taiwan',
			'tz.linkedin.com' => 'Tanzania',
			'th.linkedin.com' => 'Thailand',
			'tt.linkedin.com' => 'Trinidad and Tobago',
			'tn.linkedin.com' => 'Tunisia',
			'tr.linkedin.com' => 'Turkey',
			'ug.linkedin.com' => 'Uganda',
			'ua.linkedin.com' => 'Ukraine',
			'ae.linkedin.com' => 'United Arab Emirates',
			'uk.linkedin.com' => 'United Kingdom',
			'www.linkedin.com'=> 'United States',
			'uy.linkedin.com' => 'Uruguay',
			've.linkedin.com' => 'Venezuela',
			'vn.linkedin.com' => 'Viet Nam',
			'zw.linkedin.com' => 'Zimbabwe',
		);
	}

	public function getCountries() {
		return array( 'Afghanistan', 'Albania', 'Algeria', 'Argentina', 'Australia', 'Austria', 'Bahrain', 'Bangladesh', 'Belgium', 'Bolivia', 'Bosnia and Herzegovina', 'Brazil', 'Bulgaria', 'Canada', 'Chile', 'China', 'Colombia', 'Costa Rica', 'Croatia', 'Cyprus', 'Czech Republic', 'Denmark', 'Dominican Republic', 'Ecuador', 'Egypt', 'El Salvador', 'Estonia', 'Finland', 'France', 'Germany', 'Ghana', 'Greece', 'Guatemala', 'Hong Kong', 'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran', 'Ireland', 'Israel', 'Italy', 'Jamaica', 'Japan', 'Jordan', 'Kazakhstan', 'Kenya', 'Korea', 'Kuwait', 'Latvia', 'Lebanon', 'Lithuania', 'Luxembourg', 'Macedonia', 'Malaysia', 'Malta', 'Mauritius', 'Mexico', 'Morocco', 'Nepal', 'Netherlands', 'New Zealand', 'Nigeria', 'Norway', 'Oman', 'Pakistan', 'Panama', 'Peru', 'Philippines', 'Poland', 'Portugal', 'Puerto Rico', 'Qatar', 'Romania', 'Russian Federation', 'Saudi Arabia', 'Singapore', 'Slovak Republic', 'Slovenia', 'South Africa', 'Spain', 'Sri Lanka', 'Sweden', 'Switzerland', 'Taiwan', 'Tanzania', 'Thailand', 'Trinidad and Tobago', 'Tunisia', 'Turkey', 'Uganda', 'Ukraine', 'United Arab Emirates', 'United Kingdom', 'United States', 'Uruguay', 'Venezuela', 'Viet Nam', 'Zimbabwe', );
	}

	public function getLocations() {
		return array( 'Africa', 'Algeria', 'Cameroon', 'Egypt', 'Ghana', 'Kenya', 'Morocco', 'Nigeria', 'South Africa', 'Cape Town Area, South Africa', 'Durban Area, South Africa', 'Johannesburg Area, South Africa', 'Tanzania', 'Tunisia', 'Uganda', 'Zimbabwe', 'Antarctica', 'Asia', 'Bangladesh', 'China', 'Beijing City, China', 'Guangzhou, Guangdong, China', 'Shanghai City, China', 'Shenzhen, Guangdong, China', 'Hong Kong', 'India', 'Andaman & Nicobar Islands', 'Andhra Pradesh', 'Hyderabad Area, India', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chandigarh', 'Chattisgarh', 'Dadra& Nagar Haveli', 'Daman & Diu', 'Delhi', 'Goa', 'Gujarat', 'Ahmedabad Area, India', 'Vadodara Area, India', 'Haryana', 'Gurgaon, India', 'New Delhi Area, India', 'Himachal Pradesh', 'Jammu & Kashmir', 'Jharkhand', 'Karnataka', 'Bengaluru Area, India', 'Kerala', 'Cochin Area, India', 'Lakshadweep', 'Madhya Pradesh', 'Indore Area, India', 'Maharashtra', 'Mumbai Area, India', 'Nagpur Area, India', 'Pune Area, India', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Orissa', 'Pondicherry', 'Punjab', 'Chandigarh Area, India', 'Rajasthan', 'Jaipur Area, India', 'Sikkim', 'Tamil Nadu', 'Chennai Area, India', 'Coimbatore Area, India', 'Tripura', 'Uttar Pradesh', 'Lucknow Area, India', 'Noida Area, India', 'Uttarakhand', 'West Bengal', 'Kolkata Area, India', 'Indonesia', 'Greater Jakarta Area, Indonesia', 'Japan', 'Korea', 'Gangnam-gu, Seoul, Korea', 'Malaysia', 'Kuala Lumpur, Malaysia', 'Selangor, Malaysia', 'Nepal', 'Philippines', 'Singapore', 'Sri Lanka', 'Taiwan', 'Thailand', 'Vietnam', 'Europe', 'Austria', 'Belgium', 'Antwerp Area, Belgium', 'Brussels Area, Belgium', 'Bulgaria', 'Croatia', 'Czech Republic', 'Denmark', 'Copenhagen Area, Denmark', 'Odense Area, Denmark', 'Ålborg Area, Denmark', 'Århus Area, Denmark', 'Finland', 'France', 'Lille Area, France', 'Lyon Area, France', 'Marseille Area, France', 'Nice Area, France', 'Paris Area, France', 'Toulouse Area, France', 'Germany', 'Cologne Area, Germany', 'Frankfurt Am Main Area, Germany', 'Munich Area, Germany', 'Greece', 'Hungary', 'Ireland', 'Italy', 'Bologna Area, Italy', 'Milan Area, Italy', 'Rome Area, Italy', 'Turin Area, Italy', 'Venice Area, Italy', 'Lithuania', 'Netherlands', 'Almere Stad Area, Netherlands', 'Amsterdam Area, Netherlands', 'Apeldoorn Area, Netherlands', 'Breda Area, Netherlands', 'Eindhoven Area, Netherlands', 'Enschede Area, Netherlands', 'Groningen Area, Netherlands', 'Nijmegen Area, Netherlands', 'Rotterdam Area, Netherlands', 'The Hague Area, Netherlands', 'Tilburg Area, Netherlands', 'Utrecht Area, Netherlands', 'Norway', 'Oslo Area, Norway', 'Poland', 'Portugal', 'Lisbon Area, Portugal', 'Porto Area, Portugal', 'Romania', 'Russian Federation', 'Serbia', 'Slovak Republic', 'Spain', 'Barcelona Area, Spain', 'Madrid Area, Spain', 'Sweden', 'Switzerland', 'Geneva Area, Switzerland', 'Zürich Area, Switzerland', 'Turkey', 'Istanbul, Turkey', 'Ukraine', 'United Kingdom', 'Birmingham, United Kingdom', 'Brighton, United Kingdom', 'Bristol, United Kingdom', 'Cambridge, United Kingdom', 'Chelmsford, United Kingdom', 'Coventry, United Kingdom', 'Edinburgh, United Kingdom', 'Glasgow, United Kingdom', 'Gloucester, United Kingdom', 'Guildford, United Kingdom', 'Harrow, United Kingdom', 'Hemel Hempstead, United Kingdom', 'Kingston upon Thames, United Kingdom', 'Leeds, United Kingdom', 'Leicester, United Kingdom', 'London, United Kingdom', 'Manchester, United Kingdom', 'Milton Keynes, United Kingdom', 'Newcastle upon Tyne, United Kingdom', 'Northampton, United Kingdom', 'Nottingham, United Kingdom', 'Oxford, United Kingdom', 'Portsmouth, United Kingdom', 'Reading, United Kingdom', 'Redhill, United Kingdom', 'Sheffield, United Kingdom', 'Slough, United Kingdom', 'Southampton, United Kingdom', 'Tonbridge, United Kingdom', 'Twickenham, United Kingdom', 'Latin America', 'Argentina', 'Bolivia', 'Brazil', 'Acre', 'Alagoas', 'Amapá', 'Amazonas', 'Bahia', 'Ceará', 'Distrito Federal', 'Espírito Santo', 'Goiás', 'Maranhão', 'Mato Grosso', 'Mato Grosso do Sul', 'Minas Gerais', 'Belo Horizonte Area, Brazil', 'Paraná', 'Curitiba Area, Brazil', 'Paraíba', 'Pará', 'Pernambuco', 'Piauí', 'Rio Grande do Norte', 'Rio Grande do Sul', 'Porto Alegre Area, Brazil', 'Rio de Janeiro', 'Rio de Janeiro Area, Brazil', 'Rondônia', 'Roraima', 'Santa Catarina', 'Sergipe', 'São Paulo', 'Campinas Area, Brazil', 'São Paulo Area, Brazil', 'Tocantins', 'Chile', 'Colombia', 'Costa Rica', 'Dominican Republic', 'Ecuador', 'Guatemala', 'Mexico', 'Mexico City Area, Mexico', 'Naucalpan de Juárez Area, Mexico', 'Panama', 'Peru', 'Puerto Rico', 'Trinidad and Tobago', 'Uruguay', 'Venezuela', 'Middle East', 'Bahrain', 'Israel', 'Jordan', 'Kuwait', 'Pakistan', 'Qatar', 'Saudi Arabia', 'United Arab Emirates', 'North America', 'Canada', 'Alberta', 'Calgary, Canada Area', 'Edmonton, Canada Area', 'British Columbia', 'British Columbia, Canada', 'Vancouver, Canada Area', 'Manitoba', 'New Brunswick', 'Newfoundland And Labrador', 'Northwest Territories', 'Nova Scotia', 'Halifax, Canada Area', 'Nunavut', 'Ontario', 'Kitchener, Canada Area', 'London, Canada Area', 'Ontario, Canada', 'Toronto, Canada Area', 'Prince Edward Island', 'Quebec', 'Montreal, Canada Area', 'Ottawa, Canada Area', 'Quebec, Canada', 'Winnipeg, Canada Area', 'Saskatchewan', 'Yukon', 'United States', 'Alabama', 'Birmingham, Alabama Area', 'Alaska', 'Anchorage, Alaska Area', 'Arizona', 'Phoenix, Arizona Area', 'Tucson, Arizona Area', 'Arkansas', 'Little Rock, Arkansas Area', 'California', 'Fresno, California Area', 'Greater Los Angeles Area', 'Greater San Diego Area', 'Orange County, California Area', 'Sacramento, California Area', 'Salinas, California Area', 'San Francisco Bay Area', 'Santa Barbara, California Area', 'Stockton, California Area', 'Colorado', 'Colorado Springs, Colorado Area', 'Fort Collins, Colorado Area', 'Greater Denver Area', 'Connecticut', 'Hartford, Connecticut Area', 'New London/Norwich, Connecticut Area', 'Delaware', 'District Of Columbia', 'Washington D.C. Metro Area', 'Florida', 'Daytona Beach, Florida Area', 'Fort Myers, Florida Area', 'Fort Pierce, Florida Area', 'Gainesville, Florida Area', 'Lakeland, Florida Area', 'Melbourne, Florida Area', 'Miami/Fort Lauderdale Area', 'Orlando, Florida Area', 'Sarasota, Florida Area', 'Tampa/St. Petersburg, Florida Area', 'West Palm Beach, Florida Area', 'Georgia', 'Greater Atlanta Area', 'Jacksonville, Florida Area', 'Tallahassee, Florida Area', 'Hawaii', 'Hawaiian Islands', 'Idaho', 'Boise, Idaho Area', 'Illinois', 'Greater Chicago Area', 'Peoria, Illinois Area', 'Urbana-Champaign, Illinois Area', 'Indiana', 'Evansville, Indiana Area', 'Indianapolis, Indiana Area', 'Iowa', 'Kansas', 'Wichita, Kansas Area', 'Kentucky', 'Lexington, Kentucky Area', 'Louisville, Kentucky Area', 'Louisiana', 'Maine', 'Portland, Maine Area', 'Maryland', 'Baltimore, Maryland Area', 'Massachusetts', 'Greater Boston Area', 'Michigan', 'Fort Wayne, Indiana Area', 'Greater Detroit Area', 'Greater Grand Rapids, Michigan Area', 'Kalamazoo, Michigan Area', 'Lansing, Michigan Area', 'Saginaw, Michigan Area', 'Minnesota', 'Greater Minneapolis-St. Paul Area', 'Mississippi', 'Baton Rouge, Louisiana Area', 'Greater New Orleans Area', 'Jackson, Mississippi Area', 'Mobile, Alabama Area', 'Missouri', 'Columbia, Missouri Area', 'Davenport, Iowa Area', 'Des Moines, Iowa Area', 'Fayetteville, Arkansas Area', 'Greater St. Louis Area', 'Kansas City, Missouri Area', 'Springfield, Missouri Area', 'Montana', 'Nebraska', 'Greater Omaha Area', 'Lincoln, Nebraska Area', 'Nevada', 'Las Vegas, Nevada Area', 'Reno, Nevada Area', 'New Hampshire', 'New Jersey', 'New Mexico', 'Albuquerque, New Mexico Area', 'New York', 'Albany, New York Area', 'Buffalo/Niagara, New York Area', 'Greater New York City Area', 'Rochester, New York Area', 'Syracuse, New York Area', 'North Carolina', 'Charlotte, North Carolina Area', 'Raleigh-Durham, North Carolina Area', 'Wilmington, North Carolina Area', 'North Dakota', 'Ohio', 'Cincinnati Area', 'Cleveland/Akron, Ohio Area', 'Columbus, Ohio Area', 'Dayton, Ohio Area', 'Toledo, Ohio Area', 'Oklahoma', 'Oklahoma City, Oklahoma Area', 'Tulsa, Oklahoma Area', 'Oregon', 'Eugene, Oregon Area', 'Portland, Oregon Area', 'Pennsylvania', 'Allentown, Pennsylvania Area', 'Greater Philadelphia Area', 'Greater Pittsburgh Area', 'Harrisburg, Pennsylvania Area', 'Ithaca, New York Area', 'Lancaster, Pennsylvania Area', 'Scranton, Pennsylvania Area', 'Rhode Island', 'Providence, Rhode Island Area', 'South Carolina', 'Augusta, Georgia Area', 'Charleston, South Carolina Area', 'Columbia, South Carolina Area', 'Greenville, South Carolina Area', 'Savannah, Georgia Area', 'South Dakota', 'Sioux Falls, South Dakota Area', 'Tennessee', 'Asheville, North Carolina Area', 'Chattanooga, Tennessee Area', 'Greater Memphis Area', 'Greater Nashville Area', 'Huntsville, Alabama Area', 'Johnson City, Tennessee Area', 'Knoxville, Tennessee Area', 'Texas', 'Austin, Texas Area', 'Dallas/Fort Worth Area', 'El Paso, Texas Area', 'Houston, Texas Area', 'San Antonio, Texas Area', 'Utah', 'Greater Salt Lake City Area', 'Provo, Utah Area', 'Vermont', 'Burlington, Vermont Area', 'Springfield, Massachusetts Area', 'Virginia', 'Charlottesville, Virginia Area', 'Greensboro/Winston-Salem, North Carolina Area', 'Norfolk, Virginia Area', 'Richmond, Virginia Area', 'Roanoke, Virginia Area', 'Washington', 'Greater Seattle Area', 'Spokane, Washington Area', 'West Virginia', 'Wisconsin', 'Greater Milwaukee Area', 'Green Bay, Wisconsin Area', 'Madison, Wisconsin Area', 'Oshkosh, Wisconsin Area', 'Rockford, Illinois Area', 'Wyoming', 'Oceania', 'Australia', 'Adelaide Area, Australia', 'Brisbane Area, Australia', 'Canberra Area, Australia', 'Melbourne Area, Australia', 'Perth Area, Australia', 'Sydney Area, Australia', 'New Zealand', );
	}

	public function getIndustries() {
		return array( 'Accounting', 'Airlines/Aviation', 'Alternative Dispute Resolution', 'Alternative Medicine', 'Animation', 'Apparel &amp; Fashion', 'Architecture &amp; Planning', 'Arts and Crafts', 'Automotive', 'Aviation &amp; Aerospace', 'Banking', 'Biotechnology', 'Broadcast Media', 'Building Materials', 'Business Supplies and Equipment', 'Capital Markets', 'Chemicals', 'Civic &amp; Social Organization', 'Civil Engineering', 'Commercial Real Estate', 'Computer &amp; Network Security', 'Computer Games', 'Computer Hardware', 'Computer Networking', 'Computer Software', 'Construction', 'Consumer Electronics', 'Consumer Goods', 'Consumer Services', 'Cosmetics', 'Dairy', 'Defense &amp; Space', 'Design', 'Education Management', 'E-Learning', 'Electrical/Electronic Manufacturing', 'Entertainment', 'Environmental Services', 'Events Services', 'Executive Office', 'Facilities Services', 'Farming', 'Financial Services', 'Fine Art', 'Fishery', 'Food &amp; Beverages', 'Food Production', 'Fund-Raising', 'Furniture', 'Gambling &amp; Casinos', 'Glass, Ceramics &amp; Concrete', 'Government Administration', 'Government Relations', 'Graphic Design', 'Health, Wellness and Fitness', 'Higher Education', 'Hospital &amp; Health Care', 'Hospitality', 'Human Resources', 'Import and Export', 'Individual &amp; Family Services', 'Industrial Automation', 'Information Services', 'Information Technology and Services', 'Insurance', 'International Affairs', 'International Trade and Development', 'Internet', 'Investment Banking', 'Investment Management', 'Judiciary', 'Law Enforcement', 'Law Practice', 'Legal Services', 'Legislative Office', 'Leisure, Travel &amp; Tourism', 'Libraries', 'Logistics and Supply Chain', 'Luxury Goods &amp; Jewelry', 'Machinery', 'Management Consulting', 'Maritime', 'Marketing and Advertising', 'Market Research', 'Mechanical or Industrial Engineering', 'Media Production', 'Medical Devices', 'Medical Practice', 'Mental Health Care', 'Military', 'Mining &amp; Metals', 'Motion Pictures and Film', 'Museums and Institutions', 'Music', 'Nanotechnology', 'Newspapers', 'Nonprofit Organization Management', 'Oil &amp; Energy', 'Online Media', 'Outsourcing/Offshoring', 'Package/Freight Delivery', 'Packaging and Containers', 'Paper &amp; Forest Products', 'Performing Arts', 'Pharmaceuticals', 'Philanthropy', 'Photography', 'Plastics', 'Political Organization', 'Primary/Secondary Education', 'Printing', 'Professional Training &amp; Coaching', 'Program Development', 'Public Policy', 'Public Relations and Communications', 'Public Safety', 'Publishing', 'Railroad Manufacture', 'Ranching', 'Real Estate', 'Recreational Facilities and Services', 'Religious Institutions', 'Renewables &amp; Environment', 'Research', 'Restaurants', 'Retail', 'Security and Investigations', 'Semiconductors', 'Shipbuilding', 'Sporting Goods', 'Sports', 'Staffing and Recruiting', 'Supermarkets', 'Telecommunications', 'Textiles', 'Think Tanks', 'Tobacco', 'Translation and Localization', 'Transportation/Trucking/Railroad', 'Utilities', 'Venture Capital &amp; Private Equity', 'Veterinary', 'Warehousing', 'Wholesale', 'Wine and Spirits', 'Wireless', 'Writing and Editing', );
	}

}