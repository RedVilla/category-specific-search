<?php

/**
 * Search Template Class - 
 *
 * @Author        RedVilla
 * @Copyright:    2019 RedVilla
 */

if (!defined('ABSPATH')) {
	exit;
}  // if direct access



class CSSFW_Search_Template
{

	public $option_search_from;
	public $option_search_results;


	public function __construct()
	{

		$this->option_search_from =  apply_filters('cssfw_search_form', wp_parse_args(cssfw_get_option('cssfw_search_form')));
		$this->option_search_results =  apply_filters('cssfw_search_results', wp_parse_args(cssfw_get_option('cssfw_search_results')));



		add_action('cssfw_search_bar_preview', array($this, 'cssfw_search_style_common'));
		add_shortcode('cssfw_search_bar_preview', array($this, 'cssfw_search_shortcode'));



		add_action('wp_ajax_nopriv_cssfw_get_woo_search_result', array($this, 'cssfw_get_woo_search_result'));
		add_action('wp_ajax_cssfw_get_woo_search_result', array($this, 'cssfw_get_woo_search_result'));

		add_action('pre_get_posts', array($this, 'cssfw_replace_woocomerce_search'), 999);
	}
	/**
	 * replace woocomerce search
	 * 
	 */
	public function apsw_replace_woocomerce_search($query)
	{
		if ($query->is_search()) {
			if (isset($_GET['category']) && !empty($_GET['category'])) {
				$sanitized_category = sanitize_text_field($_GET['category']);

				$excluded_category = array('uncategorized'); // Replace 'blue' with the actual slug of the category you want to exclude

				$query->set('tax_query', array(
					array(
						'taxonomy' => 'product_cat',
						'field' => 'slug',
						'terms' => array_diff(array($sanitized_category), $excluded_category),
					)
				));
			}
		}

		return $query;
	}



	/**
	 * send ajax result
	 * 
	 */
	public function cssfw_get_woo_search_result()
	{
		global $woocommerce;
		$search_keyword =  sanitize_text_field($_POST['keyword']);
		$search_results   = array();


		$args = array(
			's'                   => esc_html($search_keyword),
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => absint($this->option_search_results['number_of_product']),

		);
		// The Query

		if (isset($_POST['category']) && !empty($_REQUEST['category'])) {


			$args['tax_query'][] = array(
				array(
					'taxonomy' => 'product_cat',
					'field' => 'slug',
					'terms' => array(sanitize_text_field($_POST['category']))
				)
			);
		}

		$the_query = new WP_Query($args);

		// The Loop
		if ($the_query->have_posts()) {
			while ($the_query->have_posts()) : $the_query->the_post();

				$product = wc_get_product(get_the_ID());

				$rating_count = $product->get_rating_count();
				$average      = $product->get_average_rating();



				$search_results[] = array(
					'id'    	=> absint($product->id),
					'title' 	=> esc_html($product->get_title()),
					'url'   	=> $product->get_permalink(),

					'img_url' 	=> (apply_filters('cssfw_show_image', $this->option_search_results['show_image']) == 'yes') ? esc_url_raw(get_the_post_thumbnail_url($product->id, 'thumbnail'))  : '',

					'price'		=> (apply_filters('cssfw_show_price', $this->option_search_results['show_price']) == 'yes') ? $product->get_price_html() : '',

					'rating'	=> (apply_filters('cssfw_show_rating', $this->option_search_results['show_rating']) == 'yes') ? wc_get_rating_html($average, $rating_count) : '',

					'category' 	=> (apply_filters('cssfw_show_category', $this->option_search_results['show_category']) == 'yes') ? esc_html(wc_get_product_category_list($product->get_id(), ', ')) : '',

					'stock'		=> (apply_filters('cssfw_show_stock', $this->option_search_results['stock_status']) == 'yes') ? $product->get_stock_status() : '',

					'content'	=> $this->cssfw_ajax_data_content($product->id),

					'featured'	=> ($product->is_on_sale() && $this->option_search_results['show_feature'] == 'yes') ? true : '',

					'on_sale'	=> ($product->is_on_sale() && $this->option_search_results['show_on_sale'] == 'yes') ?  esc_html__('Sale!', 'cssfw-lang') : '',
					'sku'	=> ($this->option_search_results['show_sku'] == 'yes') ?  $product->get_sku() : '',




				);




			endwhile;
		} else {
			if (isset($_POST['category'])  && !empty($_REQUEST['category'])) {
				$search_results[] = array(
					'id'    => 0,
					'title' => esc_html($this->option_search_results['nothing_found_cat']),
					'url'   => '#',
				);
			} else {

				$search_results[] = array(
					'id'    => 0,
					'title' => esc_html($this->option_search_results['nothing_found']),
					'url'   => '#',
				);
			}
		}

		/* Restore original Post Data */
		wp_reset_postdata();

		echo wp_json_encode($search_results);

		die();
	}

