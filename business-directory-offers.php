<?php
/*
Plugin Name: Business Directory Offers
Author URI: https://tinyscreenlabs.com
Description: Manage Special Offers in Business Directory.
Version: 1.0
Author: Tiny Screen Labs
License: GPLv2+ or later
Text Domain: business-directory-offers
*/

include_once 'includes/WPBDPOffers_Admin_Offers_Fields_Metabox.php';
include_once 'includes/WPBDPOffers_shortcode_deals_display.php';
include_once 'includes/tsl-install-manager.php';
include_once 'includes/class-tgm-plugin-activation.php';

add_action( 'plugins_loaded', array( 'business_directory_plugin_offers', 'init' )  );


class business_directory_plugin_offers {

    private $js_version = '1.0.4';
    private $is_bd_installed = false;

    public static function init() {
        $class = __CLASS__;
        new $class;
    }

    function __construct() {

        if ( !function_exists('get_plugins') ){
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
        }

        $this->set_is_bd_installed();

        if($this->is_bd_installed){
            add_action( 'admin_init', array( $this, 'register_fields' ) );
            add_action( 'admin_init', array( $this, 'add_metaboxes' )  );
            add_action( 'admin_menu', array( $this, 'add_submenu' ) , 99 );
        }else{
            add_action( 'admin_menu', array($this,'admin_menu' ));
        }

        add_action( 'wp_enqueue_scripts', array( $this , 'frontend_enqueue_scripts' ));
        add_action( 'admin_enqueue_scripts', array( $this , 'enqueue_scripts' ));
        add_action( 'wp_ajax_tsl_business_directory_plugin_offers_upload_voucher', array( $this , 'tsl_business_directory_plugin_offers_upload_voucher' ));

        $plugin = plugin_basename(__FILE__);
        add_filter("plugin_action_links_$plugin", array($this, 'plugins_settings_link' ));

    }

    public function admin_menu() {
		add_options_page(
			'Business Directory Offers',
			'BD Offers',
			'manage_options',
			'business-directory-plugin-offers',
			array(
				$this,
				'settings_page'
			)
		);
	}

	function  settings_page() {
		$this->display_offers_settings();
	}

    function plugins_settings_link($links) {
        if($this->is_bd_installed){
            $mylinks = array( '<a href="' . admin_url( 'admin.php?page=WPBD_Offers' ) . '">Settings</a>', );
        }else {
            $mylinks = array('<a href="' . admin_url('options-general.php?page=business-directory-plugin-offers') . '">Settings</a>',);
        }
        return array_merge( $mylinks , $links );

    }

    public function display_mam_required(){

        $html_line = '<br><div class="tsl_section" style="max-width:65em;"><h2>' . __('Business Directory Plugin Required', 'business-directory-offers') . '</h2>';
        $html_line .= '<p><span>' . __('The Business Directory Offers Plugin is designed to add offers to your Business Directory Plugin listings.', 'business-directory-offers') . '</span></p>';
        $html_line .= '</div>';

        echo $html_line;

    }

    function add_submenu(){

        add_submenu_page('wpbdp_admin',
                     _x('Business Directory Offers settings', 'admin menu', 'business-directory-offers'),
                     _x('Offers Settings', 'admin menu', 'business-directory-offers'),
                     'administrator',
                     'WPBD_Offers',
                     array($this, 'display_offers_settings'));

    }

    function set_is_bd_installed(){

        if( function_exists('wpbdp_get_form_fields') ) {
            $this->is_bd_installed = true;
        }else{
            $this->is_bd_installed = false;
        }
    }

    function display_offers_settings(){

        if($this->is_bd_installed) {
            $this->display_settings();
        }else{
            $this->display_mam_required();
        }

        $html_line = $this->general_instructions();
        $html_line .= $this->shortcode_instructions();
        $html_line .= $this->feedback();
        $html_line .= $this->mobile_app_manager();
        echo $html_line;

    }

    public function shortcode_instructions(){

        $html_line = '<div class="tsl_section" style="max-width:65em;"><h2>' . __('Offers Page Shortcode', 'business-directory-offers') . '</h2>';
        $html_line .= '<p>To display all current offers on a single page, add the shortcode [WPBDPOffers] to a WordPress page.</p>';
        $html_line .= '<p>This will display all of your current offers in a responsive grid along with filters based on listing categories and subcategories.</p>';
        $html_line .= '</div><br>';
        return $html_line;

    }

