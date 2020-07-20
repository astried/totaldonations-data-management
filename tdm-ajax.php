<?php
if ( !defined( 'ABSPATH' ) ) exit;

function TotaldonationsDM_Ajax_save_donations()
{
    global $wpdb;
    $response = array("status" => '0' , "message" => '');
    
    if( !wp_verify_nonce( sanitize_text_field($_POST['nonce']), 'Totaldonations_DM' ) )
    {
        $response['message'] = __('Nonce is not recognize', 'migla-donation');
    }else{
        $response['status'] = '200'; 
        
        $posts = sanitize_text_field($_POST['post']);
        $oldpost = array();
        
        if( !empty($posts) ){
            $oldpost = explode(",", $posts);
        }
        
        $campaigns_hashmap = $_POST['campaign_hashmap'];
        if(!empty($campaigns_hashmap)){
            $campaigns_hashmap = (array)$campaigns_hashmap;
        }
        
        $campaigns_new_hashmap = $_POST['campaign_new_hashmap'];
        if(!empty($campaigns_new_hashmap)){
            $campaigns_new_hashmap = (array)$campaigns_new_hashmap;
        }
        
        $fields_hashmap = $_POST['fields_hashmap'];
        if(!empty($fields_hashmap)){
            $fields_hashmap = (array)$fields_hashmap;
        }
        
        $fields_new_hashmap = $_POST['fields_new_hashmap'];
        if(!empty($fields_new_hashmap)){
            $fields_new_hashmap = (array)$fields_new_hashmap;
        }     
        
        $sql = "SELECT * FROM {$wpdb->prefix}posts WHERE ID IN ( ".$posts." )";
        $allpost = $wpdb->get_results( $sql, ARRAY_A );
        
        $deffield = array("miglad_firstname",
                        "miglad_lastname",
                        "miglad_email",
                        "miglad_amount",
                        "miglad_country",
                        "miglad_campaign"
                    );
        
        foreach( $allpost as $row )
        {
            $track_id = tdm_migla_tracking( $row['ID'] );
            
            if( $track_id > 0 )
            {
                 $response['message'] .= $row['ID']."-is-saved,";
            }else{
                $email  = get_post_meta($row['ID'], "miglad_email", true);
    		    $status = 1;
    			$firstname  = get_post_meta($row['ID'], "miglad_firstname", true);
    			$lastname   = get_post_meta($row['ID'], "miglad_lastname", true);
    		    $amount     = get_post_meta($row['ID'], "miglad_amount", true);
    		    
    		    //get the campaign name
    		    $campaign = "0";
    		    
    		    if(!empty($campaigns_hashmap)){
    		        $campaign_old   = get_post_meta($row['ID'], "miglad_campaign", true);
    		        $pos = array_search( $campaign_old, $campaigns_hashmap);
    		        $campaign = $campaigns_new_hashmap[$pos];
    		    }
    		    
    		    $country    = get_post_meta($row['ID'], "miglad_country", true);
    			
    			$anon       = get_post_meta($row['ID'], "miglad_anonymous", true);
    			if(empty($anon)) $anon = 'no';
    			
    			$repeat     = get_post_meta($row['ID'], "miglad_repeating", true);
    			if(empty($repeat)){
    			     $repeat = 'no';    
    			}else{
    			    if( strtolower($repeat) != 'no' ){
    			        $repeat = 'yes'; 
    			    }    
    			}
    			
    			$mailist    = get_post_meta($row['ID'], "miglad_mg_add_to_milist", true);
    			if(empty($mailist)) $mailist = 'no';
    			
    			$session    = get_post_meta($row['ID'], "miglad_session_id", true);
    			$session = str_replace("migla", "", $session);
    			
    			$datetime   = $row['post_date'];
    			
    			$gmt        = get_option('gmt_offset');
    			
    			$timestamp  = strtotime($datetime);
    			
    			//miglad_transactionType
    			$trans = strtolower( get_post_meta($row['ID'], "miglad_transactionType", true) );
    			$gateway = "paypal";
    			
                if( strpos($trans, "stripe") !== false )
                {
                	$gateway = "stripe";
                }else if( strpos($trans, "paypal") !== false ){
                	$gateway = "paypal";
                }else if( strpos($trans, "web_accept") !== false ){
                    $gateway = "paypal";
                }else if( strpos($trans, "subscr_payment") !== false ){
                    $gateway = "paypal";
                    $repeat  = 'yes';
                }
    			      
                $wpdb->insert( "{$wpdb->prefix}migla_donation",
    		            array(
    		            		"email"	=>  $email, //1,
    		            		"status" => $status,
    							"firstname" => $firstname, //2
    			                "lastname"   => $lastname, //3
    		                  	"amount"  => $amount, //4
    		                  	"campaign" => $campaign,
    		                  	"country"  => $country, //5
    			                "anonymous" => $anon, //6
    			                "repeating" => $repeat, //7
    			                "mailist" => $mailist, //8
    			                "gateway" => $gateway, //9
    			                "session_id" => $session, //10
    			                "date_created" => $datetime, //11
    			                "gmt" => $gmt,//12
    			                "timestamp" => $timestamp//13
    			            ),
    			        array( '%s', //1
    			                '%d',
    			                '%s', //2
    			                '%s', //3
    			                '%f', //4
    			                '%s', //5
    			                '%s', //6
    			                '%s', //7
    			                '%s', //8
    			                '%s', //9
    			                '%s', //10
    			                '%s', //11 
    			                '%s', //12
    			                '%d' //13
    			                )
    		  	);
    		  	
    		  	$donation_id = $wpdb->insert_id; 
    		  	
    		  	$sql = "SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id = %d";
                $postmeta = $wpdb->get_results( $wpdb->prepare( $sql, $row['ID'] ), ARRAY_A );
                
                if(!empty($postmeta))
                {
                    foreach($postmeta as $pm)
                    {
                        $metakey = $pm['meta_key'];
                        $metavalue = $pm['meta_value'];
                        
                        if( $metakey == "miglad_campaign" ){
                            $metakey = "miglad_campaign_name";
                        }else if( $metakey == "miglad_repeating" ){
                            $metavalue = $pm['meta_value'].",".$pm['meta_value'].",".$pm['meta_value'].",".$pm['meta_value'];
                        }
                
                        if( substr($metakey, 0, 7) == "miglac_" )
                        {
                		    if(!empty($fields_hashmap))
                		    {
                		        $fields_old   = $metakey;
                		        $pos = array_search( $fields_old, $fields_hashmap);
                		        if($pos >= 0){
                    		        $metakey = $fields_new_hashmap[$pos];
                		        }
                		    }            
                        }
                        
                        $wpdb->insert( "{$wpdb->prefix}migla_donation_meta",
    		                        array( "meta_value"     => $metavalue,
            		                        "donation_id"   => $donation_id,
            			                    "meta_key"	    => $metakey
            			                ),
            			            array( '%s', '%d', '%s' )
    		  	        ); 
                    }//foreachmeta
                    
                    $wpdb->insert( "{$wpdb->prefix}migla_donation_meta",
    		                        array( "meta_value"     => '0',
            		                        "donation_id"   => $donation_id,
            			                    "meta_key"	    => 'miglad_form_id'
            			                ),
            			            array( '%s', '%d', '%s' )
    		  	        ); 
    		  	        
    		  	    $wpdb->insert( "{$wpdb->prefix}migla_donation_meta",
    		                        array( "meta_value"     => get_locale(),
            		                        "donation_id"   => $donation_id,
            			                    "meta_key"	    => 'miglad_language'
            			                ),
            			            array( '%s', '%d', '%s' )
    		  	        ); 
                }//if meta not empty
    		  	
    		  	$track_id = tdm_migla_tracking( $row['ID'] );
    		  	
    		  	if( $track_id > 0 ){
        		  	$wpdb->update( "{$wpdb->prefix}tdm_migla_mapping_records",
    		                        array( "donation_id" => $donation_id ),
    		                        array( "id" => $track_id ),
            			            array( '%d' ),
            			            array( '%d' )
            			     );
    		  	}else{
        		  	$wpdb->insert( "{$wpdb->prefix}tdm_migla_mapping_records",
    		                        array( "post_id"     => $row['ID'],
            		                       "donation_id" => $donation_id
            			                  ),
            			            array( '%d', '%d' )
            			     );
    		  	}
    		  	
    		  	$response['message'] .= $donation_id.",";
		  	
            }//if track found it
 
        }//for all post
    }
    
    echo json_encode($response);
    die();
}

