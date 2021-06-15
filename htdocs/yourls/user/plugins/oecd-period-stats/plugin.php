<?php
/*
Plugin Name: oecd-period-stats
Plugin URI: http://no.url.yet
Description: search for stats by period
Version: 1.0
Author: OECD
Author URI: 
*/

// $Id$

define('YOURLS_INFOS', true);

define('MONTH_FILTER'," AND DATE(click_time) >= DATE(NOW()) - INTERVAL (DAY(DATE(NOW()))-1) DAY");
define('WEEK_FILTER'," AND DATE(click_time) >= SUBDATE(DATE(NOW()), INTERVAL WEEKDAY(DATE(NOW())) DAY)");
define('DAY_FILTER'," AND DATE(click_time) >= (DATE(NOW()))");
define('TWOHOURS_FILTER'," AND click_time >= (NOW() - INTERVAL 120 MINUTE)");

include_once( YOURLS_INC . '/load-yourls.php' );
require_once( YOURLS_INC . '/functions.php' );
require_once( YOURLS_INC . '/functions-infos.php' );

yourls_add_action('api', 'stats_period');


/**
 * Non utilisé / mauvais affichage graphique
 * Fonction de remplissage du tableau Traffic pour un retour SQL vide
 * @param type $min
 * @param type $max
 * @return type 
 */
function fillTraffic($min, $max){
    
    $return = array();
          
    $return[1] =  array(
                        'echelle' => $min,
			'clicks'      => 0,
		);
    $return[2] =  array(
			'echelle' => $max,
			'clicks'      => 0,
		);                  
            
    return $return;
}


/**
 * Fonction de contrôle de l'age du lien
 * @global type $ydb
 * @param type $id
 * @return type 
 */
 function isLinkLessThenOneYearOld($id){

     global $ydb;   
     
     // ALWAYS SANITIZE USER-SIDE INPUTS!!!
     $id = $ydb->escape($id);
     
     $query = "SELECT IF(TO_DAYS(NOW()) - TO_DAYS(TIMESTAMP) < 365, 1, 0) as link_age FROM yourls_url WHERE keyword = '$id'";
     
     $results = $ydb->get_results($query);
     
     return $results[0]->link_age; 
 }
 
 /**
  * Recerche de la date de création du keyword
  * @global type $ydb
  * @param type $id
  * @return type 
  */
 function getCreationDate($id){
     
     global $ydb;
     
     // ALWAYS SANITIZE USER-SIDE INPUTS!!!
     $id = $ydb->escape($id);
     
     $query = "SELECT timestamp as creation_date FROM yourls_url WHERE keyword = '$id'";
     
     $results = $ydb->get_results($query);
     
     return $results[0]->creation_date;
 }
 
