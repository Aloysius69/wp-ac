<?php
/************************************************************************************************************/
/* wpac admin functions
/* @version 0.0.1
/* @author Aloysius
/* @package wpac
/************************************************************************************************************/
include_once WPAC_PATH . '/inc-acc/wpac-admin.functions.php';

/****************************************************************************************************/
/* Administration class
/****************************************************************************************************/
class wpac_admin {

	// Version and path to check version
	public $WPACVERSION         = '';
	public $WPACURL             = 'http://www.alakhnor.com/post-thumb/ptrversion.htm';
        public $ADMINURL            = '';

	public $settings;
	public $wpObject;
        public $postedData          = array();
        
	public $update_error        = false;
	public $error_msg           = '';

        public $wpac_settings       = array();
               
        public $table               = 'wpac_movie';

	/****************************************************************/
        /*
	/****************************************************************/
	function __construct($wpObject) {

		$this->wpObject = $wpObject;
		$this->settings = $wpObject->settings;
		unset($wpObject);
                
                $this->WPACVERSION = wpac_constants::wpac_plugin_version();
                $this->ADMINPARENT = 'tools.php';
                $this->ADMINSLUG = 'wpac-admin';
                $this->ADMINURL = admin_url( $this->ADMINPARENT . '?page=' . $this->ADMINSLUG );
		
		// add option screen menu
		if (is_admin()) {
                    
			add_action( 'admin_init', array(&$this, 'adminInit') );
			add_action('admin_menu', array(&$this, 'adminHook'));

			// check if we need to upgrade
			if ( $this->settings['version'] < $this->WPACVERSION  ) {
				// Execute installation
//				$this->wpac_install();
				
				// Update version number in the options
				$this->settings['version'] = $this->WPACVERSION;
//				$this->UpdateOptions();
			}
		}

	}
        
	/****************************************************************/
	/* 
	/****************************************************************/
	function adminHook() {
            
		/* Add our plugin submenu and administration screen */
		$page_hook_suffix = add_submenu_page( $this->ADMINPARENT, 	// The parent page of this submenu
					__( 'WP AC', 'wp-ac' ), 		// The submenu title
					__( 'WP AC', 'wp-ac' ), 		// The screen title
					'manage_options', 			// The capability required for access to this submenu
					$this->ADMINSLUG,   			// The slug to use in the URL of the screen
					array(&$this, 'adminMenuOptions') 	// The function to call to display the screen
		);

		/*
		* Use the retrieved $page_hook_suffix to hook the function that links our script.
		* This hook invokes the function only on our plugin administration screen,
		* see: http://codex.wordpress.org/Administration_Menus#Page_Hook_Suffix
		*/
		add_action('admin_print_scripts-' . $page_hook_suffix, array(&$this, 'adminScripts') );
            
	}
        
	/****************************************************************/
	/* 
	/****************************************************************/
	function adminInit() {
		wp_register_style( 'wpac-admin', WPAC_URL . '/css/wpac-admin.css' );
		wp_register_script( 'wpac-admin', WPAC_URL . '/js/wpac-admin.js' );
	}
        
	/****************************************************************/
	/* 
	/****************************************************************/
	function adminScripts() {
            
   		$echo = __('Click to hide and show', 'wp-ac');
                echo '<script>var echo = "'.$echo.'";</script>';

		wp_enqueue_style( 'wpac-admin' );
                wp_enqueue_style( 'jquery-wpac', WPAC_URL."/css/jquery-ui.min.css");
                
		wp_enqueue_script( 'jquery' );
                wp_enqueue_script( 'jquery-ui-core', array('jquery') );
                wp_enqueue_script( 'jquery-ui-sortable', array('jquery') );
                wp_enqueue_script( 'wpac-admin', array('jquery') );
	}
        
