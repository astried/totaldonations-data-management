<?php
/*
 Plugin Name: Totaldonations_Data_Management
 Plugin URI: https://totaldonations.com/
 Text Domain: td-data-management
 Domain Path: /languages
 Description: This plugin use by Totaldonations for viewing donation data from previous version below 3.x.x
 Version: 1.0.1
 Author: Astried Silvanie
 Author URI: https://totaldonations.com/
 License: GPL2

 {Plugin Name} is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 2 of the License, or
 any later version.

 {Plugin Name} is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with {Plugin Name}. If not, see {License URI}.
 */

 if ( !defined( 'ABSPATH' ) ) exit;


add_action( 'plugins_loaded', array ( 'Totaldonations_DataManager', 'init' ), 9 );


register_activation_hook( __FILE__, array ( 'Totaldonations_DataManager', 'donation_active_trigger' ) );
register_deactivation_hook( __FILE__, array ( 'Totaldonations_DataManager', 'donation_deactive_trigger' ) );

if ( !class_exists( 'Totaldonations_DataManager' ) )
{
class Totaldonations_DataManager
{
	//Initial
	public static function init()
	{
	    //Call Defined Path
	  	self::setup_path();

	  	//get current language
	    $init_language = get_locale();

	    if(is_admin())
		{
		    include_once 'tdm-functions.php';
			include_once 'admin/main.php';
			include_once 'tdm-ajax.php';

			add_action( 'admin_enqueue_scripts', array( __CLASS__ , 'load_admin_scripts') );
		}
	}

	public static function load_admin_scripts($hook)
	{
		$ajax_url =  admin_url( 'admin-ajax.php' );
		
		$is_in_the_hook = ( $hook == ("toplevel_page_Totaldonations_DM") || ( strpos( $hook, 'Totaldonations_DM'  ) !== false )  );

		$version = date ( "njYHi", time() );

		
		if( $is_in_the_hook )
		{
			wp_enqueue_style( 'Totaldonations_Boot', Totaldonations_DM_DIR_URL.'assets/bootstrap-4.0/bootstrap.min.css', array(), $version );
			wp_enqueue_script( 'Totaldonations_Boot', Totaldonations_DM_DIR_URL.'assets/bootstrap-4.0/js/bootstrap.js', array('jquery'), $version );

			//wp_enqueue_style( 'Totaldonations_DTable', Totaldonations_DM_DIR_URL.'assets/datatables/datatables.css', array(), $version );
			//wp_enqueue_script( 'Totaldonations_DTable', Totaldonations_DM_DIR_URL.'assets/datatables/datatables.js', array('jquery'), $version );
			
			wp_enqueue_script( 'Totaldonations_jquery-3.5.1', 'https://code.jquery.com/jquery-3.5.1.js' );
			
			wp_enqueue_style( 'Totaldonations_DTable', Totaldonations_DM_DIR_URL.'assets/datatables/jquery.dataTables.min.css', array(), $version );
			wp_enqueue_script( 'Totaldonations_DTable', Totaldonations_DM_DIR_URL.'assets/datatables/jquery.dataTables.min.js', array('Totaldonations_jquery-3.5.1'), $version );

			wp_enqueue_style( 'Totaldonations_DM', Totaldonations_DM_DIR_URL.'assets/style.css', array(), $version  );
			wp_enqueue_script( 'Totaldonations_DM', Totaldonations_DM_DIR_URL.'assets/script.js', array('jquery', 'Totaldonations_DTable' ), $version );

			//wp_enqueue_script( 'migla-main-js', Totaldonations_DIR_URL.'assets/js/admin/admin-dashboard.js' );

			wp_localize_script( 'Totaldonations_DM', 'Totaldonations_DM',
			            array( 'ajaxurl' => $ajax_url,
			                   'nonce' => wp_create_nonce( 'Totaldonations_DM' )
			                )
			);			
		} 
	}			    

	//what to do when active
	public static function donation_active_trigger()
	{
        self::tables_creation();
        
        global $wpdb;
        
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $password = substr( str_shuffle( $chars ), 0, 8 ); 
        
        $wpdb->insert( "{$wpdb->prefix}tdm_migla_options",
    		              array( "option_name"  => 'sitecode',
            		             "option_value" => $password
            			       ),
            			  array( '%s', '%s' )
    		  	        );         
	}

	//what to do when deactive
	public static function donation_deactive_trigger()
	{
		
	}
	
    static function tables_creation()
	{
		global $wpdb;
		$charset_collate 	= $wpdb->get_charset_collate();
				
		$map_table	= $wpdb->prefix . 'tdm_migla_mapping_records';

		$sql = "CREATE TABLE IF NOT EXISTS $map_table(";
		$sql .= " id int(11) NOT NULL AUTO_INCREMENT,";
		$sql .= " post_id int(11),";
		$sql .= " donation_id int(11),";
		$sql .= " PRIMARY KEY (id)";
		$sql .= " )$charset_collate;";
		
		$option_table	= $wpdb->prefix . 'tdm_migla_options';

		$sql2 = "CREATE TABLE IF NOT EXISTS $option_table(";
		$sql2 .= " id int(11) NOT NULL AUTO_INCREMENT,";
		$sql2 .= " option_name varchar(100),";
		$sql2 .= " option_value TEXT,";
		$sql2 .= " PRIMARY KEY (id)";
		$sql2 .= " )$charset_collate;";			
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	    dbDelta( $sql );
	    dbDelta( $sql2 );
	}
	
	static public function setup_path()
	{
		if( ! defined( 'Totaldonations_DM_DIR_URL' ) )
			define( 'Totaldonations_DM_DIR_URL', plugin_dir_url( __FILE__ ) );

		if( ! defined( 'Totaldonations_DM_DIR_PATH' ) )
			define( 'Totaldonations_DM_DIR_PATH', plugin_dir_path( __FILE__ ) );

		if( ! defined( 'Totaldonations_DM_PLUGIN_DIR' ) )
			define( 'Totaldonations_DM_PLUGIN_DIR' , plugin_dir_url( __FILE__ )   );		
	}

}
}