/**
 * Fonction de recherche des données url par période
 * @global type $ydb
 * @param type $period
 * @param type $start
 * @param type $limit
 * @param type $sort_by
 * @param type $sort_order
 * @return type 
 */
 function get_stats($period, $start, $limit, $sort_by, $sort_order, $search, $namespace = 'get_all' ) {      
      
        global $ydb;
        global $namespaces;
        
        // ALWAYS SANITIZE USER-SIDE INPUTS!!!
        $start = $ydb->escape($start);
        $limit = $ydb->escape($limit);
        $sort_by = $ydb->escape($sort_by); 
        $sort_order = $ydb->escape($sort_order);
        $search = $ydb->escape($search);
        $namespace = $ydb->escape($namespace);
        
	if ( $limit > 0 )
	{
	    /** Start of query construction */
                
	    // Click statistics period selected
        switch( $period )
        {
            // All time
            case 'all':
            // Other
            default:
                $where_date = '1=1';
                break;
                
            // Two hours
            case '2h':
                $where_date = 'l.click_time >= (NOW() - INTERVAL 120 MINUTE)';
                break;
                
            // Day
            case 'day':
                $where_date = 'DATE(l.click_time) >= (DATE(NOW()))';
                break;
                
            // Week
            case 'week':
                $where_date = 'DATE(l.click_time) >= SUBDATE(DATE(NOW()), INTERVAL WEEKDAY(DATE(NOW())) DAY)';
                break;
                
            // Month
            case 'month':
                $where_date = 'DATE(l.click_time) >= DATE(NOW()) - INTERVAL (DAY(DATE(NOW()))-1) DAY';
                break;
        }
        
        // Add keyword if needed
        switch ($namespace)
        {
            
            // No filter
            case 'get_all':
                $keyword = "keyword LIKE '%$search%'";
                $long_url = "url LIKE '%$search%'";
                break;
                
            // All but keyword
            case 'get_other':
                $keyword = "keyword LIKE '%$search%'";
                $long_url = "url LIKE '%$search%'";
                $not_keyword = "";
                $not_url = "";
                foreach ( $namespaces as $prefix => $data ) {
                    $not_keyword.= "keyword NOT LIKE '$prefix/%' AND ";
                    $domains = (is_array($data['domain'])) ? $data['domain'] : array($data['domain']);
                    foreach ($domains as $domain) {
                        $domain = str_replace( array( '%', '_', '.*' ), array( '\%', '\_', '%' ), $domain );
                        $not_url.= "url NOT LIKE '%$domain%' AND ";
                    }
                }
                $not_keyword= substr($not_keyword, 0, -4);
                $not_url= substr($not_url, 0, -4);
                break;
                
            // One keyword
            default:
                $domains = ( isset( $namespaces[ $namespace ]['domain'] ) ) ? $namespaces[ $namespace ]['domain'] : null;
                if ( !is_array($domains) ) {
                    $domains = array($domains);
                }
                $keyword = "keyword LIKE '$namespace/%$search%'";
                $long_url = "url ";
                foreach ( $domains as $domain ) {
                    $domain = str_replace( array( '%', '_', '.*' ), array( '\%', '\_', '%' ), $domain );
                    $long_url .= "LIKE '%$domain%$search%' OR url ";
                }
                $long_url = substr($long_url, 0, -7);
                break;
        }
        
        // Special queries if the click period is all (huge performance gain)
        if ( $where_date == '1=1' ) {
            $query = "
            SELECT keyword, url, title, timestamp, ip, clicks as clicks_period FROM yourls_url u";
            
            $query_count = "
            SELECT COUNT(keyword) as count_links, IFNULL(SUM(clicks), 0) as sum_clicks FROM yourls_url u";
        }
        // Standard queries
        else {
            $query = "
            SELECT keyword, url, title, timestamp, ip, IFNULL(clicks_period, 0) as clicks_period FROM yourls_url u
            LEFT OUTER JOIN (
                SELECT l.shorturl, COUNT(*) AS clicks_period
                FROM yourls_log l WHERE $where_date
                GROUP BY l.shorturl
            ) l
            ON l.shorturl = u.keyword";

            $query_count = "
            SELECT COUNT(keyword) as count_links, IFNULL(SUM(clicks_period), 0) as sum_clicks FROM yourls_url u
            LEFT OUTER JOIN (
                SELECT l.shorturl, COUNT(*) AS clicks_period
                FROM yourls_log l WHERE $where_date
                GROUP BY l.shorturl
            ) l
            ON l.shorturl = u.keyword";
        }
        
        // All but keywords special queries
        if ( $namespace == 'get_other' ) {
            $query .= "
            WHERE ($keyword OR $long_url) AND ($not_keyword AND $not_url)";
            
            $query_count .= "
            WHERE ($keyword OR $long_url) AND ($not_keyword AND $not_url)";
        }
        // Standard queries
        else {
            $query .= "
            WHERE ($keyword) OR ($long_url)";
            
            $query_count .= "
            WHERE ($keyword) OR ($long_url)";
        }
        
        $query .= "
        ORDER BY $sort_by $sort_order
        LIMIT $start, $limit";
                
        /** End of query contrustion */
        
        /** Start of results construction */
                
		$results = $ydb->get_results($query);              
                
		$return = array();
		$i = 1;
		
        // Construction du tableau de liens
		foreach ( (array)$results as $res)
		{
			$return['links']['link_'.$i++] = array(
				'shorturl' => YOURLS_SITE .'/'. $res->keyword,
				'url'      => $res->url,
				'title'    => $res->title,
				'timestamp'=> $res->timestamp,
				'ip'       => $res->ip,
				'clicks'   => $res->clicks_period,
			);
		}
                
        // Contruction du tableau de statistiques
		$res_count = array_shift($ydb->get_results($query_count));
    	$return['stats'] = array (
    		'total_links'  => $res_count->count_links,
    		'total_clicks' => $res_count->sum_clicks,
    	);
	}
	
	$return['statusCode'] = 200;
    $return['message'] = "success";
        
    return json_encode($return);
}

