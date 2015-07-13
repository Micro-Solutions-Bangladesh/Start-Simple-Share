<?php
defined('ABSPATH') or die('No direct access permitted');

// add share buttons to content
add_filter('the_content', 'show_share_buttons',  11);

// get and show share buttons
function show_share_buttons($content) {

    // globals
    global $post, $sssPO;

    // variables
    $htmlContent = $content;
    $htmlShareButtons = '';

    $arrSettings = $sssPO->sssp_options;

    // placement on posts
    if ( is_single() ) { //Enable only for Post page

        // post id
        $intPostID = get_the_ID();

        // if post type is download (EDD clashes)
        if(get_post_type($intPostID) == "download") {
            // check for and remove added text
            preg_match_all("/>(.*?)>/", $strPageTitle, $matches);
            $title =  $matches[0][0];
            $title = ltrim($title, '>');
            $title = rtrim($title, '</span>');
            $strPageTitle = $title;
        }

        // ssba div
        $htmlShareButtons = '<!-- Start Simple Share microsolutionsbd.com --><div class="sssp-wrap">';
        
        if( !empty($arrSettings['sssp_title']) ) {
            $htmlShareButtons.= '<h3>'.$arrSettings['sssp_title'].'</h3>';
        }
        
        // left align by default
        $htmlShareButtons.= '<div class="share-links">';

        // use wordpress functions for page/post details
        $urlCurrentPage = get_permalink($post->ID);
        $strPageTitle = get_the_title($post->ID);

        // the buttons!
        $htmlShareButtons.= get_share_buttons($arrSettings, $urlCurrentPage, $strPageTitle, $intPostID);

        // close center if set
        $htmlShareButtons.= '</div>';
        $htmlShareButtons.= '</div>';


        // switch for placement of ssba
        switch ( $arrSettings['sssp_position'] ) {

            case 'before': // before the content
                $htmlContent = $htmlShareButtons . $content;
                break;

            case 'after': // after the content
                $htmlContent = $content . $htmlShareButtons;
                break;

            case 'both': // before and after the content
                $htmlContent = $htmlShareButtons . $content . $htmlShareButtons;
                break;
        }
    }

    // return content and share buttons
    return $htmlContent;
}





// get set share buttons
function get_share_buttons($arrSettings, $urlCurrentPage, $strPageTitle, $intPostID) {

    $htmlShareButtons = '';

    // explode saved include list and add to a new array
    $arrSelectedSSBA = explode(',', $arrSettings['sssp_selected_buttons']);

    // check if array is not empty
    if ($arrSettings['sssp_selected_buttons'] != '') {
        // add post ID to settings array
        $arrSettings['post_id'] = $intPostID;
        $booShowShareCount = ($arrSettings['sssp_show_share_count'] == 'yes') ? true : false;
        
        // for each included button
        foreach ($arrSelectedSSBA as $strSelected) {
            $strGetButton = 'sssp_' . trim($strSelected);
            // add a list item for each selected option
            $htmlShareButtons .= $strGetButton($arrSettings, $urlCurrentPage, $strPageTitle, $booShowShareCount);
        }
    }

    return $htmlShareButtons;
}



/********************************************************************************
                                              CREATE SHARE BUTTONS                                                         
 ********************************************************************************/

/**
 * Create Facebook Share Button
 **/
function sssp_facebook($arrSettings, $urlCurrentPage, $strPageTitle, $booShowShareCount) {
    
    $var_btn_cls = $booShowShareCount ? 's3d facebook' : 's3d facebook no-counter';
    
    $htmlShareButtons = '<a class="'.$var_btn_cls.'" href="http://www.facebook.com/sharer.php?u=' . $urlCurrentPage  . '" ' . ($arrSettings['sssp_share_new_window'] == 'yes' ? ' target="_blank" ' : NULL) . ($arrSettings['sssp_rel_nofollow'] == 'yes' ? ' rel="nofollow"' : NULL) .'>';

    $htmlShareButtons .= '<span><i class="fa fa-facebook"></i></span><span class="counter">';

    if ( $booShowShareCount ) {
        $htmlShareButtons .= '' . number_format(getFacebookShareCount($urlCurrentPage)) . '';
    }

    $htmlShareButtons .= '</span></a>';
    
    return $htmlShareButtons;
}


