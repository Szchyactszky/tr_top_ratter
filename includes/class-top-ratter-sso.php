<?php

/*
 * This class should handle all the SSO related activities in the plugin.
 */
class Top_Ratter_SSO {
    /*
     * Initializes the Short codes for SSO Class
     * 
     * Handles the Login and ESI api retrieval using SSO data.
     */
    public function __construct() {
        // SSO login for eve online
        add_shortcode ( 'sc_tr_sso_login_image', array (
            $this,
            'render_sso_login_stuff'
        ) );
        // SSO callback
        add_shortcode ( 'sc_sso_callback', array (
            $this,
            'render_sso_callback'
        ) );
    }
    
    /*
     * @todo put the submit stuff below in proper place.
     * 
     */
    
        //     if (isset ( $_POST ['SSO_LOGIN_REDIRECT_1'] )) {
        
        // 			$eve_sso_login_url = "https://login.eveonline.com/oauth/authorize/";
        // 			$finished_url_with_params;
        
        // 			// ?response_type=code
        // 			// &redirect_uri=https%3A%2F%2F3rdpartysite.com%2Fcallback
        // 			// &client_id=3rdpartyClientId
        // 			// &scope=characterContactsRead%20characterContactsWrite
        // 			// &state=uniquestate123
        
        // 			$response_type = "code";
        // 			$redirect_uri = "http://rlop.gargite.com/sso_callback/";
        // 			$client_id = "66b1f7f883ea4558b4c646461fcbfade";
        // 			// $scope="characterContactsRead characterAssetsRead characterCalendarRead characterMailRead characterAccountRead characterContractsRead characterBookmarksRead characterChatChannelsRead characterClonesRead";
        // 			$scope = "characterMailRead";
        
        // 			$state = "Ko_lai_te_raksta";
        
        // 			$finished_url_with_params .= $eve_sso_login_url . '?response_type=' . $response_type . '&redirect_uri=' . $redirect_uri . '&client_id=' . $client_id . '&scope=' . $scope . '&state=' . $state;
        
        // 			// echo $finished_url_with_params;
        // 			// $finished_url_with_params='http://rlop.gargite.com/';
        // 			wp_redirect ( $finished_url_with_params );
        // 			exit ();
        // 		}
        
    
    
    /**
     * Renders page where user can login to eve with sso
     * http://eveonline-third-party-documentation.readthedocs.io/en/latest/crest/authentication.html
     *
     * @return void
     */
    public function render_sso_login_stuff(){
        
        /*
         * Render the image that is a button
         * that once clicked the post is processed and redirected to eve login
         */
        
        //echo <img src=" echo plugin_dir_url( dirname( __FILE__ ) ) . 'images/facebook.png'; ">
        
        //echo '<img src="'.plugin_dir_url( dirname( __FILE__ ) ) . 'img/EVE_SSO_Login_Buttons_Large_Black.png">';
        
        $r='<p class="">SSO token login:</p>';
        //$r.= '<form method="post">';
        $r.= '<form action="' . get_admin_url () . 'admin-post.php" method="post">';
        $r.= '<input type="hidden" name="action" value="tr_action" />';
        $r.= '<input type="hidden" name="SSO_LOGIN_REDIRECT_1" value="true" />';
        $r.= '<input type="image" src="'.plugin_dir_url( dirname( __FILE__ ) ) . 'img/EVE_SSO_Login_Buttons_Large_Black.png" border="0" alt="Submit" />';
        $r.= '</form>';
        return $r;
        
        
        
    }
    
    
    /**
     * Renders page where sso callback is recieved and processed.
     *
     * @return void
     */
    public function render_sso_callback(){
        
        /*
         * https://3rdpartysite.com/callback?code=gEyuYF_rf...ofM0&state=uniquestate123
         *
         * Check if these are the values that was requested aka STATE ( it must be saved in the db or smth before theredirect)
         *
         */
        
        global $wpdb;
        
        if (isset ( $_GET ['code'] )) {
            
            
            $code=$_GET ['code'];
            $state=$_GET ['state'];
            
            $data=array(
                'code'=>$code,
                'state'=>$state
            );
            
            //insert for testing
            $wpdb->insert( $wpdb->prefix.'tr_sso_auth_code', $data);
            
            /*
             * make POST request
             * http://eveonline-third-party-documentation.readthedocs.io/en/latest/sso/authentication.html
             */
            
        }
        
        /*
         * ake CURL POST a base64 string to get token and save it in the  database
         * https://www.tools4noobs.com/online_php_functions/base64_encode/
         *
         * https://stackoverflow.com/questions/2138527/php-curl-http-post-sample-code
         *
         */
        if (isset ( $_POST['code'] )) {
            
        }
        
    }
    
    
    
}
?>