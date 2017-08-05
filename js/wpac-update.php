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
    
$uInfo['table'] = $wpdb->prefix.'wpac_movie';

$all_DATA = $_GET;
print_r($_GET);

if(!empty($all_DATA))
{
    //database settings

    
    foreach($all_DATA as $field_name => $val)
    {
        //clean post values
        $field_userid = strip_tags(trim($field_name));
        $val = strip_tags(trim(mysql_real_escape_string($val)));
 
        //from the fieldname:user_id we need to get user_id
        $split_data = explode(':', $field_userid);
        $user_id = $split_data[1];
        $field_name = $split_data[0];
 
        // for stat_data fields extract data row #
        $split_name = explode('@', $field_name);
        if ( isset($split_name[1]) ) {
            $field_name = 'ac_data';
            $row_num = $split_name[1];
            $cell_num = $split_name[2];
        }

        if(!empty($user_id) && !empty($field_name) && !empty($val))
        {
            //update the values
            $uInfo['where'] = array( 'ac_id' => $user_id);
            switch ($field_name){

                case 'stat_name':
                    $uInfo['data'] = array( $field_name => $val);
                    break;
                case 'ac_data':
                    $select = " SELECT * FROM ".$uInfo['table']." WHERE ac_id = '".$user_id."'";
                    $dbresults = $wpdb->get_results($select, ARRAY_A);
                    $data = unserialize($dbresults[0]['ac_data']);
                    $data[$row_num][$cell_num] = $val;
                    $uInfo['data'] = array( $field_name => serialize($data) );
                    break;
                    
            }
            $result = $wpdb->update( $uInfo['table'], $uInfo['data'], $uInfo['where'] );
            
            echo "Updated";
        } else {
            echo "Invalid Requests";
        }
    }
} else {
    echo "Invalid Requests";
}