/**
 * Count Facebook Shares
 **/
function getFacebookShareCount($urlCurrentPage) {
    // get results from facebook
    $htmlFacebookShareDetails = wp_remote_get('http://graph.facebook.com/'.$urlCurrentPage, array('timeout' => 1));

    if (is_wp_error($htmlFacebookShareDetails)) {
        return 0;
    }

    // decode and return count
    $arrFacebookShareDetails = json_decode($htmlFacebookShareDetails['body'], true);
    $intFacebookShareCount =  (isset($arrFacebookShareDetails['shares']) ? $arrFacebookShareDetails['shares'] : 0);
    return ($intFacebookShareCount) ? $intFacebookShareCount : '0';
}






/**
 * Create Twitter Share Button
 **/
function sssp_twitter($arrSettings, $urlCurrentPage, $strPageTitle, $booShowShareCount) {

    $var_btn_cls = $booShowShareCount ? 's3d twitter' : 's3d twitter no-counter';
    
    // format the URL into friendly code
    $twitterShareText = urlencode(html_entity_decode($strPageTitle . ' ' . $arrSettings['sssp_twitter_text'], ENT_COMPAT, 'UTF-8'));

    $htmlShareButtons = '<a class="'.$var_btn_cls.'" href="http://twitter.com/share?url=' . $urlCurrentPage . '&amp;text=' . $twitterShareText . '" ' . ($arrSettings['sssp_share_new_window'] == 'yes' ? ' target="_blank" ' : NULL) . ($arrSettings['sssp_rel_nofollow'] == 'yes' ? ' rel="nofollow"' : NULL) . '>';
    
    $htmlShareButtons .= '<span><i class="fa fa-twitter"></i></span>';

    if ( $booShowShareCount ) {
        $htmlShareButtons .= '<span class="counter">' . number_format(getTwitterShareCount($urlCurrentPage)) . '</span>';
    }
    
    $htmlShareButtons .= '</a>';
    
    return $htmlShareButtons;
}

/**
 * Count Twitter Shares
 **/
function getTwitterShareCount($urlCurrentPage) {
    // get results from twitter and return the number of shares
    $htmlTwitterShareDetails = wp_remote_get('http://urls.api.twitter.com/1/urls/count.json?url='.$urlCurrentPage, array('timeout' => 6));

    if (is_wp_error($htmlTwitterShareDetails)) {
        return 0;
    }

    // get and decode count
    $arrTwitterShareDetails = json_decode($htmlTwitterShareDetails['body'], true);
    $intTwitterShareCount =  $arrTwitterShareDetails['count'];
    return ($intTwitterShareCount) ? $intTwitterShareCount : '0';
}




/**
 * Create Google+ Share Button
 **/
function sssp_google($arrSettings, $urlCurrentPage, $strPageTitle, $booShowShareCount) {

    $var_btn_cls = $booShowShareCount ? 's3d google' : 's3d google no-counter';

    $htmlShareButtons = '<a class="'.$var_btn_cls.'" href="https://plus.google.com/share?url=' . $urlCurrentPage  . '" ' . ($arrSettings['sssp_share_new_window'] == 'yes' ? ' target="_blank" ' : NULL) . ($arrSettings['sssp_rel_nofollow'] == 'yes' ? ' rel="nofollow" ' : NULL) . '>';
    
    $htmlShareButtons .= '<span><i class="fa fa-google"></i></span>';

    if ( $booShowShareCount ) {
        $htmlShareButtons .= '<span class="counter">'.number_format(getGoogleShareCount($urlCurrentPage)).'</span>';
    }
    
    $htmlShareButtons .= '</a>';

    return $htmlShareButtons;
}

/**
 * Count Google+ Shares
 **/