    public function general_instructions(){

        $html_line = '<div class="tsl_section" style="max-width:65em;"><h2>' . __('Adding Offers to a Business Directory Listing', 'business-directory-offers') . '</h2>';
        $html_line .= '<p>The plugin adds an editor section to each Business Directory listing. This section lets you create offers and deals for each business listing in minutes: add start and end dates, offer content, featured image and coupon image. </p>';
        $html_line .= '<p>On the frontend, offers are displayed on each listing page.  Offers are only displayed if they are either currently available or should be displayed based on the "Visible Date" </p>';
        $html_line .= '</div><br>';
        return $html_line;

    }


    public static function display_settings(){
        if(isset($_REQUEST['tsl_date_format'])){
            //save date format
            update_option( 'tsl_date_format' , $_REQUEST['tsl_date_format'] );
        }

        $date_format = 'mm/dd/yy';

        if(get_option( 'tsl_date_format' )){
            $date_format = get_option( 'tsl_date_format' );
        }

        $date_format_array = array('mm/dd/yy' , 'dd/mm/yy' , 'd M, yy' , 'd MM, yy');

        echo '<div class="wrap">';
        echo '<h2>'.__('Business Directory Offers Settings', 'business-directory-offers').'</h2>';
        echo '<div class="section"><h2>'.__('Date Format', 'business-directory-offers').'</h2></div>';
        echo '<form method="get">';
        echo '<input type="hidden" value="WPBD_Offers" name="page">';
        echo '<p>'.__('Select Date Format', 'business-directory-offers').':&nbsp;&nbsp;<select style="margin-right:2em;" name="tsl_date_format">';

        for($x=0;$x<sizeof($date_format_array);$x++){
            if($date_format_array[$x] == $date_format ){
                $selected = 'selected';
            }else{
                $selected = '';
            }
            $date_value = '';
            switch($date_format_array[$x]){
                case 'dd/mm/yy':
                    $date = date_create();
                    $date_value = date_format($date,"d/m/Y") . ' - ';
                    break;
               case 'mm/dd/yy':
                    $date = date_create();
                    $date_value = date_format($date,"m/d/Y"). ' - ';
                    break;
               case 'd M, yy':
                    $date = date_create();
                    $date_value = date_format($date,"d M, Y"). ' - ';
                    break;
               case 'd MM, yy':
                    $date = date_create();
                    $date_value = date_format($date,"d F, Y"). ' - ';
                    break;
            }

            echo '<option value="'.$date_format_array[$x].'" '.$selected.'>' . $date_value . $date_format_array[$x] . '</option>';
        }

        echo '</select>&nbsp;&nbsp;'. submit_button().'</p>';

        echo '</form>';
        echo '</div>';


    }

    public static function feedback(){

        $html_line = '<div class="tsl_section" style="max-width:65em;"><h2>' . __('Feedback and Support', 'business-directory-offers') . '</h2>';
        $html_line .= '<p>If you need support, want to provide some feedback or have an idea for a new feature for Business Directory Offers, drop us an email at <a href="mailto:info@tinyscreenlabs.com">info@tinyscreenlabs.com</a></p>';
        $html_line .= '</div>';
        return $html_line;

    }