	/**
	 * ajax content type
	 * 
	 */
	public function cssfw_ajax_data_content($id)
	{
		$content = '';
		if (apply_filters('cssfw_show_description', $this->option_search_results['show_description'])  == 'yes') {

			if ($this->option_search_results['content_source'] == 'content') {
				$content = mb_strimwidth(esc_html(wp_strip_all_tags(get_the_content(absint($id)))), 0, absint($this->option_search_results['length']));
			} else {
				$scontent = mb_strimwidth(esc_html(wp_strip_all_tags(get_the_excerpt(absint($id)))), 0, absint($this->option_search_results['length']));
			}
		}
		return $content;
	}

	/**
	 * Preview the from via sortcode
	 * 
	 */
	function cssfw_search_shortcode($atts)
	{
		extract(shortcode_atts(array(
			'style' => 1,
		), $atts));
		return $this->cssfw_search_style_common(absint($style));
	}
	/**
	 * search form render
	 * 
	 */
	public function cssfw_search_style_common($style)
	{

		if (isset($style) && !empty($style)) {
			$form_style  = esc_html(apply_filters('cssfw_search_form_style', 'cssfw_search_form_style_' . absint($style)));
		} else {
			$form_style  = esc_html(apply_filters('cssfw_search_form_style', $this->option_search_from['search_form_style']));
		}

		echo '<div class="cssfw-search-wrap ' . esc_attr($form_style) . '">';

		echo wp_kses($this->cssfw_search_from_start(), aspw_alowed_tags());

		switch ($form_style) {

			case "cssfw_search_form_style_6":
				$this->cssfw_search_style_6();
				break;

			case "cssfw_search_form_style_5":
				$this->cssfw_search_style_5();
				break;

			case "cssfw_search_form_style_4":
				$this->cssfw_search_style_4();
				break;

			case "cssfw_search_form_style_3":
				$this->cssfw_search_style_3();
				break;
			case "cssfw_search_form_style_2":
				$this->cssfw_search_style_1();
				break;
			default:
				$this->cssfw_search_style_1();
		}
		if (!empty($this->cssfw_ajax_data())) {
			echo wp_kses($this->cssfw_ajax_data(), aspw_alowed_tags());
		}

		echo wp_kses($this->cssfw_search_from_end(), aspw_alowed_tags());
		echo '</div>';
	}
	/**
	 * search style 6
	 * 
	 */
	public function cssfw_search_style_6()
	{
		echo wp_kses($this->cssfw_search_element(esc_attr($this->option_search_from['search_btn'])), aspw_alowed_tags());
	}
	/**
	 * search style 5 
	 * 
	 */
	public function cssfw_search_style_5()
	{
		echo wp_kses($this->cssfw_search_element(esc_attr($this->option_search_from['search_btn'])), aspw_alowed_tags());
		echo wp_kses($this->cssfw_search_element_category(), aspw_alowed_tags());
	}
	/**
	 * search style4 
	 * 
	 */
	public function cssfw_search_style_4()
	{
		echo wp_kses($this->cssfw_search_element(esc_attr($this->option_search_from['search_btn'])), aspw_alowed_tags());
		echo wp_kses($this->cssfw_search_element_category(), aspw_alowed_tags());
	}
	/**
	 * search style 3 
	 * 
	 */
	public function cssfw_search_style_3()
	{
		echo wp_kses($this->cssfw_search_element($this->cssfw_svg_icon_btn()), aspw_alowed_tags());
	}
	/**
	 * search style 1 
	 * 
	 */
	public function cssfw_search_style_1()
	{

		echo wp_kses($this->cssfw_search_element($this->cssfw_svg_icon_btn()), aspw_alowed_tags());
		echo wp_kses($this->cssfw_search_element_category(), aspw_alowed_tags());
	}
	/**
	 * create svg icon 
	 * 
	 */
	public function cssfw_svg_icon_btn()
	{
		return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path d="M11 4C7.13 4 4 7.13 4 11C4 14.87 7.13 18 11 18C12.66 18 14.14 17.41 15.3 16.46L20.59 21.75L21.75 20.59L16.46 15.3C17.41 14.14 18 12.66 18 11C18 7.13 14.87 4 11 4ZM11 16C8.24 16 6 13.76 6 11C6 8.24 8.24 6 11 6C13.76 6 16 8.24 16 11C16 13.76 13.76 16 11 16Z" fill="black"/>
		</svg>';
	}

