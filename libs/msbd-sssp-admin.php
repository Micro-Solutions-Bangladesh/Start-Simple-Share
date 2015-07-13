<?php
class MsbdSSSAdmin {

    var $parent;

    function __construct($parent) {
        $this->parent = $parent;
        
        add_action('admin_menu', array(&$this, 'init_admin_menu'));
        
        //Loading Styles and Scripts for admin
        add_action( 'admin_enqueue_scripts', array(&$this, 'load_admin_scripts_styles'), 100);
        
    }





    function init_admin_menu() {
        global $wpdb;

        $var_manage_authority = 'manage_options';
       
       add_menu_page(
            'Simple Share',
            'Simple Share',
            $var_manage_authority,
            'msbd-sssp-settings',
            array(&$this, 'msbd_sssp_settings_page_render'),
            MSBD_ADSMP_URL.'images/msbd_favicon_16.png',
            '11.13'
        );
    }
    
    


    function msbd_sssp_settings_page_render($wrapped = false) {
        
        $options = $this->parent->sssp_options_obj->get_option();

        if (!$wrapped) {
            $this->wrap_admin_page('settings');
            return;
        }

        //Check User Permission
        $var_manage_authority = $this->parent->sssp_options['msbd_sssp_manage_authority'];
        if (!current_user_can($var_manage_authority)) {
            wp_die( __('You do not have sufficient permissions to access this page.') );
        }
        
        ?>
        <form id="msbd-sssp-settings-form" action="" method="post">
            <input type="hidden" name="action" value="msbd-sssp-update-options">
            
            <div class="form-table">
                    
                <div class="form-row">
                    <div class="grid_4">
                        <label for="sssp_title">Share Title</label>
                    </div>
                    
                    <div class="grid_8">
                        <input type="text" class="widefat" name="sssp_title" id="sssp_title" value="<?php echo $options['sssp_title'] ?>" placeholder="Share with:"  />
                    </div>
                </div>
                    
                <div class="form-row">
                    <div class="grid_4">
                        <label for="sssp_position">Select Position</label>
                    </div>
                    
                    <div class="grid_8">
                        <?php
                        echo $this->draw_position_select_box( 'name="sssp_position" id="sssp_position"', $options['sssp_position']);
                        ?>
                    </div>
                </div>
                    
                <div class="form-row">
                    <div class="grid_4">
                        <label for="sssp_selected_buttons">Share Buttons List</label>
                    </div>
                    
                    <div class="grid_8">
                        <input type="text" class="widefat" name="sssp_selected_buttons" id="sssp_selected_buttons" value="<?php echo $options['sssp_selected_buttons'] ?>" placeholder="facebook, twitter, google, pinterest"  /> <p class="note">[Write the buttons name in csv format! Available buttons are: <strong>facebook</strong>, <strong>twitter</strong>, <strong>google</strong>, <strong>pinterest</strong>, <strong>reddit</strong>, <strong>linkedin</strong>, <strong>stumbleupon</strong>, <strong>tumblr</strong>, <strong>email</strong>, <strong>print</strong>]</p>
                    </div>
                </div>
                    
                <div class="form-row">
                    <div class="grid_4">
                        <label for="sssp_show_share_count">Show share count</label>
                    </div>
                    
                    <div class="grid_8">
                        <?php
                        echo $this->draw_yes_no_select_box( 'name="sssp_show_share_count" id="sssp_show_share_count"', $options['sssp_show_share_count']);
                        ?>
                    </div>
                </div>
                    
                <div class="form-row">
                    <div class="grid_4">
                        <label for="sssp_pinterest_featured">Use featured image for Pinterest</label>
                    </div>
                    
                    <div class="grid_8">
                        <?php
                        echo $this->draw_yes_no_select_box('name="sssp_pinterest_featured" id="sssp_pinterest_featured"', $options['sssp_pinterest_featured']);
                        ?>
                    </div>
                </div>
                    
                <div class="form-row">
                    <div class="grid_4">
                        <label for="sssp_default_pinterest">Image URL (if no featured image)</label>
                    </div>
                    
                    <div class="grid_8">
                        <input type="text" class="widefat" name="sssp_default_pinterest" id="sssp_default_pinterest" value="<?php echo $options['sssp_default_pinterest'] ?>" placeholder=""  />
                    </div>
                </div>
                    
                <div class="form-row">
                    <div class="grid_4">
                        <label for="sssp_share_new_window">Use new window</label>
                    </div>
                    
                    <div class="grid_8">
                        <?php
                        echo $this->draw_yes_no_select_box('name="sssp_share_new_window" id="sssp_share_new_window"', $options['sssp_share_new_window']);
                        ?>
                    </div>
                </div>
                    
                <div class="form-row">
                    <div class="grid_4">
                        <label for="sssp_rel_nofollow">Use nofollow</label>
                    </div>
                    
                    <div class="grid_8">
                        <?php
                        echo $this->draw_yes_no_select_box('name="sssp_rel_nofollow" id="sssp_rel_nofollow"', $options['sssp_rel_nofollow']);
                        ?>
                    </div>
                </div>
                    
                <div class="form-row">
                    <div class="grid_4">
                        <label for="sssp_twitter_text">Twitter text</label>
                    </div>
                    
                    <div class="grid_8">
                        <input type="text" class="widefat" name="sssp_twitter_text" id="sssp_twitter_text" value="<?php echo $options['sssp_twitter_text'] ?>" placeholder="@atoz_reviews or #any_category"  />
                        <p class="note">[this text will be added to the last of the title!]</p>
                    </div>
                </div>
                    
                
                <div class="form-row">
                    <div class="grid_6">
                        <input name="resetButton" type="reset" value="Reset" />
                        <input type="submit" class="button" value="Save Settngs">
                    </div>
                </div>
            </div>
        </form>
        <?php
    }