/**
 * Fonction de recherche des données d'une url réduite
 * @global type $ydb
 * @param type $id
 * @param type $period
 * @return type 
 */    
function get_stats_url($id, $period){
    
    global $ydb;
    
    // ALWAYS SANITIZE USER-SIDE INPUTS!!!
    $id = $ydb->escape($id);
    
    $queryCountries = "SELECT country_name_en as country_code, COUNT(click_id) AS clicks
                        FROM yourls_log, countries
                        WHERE countries.country_code = yourls_log.country_code AND shorturl = '$id'";
    
    $queryReferrers = "SELECT referrer, COUNT(click_id) AS clicks
                        FROM yourls_log
                        WHERE shorturl = '$id'";
    
    $queryBrowsers = "SELECT CASE 
                        WHEN user_agent REGEXP ('MSIE')=1 THEN 'Internet Explorer'
                        WHEN user_agent REGEXP ('Firefox')=1 THEN 'Mozilla Firefox'
                        WHEN user_agent REGEXP ('Chrome')=1 THEN 'Google Chrome'
                        WHEN user_agent REGEXP ('Safari')=1 THEN 'Safari'
                        WHEN user_agent REGEXP ('Opera')=1 THEN 'Opera'
                        ELSE 'Others' END AS browser, 
                        COUNT(click_id) AS clicks
                      FROM yourls_log
                      WHERE shorturl = '$id'";
    
    $queryPlatforms = "SELECT CASE 
                        WHEN user_agent REGEXP ('Windows')=1 THEN 'Windows'
                        WHEN user_agent REGEXP ('iPhone')=1 THEN 'iPhone'
                        WHEN user_agent REGEXP ('iPad')=1 THEN 'iPad'
                        WHEN user_agent REGEXP ('Macintosh')=1 THEN 'Mac' 
                        WHEN user_agent REGEXP ('Android')=1 THEN 'Android'
                        WHEN user_agent REGEXP ('Bot')=1 THEN 'Search engines'
                        WHEN user_agent REGEXP ('Linux')=1 THEN 'Linux'
                        WHEN user_agent REGEXP ('Blackberry')=1 THEN 'Blackberry'
                        ELSE 'Others' END AS platform, 
                        COUNT(click_id) AS clicks
                       FROM yourls_log
                       WHERE shorturl = '$id'";
                       
    
    switch( $period ) {
                
                    case '2h':
                        
                        $queryCountries = $queryCountries.TWOHOURS_FILTER;
                        $queryReferrers = $queryReferrers.TWOHOURS_FILTER;
                        $queryBrowsers = $queryBrowsers.TWOHOURS_FILTER;
                        $queryPlatforms = $queryPlatforms.TWOHOURS_FILTER;
                        
                        $queryTraffic = "SELECT click_time AS echelle, COUNT(click_time) AS clicks
                                            FROM yourls_log  
                                            WHERE  shorturl = '$id' ".TWOHOURS_FILTER." GROUP BY HOUR(echelle)
                                            ORDER BY echelle ASC";
                        
                        // Pour les tests
                        //$min = date('Y-m-d H:i:s',mktime(8, 40, 0, 8, 14, 2012));
                        //$max = date('Y-m-d H:i:s',mktime(10, 40, 0, 8, 14, 2012));
                        
                        // Pour la production
                        $min = date('Y-m-d H:i:s',mktime(date('H')-2,date('i'),date('s')));
                        $max = date('Y-m-d H:i:s');
                                                
                        break;
                    case 'day':
                        
                        $queryCountries = $queryCountries.DAY_FILTER;
                        $queryReferrers = $queryReferrers.DAY_FILTER;
                        $queryBrowsers = $queryBrowsers.DAY_FILTER;
                        $queryPlatforms = $queryPlatforms.DAY_FILTER;
                        
                        $queryTraffic = "SELECT click_time AS echelle, COUNT(click_time) AS clicks 
                                            FROM yourls_log 
                                            WHERE shorturl = '$id' ".DAY_FILTER." GROUP BY HOUR(echelle)
                                            ORDER BY echelle ASC";
                        
                        // Pour les tests
                        //$min = date('Y-m-d H:i:s',mktime(0, 0, 0, 8, 14, 2012));
                        //$max = date('Y-m-d H:i:s',mktime(23, 59, 59, 8, 14, 2012));
                        
                        // Pour la production
                        $min = date("Y-m-d H:i:s",mktime(0, 0, 0));
                        $max = date("Y-m-d H:i:s",mktime(23, 59, 59));
                        
                        break;
                    case 'week':
                        
                        $queryCountries = $queryCountries.WEEK_FILTER;
                        $queryReferrers = $queryReferrers.WEEK_FILTER;
                        $queryBrowsers = $queryBrowsers.WEEK_FILTER;
                        $queryPlatforms = $queryPlatforms.WEEK_FILTER;
                        
                        $queryTraffic = "SELECT DATE(click_time) AS echelle, COUNT(click_time) AS clicks 
                                            FROM yourls_log l 
                                            WHERE shorturl = '$id' ".WEEK_FILTER." GROUP BY DAY(echelle)
                                            ORDER BY echelle ASC";
                        
                        // Pour les tests
                        //$min = date('Y-m-d H:i:s',mktime(0, 0, 0, 8, 13, 2012));
                        //$max = date('Y-m-d H:i:s',mktime(0, 0, 0, 8, 19, 2012));
                        
                        // Pour la production
                        $dateDuJour = strtotime(date('Y-m-d')); 
                        $min = date('Y-m-d', strtotime('this week last monday', $dateDuJour));
                        $max = date('Y-m-d', strtotime('this week next sunday', $dateDuJour));
                        
                        break;
                    case 'month':
                        
                        $queryCountries = $queryCountries.MONTH_FILTER;
                        $queryReferrers = $queryReferrers.MONTH_FILTER;
                        $queryBrowsers = $queryBrowsers.MONTH_FILTER;
                        $queryPlatforms = $queryPlatforms.MONTH_FILTER;
                        
                        $queryTraffic = "SELECT DATE(click_time) AS echelle, COUNT(click_time) AS clicks 
                                            FROM yourls_log l 
                                            WHERE shorturl = '$id' ".MONTH_FILTER." GROUP BY echelle
                                            ORDER BY echelle ASC";
                        
                        $min = date('Y-m-01');
                        $max = date ('Y-m-d', mktime(0,0,0,date('m')+1,0,date('Y')));
                        
                        break;
                    case 'all':
                        
                        if(isLinkLessThenOneYearOld($id)){
                            
                            $queryTraffic = "SELECT DATE(click_time) AS echelle, COUNT(click_time) as clicks
                                            FROM yourls_log
                                            WHERE shorturl = '$id'"." GROUP BY WEEK(echelle)
                                            ORDER BY echelle ASC";
                            
                            $min = date('Y-01-01');
                            $max = date('Y-12-31');
                            
                        } else {
                            
                            $queryTraffic = "SELECT DATE(click_time) AS echelle, COUNT(click_time) as clicks
                                            FROM yourls_log
                                            WHERE shorturl = '$id'"." GROUP BY CONCAT(MONTH(echelle),YEAR(echelle))
                                            ORDER BY echelle ASC";
                            
                            $min = getCreationDate($id);                            
                            $max = date('Y-m-d');
                        }
                                                
                        break;
                    default:
                        
                        break;
                }
    
    $queryCountries = $queryCountries." GROUP BY country_code
                                        ORDER BY clicks DESC LIMIT 0, 10";
    $queryReferrers = $queryReferrers." GROUP BY referrer
                                        ORDER BY clicks DESC LIMIT 0, 10";           
    $queryPlatforms = $queryPlatforms." GROUP BY platform
                        ORDER BY clicks DESC";
    $queryBrowsers = $queryBrowsers." GROUP BY browser
                        ORDER BY clicks DESC";
        
    $resultsCountries = $ydb->get_results($queryCountries);
    $resultsReferrers = $ydb->get_results($queryReferrers);
    $resultsTraffic = $ydb->get_results($queryTraffic);
    $resultsPlatforms = $ydb->get_results($queryPlatforms);
    $resultsBrowsers = $ydb->get_results($queryBrowsers);
    
    $return = array();
    
    $i = 1;
    // construction du tableau de résultat des pays
    foreach ( (array)$resultsCountries as $res_c) {
			$return['countries']['country_'.$i++] = array(
				'item' => utf8_encode($res_c->country_code),
				'clicks'      => $res_c->clicks,
			);
		}
    
    if($i == 1){
        
        $return['countries']['country_1'] = array('item' => 'No data available', 'clicks' => '');
    }
                
                
    $i = 1;
    // construction du tableau de résultat des referrers
    foreach ( (array)$resultsReferrers as $res_r) {
			$return['referrers']['referrer_'.$i++] = array(
				'item' => $res_r->referrer,
				'clicks'      => $res_r->clicks,
			);
		}
    
    if($i == 1){
        
        $return['referrers']['referrer_1'] = array('item' => 'No data available', 'clicks' => '');
    }
    
    $i = 1;
    // construction du tableau de résultat de l'historique du traffic
    foreach ( (array)$resultsTraffic as $res_t) {
			$return['traffic'][$i++] = array(
				'echelle' => $res_t->echelle,
				'clicks'      => $res_t->clicks,
			);
		}
    
    if($i == 1){
        
        $return['traffic']['1'] = array('echelle' => 'No data available', 'clicks' => '');
    
    }     
    
    $i = 1;
    // construction du tableau de résultat des plateformes
    foreach ( (array)$resultsPlatforms as $res_p) {
			$return['platforms'][$i++] = array(
				'item' => $res_p->platform,
				'clicks'      => $res_p->clicks,
			);
		}
    
    if($i == 1){
        
        $return['platforms']['1'] = array('item' => 'No data available', 'clicks' => '');
    }
    
    
    $i = 1;
    // construction du tableau de résultat des navigateurs
    foreach ( (array)$resultsBrowsers as $res_b) {
			$return['browsers'][$i++] = array(
				'item' => $res_b->browser,
				'clicks'      => $res_b->clicks,
			);
		}
    
    if($i == 1){
        
        $return['browsers']['1'] = array('item' => 'No data available', 'clicks' => '');
    }
    
    $return['statusCode'] = 200;
    $return['message'] = "success";
    $return['min'] = $min;
    $return['max'] = $max;
    
    $shorturls = get_shorturls($id);
    if(count($shorturls) > 1){
        
        $return['shorturls'] = $shorturls;
    }
    
    
    return json_encode($return);
}