	/*******************************************************************************/
	/* Option panel
	/*******************************************************************************/
	function adminMenuOptions() {
	
                if (isset($_GET['wpacadmin'])) $submenu = $_GET['wpacadmin'];
                else $submenu = 'general';
                
                switch($submenu) {
                    
                case 'general' :
			// Retrieve current options from database
			$new_options = wpac_GetDefaultOptions();

			// If it comes from a form validation, update database with new options
			if (isset($_POST['info_update'])) {
	                        $displayMsg = true;
                                $resetCD = (wpac_BooleanOption ($_POST['resetCustomDesign']) == 'true');
                                if($resetCD) wpac_set_defaultCustomDesign();
			  	$new_options = $this->adminGetPostedData();
				update_option( 'wpac_settings', $new_options );
			} else {
	                        $displayMsg = false;
	                }

	                if ($displayMsg) {
				$msg = ( $this->update_error ? '<strong>'.__('Update error:', 'wp-ac').'</strong>'.$this->error_msg : '<strong>'.__('Settings saved', 'wp-ac').'</strong>' );
			} else {
				$msg = '<strong>' . __("Leave default settings if you're unsure of what to set.", 'wp-ac') . '</strong>';
			}
                
			?><div class="updated"><?php echo $msg; ?></div><?php
                
	                // Prepare form display and update options 
	                // If this is the first time here or if something changed in the plugin, it will set default option
			$this->wpac_settings = wpac_GetDefaultOptions('wpac_settings');
			update_option('wpac_settings', $this->wpac_settings);

	                $this->adminOptionsForm();
                        break;
                        
                case 'design':
                    
                        ?><script>var updatepath = "<?php echo WPAC_URL . '/js/wpac-admin_update.php'; ?>";</script><?php
                        
			$msg = '<strong>' . __("Design the custom Allocine display.", 'wp-ac') . '</strong>';
			?><div class="updated"><?php echo $msg; ?></div><?php

	                $this->adminDesignForm();
			break;
                        
                case 'edit':
                    
			$msg = '<strong>' . __("Edit data inline.", 'wp-ac') . '</strong>';
			?><div class="updated"><?php echo $msg; ?></div><?php

                        $this->adminEditForm();
			break;
                }
		
  	}
        
	/*******************************************************************************/
	/* Option panel: upload form
	/*******************************************************************************/
	function adminDesignForm() {
            
                $settings = wpac_GetDefaultOptions();
                $designList = unserialize($settings['customdesign']);
                
		?>
                        
		<?php $this->adminHeadMenu(); ?>
                        
                <div id="wpacDesignZone">
                    
                    <div id="stackwidth" class="header">
                        <div class="portlet">
                            <div class="portlet-content" style="text-align: center;" ><?php _e('Elements to insert', 'wp-ac'); ?></div>
                        </div>
                    </div>
                    
                    <?php 
                        foreach($designList['columns'] as $key => $value){
                            echo '<div id="'.$key.'" class="header">';
                            $this->showPortlet( '', __('Width:', 'wp-ac').' ', $value['width'], $value['class'], $key, $key, 'column', 'width' );
                            echo '</div>';
                        }
                        
                        foreach($designList['fields'] as $key => $value){
                            echo '<div id="'.$key.'" class="column">';
                            foreach($value as $k => $v ){
                                $this->showPortlet( $v['adminLabel'], $v['valueLabel'], $v['valueContent'], $v['class'], $v['name'], $key, 'field', 'valueContent' );
                            }
                            echo '</div>';
                        }
                    ?>
                     
                </div>
		<?php
                
        }
        
        /*******************************************************************************/
	/* Option panel: design - show portlet & column header
	/*******************************************************************************/
	function showPortlet($adminLabel, $valueLabel, $valueContent, $class, $name='', $col='', $datatype='field', $content) {
            
            if ($adminLabel != ''){ $portHead ='<div class="portlet-header">'.$adminLabel.'</div>'; } else { $portHead =''; }
            ?>
                        <div class="portlet" id="<?php echo $col; ?>_<?php echo $name; ?>">
                            <?php echo $portHead; ?>
                            <div class="portlet-content">
                                <span class="portlet-content-label"><?php echo $valueLabel; ?></span><span data-type="<?php echo $datatype; ?>" id="<?php echo $name; ?>_<?php echo $content; ?>" contenteditable="true" style="display: inline-block;min-width: 3em;"><?php echo $valueContent; ?></span><br />
                                <span class="portlet-content-label">Class: </span><span data-type="<?php echo $datatype; ?>" id="<?php echo $name; ?>_class" contenteditable="true" style="display: inline-block;min-width: 3em;"><?php echo $class; ?></span>
                            </div>
                        </div>
            <?php
        }
        
