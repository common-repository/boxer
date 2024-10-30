<?php

// ------------------------------------------------------------------------
// DUPLICATE CONTENT BLOCK FUNCTIONS:                                               
// ------------------------------------------------------------------------
// THESE UTILITY FUNCTIONS ARE USED TO DUPLICATE THE SETTINGS OF ONE
// CONTENT BLOCK TO ANOTHER.                     
// ------------------------------------------------------------------------

/**
* Duplicate the current box
* 
*/
function cow_duplicate_block() 
{
    global $wpdb;

    // Get the original post
    $id = absint( $_GET['block'] );
    $post = cow_duplicate_this_dangit( $id );

    // Copy the post and insert it
    if ( isset( $post ) && $post != null ) {
        $new_id = cow_duplicate_block_process( $post );

        $duplicated = true;
        $sendback = wp_get_referer();
        $sendback = add_query_arg( 'duplicated', (int)$duplicated, $sendback );

        wp_redirect( $sendback );
        exit();
    } else {
        wp_die( __( 'Sorry, this block couldn\'t be duplicated because it could not be found in the database, check for this ID: ' ) . $id );
    }
}

/**
* Get the current post
* 
* @param mixed $id
*/
function cow_duplicate_this_dangit( $id ) 
{
    $post = get_post($id);
    return $post;
}

/**
* put your comment there...
* 
* @param mixed $post
* @return WP_Error
*/
function cow_duplicate_block_process( $post ) 
{
    global $wpdb;

    $new_post_date          = $post->post_date;
    $new_post_date_gmt      = get_gmt_from_date( $new_post_date );
    $new_post_type          = $post->post_type;
    $post_content           = str_replace( "'", "''", $post->post_content );
    $post_content_filtered  = str_replace( "'", "''", $post->post_content_filtered );
    $post_excerpt           = str_replace( "'", "''", $post->post_excerpt );
    $post_title             = str_replace( "'", "''", $post->post_title ) . __(" (Copy)", EPL_ADMIN_TEXTDOMAIN);
    $post_name              = str_replace( "'", "''", $post->post_name );
    $comment_status         = str_replace( "'", "''", $post->comment_status );
    $ping_status            = str_replace( "'", "''", $post->ping_status );
    
    $defaults = array(
        'post_status'               => $post->post_status, 
        'post_type'                 => $new_post_type,
        'post_author'               => $user_ID,
        'ping_status'               => $ping_status, 
        'post_parent'               => $post->post_parent,
        'menu_order'                => $post->menu_order,
        'to_ping'                   =>  $post->to_ping,
        'pinged'                    => $post->pinged,
        'post_excerpt'              => $post_excerpt,
        'post_title'                => $post_title,
        'post_content'              => $post_content,
        'post_content_filtered'     => $post_content_filtered,
        'import_id'                 => 0
    );
    
    // Insert the new template in the post table
    $new_post_id = wp_insert_post($defaults);

    // Copy the taxonomies
    cow_duplicate_taxonomies( $post->ID, $new_post_id, $post->post_type );

    // Copy the meta information
    cow_duplicate_block_meta( $post->ID, $new_post_id );

    // Finds children (Which includes product files AND product images), their meta values, and duplicates them.
    cow_duplicate_children( $post->ID, $new_post_id );

    return $new_post_id;
}

/**
* Duplicate the taxonomies of the current post
* 
* @param mixed $id
* @param mixed $new_id
* @param mixed $post_type
*/
function cow_duplicate_taxonomies( $id, $new_id, $post_type ) 
{
    global $wpdb;
    if ( isset( $wpdb->terms ) ) {
        // WordPress 2.3
        $taxonomies = get_object_taxonomies( $post_type ); //array("category", "post_tag");
        foreach ( $taxonomies as $taxonomy ) {
            $post_terms = wp_get_object_terms( $id, $taxonomy );
            for ( $i = 0; $i < count( $post_terms ); $i++ ) {
                wp_set_object_terms( $new_id, $post_terms[$i]->slug, $taxonomy, true );
            }
        }
    }
}

