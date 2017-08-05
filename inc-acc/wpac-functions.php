<?php
/************************************************************************************************************/
/* wpac functions
/* @version 0.0.1
/* @author Aloysius
/* @package wpac
/************************************************************************************************************/

/**********************************************************************************/
/* 
/**********************************************************************************/
class wpac_DefaultOptions{
    
    private static $defaultSettings = array(
	'version'           => '',
	'phpversion'        => '',
	'useIMDb'           => 'false',
	'chartFont'         => 'serif arial',
	'titleSizePercent'  => 0.05,
	'safe_mode'         => '',
        'customdesign'      => ''
    );
    
    private static $designList = array(
                        'columns' => array(
                            'topwidth' => array( 'width' => '100%', 'class' => '' ),
                            'leftwidth' => array( 'width' => '15%', 'class' => '' ),
                            'middlewidth' => array( 'width' => '35%', 'class' => '' ),
                            'rightwidth' => array( 'width' => '35%', 'class' => '' )
                        ),
                        'fields' => array(
                            'stack' => array(
                                array( 'name' => 'longsummary', 'class' => '', 'valueLabel' => 'Label: ', 'valueContent' => 'Résumé : ', 'adminLabel' => 'Long Summary' ),
                                array( 'name' => 'shortsummary', 'class' => '', 'valueLabel' => 'Label: ', 'valueContent' => 'Résumé court : ', 'adminLabel' => 'Short Summary' ),
                                array( 'name' => 'trailer', 'class' => '', 'valueLabel' => 'Label: ', 'valueContent' => 'Trailer: ', 'adminLabel' => 'Trailer' )
                            ),
                            'top' => array(
                                array( 'name' => 'title', 'class' => '', 'valueLabel' => 'Label: ', 'valueContent' => '', 'adminLabel' => 'Title' )
                            ),
                            'left' => array(
                                array( 'name' => 'poster', 'class' => '', 'valueType' => 'width', 'valueLabel' => 'Largeur: ', 'valueContent' => '100%', 'adminLabel' => 'Poster' )
                            ),
                            'middle' => array(
                                array( 'name' => 'production', 'class' => '', 'valueLabel' => 'Label: ', 'valueContent' => 'Réalisé en ', 'adminLabel' => 'Production' ),
                                array( 'name' => 'directors', 'class' => '', 'valueLabel' => 'Label: ', 'valueContent' => ' par ', 'adminLabel' => 'Directors' ),
                                array( 'name' => 'genre', 'class' => '', 'valueLabel' => 'Label: ', 'valueContent' => 'Genre : ', 'adminLabel' => 'Genre' )
                            ),
                            'right' => array(
                                array( 'name' => 'cast', 'class' => '', 'valueLabel' => 'Label: ', 'valueContent' => 'Distribution : ', 'adminLabel' => 'Cast' )
                            )
                      )
    );

    public $settings = array();
    
    /****************************************************************/
    /* constructor
    /****************************************************************/
    function __construct(){ 
    }

    /****************************************************************/
    /* Validates update options
    /****************************************************************/
    public static function wpac_set_defaultOptions() {

	self::$defaultSettings['phpversion'] = phpversion();
        self::$defaultSettings['customdesign'] = serialize( self::wpac_getLocalDefaultCustomDesign() );
        self::$defaultSettings['pcustomdesign'] = serialize( self::wpac_getLocalDefaultCustomDesign() );
        
	// Init parameters
	$settings = get_option('wpac_settings');
	foreach( self::$defaultSettings as $key => $value ){
		if (!isset($settings[$key]) || $settings[$key] == '') $settings[$key] = $value;
	}
            
	return $settings;
    }
    
    /****************************************************************/
    /* Validates update options
    /****************************************************************/
    public static function wpac_set_defaultCustomDesign() {

	self::$defaultSettings['phpversion'] = phpversion();
        self::$defaultSettings['customdesign'] = serialize( self::wpac_getLocalDefaultCustomDesign() );
        
	// Init parameters
	$settings = get_option('wpac_settings');
	foreach( self::$defaultSettings as $key => $value ){
		if (!isset($settings[$key]) || $settings[$key] == '') $settings[$key] = $value;
	}
        $settings['customdesign'] = self::$defaultSettings['customdesign'];
        update_option( 'wpac_settings', $settings );            
	return $settings;
    }
    