/**
 * fonction d'alimentation des sparklines
 * @global type $ydb
 * @param type $urls
 * @param type $period
 * @return type 
 */
function get_stats_traffic($urls, $period){
    
    global $ydb;
    
    $return = array(); 
    
    $order = "ORDER BY echelle ASC LIMIT 0, 20";
    
    foreach($urls as $id){
              
        // ALWAYS SANITIZE USER-SIDE INPUTS!!!
        $id = $ydb->escape($id);
        
        switch( $period ) {
                
                    case '2h':
                        
                        $queryTraffic = "SELECT click_time AS echelle, COUNT(click_time) AS clicks
                                            FROM yourls_log  
                                            WHERE  shorturl = '$id' ".TWOHOURS_FILTER."
                                            GROUP BY HOUR(echelle) ".$order;
                        
                        break;
                 
                    case 'day':
                        
                        $queryTraffic = "SELECT click_time AS echelle, COUNT(click_time) AS clicks 
                                            FROM yourls_log 
                                            WHERE shorturl = '$id' ".DAY_FILTER."
                                            GROUP BY HOUR(echelle) ".$order;
                        
                    break;
                
                    case 'week':
                        
                        $queryTraffic = "SELECT DATE(click_time) AS echelle, COUNT(click_time) AS clicks 
                                            FROM yourls_log l 
                                            WHERE shorturl = '$id' ".WEEK_FILTER."
                                            GROUP BY DAY(echelle) ".$order;
                        
                    break;
                
                    case 'month':
                        
                        $queryTraffic = "SELECT DATE(click_time) AS echelle, COUNT(click_time) AS clicks 
                                            FROM yourls_log l 
                                            WHERE shorturl = '$id' ".MONTH_FILTER."
                                            GROUP BY echelle ".$order;
                                            
                        
                        break;
                
                    case 'all':
                        
                        $queryTraffic = "SELECT DATE(click_time) AS echelle, COUNT(click_time) as clicks
                                            FROM yourls_log
                                            WHERE shorturl = '$id' 
                                            GROUP BY WEEK(echelle) ".$order;
                        
                        break;
                
                    default:
                        
                        break;
        }
        
        $resultsTraffic = $ydb->get_results($queryTraffic);
        
        $i = 1;
        foreach ( (array)$resultsTraffic as $res) {
			$return['traffic'][$id][$i++] = array(
				'clicks'      => $res->clicks,
			);
		}
        if($i == 1){
            
            $return['traffic']['1'] = array('clicks' => '0');
        }
        
    }    
    
    $return['statusCode'] = 200;
    $return['message'] = "success";
    
    return json_encode($return);
}



