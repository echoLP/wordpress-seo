<?php
/**
 * @package Admin
 */

if ( !defined('WPSEO_VERSION') ) {
	header('HTTP/1.0 403 Forbidden');
	die;
}


include_once plugin_dir_path( __FILE__ ) . '/../class-redirect-table.php';

global $wpseo_admin_pages;

$options = get_wpseo_options();

$table = new WPSEO_Redirect_Table();
// Fetch, prepare, sort, and filter our data...
$table->prepare_items();
    
?>

	<div class="wrap">
		<a href="http://yoast.com/">
			<div class="icon32" style="background: url('http://localhost/wordpress/wp-content/plugins/wordpress-seo-git-2.0/images/wordpress-SEO-32x32.png') no-repeat;" id="yoast-icon">
				<br>
			</div>
		</a>
		<h2 id="wpseo-title"><?php echo __( 'Yoast WordPress SEO: Redirects', 'wordpress-seo' ); ?></h2>
		<div style="min-width:400px; padding: 0 20px 0 0;" class="postbox-container" id="wpseo_content_top">
		<div class="metabox-holder">
		<!-- <div class="meta-box-sortables"> -->

		<div class="postbox" id="wpseo_meta" style="margin:1em 0em">
			<div title="Click to toggle" class="handlediv"><br></div>
			<h3 class="hndle"><span><?php echo __( 'Add a redirect', 'wordpress-seo' ); ?></span></h3>
			<div class="inside">
				<form id="redirect" method="post" action="">
					<fieldset><div class="inline-edit-col">
		                <label class="redirect-editfield">
		                    <span class="redirect-label"><?php _e( 'Old Url', 'wordpress-seo' ); ?></span>
		                    <span class="input-text-wrap"><input type="text" name="url" class="ptitle" value="" /></span>
		                </label>

		                <label class="redirect-editfield">
		                    <span class="redirect-label"><?php _e( 'Redirect Url', 'wordpress-seo' ); ?></span>
		                    <span class="input-text-wrap"><input type="text" name="redirect_url" class="ptitle" value="" /></span>
		                </label>

		          	<div class="submit-footer">
			            <button type="button" class="clear button-secondary alignleft first-button" title="Clear"><?php echo __( 'Clear', 'wordpress-seo' ); ?></button>
			            
			            <input type="submit" value="Save" class="save button-primary alignleft">
						<input type="hidden" name="action" value="create">

			            <span class="spinner"></span>
			            <span style="display:none;" class="error"></span>
			            <?php wp_nonce_field('create', '_wpnonce-redirect-create'); ?>
		            </div>
		            
		            </div></fieldset>
		        </form>
			</div>
		</div>
           
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="redirect-filter" method="get">
			<?php $table->search_box('Search', 'search_id'); ?>
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
			<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
			<?php $table->display() ?>
        </form>
        
		<?php
			$table->inline_edit();
		?>

    <!-- </div> -->
<?php