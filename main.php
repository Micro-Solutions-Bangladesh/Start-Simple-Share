<?php
/*
Plugin Name: Start Simple Share
Plugin URI: http://z2a.co/
Description: Start Simple Share plugin helps you to share articles to social network like-facebook, twitter, pinterest, stumbleupon, tumblr, linkedin. 
Version: 0.0.1
Author: Micro Solutions Bangladesh
Author URI: http://microsolutionsbd.com/
Text Domain: msbd-sssp
License: GPL2
*/

define('MSBD_SSSP_URL', trailingslashit(plugins_url(basename(dirname(__FILE__)))));

class MsbdStartSimpleShare {
    
    var $version = '0.0.1';
    var $plugin_name = 'Start Simple Share';

    /**
     * @var msbd_adsmp_options_obj
     */
    var $sssp_options_obj;    


    /**
     * @var todo note
     */
    var $sssp_options_name;


    /**
     * The variable that stores all current options
     */
    var $sssp_options;
    

    function __construct() {
        
        global $wpdb;
        
        //$this->markup = new MsbdMarkup();
        
        $this->sssp_options_name = "_msbd_sssp_options";
        
        $this->sssp_options_obj = new MsbdSSSOptions($this);
        
        $this->admin = new MsbdSSSAdmin($this);
        
        add_action('init', array(&$this, 'init'));
        
        add_action('wp_enqueue_scripts', array(&$this, 'load_scripts_styles'), 100);
        
    }



    function init() {

        $this->sssp_options_obj->update_options();
        $this->sssp_options = $this->sssp_options_obj->get_option();
        
    }
    /* end of function : init() */


    function load_scripts_styles() {        
        wp_enqueue_style( "msbd-sssp", MSBD_SSSP_URL . 'css/msbd-sssp.css', false, false );
    }




    /***********************************************************
                                    SANITIZATIONS
     ***********************************************************/

    /*
     * @ $field_type = text, email, number, html, no_html, custom_html, html_js default text
     */
    function msbd_sanitization($data, $field_type='text', $oArray=array()) {        
        
        $output = '';

        switch($field_type) {           
            
            case 'number':
                $output = sanitize_text_field($data);
                $output = intval($output);
                break;
            
            case 'boolean':
                $var_permitted_values = array('y', 'n', 'true', 'false', '1', '0', 'yes', 'no');
                $output = in_array($data, $var_permitted_values) ? $data : 0;//returned false if not valid
                break;
            
            case 'email':
                $output = sanitize_email($data);
                $output = is_email($output);//returned false if not valid
                break;
                
            case 'textarea': 
                $output = esc_textarea($data);
                break;
            
            case 'html':                                         
                $output = wp_kses_post($data);
                break;
            
            case 'custom_html':                    
                $allowedTags = isset($oArray['allowedTags']) ? $oArray['allowedTags'] : "";                                        
                $output = wp_kses($data, $allowedTags);
                break;
            
            case 'no_html':                                        
                $output = strip_tags( $data );
                //$output = stripslashes( $output );
                break;
            
            
            case 'html_js':
                $output = $data;
                break;
            
            
            case 'text':
            default:
                $output = sanitize_text_field($data);
                break;
        }
        
        return $output;

    }    

} // End of Class MsbdStartSimpleShare



if ( !function_exists('msbd_current_url') ) {
    /**
     * get URL function
     **/
    function msbd_current_url($atts) {
        // if multisite has been set to true
        if (isset($atts['multisite'])) {
            global $wp;
            $url = add_query_arg($_SERVER['QUERY_STRING'], '', home_url($wp->request));
            return esc_url($url);
        }

        // add http
        $urlCurrentPage = 'http';

        // add s to http if required
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $urlCurrentPage .= "s";
        }

        // add colon and forward slashes
        $urlCurrentPage .= "://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

        // return url
        return esc_url($urlCurrentPage);
    }
}






if (!class_exists('MsbdSSSAdminHelper')) {
    require_once('libs/views/admin-view-helper-functions.php');
}

if (!class_exists('MsbdSSSOptions')) {
    require_once('libs/msbd-sssp-options.php');
}

if (!class_exists('MsbdSSSAdmin')) {
    require_once('libs/msbd-sssp-admin.php');
}


require_once('libs/sssp_buttons.php');



global $sssPO;
$sssPO = new MsbdStartSimpleShare();

/* end of file main.php */