function getGoogleShareCount($urlCurrentPage) {

    $args = array(
        'method' => 'POST',
        'headers' => array(
            // setup content type to JSON
            'Content-Type' => 'application/json'
        ),
        // setup POST options to Google API
        'body' => json_encode(array(
                'method' => 'pos.plusones.get',
                'id' => 'p',
                'method' => 'pos.plusones.get',
                'jsonrpc' => '2.0',
                'key' => 'p',
                'apiVersion' => 'v1',
                'params' => array(
                    'nolog'=>true,
                    'id'=> $urlCurrentPage,
                    'source'=>'widget',
                    'userId'=>'@viewer',
                    'groupId'=>'@self'
                )
            )),
        // disable checking SSL sertificates
        'sslverify'=>false
    );

    // retrieves JSON with HTTP POST method for current URL
    $json_string = wp_remote_post("https://clients6.google.com/rpc", $args);

    if (is_wp_error($json_string)){
        // return zero if response is error
        return "0";
    } else {
        $json = json_decode($json_string['body'], true);
        // return count of Google +1 for requsted URL
        return intval( $json['result']['metadata']['globalCounts']['count'] );
    }
}




/**
 * Create Reddit Share Button
 **/
function sssp_reddit($arrSettings, $urlCurrentPage, $strPageTitle, $booShowShareCount) {
    
    $var_btn_cls = $booShowShareCount ? 's3d reddit' : 's3d reddit no-counter';
    
    $htmlShareButtons = '<a class="'.$var_btn_cls.'" href="http://reddit.com/submit?url=' . $urlCurrentPage  . '&amp;title=' . $strPageTitle . '" ' . ($arrSettings['sssp_share_new_window'] == 'yes' ? ' target="_blank" ' : NULL) . ($arrSettings['sssp_rel_nofollow'] == 'yes' ? ' rel="nofollow" ' : NULL) . '>';

    $htmlShareButtons .= '<span><i class="fa fa-reddit"></i></span>';

    if ( $booShowShareCount ) {
        $htmlShareButtons .= '<span class="counter">'.number_format(getRedditShareCount($urlCurrentPage)).'</span>';
    }
    
    $htmlShareButtons .= '</a>';

    return $htmlShareButtons;
}

/**
 * Count Reddt Shares
 **/
function getRedditShareCount($urlCurrentPage) {
    // get results from reddit and return the number of shares
    $htmlRedditShareDetails = wp_remote_get('http://www.reddit.com/api/info.json?url='.$urlCurrentPage, array('timeout' => 6));

    if (is_wp_error($htmlRedditShareDetails)) {
        return 0;
    }

    // decode and get share count
    $arrRedditResult = json_decode($htmlRedditShareDetails['body'], true);
    $intRedditShareCount = (isset($arrRedditResult['data']['children']['0']['data']['score']) ? $arrRedditResult['data']['children']['0']['data']['score'] : 0);
    return ($intRedditShareCount) ? $intRedditShareCount : '0';
}





/**
 * Create Linkedin Share Button
 **/
function sssp_linkedin($arrSettings, $urlCurrentPage, $strPageTitle, $booShowShareCount) {

    $var_btn_cls = $booShowShareCount ? 's3d linkedin' : 's3d linkedin no-counter';
    
    $htmlShareButtons = '<a class="'.$var_btn_cls.'" href="http://www.linkedin.com/shareArticle?mini=true&amp;url=' . $urlCurrentPage  . '" ' . ($arrSettings['sssp_share_new_window'] == 'yes' ? ' target="_blank" ' : NULL) . ($arrSettings['sssp_rel_nofollow'] == 'yes' ? ' rel="nofollow" ' : NULL) . '>';
    
    $htmlShareButtons .= '<span><i class="fa fa-linkedin"></i></span>';

    if ( $booShowShareCount ) {
        $htmlShareButtons .= '<span class="counter">'.number_format(getLinkedinShareCount($urlCurrentPage)).'</span>';
    }
    
    $htmlShareButtons .= '</a>';

    return $htmlShareButtons;
}

/**
 * Count Linkedin Shares
 **/