	/*******************************************************************************/
	/* Option panel: Edit data
	/*******************************************************************************/
	function adminEditForm() {
        
            global $wpdb;
            $sql = 'SELECT * FROM '.$wpdb->prefix.$this->table;
            $result = $wpdb->get_results($sql, ARRAY_A);
            
               ?><script>var updatepath = "<?php echo WPAC_URL . '/js/wpac_update.php'; ?>";</script>
                    
		<?php $this->adminHeadMenu(); ?>
                        
                <table>
                    <tbody>
		<?php
                foreach ($result as $key => $value){
                    
                    echo '<tr><td>';

                    $data = unserialize($value['ac_data']);
                    
                    echo __('Id:', 'wp-ac').' '.$value['ac_id'].' | '
                            .__('Name:', 'wp-ac').' <span class="wps_cell" id="ac_name:'.$value['ac_id'].'" contenteditable="true">'.$value['ac_name'].'</span> | '
                            .__('File:', 'wp-ac').' '.$value['ac_file'].'<br />';
                            
                    echo '<table><tr>'
                            .'<td><b>Header</b>: </td>';

                    echo '</tr><tr>';
                    echo '</tr></table>';
                    
                    echo '<br /></tr>';
                }
                ?>
                    </tbody>
                </table>
                <?php
                
        }
                
	/*******************************************************************************/
	/* 
	/*******************************************************************************/
       function adminGetPostedData(){

                $safe_mode = (ini_get('safe_mode') ? 'true' : 'false');

	  	return array( 	'version'           => $this->WPACVERSION,
				'phpversion'        => phpversion(),
				'useIMDb'           => wpac_BooleanOption ($_POST['useIMDb']),
				'chartFont'         => wpac_CleanPOST($_POST['chartFont']),
				'titleSizePercent'  => wpac_CleanPOST($_POST['titleSizePercent']),
				'safe_mode'         => $safe_mode
		);     
                
        }
        
	/*******************************************************************************/
	/* Option panel: option form
	/*******************************************************************************/
	function adminOptionsForm() {
	
                // Prepare form display and update options 
                $wpac_settings = $this->wpac_settings;

                // Display form
		?>
                    
		<?php $this->adminHeadMenu(); ?>
                    
		<div class="wrap">
                    
			<form method="post">       	

				<?php if ($this->versionCheck()) { ?>
					<p class="info"><?php _e('New Version available: ', 'wp-ac'); ?>
					<?php _e('The server reports that a new wp-ac Version is now available. Please visit the plugin homepage for more information.', 'wp-ac'); ?></p>
		                <?php } else ?><p class="info"><?php _e('Version number: ', 'wp-ac');echo $this->WPACVERSION; ?></p>
				<br />
        	
				<div style="width: 90%; display: block;">
					<p id="basicoptions" style="margin-right: 1em;display: inline-block;width: 10em;text-align: center" title="<?php _e('Click to view basic options only.', 'wp-ac'); ?>"><?php _e('Basic options', 'wp-ac'); ?></p>
					<p id="advancedoptions" style="display: inline-block;width: 10em;text-align: center" title="<?php _e('Click to view advanced options.', 'wp-ac'); ?>"><?php _e('Advanced options', 'wp-ac'); ?></p>
					<p class="submit" style="display: inline-block;margin: 0; padding: 0;width: 15%;float: right"><input style="align: right;" type="submit" name="info_update" value="<?php _e('Update', 'wp-ac'); ?>" class="button" /></p>
				</div>
        	
                                <p class="title showhide"><?php _e('Location settings', 'wp-ac'); ?></p>
				<fieldset class="options wpacswitch">
        	
					<p class="tabs"><?php _e('Font family', 'wp-ac'); ?></p>
					<input type="text" name="chartFont" value="<?php echo $wpac_settings['chartFont']; ?>" size="60" /><br />
					<p class="info"><?php _e('Choose font family to use in chart.', 'wp-ac'); ?>

					<p class="tabs"><?php _e('Title Font', 'wp-ac'); ?></p>
					<input type="text" name="titleSizePercent" value="<?php echo $wpac_settings['titleSizePercent']; ?>" size="3" />
					<?php _e('Size of the font title in percent of the chart width. Default is 5%.', 'wp-ac'); ?><br />
                                        
				</fieldset>
                                <br />
                                
				<p class="title showhide"><?php _e('Some settings', 'wp-ac'); ?></p>
				<fieldset class="options wpacswitch">
        	
					<p class="tabs"><?php _e('Use IMDb backup:', 'wp-ac'); ?></p>
					<input type="checkbox" name="useIMDb" <?php if ($wpac_settings['useIMDb'] == 'true') echo 'checked'; ?> /><br />       	
					<p class="info"><?php _e('Check if you want to use IMDb as a backup if Allocine is not available.', 'wp-ac'); ?></p>
		
					<p class="tabs"><?php _e('Reset custom design:', 'wp-ac'); ?></p>
					<input type="checkbox" name="resetCustomDesign" /><br />       	
					<p class="info"><?php _e('Check if you want to reset your customized display.', 'wp-ac'); ?></p>
		
				</fieldset>
                                <br />

                                <div class="wpacadvanced">
				<p class="title showhide"><?php _e('System check', 'wp-ac'); ?></p>
				<fieldset class="options wpacswitch">
        	
					<p class="tabs"><?php _e('PHP version', 'wp-ac'); ?></p>
					<?php echo $wpac_settings['phpversion']; ?><br />
        	
					<p class="tabs"><?php _e('Remote files', 'wp-ac'); ?></p>
					<?php if (ini_get('allow_url_fopen')) _e('Retrieve remote file is OK', 'wp-ac'); elseif (function_exists('curl_init'))_e('Retrieve remote file is OK using cURL', 'wp-ac'); else _e('Retrieve remote file not allowed on this server.', 'wp-ac'); ?><br />
					<p class="info"><?php _e('Dealing with remote file also requires php 4.3.0 or later', 'wp-ac'); ?></p><br />
        	
					<p class="tabs"><?php _e('Safe mode', 'wp-ac'); ?></p>
					<?php if ($wpac_settings['safe_mode']=='true') _e('Safe mode on', 'wp-ac'); else _e('Safe mode off', 'wp-ac'); ?><br />
			
					<p class="tabs"><?php _e('Memory limit', 'wp-ac'); ?></p>
					<?php if ($ml = ini_get('memory_limit')) echo $ml; else _e('Cannot Retrieve memory limit', 'wp-ac'); ?><br />
        	
					<p class="tabs"><?php _e('Memory usage', 'wp-ac'); ?></p>
					<?php if (function_exists('memory_get_usage')) _e('Function memory_get_usage available', 'wp-ac'); else _e('Function memory_get_usage not available', 'wp-ac'); ?><br />
        	
					<p class="tabs"><?php _e('Set Memory limit', 'wp-a'); ?></p>
					<?php $ms = ini_set('memory_limit', $ml); if (empty($ms)) _e('Memory cannot be set', 'wp-ac'); else _e('Memory can be set', 'wp-ac'); ?><br />
        	
				</fieldset>
				</div>

	         		<div class="submit"><input type="submit" name="info_update" value="<?php _e('Update', 'wp-ac'); ?>" class="button" /></div>

			</form>
		</div>
		<?php
	
        }
        