	public function cssfw_ajax_data()
	{

?>
		<div class="cssfw_ajax_result">

		</div>
<?php
	}
	/**
	 * Search from start
	 * 
	 */
	public function cssfw_search_from_start()
	{


		$html = '<form role="search" class="cssfw-search-form ' . esc_attr($this->option_search_from['search_action']) . '" autocomplete="off" action="' . esc_url(get_permalink(wc_get_page_id('shop'))) . '" method="get">';

		return apply_filters('cssfw_search_from_start', $html);
	}

	/**
	 * Search from end
	 * 
	 */
	public function cssfw_search_from_end()
	{

		$html = '</form>';

		return apply_filters('cssfw_search_from_end', $html);
	}

	/**
	 * Render Product Search element
	 * 
	 * @param $post_type = string .
	 *
	 * @return html input type = search ,submit button ,post_type = product
	 */
	public function cssfw_search_element($button = '')
	{

		$html = '<input type="search" name="s" class="cssfw-search-input" value="' . esc_attr(get_search_query()) . '" placeholder="' . esc_attr($this->option_search_from['search_value']) . '" data-charaters="' . esc_attr($this->option_search_from['action_charaters']) . '" data-functiontype="' . esc_attr($this->option_search_from['search_action']) . '" />';

		$html .= '<button class="cssfw-search-btn" type="submit">' . $button . '</button>';

		$html .= '<input type="hidden" name="post_type" value="product" />';

		if (apply_filters('cssfw_show_loader', $this->option_search_from['show_loader']) == 'yes') {

			$html .= '<img class="cssfw_loader" src="' . esc_url_raw(CSSFW_PLUGIN_URL) . 'assets/images/loader.gif"/>';
		}

		return apply_filters('cssfw_cssfw_search_element', $html);
	}
	/**
	 * Render woocommerce category select box view
	 * 
	 * @param array .
	 *
	 * @return woocommerce category selectbox html
	 */
	public function cssfw_search_element_category()
	{

		$cat_args = array(
			'taxonomy' => 'product_cat',
			'orderby' => 'name',
			'show_count' => '0',
			'pad_counts' => '0',
			'hierarchical' => '1',
			'title_li' => '',
			'hide_empty' => '0',
			'parent' => '0',  // Only get parent categories
		);


		$all_categories = apply_filters('cssfw_get_categories_list', get_categories($cat_args));

		$current_cat = (isset($_GET['category']) && $_GET['category'] != "") ? sanitize_text_field($_GET['category']) : '';

		$html = '<div class="cssfw-select-box-wrap"><select class="cssfw-category-items" name="category">
			
			<option value="0">' . esc_html__('All Categories', 'cssfw-lang') . '</option>';

		$excluded_category_slug = 'uncategorized'; // Replace 'uncategorized' with the actual slug of your category



		if (is_array($all_categories) && count($all_categories) > 0) :

			foreach ($all_categories as $category) :
				if ($category->slug != $excluded_category_slug) {
					$selected  = ($category->slug == $current_cat) ? 'selected="selected"' : '';

					$html .= '<option  value="' . esc_attr($category->slug) . '" data-value="' . esc_attr($category->slug) . '" ' . esc_attr($selected) . '>' . esc_html($category->cat_name) . '</option>';
				}
			endforeach;
		endif;


		$html .= '</select></div>';

		return apply_filters('cssfw_woo_categories_select_box', $html);
	}
}

new CSSFW_Search_Template();
