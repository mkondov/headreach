<?php

namespace app\models;
use Yii;

class GoogleAPI extends \yii\base\Object
{

    public $key = 'AIzaSyCMGfdDaSfjqv5zYoS0mTJnOT3e9MURWkU';
    public $cx = '006728377330183617582:4lqwegbhtm0';
    // public $cx = '006728377330183617582:_-oano1fzwa';

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    public function getAPIResults( $query, $pages = 3 ) {

        $queries_model = new GoogleQuery();

        $results_total = array();
        $search_data = array();
        $fullurl = '';

        for ($i=1; $i < ($pages+1); $i++) {
            $start = ( $i > 1 ? ($i-1) * 10 : $i );
            $url = 'https://www.googleapis.com/customsearch/v1?key='. $this->key .'&cx='. $this->cx .'&q='. rawurlencode($query) . '&filter=0&start=' . $start;
            
            $data = $this->cURLGoogle( $url );
            $fullurl = $url;
            
            // Break the loop if no data
            if ( empty($data) ) {
                break;
            }

            $results_data = $data;

            if ( isset($data['kind']) ) {
                $results_data = $data['items'];
                if ( empty($search_data) ) {
                    $search_data = $data;
                }
            }

            foreach ($results_data as $result) {
                $results_total[] = $this->formatResult( $result );
            }

            // Stop the loop, if next page doesn't exists
            if ( $this->noNextPage( $data ) ) {
                break;
            }

            unset($url);
        }

        $results_total = $this->getUniqueResults( $results_total );

        return array(
            'results_total' => $results_total,
            'total_count' => $this->getTotalCount( $search_data ),
            'raw_data' => $data,
            'fullurl' => $fullurl,
        );
    }

    public function getDirectResults( $query ) {

        $queries_model = new GoogleQuery();

        $results_total = array();
        $search_data = array();
        
        $data = $this->cURLGoogle( $query );
        
        // Break the loop if no data
        if ( empty($data) ) {
            return array(
                'results_total' => $results_total,
                'total_count' => $this->getTotalCount( $search_data ),
                'raw_data' => $data,
                'fullurl' => $query,
            );
        }

        $results_data = $data;

        if ( isset($data['kind']) ) {
            $results_data = $data['items'];
            if ( empty($search_data) ) {
                $search_data = $data;
            }
        }

        foreach ($results_data as $result) {
            $results_total[] = $this->formatResult( $result );
        }

        $results_total = $this->getUniqueResults( $results_total );

        return array(
            'results_total' => $results_total,
            'total_count' => $this->getTotalCount( $search_data ),
            'raw_data' => $data,
            'fullurl' => $query,
        );
    }

    public function noNextPage( $data ) {

        // Backwards compatibility
        if ( !isset($data['url']) ) {
            return false;
        }

        // Next page doesn't exists
        if ( !isset($data['queries']['nextPage']) ) {
            return true;
        }

        return false;
    }

    public function getCompanyResults( $query ) {

        $queries_model = new GoogleQuery();

        $url = 'https://www.googleapis.com/customsearch/v1?key='. $this->key .'&cx='. $this->cx .'&q='. rawurlencode($query) . '&filter=0';
        $data = $this->cURLGoogle( $url );

        if ( isset($data['items']) ) {
            return $data;
        }

        /*
        $db_data = $queries_model->getCachedData( $url );

        if ( $db_data['found'] == true AND $db_data['results'] ) {
            $data = $db_data['results'];
        } else if ( $db_data['found'] == true AND !$db_data['results'] ) {
            $data = false;
        } else {
            $data = $this->cURLGoogle( $url );
            $queries_model->storeData( $url, $data );
        }
        
        if ( empty($data) ) {
            return array();
        }

        if ( isset($data['kind']) ) {
            $data = $data['items'];
        }

        return $data;
        */
        
    }

    public function getTmpData( $query ) {

        $queries_model = new GoogleQuery();

        $url = 'https://www.googleapis.com/customsearch/v1?key='. $this->key .'&cx='. $this->cx .'&q='. rawurlencode($query) . '&filter=0';
        $data = $this->cURLGoogle( $url );

        echo '<pre>';
        print_r( $data );
        echo '</pre>';
        exit;

        $db_data = $queries_model->getCachedData( $url );

        if ( $db_data['found'] == true AND $db_data['results'] ) {
            $data = $db_data['results'];
        } else if ( $db_data['found'] == true AND !$db_data['results'] ) {
            $data = false;
        } else {
            $data = $this->cURLGoogle( $url );
            $queries_model->storeData( $url, $data );
        }
        
        if ( empty($data) ) {
            return array();
        }

        if ( isset($data['kind']) ) {
            $data = $data['items'];
        }

        return $data;
    }

    public function getTotalCount( $data ) {
        if ( empty($data) ) {
            return false;
        }

        if ( isset($data['queries']['request'][0]['totalResults']) ) {
            return $data['queries']['request'][0]['totalResults'];
        }
    }

    public function cURLGoogle( $url ) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        
        if ( isset($_SERVER['HTTP_USER_AGENT']) ) {
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        }

        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($ch);
        curl_close($ch);

        if ( $result ) {
            $results_arr = json_decode($result, true);

            if ( isset($results_arr['items']) ) {
                return $results_arr;
            }

        }

        return false;
    }

    public function formatResult( $result ) {

        $data = array();

        if (
            !isset($result['pagemap']) OR
            empty($result['pagemap']) OR
            !isset($result['pagemap']['hcard'][0]) OR
            !isset($result['link'])
        ) {
            return array();
        }

        $data['snippet'] = ( isset($result['snippet']) ? $result['snippet'] : '' );

        $hcard = $result['pagemap']['hcard'][0];

        if ( isset($hcard['fn']) ) {
            $data['name'] = $hcard['fn'];
        }

        if ( isset($hcard['title']) ) {
            $data['title'] = $hcard['title'];
        }

        if ( isset($hcard['photo']) ) {

            if ( filter_var($hcard['photo'], FILTER_VALIDATE_URL) ) {
                $data['photo'] = $hcard['photo'];
            }

        }

        if ( !isset($data['photo']) ) {
            if ( isset($result['pagemap']['cse_image'][0]['src']) ) {
                $cse_image = $result['pagemap']['cse_image'][0]['src'];
                if ( filter_var($cse_image, FILTER_VALIDATE_URL) ) {
                    $data['photo'] = $cse_image;
                }
            }
        }

        if ( isset($result['pagemap']['person'][0]) ) {
            $cperson = $result['pagemap']['person'][0];

            if ( isset($cperson['org']) ) {
                $data['company'] = $cperson['org'];
            }

            if ( isset($cperson['location']) ) {
                $data['location'] = $cperson['location'];
            }
        }

        $data['profile_link'] = $result['link'];

        return $data;
    }

    public function getUniqueResults( $results ) {

        if ( empty($results) ) {
            return $results;
        }

        $uq_results = array();
        $uq_urls = array();

        foreach ($results as $result) {
            
            if ( empty($result) ) {
                continue;
            }

            $link = $result['profile_link'];

            // Get everything after .com/in/
            preg_match('~^.*com\/in\/(.*)$~', $link, $parts);

            if ( !isset($parts[1]) ) {
                continue;
            }

            $tmp_last_part = strtok( $parts[1], '?' );
            $lp_parts = explode('/', $tmp_last_part);
            $last_part = $lp_parts[0];

            if ( !in_array($last_part, $uq_urls) ) {
                $uq_results[] = (object) $result; // Cast objects
                $uq_urls[] = $last_part;
            }

        }

        return $uq_results;
    }

}