    public static function mobile_app_manager(){

        $html_line = '';

        if( ! is_plugin_active( 'wp-local-app/wp-local-app.php' ) ) {

            $installer = new tsl_install_manager_for_wpbdo();
            $is_on_internet = $installer->is_connected_to_internet();
            $can_user_install = current_user_can('install_plugins');
            $button = '';

            $html_line .= '<br><div class="tsl_section" style="max-width:65em;">';
            if($is_on_internet) {
                if($can_user_install) $button = '<input id="tsl-install-plugin" class="button button-primary" value="Install from tinyscreenlabs.com" type="submit">';
                $html_line .= '<form method="post" action="https://tinyscreenlabs.com/install-plugins/">';
                $html_line .= '<input type="hidden" name="tslplugin" value="wpbdpo">';
                $html_line .= '<table style="width:100%"><tr><td><h2>' . __('Mobile App Manager (Premium)', 'business-directory-offers') . '</h2></td><td align="right">' . $button . '</td></tr></table>';
                $html_line .= '</form>';
            }else{
                if($can_user_install) $button = '<input id="tsl-install-plugin" class="button button-primary" value="Download from tinyscreenlabs.com" type="submit" >';
                $html_line .= '<form target="_blank" action="https://tinyscreenlabs.com/">';
                $html_line .= '<table style="width:100%"><tr><td><h2>' . __('Mobile App Manager (Premium)', 'business-directory-offers') . '</h2></td><td align="right">' . $button . '</td></tr></table>';
                $html_line .= '</form>';
            }
            $html_line .= '<p><span>' . __('This plugin also works with TSL Mobile App Manager to enable you to publish your listings, events and special offers on your own mobile app.  TSL Mobile App Manager is a WordPress plugin and cloud based service that enables WordPress Admins to design a mobile app and complete the submission process right inside the WordPress dashboard.', 'business-directory-offers') . '</span></p>';
            $html_line .= '<ul style="list-style-type:disc;margin-left:2em;">';
            $html_line .= '<li>' . __('WordPress administrators have the ability to manage content for their website and mobile apps in one place<', 'business-directory-offers') . '/li>';
            $html_line .= '<li>' . __('Business Directory Offers are displayed on your mobile app', 'business-directory-offers') . '</li>';
            $html_line .= '<li>' . __('App Setup is a drag and drop interface where you design your mobile app before you purchase a TSL Pro Plan', 'business-directory-offers') . '</li>';
            $html_line .= '<li>' . __('The TSL Local App Previewer is a WYSIWYG viewer that connects to your website', 'business-directory-offers') . '</li>';
            $html_line .= '<li>' . __('Updates to app page content are automatically pushed to the mobile app whenever you update pages and posts in WordPress', 'business-directory-offers') . '</li>';
            $html_line .= '<li>' . __('TSL publishes your app to iTunes and Google Play when you purchase the TSL Pro Plan', 'business-directory-offers') . '</li>';
            $html_line .= '</ul>';

            $html_line .= '<p><span>' . __('For more information go to the ', 'business-directory-offers') . '<a href="https://tinyscreenlabs.com/?tslref=tslaffiliate" target="_blank">' . __('Tiny Screen Labs', 'business-directory-offers') . '</a> (TSL) '.__('website', 'business-directory-offers').'. </span></p>';

            $html_line .= '</div>';
        }

        return $html_line;
    }

    function add_metaboxes() {

        add_meta_box( 'BusinessDirectory_listingOffers',
            __( 'Special Offers', 'business-directory-offers' ),
            array( 'WPBDPOffers_Admin_Offers_Fields_Metabox' , 'tsl_metabox_callback' ),
            'wpbdp_listing',
            'normal',
            'core' );

    }

    function register_fields(){

        $all_fields = wpbdp_get_form_fields();
        $add_offers = true;

        for($f=0;$f<sizeof($all_fields);$f++){
            $this_obj = $all_fields[$f];
            if(strtolower($this_obj->get_shortname()) == 'offers'){
                $add_offers = false;
            }
        }

        if($add_offers) {
            $fields['field']['association'] = 'meta';
            $fields['field']['field_type'] = 'textarea';
            $fields['field']['id'] = 0;
            $fields['field']['allow_html'] = 1;
            $fields['field']['label'] = 'Offers';
            $fields['field']['shortname'] = 'offers';
            $fields['field']['display_flags'][] = 'listing';

            $field = new WPBDP_Form_Field(stripslashes_deep($fields['field']));
            $field->save();

        }

    }

    function frontend_enqueue_scripts(){

        $this->enqueue_scripts();

        wp_localize_script( 'business-directory-plugin-offers-scripts', 'tsl_content', $this->handle_front_end() );
    }

    function enqueue_scripts($hook = null ){

        wp_register_script('business-directory-plugin-offers-scripts', plugins_url( 'js/business-directory-plugin-offers-scripts.js', __FILE__ ), array(), $this->js_version, true);
        wp_register_script('moment', plugins_url( 'js/moment.js', __FILE__ ), array(), $this->js_version, true);
        wp_register_script('qtip',   plugins_url( 'js/jquery.qtip.min.js', __FILE__ ), array(), $this->js_version, true);

        if(get_option( 'tsl_date_format' )){
            $date_format = get_option( 'tsl_date_format' );
        }

        if(!isset($date_format)) $date_format = 'mm/dd/yy';

        if($this->is_bd_installed) {
            $all_fields = wpbdp_get_form_fields();

            for ($f = 0; $f < sizeof($all_fields); $f++) {
                $this_obj = $all_fields[$f];
                if (strtolower($this_obj->get_shortname()) == 'offers') {
                    wp_localize_script('business-directory-plugin-offers-scripts', 'tsl_field_id', array('id' => $this_obj->get_id()));
                }
            }
        }

        wp_localize_script( 'business-directory-plugin-offers-scripts', 'tsl_manager', $date_format);
        wp_localize_script( 'business-directory-plugin-offers-scripts', 'tsl_ajax_url', admin_url( 'admin-ajax.php' ) );
        wp_localize_script( 'business-directory-plugin-offers-scripts', 'tsl_business_lng', $this->language());

        wp_enqueue_script(array(  'jquery' , 'jquery-ui-datepicker' , 'jquery-ui-dialog' , 'jquery-masonry', 'business-directory-plugin-offers-scripts' , 'moment' , 'qtip' ));

        wp_register_style('qtip', plugins_url( "css/jquery.qtip.min.css", __FILE__ ), array(), $this->js_version, 'screen');
        wp_register_style('qtip-settings', plugins_url( "css/jquery.qtip.css", __FILE__ ), array(), $this->js_version, 'screen');
        wp_register_style('fontawesome', plugins_url( "css/font-awesome.min.css", __FILE__ ), array(), $this->js_version, 'screen');
        wp_register_style('business-directory-plugin-offers', plugins_url( "css/business-directory-plugin-offers.css", __FILE__ ), array(), $this->js_version, 'screen');

        wp_enqueue_style(array( 'fontawesome' , 'qtip', 'qtip-settings' , 'business-directory-plugin-offers' ));

    }