    function load_admin_scripts_styles() {
        wp_enqueue_style( "msbd-sssp-admin", MSBD_SSSP_URL . 'css/msbd-sssp-admin.css', false, false );           
        wp_enqueue_script( "msbd-sssp-admin-script", MSBD_SSSP_URL ."js/msbd-sssp-admin.js", "jquery", false, true);
    }




    function wrap_admin_page($page = null) {
        
        $page_header = '';
        switch($page) {                
            case 'settings':
                $page_header = $this->parent->plugin_name.' Settings';
                break;
        }
        
        echo '<div class="wrap msbd-sssp">';
        echo '<h2><img src="' . MSBD_SSSP_URL . 'images/msbd_favicon_32.png" /> '.$page_header.' </h2>';
        
        echo '<div class="sssp-body-content">';
        
        MsbdSSSAdminHelper::render_container_open('content-container');        
        
        if ($page == 'settings') {
            MsbdSSSAdminHelper::render_postbox_open('Settings');
            echo $this->msbd_sssp_settings_page_render(TRUE);
            MsbdSSSAdminHelper::render_postbox_close();
        }
        
        MsbdSSSAdminHelper::render_container_close();
        
        MsbdSSSAdminHelper::render_container_open('sidebar-container');        
        MsbdSSSAdminHelper::render_sidebar();
        MsbdSSSAdminHelper::render_container_close();
        
        echo '</div>'; /* .sssp-body-content */
        echo '</div>'; /* .wrap msbd-sssp */
    }



    function draw_yes_no_select_box($att, $selVal='') {
        
        $record = array(
            "yes"   => "Yes",
            "no"     => "No"
        );
        
        $html = '<select '.$att.'>';
        foreach($record as $i=>$v) {
            if($selVal==$i)
                $html .= '<option value="'.$i.'" selected="selected">'.$v.'</option>';
            else
                $html .= '<option value="'.$i.'">'.$v.'</option>';
        }
        $html .= '</select>';
        
        return $html;
    }



    function draw_position_select_box($att, $selVal='') {
        
        $record = array(
            "after"   => "After",
            "before"     => "Before",
            "both"     => "After and Before"
        );
        
        $html = '<select '.$att.'>';
        foreach($record as $i=>$v) {
            if($selVal==$i)
                $html .= '<option value="'.$i.'" selected="selected">'.$v.'</option>';
            else
                $html .= '<option value="'.$i.'">'.$v.'</option>';
        }
        $html .= '</select>';
        
        return $html;
    }
    
}
/* end of file msbd-sssp-admin.php */
