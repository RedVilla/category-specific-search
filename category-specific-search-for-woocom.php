<?php
/*
Plugin Name: Category Specific Search For WooCom
Plugin URI: https://redvilla.tech/download/apps/plugins/category-specific-search-for-woocom/
Description: Category Specific Search For WooCom  – A search plugin for WooCommerce
Version: 1.0.0
Author: RedVilla
Author URI:  https://redvilla.tech
Domain Path: /languages/
Tested up to: 6.5.3
WC requires at least: 3.0.0
WC tested up to: 8.9.1
*/

if (!defined('ABSPATH')) exit;  // if direct access

class CSSFW_Product_Search_Finale_Class
{

	/**
	 * The option_search_results
	 *
	 */
	public $option_search_from;
	/**
	 * The option_search_results
	 *
	 */
	public $option_search_results;

	/**
	 * The option_search_results
	 *
	 */
	public $option_color;
	/**
	 * The single instance of the class
	 *
	 * @var Advanced_Product_Search_For_Woo
	 */
	private static $_instance;
	/**
	 * Class constructor.
	 */
	function __construct()
	{

		$this->cssfw_load_defines();
		$this->cssfw_load_scripts();
		$this->cssfw_load_textdomain();
		$this->cssfw_load_functions();
		$this->cssfw_load_classes();
		$this->option_search_from 		= wp_parse_args(cssfw_get_option('cssfw_search_form'));
		$this->option_search_results 	= wp_parse_args(cssfw_get_option('cssfw_search_results'));
		$this->option_color 			= wp_parse_args(cssfw_get_option('cssfw_color_scheme'));

		add_filter('plugin_action_links', array($this, 'go_pro'), 999, 2);
		add_action('admin_notices', array($this, 'sample_admin_notice__success'));


		add_action('wp_ajax_nopriv_cssfw_cssfw_dismiss_notice', array($this, 'dismiss_nux'));
		add_action('wp_ajax_cssfw_dismiss_notice', array($this, 'dismiss_nux'));

		//add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'settings_action_links' )  );
	}
	/**
	 * Main instance
	 *
	 * @return Advanced_Product_Search_For_Woo
	 */
	public static function getInstance()
	{
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}
	/**
	 *
	 * @return plugins related function 
	 */
	public function cssfw_load_functions()
	{

		// require CSSFW_PLUGIN_DIR . 'includes/search-template.php';

		$this->cssfw_load_module('includes/aspw-helper-functions');
	}
	/**
	 *
	 * @return plugins related function 
	 */
	public function cssfw_load_classes()
	{


		if (!class_exists('Predic_Widget')) {
			$this->cssfw_load_module('lib/predic-widget/predic-widget');
		}

		$this->cssfw_load_module('lib/class.settings-api');

		$this->cssfw_load_module('includes/classes/classes-settings-api');

		$this->cssfw_load_module('includes/classes/classes-search');
		$this->cssfw_load_module('includes/classes/widgets');
	}

	protected static function cssfw_load_module($mod)
	{
		$dir = CSSFW_PLUGIN_DIR;

		if (empty($dir) or !is_dir($dir)) {
			return false;
		}

		$file = path_join($dir, $mod . '.php');

		if (file_exists($file)) {
			require_once $file;
		}
	}



	public function cssfw_admin_scripts()
	{

		wp_enqueue_style('cssfw-style',  plugins_url('assets/admin/css/style.css', __FILE__), array(), time());


		wp_enqueue_script('jquery');

		wp_enqueue_script('cssfw-plugins-scripts', plugins_url('assets/admin/js/admin-scripts.js', __FILE__), array('jquery'));

		$cssfw_notify = array(
			'nonce' => wp_create_nonce('cssfw_notice_dismiss_nonce'),
			'ajaxurl' => admin_url('admin-ajax.php'),
		);

		wp_localize_script('cssfw-plugins-scripts', 'cssfw_loc', $cssfw_notify);
	}