    function language(){

        $lng['save_alert'] = __("Don't forget to update the page to save your changes!", 'business-directory-plugin-offers');
        $lng['title'] = __('Title', 'business-directory-plugin-offers');
        $lng['visible_date'] = __('Visible Date', 'business-directory-plugin-offers');
        $lng['start_date'] = __('Start Date', 'business-directory-plugin-offers');
        $lng['end_date'] = __('End Date', 'business-directory-plugin-offers');
        $lng['always'] = __('Always', 'business-directory-plugin-offers');
        $lng['edit_item'] = __('Edit Item', 'business-directory-plugin-offers');
        $lng['add_item'] = __('Add Item', 'business-directory-plugin-offers');
        $lng['close'] = __('Close', 'business-directory-plugin-offers');
        $lng['delete_item'] = __('Delete Item', 'business-directory-plugin-offers');
        $lng['offer_title'] = __('Offer Title', 'business-directory-plugin-offers');
        $lng['visible_always'] = __('Make this offer visible all the time', 'business-directory-plugin-offers');
        $lng['offer_description'] = __('Offer Description', 'business-directory-plugin-offers');
        $lng['cancel'] = __('Cancel', 'business-directory-plugin-offers');
        $lng['select_file'] = __('Select File', 'business-directory-plugin-offers');
        $lng['voucher_image'] = __('Voucher Image', 'business-directory-plugin-offers');
        $lng['deal_image'] = __('Deal Image', 'business-directory-plugin-offers');
        $lng['upload_instructions'] = __('Upload an image and then use the short code [VOUCHER Coupon] in the offer text above to insert a link to the image. You can change the word Coupon to anything you want for the link name.', 'business-directory-plugin-offers');
        $lng['manage_special_offers'] = __('Manage Special Offers', 'business-directory-plugin-offers');
        $lng['edit_offers'] = __('Edit Offers', 'business-directory-plugin-offers');
        $lng['save_item'] = __('Save Item', 'business-directory-plugin-offers');
        $lng['save'] = __('Save', 'business-directory-plugin-offers');
        $lng['starts'] = __('Starts', 'business-directory-plugin-offers');
        $lng['ends'] = __('Ends', 'business-directory-plugin-offers');

        $parts = explode('_',get_locale());

        $lng['locale'] = $parts[0] ;

        return $lng;
    }