function getLinkedinShareCount($urlCurrentPage) {
    // get results from linkedin and return the number of shares
    $htmlLinkedinShareDetails = wp_remote_get('http://www.linkedin.com/countserv/count/share?url='.$urlCurrentPage, array('timeout' => 6));

    if (is_wp_error($htmlLinkedinShareDetails)) {
        return 0;
    }

    // extract/decode share count
    $htmlLinkedinShareDetails = str_replace('IN.Tags.Share.handleCount(', '', $htmlLinkedinShareDetails);
    $htmlLinkedinShareDetails = str_replace(');', '', $htmlLinkedinShareDetails);
    $arrLinkedinShareDetails = json_decode($htmlLinkedinShareDetails['body'], true);
    $intLinkedinShareCount =  $arrLinkedinShareDetails['count'];
    return ($intLinkedinShareCount) ? $intLinkedinShareCount : '0';
}







/**
 * Create Pinterest Share Button
 **/
function sssp_pinterest($arrSettings, $urlCurrentPage, $strPageTitle, $booShowShareCount) {

    $var_btn_cls = $booShowShareCount ? 's3d pinterest' : 's3d pinterest no-counter';
    
    // if using featured images for Pinteres
    if($arrSettings['sssp_pinterest_featured'] == 'yes') {
        
        if(has_post_thumbnail($arrSettings['post_id'])) { // if this post has a featured image
            // get the featured image
            $urlPostThumb = wp_get_attachment_image_src(get_post_thumbnail_id($arrSettings['post_id']), 'full');
            $urlPostThumb = $urlPostThumb[0];
        
        } else {// no featured image set
            // use the pinterest default
            $urlPostThumb = $arrSettings['sssp_default_pinterest'];
        }
    }
    
    
    if( !empty($urlPostThumb) ) {
        // pinterest share link
        $htmlShareButtons = '<a href="http://pinterest.com/pin/create/bookmarklet/?is_video=false&url='.$urlCurrentPage.'&media='.$urlPostThumb.'&description='.$strPageTitle.'" class="'.$var_btn_cls.'" '.($arrSettings['sssp_share_new_window'] == 'yes' ? ' target="_blank" ' : NULL) . ($arrSettings['sssp_rel_nofollow'] == 'yes' ? ' rel="nofollow" ' : NULL).'>';    
    
    } else { // not using featured images for pinterest
        
        // use the choice of pinnable images approach
        $htmlShareButtons = "<a class='".$var_btn_cls."' href='javascript:void((function()%7Bvar%20e=document.createElement(&apos;script&apos;);e.setAttribute(&apos;type&apos;,&apos;text/javascript&apos;);e.setAttribute(&apos;charset&apos;,&apos;UTF-8&apos;);e.setAttribute(&apos;src&apos;,&apos;//assets.pinterest.com/js/pinmarklet.js?r=&apos;+Math.random()*99999999);document.body.appendChild(e)%7D)());'>";
    }

    $htmlShareButtons .= '<span><i class="fa fa-pinterest"></i></span>';

    if ( $booShowShareCount ) {
        $htmlShareButtons .= '<span class="counter">'.number_format(getPinterestShareCount($urlCurrentPage)).'</span>';
    }
    
    $htmlShareButtons .= '</a>';
    
    return $htmlShareButtons;
}


/**
 * Count Pinterest Shares
 **/
function getPinterestShareCount($urlCurrentPage) {

     // get results from pinterest
    $htmlPinterestShareDetails = wp_remote_get('http://api.pinterest.com/v1/urls/count.json?url='.$urlCurrentPage, array('timeout' => 6));

    // check there was an error
    if (is_wp_error($htmlPinterestShareDetails)) {
        return 0;
    }

    // decode data
    $htmlPinterestShareDetails = str_replace('receiveCount(', '', $htmlPinterestShareDetails);
    $htmlPinterestShareDetails = str_replace(')', '', $htmlPinterestShareDetails);
    $arrPinterestShareDetails = json_decode($htmlPinterestShareDetails['body'], true);
    $intPinterestShareCount =  $arrPinterestShareDetails['count'];
    return ($intPinterestShareCount) ? $intPinterestShareCount : '0';
}





/**
 * Create Stumbleupon Share Button
 **/
