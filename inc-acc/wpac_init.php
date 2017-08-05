<?php
/* 
 * wpac Init functions
 * @version 0.0.1
 * @author Aloysius
 * @package wpac
 */

// Add one library admin function for next function
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

class wpac_init {
    
    private static $table_list = array(
                            'wpac_movie' => array (
                                'name'      => 'wpac_movie',
                                'fields'    => array(
                                    // array values: type, default, unique y/n, primary_key y/n, auto_increment y/n
                                    'ac_id'         => array( 'VARCHAR(6)', "'000000'", true, false, false),
                                    'ac_type'       => array( 'VARCHAR(15)', "'movie'", false, false, false),
                                    'ac_data'       => array( 'LONGBLOB', "''", false, false, false),
                                    'ac_img'        => array( 'TEXT', "''", false, false, false),
                                    'imdb_id'       => array( 'VARCHAR(9)', "'000000'", false, false, false),
                                    'imdb_data'     => array( 'LONGBLOB', "''", false, false, false)
                                    )
                                )
                            );
 
    public static function sql_create($table_name){
        
		global $wpdb;
		$tempTable = self::$table_list[$table_name];
		$sql = "CREATE TABLE ".$wpdb->prefix.$tempTable['name']." (";
                foreach($tempTable['fields'] as $key => $value){
                	$sql .= $key." ".$value[0]." DEFAULT ".$value[1];
                	if ($value[2]) { $sql .= ' UNIQUE'; };
                	if ($value[3]) { $sql .= ' PRIMARY KEY'; }
                	if ($value[4]) { $sql .= ' AUTO_INCREMENT'; };
                        $sql .= ",";
                }
                $sql = substr_replace($sql,");",-1);
                return $sql;
                
    }
    
    public static function activate(){

		$tl = self::returnTableList();        
		// Try to create the plugin tables
		foreach($tl as $key => $value){
			$sql = self::sql_create($key);
			dbDelta($sql);
		}
		add_option( "ac_db_version", wpac_constants::wpac_db_version() );
        
    }
    
    public static function deactivate(){
        
		global $wpdb;
		$tl = self::returnTableList();        
                // Delete the tables
		foreach($tl as $key => $value){
			$sql = "DROP TABLE IF EXISTS ".$wpdb->prefix.$value['name']." ;";
			$wpdb->query($sql);
		}
        
    }
    
    public static function returnTableList(){
		return self::$table_list;
    }
    
}

/** Activation hook function */
function wpac_activate() {
	wpac_init::activate();
}

function wpac_deactivate() {
	wpac_init::deactivate();
}
