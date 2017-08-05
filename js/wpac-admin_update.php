<?php
/************************************************************************************************************/
/* wpac update in admin
/* @version 0.0.1
/* @author Aloysius
/* @package wpac
/************************************************************************************************************/

// Include the necessary admin stuff.
require_once('../../../../wp-load.php');
global $wpdb;

/** Path for Includes */
define( 'WPAC_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
include_once WPAC_PATH . '/inc-acc/wpac-admin.functions.php';
    
$all_DATA = $_GET;
print_r($_GET);

$settings = wpac_GetDefaultOptions();
$design = unserialize($settings['customdesign']);
$pdesign = unserialize($settings['pcustomdesign']);
$spdesign = array();
foreach($pdesign['fields'] as $key => $value){
    foreach($value as $v){
        $spdesign[]=$v;
    }
}


$npdesign = $design;

// print_r($design);
// var_dump($design);
// var_dump($pdesign);
// var_dump($spdesign);

if ( !empty($all_DATA) ){

var_dump('$all_DATA', $all_DATA);

    // Update option if a value has been changed
    if (key($all_DATA) == 'field'){
        
        next($all_DATA);
        $field_userid = strip_tags(trim(key($all_DATA)));
        $val = strip_tags(trim(mysql_real_escape_string(current($all_DATA))));
        $t = explode("_", $field_userid);
        $field = $t[0];
        $fieldType = $t[1];
        foreach($design['fields'] as &$v){
            foreach($v as $key => &$value){
                if($value['name'] == $field) $value[$fieldType] = $val;
            }
        }

    }else if (key($all_DATA) == 'column'){
        
        next($all_DATA);
        $field_userid = strip_tags(trim(key($all_DATA)));
        $val = strip_tags(trim(mysql_real_escape_string(current($all_DATA))));
        $t = explode("_", $field_userid);
        $field = $t[0];
        $fieldType = $t[1];
        $design['columns'][$field][$fieldType] = $val;
        
    }else{
        $col = strip_tags(trim(key($all_DATA)));
        $newsort = array($col => array() );
        foreach($all_DATA[$col] as $k => $val){
            
var_dump('$val', $val);        
            // parse design array to collect full data
            foreach($spdesign as $v){
// var_dump('$v', $v);                
                if ($v['name'] == $val) {
                    $newsort[$col][] = $v;                
                }
            }
              
        }
        $design['fields'][$col] = $newsort[$col];
print_r($design);   

    }
    $settings['customdesign']=serialize($design);
    $settings['pcustomdesign']=serialize($npdesign);
    update_option('wpac_settings', $settings);
} else {
    echo "Invalid Requests";
}