    /****************************************************************/
    /* Validates update options
    /****************************************************************/
    public static function wpac_getLocalDefaultCustomDesign() {
        
        return array(
                        'columns' => array(
                            'topwidth' => array( 'width' => '100%', 'class' => '' ),
                            'leftwidth' => array( 'width' => '15%', 'class' => '' ),
                            'middlewidth' => array( 'width' => '35%', 'class' => '' ),
                            'rightwidth' => array( 'width' => '35%', 'class' => '' )
                        ),
                        'fields' => array(
                            'stack' => array(
                                array( 'name' => 'longsummary', 'class' => '', 'valueLabel' => __('Label:', 'wp-ac').' ', 'valueContent' => __('Summary: ', 'wp-ac').' ', 'adminLabel' => __('Long Summary', 'wp-ac') ),
                                array( 'name' => 'shortsummary', 'class' => '', 'valueLabel' => __('Label:', 'wp-ac').' ', 'valueContent' => __('Short summary: ', 'wp-ac').' ', 'adminLabel' => __('Short Summary', 'wp-ac') ),
                                array( 'name' => 'trailer', 'class' => '', 'valueLabel' => __('Label:', 'wp-ac').' ', 'valueContent' => __('Trailer: ', 'wp-ac').' ', 'adminLabel' => __('Trailer', 'wp-ac') )
                            ),
                            'top' => array(
                                array( 'name' => 'title', 'class' => '', 'valueLabel' => __('Label:', 'wp-ac').' ', 'valueContent' => '', 'adminLabel' => __('Title', 'wp-ac') )
                            ),
                            'left' => array(
                                array( 'name' => 'poster', 'class' => '', 'valueType' => 'width', 'valueLabel' => __('Width:', 'wp-ac').' ', 'valueContent' => '100%', 'adminLabel' => __('Poster', 'wp-ac') )
                            ),
                            'middle' => array(
                                array( 'name' => 'production', 'class' => '', 'valueLabel' => __('Label:', 'wp-ac').' ', 'valueContent' => __('Directed in', 'wp-ac').' ', 'adminLabel' => __('Production', 'wp-ac') ),
                                array( 'name' => 'directors', 'class' => '', 'valueLabel' => __('Label:', 'wp-ac').' ', 'valueContent' => ' '.__('by', 'wp-ac').' ', 'adminLabel' => __('Directors', 'wp-ac') ),
                                array( 'name' => 'genre', 'class' => '', 'valueLabel' => __('Label:', 'wp-ac').' ', 'valueContent' => __('Genre:', 'wp-ac').' ', 'adminLabel' => __('Genre', 'wp-ac') )
                            ),
                            'right' => array(
                                array( 'name' => 'cast', 'class' => '', 'valueLabel' => __('Label:', 'wp-ac').' ', 'valueContent' => __('Cast:', 'wp-ac').' ', 'adminLabel' => __('Cast', 'wp-ac') )
                            )
                        )
        );


    } 
    
}

/****************************************************************/
/* Options
/****************************************************************/
function wpac_GetDefaultOptions() {
	return wpac_DefaultOptions::wpac_set_defaultOptions();
}

/****************************************************************/
/* Options
/****************************************************************/
function wpac_set_defaultCustomDesign() {
	return wpac_DefaultOptions::wpac_set_defaultCustomDesign();
}

/**********************************************************************************/
/* Gets options. Sets minimum options to operate before first validation.
/**********************************************************************************/
function wpac_GetStarterOptions() {

	// Init parameters
	$settings = get_option('wpac_settings');
	if ($settings['useIMDb'] == '') $settings['useIMDb'] = 'false';
	return $settings;
        
}

function wpac_CleanPOST($getpost){
	return htmlentities($getpost, ENT_QUOTES);
}

	/*******************************************************************************/
	/* 
	/*******************************************************************************/
	function wpac_BooleanOption($boolean) {
              	return (wpac_CleanPOST($boolean) == 'on' ? 'true' : 'false' );
	}
        
	/****************************************************************/
	/* Delete leading slash
	/****************************************************************/
	function wpac_noLeadSlash ($text) {
		
		if (substr($text, 0, 1) == '/') return substr($text, 1);
		return $text;
                
	}
        
	/****************************************************************/
	/* Delete trailing slash
	/****************************************************************/
	function wpac_noTrailSlash ($text) {
		
		if (substr($text, -1) == '/') return substr($text, 0, strlen($text)-1);
		return $text;
                
	}