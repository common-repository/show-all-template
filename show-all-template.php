<?php
/**
* Plugin Name: Show All Template
* Plugin URI:  https://galaxyweblinks.com
* Description: Display the page template name which is assigned to the page type on the page listing, also add an admin menu for showing all page template listings that we have added.
* Version: 1.0
* Author: Galaxy Weblinks
* Author URI: https://www.galaxyweblinks.com/
* License:     GPLv2 or later
* License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
* Text Domain: show-all-template
* Requires at least: 5.9
* Requires PHP: 7.4
*/

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {	die(); }

// Define the plugin directory path & url
define( 'SAPT_VERSION', '1.0' );
define( 'SAPT_URL', plugin_dir_url(__FILE__));
define( 'SAPT_Path', plugin_dir_path(__FILE__));
define( 'SAPT_TEXT_DOMAIN', 'show-all-template');
/**
 * Main class of the plugin
 *
 * @package SAPT_Main_Template
 * @author Galaxy Weblinks
 * @since 1.0
 */
class SAPT_Main_Template {

	/**
	 * Initialize the class sets its properties.
     * 
	 * @since 1.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( &$this, 'sapt_add_admin_menu_callback') );
		add_filter( 'plugin_action_links', array( $this, 'sapt_plugin_settings_link' ), 10, 2 );
		add_filter( 'manage_pages_columns', array( &$this, 'sapt_add_template_column') );
		add_action( 'manage_pages_custom_column', array( &$this, 'sapt_show_template_name'), 10, 2);
		add_filter( 'manage_edit-page_sortable_columns', array( &$this, 'sapt_sortable_columns') ); 
		add_filter( 'request', array( &$this, 'sapt_custom_column_sort'));
		add_action( 'pre_get_posts', array( &$this,'sapt_custom_page_filter'));
	}

	/**
	 * Adds a custom column to the page list table to show the page template name.
	 */
	public function sapt_add_admin_menu_callback(){
		add_options_page('Page Templates', 'Page Templates', 'manage_options', 'sapt-page', array( &$this, 'sapt_show_all_templates' ));
	}
	
	/**
	 * Adds a custom column to the page list table to show the page template name.
	 */
	public function sapt_show_all_templates() {
		$templates = wp_get_theme()->get_page_templates();	
		require_once ( SAPT_Path . 'includes/page-template-html.php');
	}

	/**
    * Plugin Template Listing link
    */
    public function sapt_plugin_settings_link( $actions, $plugin_file ){
        static $plugin;

        if ( !isset($plugin) ) {
            $plugin = plugin_basename(__FILE__);
        }

        if ( $plugin == $plugin_file ) {
            $settings = 
				array(
					'settings' => '<a href="' . esc_url(admin_url('/options-general.php?page=sapt-page')) . '">Template Listing</a>');
            $actions = array_merge($settings, $actions);
        }
        return $actions;
    }
	
	/**
	 * Adds a custom column to the page list table to show the page template name.
	 */
	public function sapt_add_template_column($columns) {
		$columns['page_template'] = 'Template';
		return $columns;
	}

	/**
	 * Fills the custom column in the page list table with the name of the page template.
	 */
	public function sapt_show_template_name($column_name, $post_id) {
		if ('page_template' === $column_name) {
			
			$templates = wp_get_theme()->get_page_templates();	
			$current_template = get_page_template_slug($post_id);

			$tmp_arr = [];
			foreach($templates as $temp_name => $temp_arr){
				$template_name_data = explode('/', $temp_name);
                $temp_filename = end($template_name_data);
				$tmp_arr[] = $temp_filename;
			}

			if( in_array($current_template, $tmp_arr) ){
				$template_name = !empty($current_template) ? $current_template : 'Default';
			}else{
				$template_name = 'Default';
			}
			echo esc_html($template_name);
		}
	}

	/**
	 * Added feature to sort custom column (page template) in list table.
	 */
	public function sapt_sortable_columns($columns) {
		$columns['page_template'] = 'page_template';
		return $columns;
	}

	/**
	 * Added feature to sort custom column (page template) in list table.
	 */
	public function sapt_custom_column_sort($request) {
		if ( isset( $request['orderby'] ) && $request['orderby'] === 'page_template' ) {
			$request['orderby'] = 'page_template';
		}
		return $request;
	}

	/**
	 * Added feature to filter by custom column (page template) in list table.
	 */
	public function sapt_custom_page_filter( $query ) {
		if ( !is_admin() || ! $query->is_main_query() || $query->is_singular() ) {
		  return;
		}

		// Get nonce value
		$nonce = (isset($_GET['nonce']) && !empty($_GET['nonce'])) ? sanitize_text_field(wp_unslash( $_GET['nonce'] )) : '';
		
		if ( isset( $_GET['page_template'] ) && !empty( $_GET['page_template'] ) && !empty($nonce) &&  wp_verify_nonce( $nonce, 'total_count_url' ) ) {
			$template_to_filter = sanitize_text_field(wp_unslash($_GET['page_template']));
			$query->set( 'post_status', array( 'publish' ) );
			$query->set( 'meta_key', '_wp_page_template' );
			$query->set( 'meta_value', $template_to_filter );
		}
	}

	/**
	 * Page Templates Get the total number of templates added to the page.
	 */
	public function sapt_get_page_by_template() {
		$page_arr = [];
		
		// Retrieve the pages that match the criteria
		$pages = get_posts(array(
			'post_type' => 'page',
			'post_status' => 'publish',
			'posts_per_page' => -1
		));
		
		if(count($pages) > 0){
			foreach($pages as $pageObj){
				$page_template = get_post_meta( $pageObj->ID, '_wp_page_template', true );
				$page_arr[$page_template][] = $pageObj->post_title;
			}
		}

		return $page_arr;
	}
}

//Initialize the class
$pluginObj = new SAPT_Main_Template();