	public function cssfw_front_scripts()
	{

		wp_enqueue_style('cssfw-styles', plugins_url('assets/front/css/style.css', __FILE__), array(), time());



		$custom_css = "  .cssfw-search-wrap {max-width:" . absint($this->option_search_from['search_bar_width']) . "px;}
		.cssfw-search-wrap .cssfw-search-form input[type='search'],.cssfw-search-wrap.cssfw_search_form_style_4 button.cssfw-search-btn,.cssfw-search-wrap.cssfw_search_form_style_5 button.cssfw-search-btn,.cssfw-search-wrap.cssfw_search_form_style_6 button.cssfw-search-btn,.cssfw-search-wrap .cssfw-search-btn{ height:" . absint($this->option_search_from['search_bar_height']) . "px; line-height: " . absint($this->option_search_from['search_bar_height']) . "px }
		.cssfw-search-wrap .cssfw-select-box-wrap{height:" . absint($this->option_search_from['search_bar_height']) . "px;}
		.cssfw-search-wrap .cssfw-category-items{ line-height: " . absint($this->option_search_from['search_bar_height']) . "px; }
		.cssfw_ajax_result{ top:" . absint($this->option_search_from['search_bar_height'] + 1) . "px; }
		";

		$custom_css .= ".cssfw-search-wrap .cssfw-search-form{ background:" . esc_attr($this->option_color['search_bar_bg']) . "; border-color:" . esc_attr($this->option_color['search_bar_border']) . "; }";

		$custom_css .= ".cssfw-search-wrap .cssfw-category-items,.cssfw-search-wrap .cssfw-search-form input[type='search']{color:" . esc_attr($this->option_color['search_bar_text']) . "; }";

		$custom_css .= ".cssfw-search-wrap.cssfw_search_form_style_4 button.cssfw-search-btn, .cssfw-search-wrap.cssfw_search_form_style_5 button.cssfw-search-btn, .cssfw-search-wrap.cssfw_search_form_style_6 button.cssfw-search-btn{ color:" . esc_attr($this->option_color['search_btn_text']) . "; background:" . esc_attr($this->option_color['search_btn_bg']) . "; }";

		$custom_css .= ".cssfw-search-wrap .cssfw-search-btn svg{ fill:" . esc_attr($this->option_color['search_btn_bg']) . "; }";

		$custom_css .= ".cssfw-search-wrap.cssfw_search_form_style_4 button.cssfw-search-btn::before, .cssfw-search-wrap.cssfw_search_form_style_5 button.cssfw-search-btn::before, .cssfw-search-wrap.cssfw_search_form_style_6 button.cssfw-search-btn::before { border-color: transparent " . esc_attr($this->option_color['search_btn_bg']) . "  transparent;; }";

		$custom_css .= ".cssfw_ajax_result .cssfw_result_wrap{ background:" . esc_attr($this->option_color['results_con_bg']) . "; border-color:" . esc_attr($this->option_color['results_con_bor']) . "; } ";

		$custom_css .= "ul.cssfw_data_container li:hover{ background:" . esc_attr($this->option_color['results_row_hover']) . "; border-color:" . esc_attr($this->option_color['results_con_bor']) . "; } ";
		$custom_css .= "ul.cssfw_data_container li .cssfw-name{ color:" . esc_attr($this->option_color['results_heading_color']) . ";} ";
		$custom_css .= "ul.cssfw_data_container li .cssfw-price{ color:" . esc_attr($this->option_color['price_color']) . ";} ";

		$custom_css .= "ul.cssfw_data_container li .cssfw_result_excerpt{ color:" . esc_attr($this->option_color['results_text_color']) . ";} ";
		$custom_css .= "ul.cssfw_data_container li .cssfw_result_category{ color:" . esc_attr($this->option_color['category_color']) . ";} ";
		$custom_css .= "ul.cssfw_data_container li.cssfw_featured{ background:" . esc_attr($this->option_color['featured_product_bg']) . ";} ";
		$custom_css .= "ul.cssfw_data_container li .cssfw_result_on_sale{ background:" . esc_attr($this->option_color['on_sale_bg']) . ";} ";
		$custom_css .= "ul.cssfw_data_container li .cssfw_result_stock{ color:" . esc_attr($this->option_color['results_stock_color']) . ";} ";

		//$this->option_color	
		wp_add_inline_style('cssfw-styles', $custom_css);

		wp_enqueue_script('jquery');
		wp_enqueue_script('cssfw-plugins-scripts', plugins_url('assets/front/js/scripts.js', __FILE__), array('jquery'));
		wp_localize_script('cssfw-plugins-scripts', 'cssfw_localize', $this->cssfw_get_localize_script());
	}