	/*******************************************************************************/
	/* admin menu
	/*******************************************************************************/
	function adminHeadMenu() {
            
            	?>
		<div class="wrap">
                    
			<h2 class="title showhide"><?php _e('WP-AC Options', 'wp-ac'); ?></h2>
                        
			<div>
				<a href="<?php echo $this->ADMINURL; ?>" class='button-primary' title="options sub-menu">Options</a>
				<a href="<?php echo $this->ADMINURL."&wpacadmin=design"; ?>" class='button-primary' title="design sub-menu">Design</a>
				<a href="<?php echo $this->ADMINURL."&wpacadmin=edit"; ?>" class='button-primary' title="data edit sub-menu">Edit Data</a>
			</div>
			<br />
                        
		</div>
                 <?php
        }
                
	/****************************************************************/
	/* Return the error message
	/****************************************************************/
	function isError ($text) {
		
		$this->update_error = true;
		$this->error_msg = $text;
                
	}
        
	/***********************************************************************************/
	/* Check for a new version of wp-ac on server. This one is basic
	/***********************************************************************************/
	function versionCheck() {

		require_once(ABSPATH . WPINC . '/class-snoopy.php');
	
		// check for a new version
		$check_intervall = get_option( "wpac_next_update" );
	
		if ( ($check_intervall < time() ) or (empty($check_intervall)) ) {
			if (class_exists(snoopy)) {

				$client = new Snoopy();
				$client->_fp_timeout = 10;

				if (@$client->fetch($this->WPACURL) === false)
					return false;

			   	$remote = $client->results;
				$server_version = $remote;

				if ( version_compare($server_version, $this->WPACVERSION, '>'))
			 		return true;

				// come back in 24 hours :-)
				$check_intervall = time() + 86400;
				update_option( "wpac_next_update", $check_intervall );
				return false;
			}
		}

		return false;
	}

}
