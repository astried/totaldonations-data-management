<?php
if ( !defined( 'ABSPATH' ) ) exit;

function TotaldonationsDM_Ajax_save_donations()
{
    $response = array("status" => '0' , "message" => '');
    
    if( !wp_verify_nonce( sanitize_text_field($_POST['nonce']), 'Totaldonations_DM' ) )
    {
        $response['message'] = __('Nonce is not recognize', 'migla-donation');
    }else{
        $response['status'] = '200';        
    }
    
    echo json_encode($response);
    die();
}

$migla_ajax_callers = array(
                        "TotaldonationsDM_Ajax_save_donations"
                        );

if ( is_user_logged_in() ) 
{
	foreach( $migla_ajax_callers as $migla_ajax_call )
	{
		add_action("wp_ajax_" . $migla_ajax_call, $migla_ajax_call );
	}	
}
?>