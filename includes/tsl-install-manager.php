<?php
/**
 * Created by PhpStorm.
 * User: fred
 * Date: 11/3/17
 * Time: 9:36 AM
 */

add_action( 'plugins_loaded', array( 'tsl_install_manager_for_wpbdo', 'init' ));

class tsl_install_manager_for_wpbdo{

    public static function init(){
        $class = __CLASS__;
        new $class;
    }

    public function __construct(){

        add_action( 'init', array($this, 'check_for_installs') );
        add_action( 'admin_init', array($this, 'tsl_wpbdo_nag_ignore' ));
        add_action( 'admin_notices', array($this, 'install_admin_notice' ));
    }

    function install_admin_notice(){

        global $current_user , $pagenow;

        $plugins = get_plugins();

        if ( ! isset($plugins['wp-local-app/wp-local-app.php'])) {

            $user_id = $current_user->ID;

            if ( ($_REQUEST['page'] == 'WPBD_Offers' || $pagenow == 'plugins.php') && $_REQUEST['page'] !=  'tgmpa-install-plugins' && !get_user_meta($user_id, 'tsl_bus_dir_offers_notices')) {

                if ( $_REQUEST['page'] == 'tgmpa-install-plugins') {
                    $install_button = '';
                } else {

                    $plugins_array = get_option('tsl-wpbdo-plugins');

                    $goto = '?page=tgmpa-install-plugins&plugin_status=install';

                    if(is_array($plugins_array)) {
	                	if (sizeof($plugins_array) == 0) $goto = 'https://tinyscreenlabs.com/install-plugins/';
                	}

                    $install_link = '  '.__('Click here to', 'business-directory-offers').' <a href="'.$goto.'">'.__('install', 'business-directory-offers').'</a> '.__('the Mobile App Manager plugin from the tinyscreenlabs.com website', 'business-directory-offers').'.';
                }


                $nag_ignore_url = "?".$_SERVER['QUERY_STRING'].'&tsl_wpbdpio_nag_ignore=0';

                echo '<div class="updated"><p>';
                echo __('Install the', 'business-directory-offers').' <a href="https://tinyscreenlabs.com/tslref/busdir" target="_blank" >'.__('TSL Mobile App Manager', 'business-directory-offers').'</a> '.__('plugin and the Business Diretory Offers plugin to build a native mobile app for your Business Directory website. Works with the Business Directory plugin to display your directory listings and special offers in a private labeled iPhone and Android app.', 'business-directory-offers') . $install_link . '<a style="float:right" href="'.$nag_ignore_url.'">'.__('Hide Notice', 'business-directory-offers').'</a>';                echo '</p></div>';
            }
        }
    }

    function tsl_wpbdo_nag_ignore(){
        global $current_user;
        $user_id = $current_user->ID;
        if (isset($_GET['tsl_wpbdpio_nag_ignore']) && '0' == $_GET['tsl_wpbdpio_nag_ignore']) {
            add_user_meta($user_id, 'tsl_bus_dir_offers_notices', 'true', true);
        }
    }

    public function check_for_installs(){

        if(current_user_can('install_plugins')) {

            if (isset($_REQUEST['tsl_load'])) {

                $plugins = array();

                $plugins[] = array(
                        'name' => $_REQUEST['tslname'],
                        'slug' => $_REQUEST['tslslug'],
                        'source' => $_REQUEST['tslsource_url'],
                        'required' => false,
                        'external_url' => $_REQUEST['tslext_url'],
                );

                    update_option('tsl-wpbdo-plugins', $plugins);
            }
        }
    }


    public function is_connected_to_internet(){
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip);

                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return true;
                    }else{
                        return false;
                    }
                }
            }
        }
    }
}