	function cssfw_load_scripts()
	{


		add_action('admin_enqueue_scripts', array($this, 'cssfw_admin_scripts'));
		add_action('wp_enqueue_scripts', array($this, 'cssfw_front_scripts'));
	}

	function cssfw_load_defines()
	{

		$this->define('CSSFW_PLUGIN_URL', WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)) . '/');
		$this->define('CSSFW_PLUGIN_DIR', plugin_dir_path(__FILE__));
		$this->define('CSSFW_PLUGIN_FILE', __FILE__);
		$this->define('CSSFW_PLUGIN_VERSION', '1.0.0');
		$this->define('CSSFW_PLUGIN_FILE', plugin_basename(__FILE__));
		$this->define('CSSFW', 'category-specific-search-for-woocom');
	}

	private function define($name, $value)
	{
		if (!defined($name)) define($name, $value);
	}

	private function cssfw_get_localize_script()
	{

		return apply_filters('cssfw_localize_filters_', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'view_text'	=> esc_html($this->option_search_results['view_all_text']),
			'text' => array(
				'working' => esc_html__('Working...', 'cssfw-lang'),
			),
		));
	}

	public function cssfw_load_textdomain($locale = null)
	{
		global $l10n;

		$domain = 'cssfw-lang';

		if ((is_admin() ? get_user_locale() : get_locale()) === $locale) {
			$locale = null;
		}

		if (empty($locale)) {
			if (is_textdomain_loaded($domain)) {
				return true;
			} else {
				return load_plugin_textdomain($domain, false, $domain . '/languages');
			}
		} else {
			$mo_orig = $l10n[$domain];
			uncssfw_load_textdomain($domain);

			$mofile = $domain . '-' . $locale . '.mo';
			$path = WP_PLUGIN_DIR . '/' . $domain . '/languages';

			if ($loaded = cssfw_load_textdomain($domain, $path . '/' . $mofile)) {
				return $loaded;
			} else {
				$mofile = WP_LANG_DIR . '/plugins/' . $mofile;
				return cssfw_load_textdomain($domain, $mofile);
			}

			$l10n[$domain] = $mo_orig;
		}

		return false;
	}
	public function go_pro($actions, $file)
	{
		if ($file == plugin_basename(__FILE__)) {

			$actions['cssfw_go_pro'] = '<a href="https://redvilla.tech/download/apps/plugins/category-specific-search-for-woocom/" target="_blank" style="color: green; font-weight: bold">Review Now</a>';
			$action = $actions['cssfw_go_pro'];
			unset($actions['cssfw_go_pro']);
			array_unshift($actions, $action);

			$actions['cssfw_go_settings'] = '<a href="' . esc_url(admin_url('admin.php?page=category-specific-search-for-woocom')) . '">' . __('Settings', 'cssfw-lang') . '</a>';
			$action = $actions['cssfw_go_settings'];
			unset($actions['cssfw_go_settings']);
			array_unshift($actions, $action);
		}
		return $actions;
	}

	public function sample_admin_notice__success()
	{
		if (get_option('cssfw_notice_dismiss') != "") {
			return false;
		}
?>
		<div class="notice notice-success cssfw-notice-nux is-dismissible">

			<p><?php _e('Thank you for installing Category Specific Search For WooCom', 'cssfw-lang'); ?>
				<a href="<?php echo esc_url(admin_url('admin.php?page=category-specific-search-for-woocom')); ?>"><?php _e('Get started ', 'cssfw-lang'); ?></a>
			</p>
		</div>
<?php

	}


	public function dismiss_nux()
	{
		$nonce = !empty($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : false;

		if (!$nonce || !wp_verify_nonce($nonce, 'cssfw_notice_dismiss_nonce') || !current_user_can('manage_options')) {
			die();
		}
		update_option('cssfw_notice_dismiss', true);
		die();
	}
}

global $cssfw_product_search_final_class;
if (!$cssfw_product_search_final_class) {
	$cssfw_product_search_final_class = CSSFW_Product_Search_Finale_Class::getInstance();
}