    function tsl_business_directory_plugin_offers_upload_voucher(){

        $condition_img = 7;
        $img_count = 0;
        if(isset($_POST["image_gallery"])) $img_count = count(explode(',', $_POST["image_gallery"]));

        if (!empty($_FILES["tsl_voucher_file_upload"])) {

            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');


            $files = $_FILES["tsl_voucher_file_upload"];
            $attachment_ids = array();

            if ($img_count >= 1) {
                $imgcount = $img_count;
            } else {
                $imgcount = 1;
            }

            $ul_con = '';

            foreach ($files['name'] as $key => $value) {
                if ($files['name'][$key]) {
                    $file = array(
                        'name' => $files['name'][$key],
                        'type' => $files['type'][$key],
                        'tmp_name' => $files['tmp_name'][$key],
                        'error' => $files['error'][$key],
                        'size' => $files['size'][$key]
                    );
                    $_FILES = array("tsl_voucher_file_upload" => $file);


                    foreach ($_FILES as $file => $array) {

                        if ($imgcount >= $condition_img) {
                            break;
                        }

                        require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                        require_once(ABSPATH . "wp-admin" . '/includes/file.php');
                        require_once(ABSPATH . "wp-admin" . '/includes/media.php');

                        $attach_id = media_handle_upload($file, 0 );
                        $attachment_ids[] = $attach_id;

                        $image_link = wp_get_attachment_image_src($attach_id, 'thumbnail');
                        $image_link_large = wp_get_attachment_image_src($attach_id, 'full');

                        //Correct protocol for https
                        list($protocol, $uri) = explode('://', $image_link[0], 2);

                        if(is_ssl()) {
                            if('http' == $protocol) {
                              $protocol = 'https';
                            }
                            } else {
                            if('https' == $protocol) {
                              $protocol = 'http';
                            }
                        }

                        $image_link[0] = $protocol.'://'.$uri;

                        list($protocol, $uri) = explode('://', $image_link_large[0], 2);

                        if(is_ssl()) {
                            if('http' == $protocol) {
                              $protocol = 'https';
                            }
                            } else {
                            if('https' == $protocol) {
                              $protocol = 'http';
                            }
                        }

                        $image_link_large[0] = $protocol.'://'.$uri;

                        if($_REQUEST['image_type'] == 2 ){
                            $ul_con = '<div class="plupload-thumbs" style="clear:inherit; margin-top:0; margin-left:5em; padding-top:10px; float:left; width:50%;">
                                        <div class="thumb">
                                            <div class="thumbi">
                                            <a onclick="tsl_business_directory_offers_handler.remove_deal_image()" href="javascript:;" class="delete check">Remove</a>
                                           </div>
                                           <img style="margin-top:.5em" src="' . $image_link[0] . '" >
                                       </div>
                                    </div>
                                    <div id="tsl_deal_thumbnail" style="display:none;">' . $image_link[0] . '</div>
                                    <div id="tsl_deal_image" style="display:none;">' . $image_link_large[0] . '</div>';
                        }else {
                           $ul_con = '<div class="plupload-thumbs" style="clear:inherit; margin-top:0; margin-left:5em; padding-top:10px; float:left; width:50%;">
                                        <div class="thumb">
                                            <div class="thumbi">
                                            <a onclick="tsl_business_directory_offers_handler.remove_voucher()" href="javascript:;" class="delete check">Remove</a>
                                           </div>
                                           <img style="margin-top:.5em" src="' . $image_link[0] . '" >
                                       </div>
                                    </div>
                                    <div id="tsl_thumbnail" style="display:none;">' . $image_link[0] . '</div>
                                    <div id="tsl_image" style="display:none;">' . $image_link_large[0] . '</div>';
                        }
                    }
                    if ($imgcount > $condition_img) {
                        break;
                    }
                    $imgcount++;
                }
            }


        }
        /*img upload */

        if(isset($_POST["image_gallery"])) $image_gallery = $_POST['image_gallery'];

        $attachment_idss = array_filter($attachment_ids);
        $attachment_idss = implode(',', $attachment_idss);

        if (isset($image_gallery)) {
            $attachment_idss = $image_gallery . "," . $attachment_idss;
        }

        $arr = array();
        $arr['attachment_idss'] = $attachment_idss;
        $arr['ul_con'] = $ul_con;

        echo json_encode($arr);
        die();

    }

    function date_options(){
        $dates = array('mm/dd/yy' , 'dd/mm/yy' , 'd M, yy' , 'd MM, yy');
        return $dates;
    }

    function handle_front_end(){

        if( ! class_exists('WPBDP_Listing')) return '';

        $listing = WPBDP_Listing::get( get_the_ID() );

        if ( ! $listing ) {
            return '';
        }

        $all_fields = wpbdp_get_form_fields();
        $offers_id = 0;

        for($f=0;$f<sizeof($all_fields);$f++){
            $this_obj = $all_fields[$f];
            if(strtolower($this_obj->get_shortname()) == 'offers'){
                $offers_id = $this_obj->get_id();
            }
        }

        if($offers_id > 0 ){

            foreach ( wpbdp_get_form_fields( array( 'association' => 'meta' ) ) as $field ) {
                if( $field->get_id() == $offers_id ){
                    return rawurlencode( $field->value( get_the_ID() ) );
                }
            }

        }

    }
}


function wpbdo_debug( $message ) {
	echo '<div style="margin-left:15em;">';
	echo '<pre>';
	print_r( $message );
	echo '</pre>';
	echo '</div>';
}