<?php
/**
 * @package Admin
 */

if ( !defined( 'WPSEO_VERSION' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	die;
}

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

// TODO: search and filter
// TODO: bulk action
// TODO: support for cpt

/**
 * class WPSEO_Redirect_Table
 *
 * Class for creating a table to mangage redirect information defined by WPSEO
 */

class WPSEO_Redirect_Table extends WP_List_Table {
	// variable to track special redirect info
	private $wpseo_404_redirect = null;
	private $wpseo_unknown_slug_redirect = null;
		
    function __construct() {
        global $status, $page;

        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'redirect',     //singular name of the listed records
            'plural'    => 'redirects',    //plural name of the listed records
            'ajax'      => false           //does this table support ajax?
        ) );
		
		$this->init();
    }
    
	
	function init() {
		// grab and store the 404 and unknown slug redirect here
		$wpseo = get_option('wpseo');

		if( isset( $wpseo['404_redirect'] ) ) {
			$record = array();

			$record['id']  	            = '404_redirect';
			$record['old_url']          = '404 redirect';
			$record['relative_old_url'] = '404 redirect';
			$record['new_url']          = $wpseo['404_redirect'];
			
			$this->wpseo_404_redirect = $record;
		}
		
		if( isset( $wpseo['unknown_slug_redirect'] ) ) {
			$record = array();

			$record['id']  	            = 'unknown_slug_redirect';
			$record['old_url']          = 'unknown slug';
			$record['relative_old_url'] = 'unknown slug';
			$record['new_url']          = $wpseo['unknown_slug_redirect'];
			
			$this->wpseo_unknown_slug_redirect = $record;
		}
	}
	
    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
		switch($column_name){
            default:
				return $item[$column_name];
        }
    }
    
	
	function column_id($item) {
        //Build row actions
        $actions = array(
            'quick edit'    => sprintf('<a class="editinline" href="#">Quick Edit</a>','edit', $item['id']),
            'delete'        => sprintf('<a href="?page=%s&action=%s&id=%s">Delete</a>',
                                        'wpseo_redirect',                                        
                                        'delete',
                                        $item['id'])
        );

        // Special label for 404 and unknown slug
		if ($item['id'] == '404_redirect') {
            unset( $actions['delete'] );

            return sprintf('%s %s',
                '404 Redirect',                
                $this->row_actions($actions)
            );
        }
			
		if ($item['id'] == 'unknown_slug_redirect')
			return 'Uknown Slug Redirect';
		
        return sprintf('%s %s',
            /*$1%s*/ $item['id'],
            // /*$2%s*/ $item['detail'],
            $this->row_actions($actions)
        );
	
	}
	
    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named 
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     * 
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     * 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_title($item){        
        // Build row actions
        $actions = array(
            // 'quick edit'      => sprintf('<a href="?page=%s&action=%s&movie=%s">Edit</a>',$_REQUEST['page'],'edit',$item['ID']),
            // 'delete'    => sprintf('<a href="?page=%s&action=%s&movie=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID']),
        );
        
        return sprintf('%1$s',
            /*$1%s*/ $item['title']
            // /*$2%s*/ $item['detail'],
            // $3%s $this->row_actions($actions)
        );
    }
    
	function column_old_url($item) {
		// Special label for 404 and unknown slug
		if ($item['id'] == '404_redirect')
			return '404 Redirect';
			
		if ($item['id'] == 'unknown_slug_redirect')
			return 'Uknown Slug Redirect';
	
		return $this->hyperlink_template( $item['old_url'], $item['relative_old_url'] );
	
	}
	
	function column_new_url($item) {        
		return $this->hyperlink_template( $item['new_url'] );
        
    }
	
	
    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ 'id',
            /*$2%s*/ $item['id']                // The value of the checkbox should be the record's id
        );
    }
    
    
    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
		$columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
			'id'		=> 'Post ID',
			'old_url'		=> 'Old Url',
			'new_url'		=> 'New Url',
        );
        return $columns;
    }
    
    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'id'         => array('id',false),
			'old_url'    => array('old_url',false),
			'new_url'    => array('new_url',false),
        );
        return $sortable_columns;
    }
    
    
    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }
    
    
    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {
        
        // Detect when a bulk action is being triggered...
        if ( 'delete' === $this->current_action() ) {
            // TODO: nonce check
            // check_admin_referer( 'delete', '_wpnonce-redirect-delete' ); 

            $ids = $_REQUEST['id'];
            foreach( (array) $ids as $post_id ) {
                delete_post_meta( $post_id, '_yoast_wpseo_redirect' );
            }
            // wp_die('Items deleted (or they would be if we had items to delete)!');
        } else if ( 'create' === $this->current_action() ) {
            // nonce check
            check_admin_referer( 'create', '_wpnonce-redirect-create' );

            $url          = $_REQUEST['url'];
            $redirect_url = $_REQUEST['redirect_url'];            

            // find post via slug / permalink
            $post_id = url_to_postid( $url );
            $post = get_post( $post_id, OBJECT );

            // TODO: currently does not handle CPTs 
            // http://core.trac.wordpress.org/ticket/19744
            // possible workaround: http://betterwp.net/wordpress-tips/url_to_postid-for-custom-post-types/#comments

            if ( $post && $redirect_url )
                wpseo_set_value( 'redirect', $redirect_url, $post->ID );
        }
    }
    
    
    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items( $mydata = null ) {
        global $wpdb; //This is used only if making any database queries

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 20;
        
        
        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        
        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();
        
        
        /**
         * Instead of querying a database, we're going to fetch the example data
         * property we created for use in this plugin. This makes this example 
         * package slightly different than one you might build on your own. In 
         * this example, we'll be using array manipulation to sort and paginate 
         * our data. In a real-world implementation, you will probably want to 
         * use sort and pagination data to build a custom query instead, as you'll
         * be able to use your precisely-queried data immediately.
         */
		
        // if data is not passed in, then query the default list
        $mydata = false;
        if ( $mydata )
            $data = $mydata;
        else
            $data = $this->get_redirect_records();
        // var_dump($data);
        
        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         * 
         * In a real-world situation involving a database, you would probably want 
         * to handle sorting by passing the 'orderby' and 'order' values directly 
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id'; //If no sort, default to id
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }

        // only do this sort if a sort if orderby is given
        if ( !empty($_REQUEST['orderby']) )
            usort($data, 'usort_reorder');
        
        
        /***********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         * 
         * In a real-world situation, this is where you would place your query.
         * 
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         **********************************************************************/

        
        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently 
         * looking at. We'll need this later, so you should always include it in 
         * your own package classes.
         */

	   $current_page = $this->get_pagenum();
        
		// Always inject 404 and unknown slug redirect at the front of the array after sorting 
		// but before paging to keep the display consistent
		if ( $this->wpseo_unknown_slug_redirect ) {
			array_unshift($data, $this->wpseo_unknown_slug_redirect);
		}
		
		if ( $this->wpseo_404_redirect ) {
			array_unshift($data, $this->wpseo_404_redirect);
		}
		
        /**
         * REQUIRED for pagination. Let's check how many items are in our data array. 
         * In real-world use, this would be the total number of items in your database, 
         * without filtering. We'll need this later, so you should always include it 
         * in your own package classes.
         */
        $total_items = count($data);
        
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to 
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        
        
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $data;
        
        
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
	
	
    /**
     * Generates content for a single row of the table
     *
     * @since 3.1.0
     * @access protected
     *
     * @param object $item The current item
     */
    function single_row( $item ) {
        static $row_class = '';

        $row_class = ( $row_class == '' ? ' class="alternate"' : '' );

        echo '<tr id="post-' . $item['id'] . '" ' . $row_class . '>';
        // echo '<tr' . 'id="post-$item['id']" valign="top">';
        // echo '<tr 'id="post-$item['id']" ' . $row_class . '>';
        echo $this->single_row_columns( $item );
        echo '</tr>';

    }


	function extra_tablenav( $a ) 
	{
		// echo '<label class="textinput">Filter:</label> <input class="textinput" type="text"/>';
	}
	
	private function hyperlink_template( $href, $display = null) {
		if (!$display)
			$display = $href;
		
		return sprintf( '<a href="%s">%s</a>', $href, $display );
	}


    /**
     * Outputs the hidden row displayed when inline editing
     *
     * @since 3.1.0
     */
    function inline_edit() {
        
?>
    <form method="get" action=""><table style="display: none"><tbody id="inlineedit">
        <tr id="inline-edit" class="inline-edit-row"><td colspan="<?php echo $this->get_column_count(); ?>" class="colspanchange">
            <fieldset id="inline-fieldset"><div class="inline-edit-col">
                <h4><?php _e( 'Quick Edit' ); ?></h4>
                    
                <label class="redirect-editfield">
                    <span class="redirect-label"><?php _e( 'Old Url', 'wordpress-seo' ); ?></span>
                    <span class="input-text-wrap"><input type="text" name="old_url" value="" /></span>
                </label>

                <label class="redirect-editfield">
                    <span class="redirect-label"><?php _e( 'Redirect Url', 'wordpress-seo' ); ?></span>
                    <span class="input-text-wrap"><input type="text" name="new_url" value="" /></span>
                </label>

                <div class="submit-footer">
                    <button type="button" class="cancel button-secondary alignleft first-button" title="Cancel"><?php echo __( 'Cancel', 'wordpress-seo' ); ?></button>
                    <button type="button" class="save button-primary alignleft" title="Update"><?php echo __( 'Update', 'wordpress-seo' ); ?></button>

                    <span class="spinner"></span>
                    <span style="display:none;" class="error"></span>
                    <?php wp_nonce_field( 'save', '_wpnonce-redirect-save' ); ?>
                </div>
            </div></fieldset>

    <?php
        $core_columns = array( 'cb' => true, 'description' => true, 'name' => true, 'slug' => true, 'posts' => true );

        list( $columns ) = $this->get_column_info();

        foreach ( $columns as $column_name => $column_display_name ) {
            if ( isset( $core_columns[$column_name] ) )
                continue;
        }
    ?>

        </td></tr>
        </tbody></table></form>
    <?php
    }    
    
    /**
     * Get the list of redirect items to be displayed
     *
     * @since 3.1.0
     */
    function get_redirect_records() {
        $s = !empty( $_REQUEST['s'] ) ? $_REQUEST['s'] : '';
        $direct_match = url_to_postid( $s );

        $args = array(
            'post__not_in' => (array) $direct_match,
            'search_redirect_term' => $s,
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_yoast_wpseo_redirect', 
                    'value' => false, 
                    'compare' => '!=',
                ),
            )                
        );

        add_filter( 'posts_where', array( $this, 'search_redirect_query' ), 10, 2 );
        $query = new WP_Query($args);
  
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

        // put the direct match in front of the array
        if ( $post = get_post( $direct_match ) ) {
            $record = array();

            $wpurl            = get_bloginfo( 'url' );
            $permalink        = get_permalink( $post );
            $relative_old_url = str_replace( $wpurl, '', $permalink );
            
            $record['id']               = $post->ID;
            $record['old_url']          = $permalink;
            $record['relative_old_url'] = $relative_old_url;
            $record['new_url']          = wpseo_get_value( 'redirect', $post->ID );

            array_unshift( $records, $record );
        }

        remove_filter( 'posts_where', array( $this, 'search_redirect_query' ), 10, 2 );
        return $records;
    }

    /**
     * Custom search query for redirect table
     *
     * @since 3.1.0
     */
    function search_redirect_query( $where, &$wp_query ) {
        global $wpdb;
        if ( $search_term = $wp_query->get( 'search_redirect_term' ) ) {
            $search_term = strtolower( $search_term );
            $search_term = preg_replace( "/[^a-z0-9\s-]/", "", $search_term );
            $search_term = trim( preg_replace( "/[\s-]+/", " ", $search_term ) );

            $terms = explode( ' ', $search_term );

            $pre = 'AND (';
            $clause = $pre;
            foreach( $terms as $index => $term ) {
                if ($index != 0) {
                    $clause .= " OR "; 
                }

                // search for term in meta
                $clause .= "( ( $wpdb->postmeta.meta_key = '_yoast_wpseo_redirect' AND CAST($wpdb->postmeta.meta_value AS CHAR) LIKE '%" . esc_sql( like_escape( $term ) ) . "%' ) ";

                // search for term in title
                $clause .= ' OR ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( like_escape( $term ) ) . '%\' )';
            }
            $sub = ') ';
            $clause .= $sub;

            $where .= $clause;
        }
        return $where;
    }

}