function TotaldonationsDM_Ajax_rollback_donations()
{
    global $wpdb;
    $response = array("status" => '0' , "message" => '');
    
    if( !wp_verify_nonce( sanitize_text_field($_POST['nonce']), 'Totaldonations_DM' ) )
    {
        $response['message'] = __('Nonce is not recognize', 'migla-donation');
    }else{
        $response['status'] = '200'; 
        
        $trackid = sanitize_text_field($_POST['trackid']);
        
        $sql = "SELECT donation_id FROM {$wpdb->prefix}tdm_migla_mapping_records";
        $sql .= " WHERE id = %d";
        $donation_id = $wpdb->get_var( $wpdb->prepare( $sql, $trackid ) );
    
        if( $donation_id > 0 ){
            $sql = "DELETE FROM {$wpdb->prefix}migla_donation WHERE id = %d" ;
            $wpdb->query(  $wpdb->prepare( $sql, $donation_id ) );
    
            $sql = "DELETE FROM {$wpdb->prefix}migla_donation_meta WHERE donation_id = %d" ;
            $wpdb->query(  $wpdb->prepare( $sql, $donation_id ) );
            
            $sql = "DELETE FROM {$wpdb->prefix}tdm_migla_mapping_records WHERE id = %d" ;
            $wpdb->query(  $wpdb->prepare( $sql, $trackid ) );
        }
    }
    
    echo json_encode($response);
    die();
}

$migla_ajax_callers = array(
                        "TotaldonationsDM_Ajax_save_donations",
                        "TotaldonationsDM_Ajax_rollback_donations"
                        );

if ( is_user_logged_in() ) 
{
	foreach( $migla_ajax_callers as $migla_ajax_call )
	{
		add_action("wp_ajax_" . $migla_ajax_call, $migla_ajax_call );
	}	
}
?>