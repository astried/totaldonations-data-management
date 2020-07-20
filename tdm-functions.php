<?php

function tdm_migla_tracking( $post_id )
{
    global $wpdb;
    
    $sql = "SELECT id FROM {$wpdb->prefix}tdm_migla_mapping_records";
    $sql .= " WHERE post_id = %d";
    $id = $wpdb->get_var( $wpdb->prepare( $sql, $post_id ) );    
    
    return $id;
}

?>