/**
 * Fonction de recherche des short URL et des clicks d'une URL
 * @global type $ydb
 * @param type $id
 * @return type 
 */
function get_shorturls($id){
    
    global $ydb;
    
    $longurl = yourls_get_keyword_longurl($id); // Input sanitized in the function
    $queryShortUrls = "SELECT keyword, clicks FROM yourls_url where url = '$longurl'";
    
    $return = array();
    $resultsShortUrls = $ydb->get_results($queryShortUrls);
    
    $i = 1;
    foreach ( (array)$resultsShortUrls as $res) {
			$return[$i++] = array(
                                'site'      => YOURLS_SITE,
				'item'    => $res->keyword,
                                'clicks'      => $res->clicks,
			);
		}
                
    return $return;
}

/**
 * Fonction appelée par yourls au point d'ancrage 'api'
 * @param type $action 
 */
function stats_period($args) {
    
    $action = $args[0];
    
    $period = $_REQUEST['period'];
    $filter = $_REQUEST['filter'];
    $start = $_REQUEST['start'];
    $limit = $_REQUEST['limit'];
    $shorturl = $_REQUEST['shorturl'];
    $urls = $_REQUEST['urls'];
    $search = $_REQUEST['search'];
    $namespace= $_REQUEST['namespace'];
    
    switch( $filter ) {
        case 'bottom':
            $sort_by = 'clicks';
    		$sort_order = 'asc';
    		break;
        case 'last':
    		$sort_by = 'timestamp';
    		$sort_order = 'desc';
			break;
    	case 'rand':
    	case 'random':
    		$sort_by = 'RAND()';
    		$sort_order = '';
    		break;
    	case 'top':
    	default:
    		$sort_by = 'clicks';
    		$sort_order = 'desc';
    		break;
    }
    
    // recherche des données pour la page principale
    if($period != '' && $shorturl == '' && $action == "stats_period"){
        
        header('Content-type: application/json');
        echo get_stats($period, $start, $limit, $sort_by, $sort_order, $search, $namespace);
        die();
    
    // recherche des données pour la page de détail
    } else if($shorturl != '' && $action == "stats_period_url") {
        
        header('Content-type: application/json');
        echo get_stats_url($shorturl, $period);
        die();
    
    } else if($action == "stats_traffic"){
        
        header('Content-type: application/json');
        echo get_stats_traffic($urls, $period);
        die();
            
    }
}
?>