/**
* Duplicate the custom fields of the current post
* 
* @param mixed $id
* @param mixed $new_id
*/
function cow_duplicate_block_meta( $id, $new_id ) 
{
    global $wpdb;
    
    $post_meta_infos = $wpdb->get_results( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$id" );

    if ( count( $post_meta_infos ) != 0 ) 
    {
        $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";

        foreach ( $post_meta_infos as $meta_info ) 
        {
            $meta_key = $meta_info->meta_key;
            $meta_value = addslashes( $meta_info->meta_value );
            
            // make sure custom fields of type CHECK are overwritten, because they are created when a post is created
            if ($meta_info->meta_value != 'on' && $meta_info->meta_value != 'off')
            {               
                $sql_query_sel[] = "SELECT $new_id, '$meta_key', '$meta_value'";
            } 
            else 
            {
                $sql_query_upd = "UPDATE $wpdb->postmeta SET meta_value = '$meta_value' WHERE meta_key = '$meta_key' and post_id = $new_id;";
                $wpdb->query( $sql_query_upd );
            }     
        }
        $sql_query.= implode( " UNION ALL ", $sql_query_sel );
        
        // Insert all custom fields at once
        $wpdb->query( $sql_query );
        
        // Increment out index
        $wpdb->query( "UPDATE $wpdb->postmeta SET meta_value = meta_value + 1 WHERE meta_key = 'box_general_index' and post_id = $new_id;" );
    }
}

/**
* Duplicate children of the current post
* 
* @param mixed $old_parent_id
* @param mixed $new_parent_id
*/
function cow_duplicate_children( $old_parent_id, $new_parent_id ) 
{
    global $wpdb;

    //Get children products and duplicate them
    $child_posts = $wpdb->get_results( "SELECT * FROM $wpdb->posts WHERE post_parent = $old_parent_id" );

    foreach ( $child_posts as $child_post ) {

        $new_post_date = $child_post->post_date;
        $new_post_date_gmt = get_gmt_from_date( $new_post_date );

        $new_post_type = $child_post->post_type;
        $post_content = str_replace( "'", "''", $child_post->post_content );
        $post_content_filtered = str_replace( "'", "''", $child_post->post_content_filtered );
        $post_excerpt = str_replace( "'", "''", $child_post->post_excerpt );
        $post_title = str_replace( "'", "''", $child_post->post_title );
        $post_name = str_replace( "'", "''", $child_post->post_name );
        $comment_status = str_replace( "'", "''", $child_post->comment_status );
        $ping_status = str_replace( "'", "''", $child_post->ping_status );

        $wpdb->query(
                "INSERT INTO $wpdb->posts
            (post_author, post_date, post_date_gmt, post_content, post_content_filtered, post_title, post_excerpt,  post_status, post_type, comment_status, ping_status, post_password, to_ping, pinged, post_modified, post_modified_gmt, post_parent, menu_order, post_mime_type)
            VALUES
            ('$child_post->post_author', '$new_post_date', '$new_post_date_gmt', '$post_content', '$post_content_filtered', '$post_title', '$post_excerpt', '$child_post->post_status', '$new_post_type', '$comment_status', '$ping_status', '$child_post->post_password', '$child_post->to_ping', '$child_post->pinged', '$new_post_date', '$new_post_date_gmt', '$new_parent_id', '$child_post->menu_order', '$child_post->post_mime_type')" );

        $old_post_id = $child_post->ID;
        $new_post_id = $wpdb->insert_id;
        $child_meta = $wpdb->get_results( "SELECT post_id, meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = $old_post_id" );

        foreach ( $child_meta as $child_meta ) {
            $wpdb->query(
                    "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value)
                  VALUES('$new_post_id', '$child_meta->meta_key', '$child_meta->meta_value')"
            );
        }
    }
}

if ( isset( $_GET['cow_admin_action'] ) && ($_GET['cow_admin_action'] == 'duplicate_block') ) {
    add_action( 'admin_init', 'cow_duplicate_block' );
}