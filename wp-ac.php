<?php

/*
Plugin Name: wp-ac
Plugin URI: 
Description: 
Version: 1.0.0
Author: Aloysius
Author URI: 
License: GPLv2
*/

/* 
Copyright (C) 2014 Aloysius

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

/** Plugin Version */
define( 'wp-ac', wpac_constants::wpac_plugin_version() );

/** Path for Includes */
define( 'WPAC_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

/** Path for front-end links */
define( 'WPAC_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

/** Loads the init functions */
include_once WPAC_PATH . '/inc-acc/wpac-functions.php';
include_once WPAC_PATH . '/inc-acc/wpac_init.php';
include_once WPAC_PATH . "/inc-acc/wpac-display.class.php";
include_once WPAC_PATH . '/inc-acc/PCRE_Exception.php';
include_once WPAC_PATH . "/inc-acc/api-allocine-helper.php";
include_once WPAC_PATH . "/inc-acc/IMDb.php";

/** Loads the main functions for the crm pages */
// include_once WPAC_PATH . '/core/wpac_functions.php';

//* Register activation hook -> has to be in the main plugin file */
register_activation_hook( __FILE__, 'wpac_activate' );

//* Register activation hook -> has to be in the main plugin file */
register_deactivation_hook( __FILE__, 'wpac_deactivate' );

class wpac_constants {
    
    private static $ac_db_version = "1.0";
    private static $ac_plugin_version = "0.0.1";
    
    public static function wpac_db_version(){
        return self::$ac_db_version;
    }
    public static function wpac_plugin_version(){
        return self::$ac_plugin_version;
    }
}

class wpac {
    
    public $tag = "wpac";
    public $table = 'wpac_movie';
    
    
    public $displayData = array(
        'dataType'          => '',
        'displayType'       => '',
        'title'             => '',
        'production'        => '',
        'nbseason'          => '',
        'season'            => '',
        'episode'           => '',
        'series'            => '',
        'creators'          => '',
        'directors'         => '',
        'genre'             => '',
        'cast'              => '',
        'longsummary'       => '',
        'shortsummary'      => '',
        'imgString'         => '',
        'trailerString'     => '',
        'allocineString'    => ''
    );
    
    /****************************************************************/
    /* 
    /****************************************************************/
    function __construct(){
        
	$this->settings = wpac_GetStarterOptions();
        
        add_action( 'plugins_loaded', array($this, 'loadTextDomain') );

        // Create shortcode
	add_shortcode('wpac', array(&$this, 'tagsReplace'));
        add_filter('the_excerpt', 'do_shortcode');

	if (is_admin()) {
		add_filter( 'mce_external_plugins', array(&$this, 'add_tinymce_plugin') );
		add_filter( 'mce_buttons', array(&$this, 'add_tinymce_button') );
		include_once WPAC_PATH . '/inc-acc/wpac-admin.php';
		$wpacAdmin = new wpac_admin($this);
 	}
	else {
		add_action('wp_enqueue_scripts', array(&$this, 'include_header'));
	}
        
    }
    
    /****************************************************************/
    /* Includes features in header
    /****************************************************************/
    function include_header() {
        
        wp_enqueue_style( 'wp-ac', WPAC_URL."/css/wpac.css");
        wp_enqueue_style( 'fancybox', WPAC_URL."/js/fancybox/jquery.fancybox.css");
        
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'fancybox', WPAC_URL."/js/fancybox/jquery.fancybox.pack.js", array( 'jquery' ) );
        wp_enqueue_script( 'wpac', WPAC_URL."/js/wpac.js", array( 'jquery' ) );

    }
    
    /****************************************************************/
    /* Includes features in header
    /****************************************************************/
    function add_tinymce_button( $buttons ) {

        array_push( $buttons, 'wpac_button_key' );
        return $buttons;
        
    }
    
    /****************************************************************/
    /* Includes features in header
    /****************************************************************/
    function add_tinymce_plugin( $plugin_array ) {

        $plugin_array['wpac_editor'] = plugins_url( '/js/wpac-editor.js', __FILE__ );
        return $plugin_array;
        
    }
    
    /********************************************************************************/
    /* Load plugin textdomain.
    /********************************************************************************/
    function loadTextDomain() {
        
        load_plugin_textdomain( 'wp-ac', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
        
    }
  
    /********************************************************************************/
    /* Convert one-off tags (with IDs embedded into the tag directly) to subprefixed tags
    /*
    /* @param string $content Content widh tags
    /* @param string $prefix Starting tagname
    /*
    /* @throws PCRE_Exception
    /* @since 2.1
    /*
    /* @return string content with one-off tags converted
    /********************************************************************************/
    public function tagsReplace($atts, $content = null){

        $dt = "";
        $this->wpac_initTag();
        
	if ( isset($atts['id'])) {
            $dt = ( is_archive() ? 'tiny' : $atts['display'] );
            return $this->getTagDataByID( $atts['id'], $dt , $atts, 'movie');
        }
        
	if ( isset($atts['seid'])) {
            $dt = ( is_archive() ? 'tiny' : $atts['display'] );
            return $this->getTagDataByID( $atts['seid'], $dt , $atts, 'series');
        }
               
 	if ( isset($atts['epid'])) {
            $dt = ( is_archive() ? 'tiny' : $atts['display'] );
            return $this->getTagDataByID( $atts['epid'], $dt , $atts, 'episode');
        }

    }
    
    /********************************************************************************/
    /* Reset display data array
    /*
    /* @return no return - update object data
    /********************************************************************************/
    public function wpac_initTag(){     
        foreach ($this->displayData as $key => $v){
            $this->displayData[$key] = '';
        }
    }

    /********************************************************************************/
    /* Convert one-off tags (with IDs embedded into the tag directly) to subprefixed tags
    /*
    /* @param string $ID 
    /* @param string $display
    /*
    /* @return string content with one-off tags converted
    /********************************************************************************/
    public function getTagDataByID($ID, $display="normal", $atts="", $dataType='movie'){

        // Search for movie with AC id
	$result = $this->wpac_selectDataByID($ID, 'large');       
        if ($result === false) {

            // If movie does not exist in database retrieve movie from AC and save it to database
            $data = $this->wpac_getDataByID($ID, $dataType, 'large');  
            // If it doesn't exist on Allocine, return error string
            if ($data === false) return __('ID: ', 'wp-ac').$ID.__(' - No data found', 'wp-ac');
            // Else, save to database
            $imgurl = $this->wpac_insertDataByID( $ID, $data, $atts, $result, $dataType );
            
        } else {

            // Otherwise, use data from database
            $data = $result['ac_data'];
            $imgurl = $result['ac_img'];
            // Update imdb data if necessary
            if ( isset($atts['imdb']) && $result['imdb_id'] != $atts['imdb'] ){
                $any = $this->wpac_updateIMDBDataByID( $ID, $atts, $result );
            }
            
        }
//            print_r($data);echo '<br /><br /><br /><br />';
            
        // Display form
        $this->wpac_setDisplayData( $data, $imgurl, $display, $dataType );
        return wpac_display::Display( $this->displayData, $dataType );   
        
    }
    
    /********************************************************************************/
    /* Retrieve AlloCine data from given ID
    /*
    /* @param string $ID
    /* @param string $profile
    /*
    /* @throws PCRE_Exception
    /* @since 2.1
    /*
    /* @return string content with one-off tags converted
    /********************************************************************************/
    public function wpac_getDataByID($ID, $dataType='movie', $profile='large'){
	
	// Créer un objet AlloHelper.
	$allohelper = new AlloHelper;
        
	// Il est important d'utiliser le bloc try-catch pour gérer les erreurs.
	try {
                // Envoi de la requête avec les paramètres, et enregistrement des résultats dans $donnees.
                switch($dataType){
                    case 'movie':
                        $return = $allohelper->movie( $ID, $profile );
                        break;
                    case 'episode':
                        $return = $allohelper->episode( $ID, $profile );
                        break;
                    case 'series':
                        $return = $allohelper->tvserie( $ID, $profile );
                        break;
                }
	}   
	// En cas d'erreur.
	catch ( ErrorException $e ){

                return false;
		// Affichage des informations sur la requête
		echo "<pre>", print_r($allohelper->getRequestInfos(), 1), "</pre>";
        
		// Afficher un message d'erreur.
		echo "Erreur " . $e->getCode() . ": " . $e->getMessage();
	}
        
        return $return;
         
    }

    /********************************************************************************/
    /* Retrieve AlloCine data from given ID
    /*
    /* @param string $ID
    /*
    /* @return movie object or false if not found
    /********************************************************************************/
    public function wpac_selectDataByID($ID){
        
	global $wpdb;
        
	$select = " SELECT * FROM ".$wpdb->prefix.$this->table." WHERE ac_id = '".$ID."'";
	$dbresults = $wpdb->get_results($select, ARRAY_A);
        
	if ( $dbresults ) {
        
                $dbresults[0]['ac_data'] = unserialize($dbresults[0]['ac_data']);
                $dbresults[0]['imdb_data'] = unserialize($dbresults[0]['imdb_data']);
                if ( !$this->wpac_remoteFileExists($dbresults[0]['ac_img']) && isset($dbresults[0]['ac_data']->poster) ) {
                    $this->wpac_remoteFileCopy( $dbresults[0]['ac_data']->poster->url(), 'imgac'.$dbresults[0]['ac_id'].'.jpg' );
                }
       		return $dbresults[0];
                
        } else { 
            
            return false; 
            
        }

    }
    
     /********************************************************************************/
    /* Retrieve AlloCine data from given ID
    /*
    /* @param string $ID
    /* @param string $movie
    /*
    /* @return true/false depending on wether the insert is successful
    /********************************************************************************/
    public function wpac_updateIMDBDataByID($ID, $atts, $result){
        
	global $wpdb;
        
        $result['imdb_id'] = $atts['imdb'];
        $imdb_dude = new Imdb();
        $result['imdb_data'] = serialize( $imdb_dude->getMovieInfoById($result['imdb_id']) );     

        $update = array( 'imdb_id' => $result['imdb_id'], 'imdb_data' => $result['imdb_data'] );
        $where = array( 'ac_id' => $ID );
	$dbresult = $wpdb->update( $wpdb->prefix.$this->table, $update, $where );

	return $dbresult; 
        
    }
    
    /********************************************************************************/
    /* Insert AlloCine movie
    /*
    /* @param string $ID
    /* @param string $movie
    /* @param string $atts
    /* @param string $result
    /*
    /* @return url of the img
    /********************************************************************************/
    public function wpac_insertDataByID($ID, $data, $atts, $result, $dataType='movie'){
        
	global $wpdb;

        // Retrieve image url and copy it to server directory
        switch ($dataType){
            case 'movie':
                $url = $data->poster->url();
                break;
            case 'series':
                $url = $this->wpac_getSeriesURL($data->poster);
                break;
            case 'episode':
                $url = $this->wpac_getSeriesURL($data->picture);
                break;
        }
        $img_url = $this->wpac_remoteFileCopy($url, 'imgac'.$ID.'.jpg');
      
        // If IMDb code is given, check if it has to be updated
        if ( isset($atts['imdb']) && $result['imdb_id'] != $atts['imdb'] ) {

                $result['imdb_id'] = $atts['imdb'];
                $imdb_dude = new Imdb();
                $result['imdb_data'] = serialize( $imdb_dude->getMovieInfoById($result['imdb_id']) );     
            
        } else {            
                switch ($dataType){
                    case 'movie':
                        $result['imdb_data'] = $this->wpac_findIMDBKey($data->originalTitle);
                        $result['imdb_id'] = $result['imdb_data']['title_id'];
                        break;
                    case 'series':
                        $result['imdb_data'] = $this->wpac_findIMDBKey($data->originalTitle);
                        $result['imdb_id'] = $result['imdb_data']['title_id'];
                        break;
                    case 'episode':
                        $result['imdb_data'] = $this->wpac_findIMDBKey($data->originalTitle);
                        $result['imdb_id'] = $result['imdb_data']['title_id'];
                        break;
                }
                $result['imdb_data'] = serialize( $result['imdb_data'] );

        }

        // Insert new record in database
        $insert = array( 'ac_id' => $ID, 'ac_data' => serialize($data), 'ac_img' => $img_url, 'imdb_id' => $result['imdb_id'], 'imdb_data' => $result['imdb_data'] );
	$dbresult = $wpdb->insert($wpdb->prefix.$this->table, $insert);

	if ($dbresult) return $img_url; else return false; 

    }
    
    /********************************************************************************/
    /* Convert one-off tags (with IDs embedded into the tag directly) to subprefixed tags
    /*
    /* @param string $ID 
    /* @param string $display
    /*
    /* @return string content with one-off tags converted
    /********************************************************************************/
    public function wpac_setDisplayData($data, $imgurl, $display='normal', $dataType='movie'){
      
        $this->displayData['displayType']                   = $display;
       
        switch ($dataType) {
            
            case 'movie':
                
                if (isset($data->title))                            $this->displayData['title']                 = utf8_encode($data->title);
                if (isset($data->productionYear))                   $this->displayData['production']            = $data->productionYear;
                if (isset($data->synopsis))                         $this->displayData['longsummary']           = strip_tags(utf8_encode($data->synopsis));
                if (isset($data->castingShort['actors']))           $this->displayData['cast']                  = utf8_encode($data->castingShort['actors']);
                if (isset($data->castingShort['directors']))        $this->displayData['directors']             = utf8_encode($data->castingShort['directors']);
                if (isset($data->genre))                            $this->displayData['genre']                 = $this->wpac_getGenre($data->genre);
                break;
                
            case 'series':
                
                if (isset($data->title))                            $this->displayData['title']                 = utf8_encode($data->title);
                if (isset($data->originalBroadcast))                $this->displayData['production']            = substr($data->originalBroadcast['dateStart'],0,4);
                if (isset($data->synopsis))                         $this->displayData['longsummary']           = strip_tags(utf8_encode($data->synopsis));
                if (isset($data->castingShort['actors']))           $this->displayData['cast']                  = utf8_encode($data->castingShort['actors']);
                if (isset($data->castingShort['creators']))         $this->displayData['creators']              = utf8_encode($data->castingShort['creators']);
                if (isset($data->genre))                            $this->displayData['genre']                 = $this->wpac_getGenre($data->genre);
                if (isset($data->season))                           $this->displayData['nbseasons']             = count($data->season);
                break;

            case 'episode':
                
                if (isset($data->originalTitle))                    $this->displayData['title']                 = utf8_encode($data->originalTitle);
                if (isset($data->originalBroadcastDate))            $this->displayData['production']            = substr($data->originalBroadcastDate,0,10);
                if (isset($data->synopsis))                         $this->displayData['longsummary']           = strip_tags(utf8_encode($data->synopsis));
                if (isset($data->castMember))                       {
                    $cast = $this->wpac_episodeCast($data->castMember);
                    $this->displayData['cast']                      = utf8_encode($cast['actors']);
                    $this->displayData['directors']                 = utf8_encode($cast['directors']);
                }
                if (isset($data->parentSeries))                     $this->displayData['series']                = utf8_encode($data->parentSeries['name']);
                if (isset($data->parentSeason))                     $this->displayData['season']                = $data->parentSeason['name'];
                if (isset($data->episodeNumberSeason))              $this->displayData['episode']               = $data->episodeNumberSeason;
                break;
                
        }
        
        if (isset($data->synopsisShort)) {
            if ($data->synopsisShort == ''){
                $this->displayData['shortsummary'] = $this->displayData['longsummary'];
            } else { 
                $this->displayData['shortsummary'] = strip_tags(utf8_encode($data->synopsisShort));
            }
        }
                
        if (isset($data->trailerEmbed)) {
            $this->displayData['trailerString']             = $this->wpac_getTrailerString( $data->trailer['code'] );
            $this->displayData['allocineString']            = $this->wpac_getAllocineString( $data->trailerEmbed, $this->displayData['title'] );
        } else {
            $this->displayData['trailerString']             = '';
            $this->displayData['allocineString']            = '';
        }
        $this->displayData['imgString']                     = $this->wpac_getImgString( $imgurl, $this->displayData['title'] );

    }
    
    /********************************************************************************/
    /* Retrieve AlloCine data from given ID
    /*
    /* @param string $genre
    /*
    /* @throws PCRE_Exception
    /* @since 2.1
    /*
    /* @return string content with one-off tags converted
    /********************************************************************************/
    public function wpac_getSeriesURL($poster){
        
        if ( is_null($poster) ) return "";    
        $d = array_values(unserialize(str_replace('O:8:"AlloData":3',"a:3",serialize($poster))));
        return $d[0]['href'];

    }
    
    /********************************************************************************/
    /* Insert AlloCine movie
    /*
    /* @param string $ID
    /* @param string $movie
    /* @param string $atts
    /* @param string $result
    /*
    /* @return url of the img
    /********************************************************************************/
    public function wpac_findIMDBKey($title, $production=''){
        
        $imdb_dude = new Imdb();
        $imdb_movies = $imdb_dude->getMovieInfo($title);
        return $imdb_movies;
        
    }
    
    /********************************************************************************/
    /* Copy a remote file in upload directory
    /*
    /* @param string $remoteURL
    /* @param string $filename
    /*
    /* @return file url
    /********************************************************************************/
    public function wpac_remoteFileCopy($remoteURL, $filename){
        
	$upload_dir = wp_upload_dir();
        
	if ( ini_get('allow_url_fopen') ) {

		// requires allow_url_fopen
		$image = file_get_contents( $remoteURL );
		file_put_contents( $upload_dir['path'].'/'.$filename, $image );

        } else {

	        // or the cURL route:
		$ch = curl_init( $remoteURL );
		$fp = fopen( $upload_dir['path'].'/'.$filename, 'wb' );
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
                
        }
        return $upload_dir['url'].'/'.$filename;
        
    }
    
    /********************************************************************************/
    /* Retrieve AlloCine data from given ID
    /*
    /* @param string $url
    /*
    /* @return true/false depending on wether the remote file exists
    /********************************************************************************/
    function wpac_remoteFileExists($url) {
     
	$curl = curl_init($url);

	//don't fetch the actual page, you only want to check the connection is ok
	curl_setopt($curl, CURLOPT_NOBODY, true);

	//do request
	$result = curl_exec($curl);

	$ret = false;

	//if request did not fail
	if ($result !== false) {
            
		//if request was ok, check response code
		$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);  

		if ($statusCode == 200) $ret = true;
        
	}

	curl_close($curl);

	return $ret;
        
    }
    
     /********************************************************************************/
    /* Retrieve AlloCine data from given ID
    /*
    /* @param string $genre
    /*
    /* @throws PCRE_Exception
    /* @since 2.1
    /*
    /* @return string content with one-off tags converted
    /********************************************************************************/
    public function wpac_getGenre($genre){

        if ( is_null($genre) ) return "";
        
        $d = array_values(unserialize(str_replace('O:8:"AlloData":3',"a:3",serialize($genre))));
        
	$arrayGenre = array();
	foreach ($d[0] as $key => $value) {
		$arrayGenre[] = $value['$'];
        }
        
        return ( implode( ", ", array_values($arrayGenre) ) );
        
    }
    
    /********************************************************************************/
    /* Retrieve AlloCine data from given ID
    /*
    /* @param string $trailerEmbed
    /*
    /* @throws PCRE_Exception
    /* @since 2.1
    /*
    /* @return string content with one-off tags converted
    /********************************************************************************/
    public function wpac_episodeCast($castArray){

        $cast = array('directors' => array(), 'actors' => array());
        
        foreach ($castArray as $value){
          
            if (substr($value['activity']['$'],0,1) == 'R') $cast['directors'][]    = $value['person']['name'];
            if ($value['activity']['$'] == 'Acteur')        $cast['actors'][]       = $value['person']['name'];
            
        }
        
        $cast['actors'] = implode( ", ", array_values($cast['actors']));
        $cast['directors'] = implode( ", ", array_values($cast['directors']));

        return $cast;
        
    }
    
    /********************************************************************************/
    /* Retrieve AlloCine data from given ID
    /*
    /* @param string $trailerEmbed
    /*
    /* @throws PCRE_Exception
    /* @since 2.1
    /*
    /* @return string content with one-off tags converted
    /********************************************************************************/
    public function wpac_getTrailerString($trailerCode){

        $img = WPAC_URL . '/js/img/trailer.png';
        return utf8_encode('<a class="IEmbed fancybox.iframe" href="http://www.allocine.fr/_video/iblogvision.aspx?cmedia=' . $trailerCode . '"><img src="' . $img . '" width="25%" height="25%" alt="trailer" /></a>');
                
    }
    
    /********************************************************************************/
    /* Retrieve AlloCine data from given ID
    /*
    /* @param string $trailerEmbed
    /*
    /* @throws PCRE_Exception
    /* @since 2.1
    /*
    /* @return string content with one-off tags converted
    /********************************************************************************/
    public function wpac_getAllocineString($trailerEmbed, $title){
    
        $regex = "<a.+?href=[\"'](.+?)[\"'].*?>";
        preg_match( $regex, $trailerEmbed, $match );
       
        $img = '<img src="' . WPAC_URL . '/js/img/allocine500x135.png" width="25%" height="25%" style="margin-bottom:-0.7em;" alt="allocine.com" />';
      
        $t = '<a class="IEmbed fancybox.iframe" href="' . $match[1] . '">' . $title . ' ' . __('on ', 'wp-ac') . $img . '</a>';
       return utf8_encode($t);
                
    }
    
    /********************************************************************************/
    /* Retrieve AlloCine data from given ID
    /*
    /* @param string $url
    /* @param string $title
    /* @param string $alt
    /*
    /* @throws PCRE_Exception
    /* @since 2.1
    /*
    /* @return string content with one-off tags converted
    /********************************************************************************/
    public function wpac_getImgString($url, $title, $alt=""){
    
        if ($alt == '') $alt = $title;
        return '<a class="fancybox" href="' . $url . '" data-lightbox="' . $title . '" data-title="' . $alt . '" width="100%" height="100%" />'.
                    '<img src="' . $url . '" alt="' . $title . '" width="100%" height="100%" />'.
                '</a>';

    }
}

$wpac = new wpac();