function sssp_stumbleupon($arrSettings, $urlCurrentPage, $strPageTitle, $booShowShareCount) {
    
    $var_btn_cls = $booShowShareCount ? 's3d stumbleupon' : 's3d stumbleupon no-counter';
    
    // stumbleupon share link
    $htmlShareButtons = '<a class="'.$var_btn_cls.'" href="http://www.stumbleupon.com/submit?url=' . $urlCurrentPage  . '&amp;title=' . $strPageTitle . '" ' . ($arrSettings['sssp_share_new_window'] == 'yes' ? ' target="_blank" ' : NULL) . ($arrSettings['sssp_rel_nofollow'] == 'yes' ? ' rel="nofollow" ' : NULL) . '>';

    $htmlShareButtons .= '<span><i class="fa fa-stumbleupon"></i></span>';

    if ( $booShowShareCount ) {
        $htmlShareButtons .= '<span class="counter">'.number_format(getStumbleUponShareCount($urlCurrentPage)).'</span>';
    }
    
    $htmlShareButtons .= '</a>';
    
    return $htmlShareButtons;
}

/**
 * Count Stumbleupon Shares
 **/
function getStumbleUponShareCount($urlCurrentPage) {

    // get results from stumbleupon and return the number of shares
    $htmlStumbleUponShareDetails = wp_remote_get('http://www.stumbleupon.com/services/1.01/badge.getinfo?url='.$urlCurrentPage, array('timeout' => 6));

    if (is_wp_error($htmlStumbleUponShareDetails)) {
        return 0;
    }

    // decode data
    $arrStumbleUponResult = json_decode($htmlStumbleUponShareDetails['body'], true);
    $intStumbleUponShareCount = (isset($arrStumbleUponResult['result']['views']) ? $arrStumbleUponResult['result']['views'] : 0);
    return ($intStumbleUponShareCount) ? $intStumbleUponShareCount : '0';
}





/**
 * Create Email Share Button 
 **/
function sssp_email($arrSettings, $urlCurrentPage, $strPageTitle, $booShowShareCount) {

    // replace ampersands as needed for email link
    $strPageTitle = str_replace('&', '%26', $strPageTitle);

    $htmlShareButtons = '<a class="s3d email no-counter" href="mailto:?subject=' . $strPageTitle . '&amp;body=' . $arrSettings['sssp_email_message'] . '%20' . $urlCurrentPage  . '">';

    $htmlShareButtons .= '<span><i class="fa fa-envelope-o"></i></span></a>';

    return $htmlShareButtons;
}





/**
 * Create Tumblr Share Button
 **/
function sssp_tumblr($arrSettings, $urlCurrentPage, $strPageTitle, $booShowShareCount) {

    // check if http:// is included
    if (preg_match('[http://]', $urlCurrentPage)) {

        // remove http:// from URL
        $urlCurrentPage = str_replace('http://', '', $urlCurrentPage);
    } else if (preg_match('[https://]', $urlCurrentPage)) { // check if https:// is included
            // remove http:// from URL
            $urlCurrentPage = str_replace('https://', '', $urlCurrentPage);
    }

    // strip http:// or https:// from URL (tumblr doesn't work with this set)
    $urlCurrentPage =  str_replace("http://", '', $urlCurrentPage);

    // tumblr share link
    $htmlShareButtons = '<a class="s3d tumblr no-counter" href="http://www.tumblr.com/share/link?url=' . $urlCurrentPage . '&amp;name=' . $strPageTitle . '" ' . ($arrSettings['sssp_share_new_window'] == 'yes' ? ' target="_blank" ' : NULL) . ($arrSettings['sssp_rel_nofollow'] == 'yes' ? ' rel="nofollow" ' : NULL) . '>';

    $htmlShareButtons .= '<span><i class="fa fa-tumblr"></i></span></a>';

    return $htmlShareButtons;
}





/**
 * Create Print Share Button
 **/
function sssp_print($arrSettings, $urlCurrentPage, $strPageTitle, $booShowShareCount) {

    // linkedin share link
    $htmlShareButtons = '<a class="s3d print no-counter" href="#" onclick="window.print()">';
    
    $htmlShareButtons .= '<span><i class="fa fa-print"></i></span></a>';

    return $htmlShareButtons;
}
