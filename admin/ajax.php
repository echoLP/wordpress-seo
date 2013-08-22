<?php
/**
 * @package Admin
 */

if ( !defined('WPSEO_VERSION') ) {
	header('HTTP/1.0 403 Forbidden');
	die;
}

/**
 * Function used from AJAX calls, takes it variables from $_POST, dies on exit.
 */
function wpseo_set_option() {
	if ( !current_user_can( 'manage_options' ) )
		die( '-1' );
	check_ajax_referer( 'wpseo-setoption' );

	$option = esc_attr( $_POST['option'] );
	if ( $option != 'page_comments' )
		die( '-1' );

	update_option( $option, 0 );
	die( '1' );
}

add_action( 'wp_ajax_wpseo_set_option', 'wpseo_set_option' );

/**
 * Function used to remove the admin notices for several purposes, dies on exit.
 */
function wpseo_set_ignore() {
	if ( !current_user_can( 'manage_options' ) )
		die( '-1' );
	check_ajax_referer( 'wpseo-ignore' );

	$options                               = get_option( 'wpseo' );
	$options['ignore_' . $_POST['option']] = 'ignore';
	update_option( 'wpseo', $options );
	die( '1' );
}

add_action( 'wp_ajax_wpseo_set_ignore', 'wpseo_set_ignore' );

/**
 * Function used to remove the admin notices for several purposes, dies on exit.
 */
function wpseo_kill_blocking_files() {
	if ( !current_user_can( 'manage_options' ) )
		die( '-1' );
	check_ajax_referer( 'wpseo-blocking-files' );

	$message = 'There were no files to delete.';
	$options = get_option( 'wpseo' );
	if ( isset( $options['blocking_files'] ) && is_array( $options['blocking_files'] ) && count( $options['blocking_files'] ) > 0 ) {
		$message = 'success';
		foreach ( $options['blocking_files'] as $k => $file ) {
			if ( !@unlink( $file ) )
				$message = __( 'Some files could not be removed. Please remove them via FTP.', 'wordpress-seo' );
			else
				unset( $options['blocking_files'][$k] );
		}
		update_option( 'wpseo', $options );
	}

	die( $message );
}

add_action( 'wp_ajax_wpseo_kill_blocking_files', 'wpseo_kill_blocking_files' );

/**
 * Retrieve the suggestions from the Google Suggest API and return them to be
 * used in the suggest box within the plugin. Dies on exit.
 */
function wpseo_get_suggest() {
	check_ajax_referer( 'wpseo-get-suggest' );

	$term   = urlencode( $_GET['term'] );
	$result = wp_remote_get( 'http://www.google.com/complete/search?output=toolbar&q=' . $term );

	preg_match_all( '`suggestion data="([^"]+)"/>`u', $result['body'], $matches );

	$return_arr = array();

	foreach ( $matches[1] as $match ) {
		$return_arr[] = html_entity_decode( $match, ENT_COMPAT, "UTF-8" );
	}
	echo json_encode( $return_arr );
	die();
}

add_action( 'wp_ajax_wpseo_get_suggest', 'wpseo_get_suggest' );


/**
 * Function used to save a redirect from the manage redirect page
 * 
 */
function wpseo_redirect_quicksave() {
	include_once( WPSEO_PATH . '/admin/class-redirect-table.php' );
	
	// nonce check
	check_ajax_referer( 'save', '_wpnonce-redirect-save' );

	// save 404 redirect
	if ( $_REQUEST['post_ID'] == '404_redirect' ) {
		$options = get_option('wpseo');
		$options['404_redirect'] = $_REQUEST['new_url'];
		update_option( 'wpseo', $options );
	
		$record['id']  	            = '404_redirect';
		$record['old_url']          = '404 redirect';
		$record['relative_old_url'] = '404 redirect';
		$record['new_url']          = $_REQUEST['new_url'];

		$wp_list_table = new WPSEO_Redirect_Table();
		$wp_list_table->single_row( $record );

		wp_die();	
	}
		
	// set the new redirect value
	wpseo_set_value( 'redirect', $_POST['new_url'], $_POST['post_ID'] );

	// retrieve the post for the data to be return
	$query = new WP_Query( array(
		'p' => $_POST['post_ID'],
		'meta_key' => '_yoast_wpseo_redirect', 
		'meta_value' => false, 
		'meta_compare' => '!=' )
	);

	$records = array();
	foreach( $query->get_posts() as $post ) {
		$record = array();

		$wpurl            = get_bloginfo( 'url' );
		$permalink        = get_permalink( $post );
		$relative_old_url = str_replace( $wpurl, '', $permalink );
		
		$record['id']               = $post->ID;
		$record['old_url']          = $permalink;
		$record['relative_old_url'] = $relative_old_url;
		$record['new_url']          = wpseo_get_value( 'redirect', $post->ID );
		
		$records[] = $record;
	}

	// return html display as ajax output
	if ( $records ) {
		$wp_list_table = new WPSEO_Redirect_Table();
		$wp_list_table->single_row($records[0]);
	}

	wp_die();	
}
add_action('wp_ajax_redirect_save', 'wpseo_redirect_quicksave');