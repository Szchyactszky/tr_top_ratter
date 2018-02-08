<?php

/*
 * This class should handle all the SSO related activities in the plugin.
 *
 * user login ->get user token
 * call ESI api- > get public data
 * check if user is in RLOP and get the name of char.
 * check if this user have related chars and if this is one of the related chars then login
 * if user dont exist create user with this char name and log in. admins assign chars?
 * User can login with any related char credentials while this char is in corporation.
 *
 * ----
 * officer login with wp admin account and add secret and client id in cpanel of top ratter
 * this is stored in seperate table for cron jobs.
 *
 *
 *
 *
 *
 */
class Top_Ratter_SSO
{

    /*
     * Initializes the Short codes for SSO Class
     *
     * Handles the Login and ESI api retrieval using SSO data.
     */
    public function __construct()
    {
        $this->check_required_sso_pages();
        $this->table_check();
        
        // testing short code
        add_shortcode('test_test_ottt', array(
            $this,
            'test_test_ottt'
        ));
        
        // add submit action form to redirect and catch from admin.php
        add_action('admin_post_SSO_action', array(
            $this,
            'admin_post_SSO_action'
        ));
        // for not loged in users
        add_action('admin_post_nopriv_SSO_action', array(
            $this,
            'admin_post_SSO_action'
        ));
        
        // SSO login for eve online
        add_shortcode('sc_tr_sso_login_image', array(
            $this,
            'render_sso_login_stuff'
        ));
        // SSO token mgmt
        add_shortcode('render_user_sso_token_mgmt', array(
            $this,
            'render_user_sso_token_mgmt'
        ));
        
        // SSO callback
        add_shortcode('sc_sso_callback', array(
            $this,
            'render_sso_callback'
        ));
        // Transient Debug information
        add_shortcode('sso_debug_transients', array(
            $this,
            'sso_debug_transients'
        ));
        
        // login token check for site access.
        /*
         * The hook is not working for some reason :(
         */
        
//         add_action('wp_login', array(
//             $this,
//             'user_login_token_check'
//         ), 10, 2);
    }

    public function test_test_ottt()
    {
        
        $this->user_login_token_check();
    }

    /**
     * Checks if the required pages with short codes are set up.
     *
     * @return void
     */
    public function check_required_sso_pages()
    {
        
        // callback page set up
        if (get_page_by_title('sso_callback') === NULL) {
            $createPage = array(
                'post_title' => 'sso_callback',
                'post_content' => '[sc_sso_callback]',
                'post_status' => 'publish',
                'post_author' => 1,
                'post_type' => 'page',
                'post_name' => 'sso_callback'
            );
            // Insert the post into the database
            wp_insert_post($createPage);
        }
    }

    /**
     * Handles the Form submits on the page.
     *
     * @return void
     */
    public function sso_debug_transients()
    {
        
        // echo'Transient name: finished_url_with_params_before_redirect<br>';
        // $value1=get_site_transient( 'finished_url_with_params_before_redirect1' );
        // echo "<pre>".var_dump($value1)."</pre>";
        
        // echo'Transient name: server_response_auth_code_to_token_exchange<br>';
        // $value2=get_site_transient( 'server_response_auth_code_to_token_exchange2' );
        // echo "<pre>".var_dump($value2)."</pre>";
        
        // echo'Transient name: server_response_token_curl_before_decoding3<br>';
        // $value3=get_site_transient( 'server_response_token_curl_before_decoding3' );
        // echo "<pre>".var_dump($value3)."</pre>";
        echo 'Transient name: SSO_TOKEN_REFRESH_ERROR_eve<br>';
        $value3 = get_site_transient('SSO_TOKEN_REFRESH_ERROR_eve');
        echo "<pre>" . var_dump($value3) . "</pre>";
    }

    /**
     * Checks for plugin tables
     *
     * This function looks in the database and checks for required tables for this plugin.
     * if tables are not found they are created.
     *
     * @return void
     */
    public function table_check()
    {
        // check if tables exist then create if not
        global $wpdb;
        
        $required_tables = array(
            "$wpdb->prefix" . "tr_sso_tokens",
            "$wpdb->prefix" . "tr_sso_auth_state",
            "$wpdb->prefix" . "tr_sso_credentials"
        );
        
        foreach ($required_tables as $table) {
            $val = $wpdb->get_var("SHOW TABLES LIKE '$table'");
            if ($val == $table) {
                // exists
            } else {
                // create non existing
                $this->create_table($table);
            }
        }
    }

    private function create_table($table)
    {
        global $wpdb;
        require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
        switch ($table) {
            case "$wpdb->prefix" . "tr_sso_auth_state":
                
                $sql = "CREATE TABLE `" . $wpdb->prefix . "tr_sso_auth_state`(
						  `id` INT(100) NOT NULL AUTO_INCREMENT,
						  `state` VARCHAR(250) NOT NULL,
						  PRIMARY KEY(`id`)
						) ENGINE = InnoDB;";
                
                dbDelta($sql);
                break;
            case "$wpdb->prefix" . "tr_sso_tokens":
                
                $sql = "CREATE TABLE `" . $wpdb->prefix . "tr_sso_tokens`(
					  `id` INT(100) NOT NULL AUTO_INCREMENT,
					  `access_token` VARCHAR(250) NOT NULL,
					  `token_type` VARCHAR(250) NOT NULL,
					  `expires_in` VARCHAR(250) NOT NULL,
					  `refresh_token` VARCHAR(250) NOT NULL,
					  `ts` DATETIME NOT NULL,
                      `character_id` int(10) NOT NULL COMMENT 'reference tr_characters id',
                      `oficer_token` int(1) NULL,
					  PRIMARY KEY(`id`)
					) ENGINE = InnoDB;";
                
                dbDelta($sql);
                break;
            case "$wpdb->prefix" . "tr_sso_credentials":
                
                $sql = "CREATE TABLE `" . $wpdb->prefix . "tr_sso_credentials`(
					  `id` INT(100) NOT NULL AUTO_INCREMENT,
					  `client_id` VARCHAR(250) NOT NULL,
					  `client_secret` VARCHAR(250) NOT NULL,
                      `redirect_url` VARCHAR(250) NOT NULL,
                      `corp_return_percent` int(3) NOT NULL,
                      `corp_top_ratter_count` int(10) NOT NULL,
                      `show_top5_pvp` int(1) NOT NULL,
                      `show_chart1` int(1) NOT NULL,
                      `show_chart2` int(1) NOT NULL,
                      `corporation_id` int(20) NULL,
                      `cached_until` DATETIME NULL,
                      `z_kill_api_page` int(10) NULL,
                      `show_npc_kills_by_systems_total` int(1) NULL,
					  PRIMARY KEY(`id`)
					) ENGINE = InnoDB;";
                
                dbDelta($sql);
                break;
        }
    }

    /**
     * Handles the Form submits on the page.
     *
     * @return void
     */
    public function admin_post_SSO_action()
    {
        global $wpdb;
        
        if (isset($_POST['SSO_LOGIN_REDIRECT_1'])) {
            $this->normal_sso_get_authorization_code();
        }
        
        if (isset($_POST['new_credentials_insert_atempt'])) {
            $this->handle_SSO_credential_input();
        }
        if (isset($_POST['delete_credentials_id'])) {
            $wpdb->delete($wpdb->prefix . 'tr_sso_credentials', array(
                'id' => $_POST['delete_credentials_id']
            ));
        }
        if (isset($_POST['edit_credentials_id'])) {
            $this->handle_edit_SSO_credentials();
        }
        
        if (isset($_POST['unlink_characteracter'])) {
            $this->handle_unlink_characters_submit();
        }
        
        wp_redirect(esc_url($_SERVER['HTTP_REFERER']));
        exit();
    }

    /**
     * Handle SSO login URL and SCOPES
     *
     * Compose the URL for EVE SSO redirect and save the unique state in the db.
     * This function generates the url for sso login.
     *
     * @return array of parameters
     */
    public function normal_sso_get_authorization_code()
    {
        global $wpdb;
        $eve_sso_login_url = "https://login.eveonline.com/oauth/authorize";
        
        $response_type = "code";
        
        // querry db for credentials
        $sql = "SELECT * FROM `" . $wpdb->prefix . "tr_sso_credentials`";
        $credentials = $wpdb->get_row("$sql", ARRAY_A);
        $redirect_uri = $credentials['redirect_url'];
        $client_id = $credentials['client_id'];
        
        $scope_string = "publicData characterLocationRead characterSkillsRead characterAccountRead corporationWalletRead corporationAssetsRead corporationKillsRead esi-location.read_location.v1 esi-location.read_ship_type.v1 esi-mail.read_mail.v1 esi-skills.read_skills.v1 esi-skills.read_skillqueue.v1 esi-wallet.read_character_wallet.v1 esi-wallet.read_corporation_wallet.v1 esi-characters.read_contacts.v1 esi-assets.read_assets.v1 esi-industry.read_character_jobs.v1 esi-characters.read_corporation_roles.v1 esi-location.read_online.v1 esi-contracts.read_character_contracts.v1 esi-killmails.read_corporation_killmails.v1 esi-wallet.read_corporation_wallets.v1 esi-industry.read_character_mining.v1 esi-industry.read_corporation_mining.v1";
        
        $pieces = explode(" ", $scope_string);
        $scope_count = count($pieces);
        $i = 0;
        foreach ($pieces as $scope_parts) {
            // loop trough and make value + value
            $scope .= $scope_parts;
            if ($i == $scope_count) {} else {
                $scope .= '+';
            }
            $i ++;
        }
        
        if (current_user_can('administrator')) {
            $state = 'SSSStateClient_23egy34DFHJ5y';
        } else {
            // random state
            $state = substr(md5(microtime()), rand(0, 26), 20);
        }
        
        $finished_url_with_params .= $eve_sso_login_url . '?response_type=' . $response_type . '&redirect_uri=' . $redirect_uri . '&client_id=' . $client_id . '&scope=' . $scope . '&state=' . $state;
        
        /*
         * ######################
         * Debug transient
         */
        // ####################
        set_site_transient('finished_url_with_params_before_redirect1', $finished_url_with_params, 60 * 5);
        
        // save state in the db.
        $data = array(
            'state' => $state
        );
        $wpdb->insert($wpdb->prefix . 'tr_sso_auth_state', $data);
        
        wp_redirect($finished_url_with_params);
        exit();
    }

    /**
     * Handle for SSO credentials and corporation settings.
     *
     * User with admin rights on wp and officer rights on eve fills in data and saves it.
     */
    public function handle_SSO_credential_input()
    {
        global $wpdb;
        
        $errors = null;
        if ($_POST['new_client_id'] == '') {
            $errors['new_client_id'] = 'client ID missing.';
        }
        set_transient('new_client_id_v', $_POST['new_client_id'], 60 * 2);
        
        if ($_POST['new_secret'] == '') {
            $errors['new_secret'] = 'Client secret missing.';
        }
        set_transient('new_secret_v', $_POST['new_secret'], 60 * 2);
        
        if ($_POST['new_callback_url'] == '') {
            $errors['new_callback_url'] = 'callback_url name missing.';
        }
        set_transient('new_callback_url_v', $_POST['new_callback_url'], 60 * 2);
        
        if ($_POST['new_corp_return_percent'] == '') {
            $errors['new_corp_return_percent'] = 'new_corp_return_percent missing';
        }
        set_transient('new_corp_return_percent_v', $_POST['new_corp_return_percent'], 60 * 2);
        
        if ($_POST['new_corp_top_ratter_count'] == '') {
            $errors['new_corp_top_ratter_count'] = 'new_corp_top_ratter_count missing';
        }
        set_transient('new_corp_top_ratter_count_v', $_POST['new_corp_top_ratter_count'], 60 * 2);
        
        // if errrors not null the exit
        if ($errors) {
            // make array of transients showing whats missing.
            foreach ($errors as $error_name => $value) {
                set_transient($error_name, $value, 60 * 60);
            }
        } else {
            // everything is OK so insert in the db
            $data = array(
                'client_id' => $_POST['new_client_id'],
                'client_secret' => $_POST['new_secret'],
                'redirect_url' => $_POST['new_callback_url'],
                'corp_return_percent' => $_POST['new_corp_return_percent'],
                'corp_top_ratter_count' => $_POST['new_corp_top_ratter_count'],
                'show_top5_pvp' => 1,
                'show_chart1' => 1,
                'show_chart2' => 1,
                'show_npc_kills_by_systems_total' => 1
            );
            $wpdb->insert($wpdb->prefix . 'tr_sso_credentials', $data);
            // echo $wpdb->last_error;
        }
    }

    /**
     * Unlink the character from account and delte the token.
     * Do additional main char check.
     */
    public function handle_unlink_characters_submit()
    {
        global $wpdb;
        
        // delete the token.
        $wpdb->delete($wpdb->prefix . 'tr_sso_tokens', array(
            'character_id' => $_POST['unlink_characteracter']
        ));
        
        // unlink from user chars table
        $wpdb->delete($wpdb->prefix . 'tr_users_chars', array(
            'char_id' => $_POST['unlink_characteracter']
        ));
        
        /*
         * set character corporation to 0 intr_characters table.
         *
         */
        $wpdb->update($wpdb->prefix . 'tr_characters', array(
            'corp_id' => '0'
        ), array(
            'id' => $_POST['unlink_characteracter']
        ));
        
        $user = wp_get_current_user();
        // check if user has main char
        $sql = "SELECT * FROM `" . $wpdb->prefix . "tr_users_chars` WHERE is_main_char='1' AND user_id='" . $user->ID . "';";
        $hasmainchar = $wpdb->get_row("$sql", ARRAY_A);
        
        if (! $hasmainchar) {
            // get first char and make it main.
            $sql = "SELECT * FROM `" . $wpdb->prefix . "tr_users_chars` WHERE user_id='" . $user->ID . "';";
            $haschars = $wpdb->get_row("$sql", ARRAY_A);
            
            // if it has any chars at all left
            if ($haschars) {
                $wpdb->update($wpdb->prefix . 'tr_users_chars', array(
                    'is_main_char' => '1'
                ), array(
                    'uc_id' => $haschars['uc_id']
                ));
            }
        }
    }

    /**
     * Handle the Editing/ updating of the SSO third party credentials in wp admin dashboard
     */
    public function handle_edit_SSO_credentials()
    {
        global $wpdb;
        
        $errors = null;
        if ($_POST['edit_client_id'] == '') {
            $errors['edit_client_id'] = 'client ID missing.';
        }
        set_transient('edit_client_id_v', $_POST['edit_client_id'], 60 * 2);
        
        if ($_POST['edit_secret'] == '') {
            $errors['edit_secret'] = 'Client secret missing.';
        }
        set_transient('edit_secret_v', $_POST['edit_secret'], 60 * 2);
        
        if ($_POST['edit_callback_url'] == '') {
            $errors['edit_callback_url'] = 'callback_url name missing.';
        }
        set_transient('edit_callback_url_v', $_POST['edit_callback_url'], 60 * 2);
        
        if ($_POST['edit_corp_return_percent'] == '') {
            $errors['edit_corp_return_percent'] = 'edit_corp_return_percent missing';
        }
        set_transient('edit_corp_return_percent_v', $_POST['edit_corp_return_percent'], 60 * 2);
        
        if ($_POST['edit_corp_top_ratter_count'] == '') {
            $errors['edit_corp_top_ratter_count'] = 'edit_corp_top_ratter_count missing';
        }
        set_transient('new_corp_top_ratter_count_v', $_POST['edit_corp_top_ratter_count'], 60 * 2);
        
        $show_pvp = 0;
        if ($_POST['edit_show_top5_pvp'] == '1') {
            // show
            $show_pvp = 1;
        }
        $show_chart1 = 0;
        if ($_POST['edit_show_chart1'] == '1') {
            // show
            $show_chart1 = 1;
        }
        $show_chart2 = 0;
        if ($_POST['edit_show_chart2'] == '1') {
            // show
            $show_chart2 = 1;
        }
        
        $show_npc_kills_by_systems_total = 0;
        if ($_POST['edit_show_npc_kills_by_systems_total'] == '1') {
            // show
            $show_npc_kills_by_systems_total = 1;
        }
        
        // if errrors not null the exit
        if ($errors) {
            // make array of transients showing whats missing.
            foreach ($errors as $error_name => $value) {
                set_transient($error_name, $value, 60 * 60);
            }
        } else {
            // everything is OK so insert in the db
            $data = array(
                'client_id' => $_POST['edit_client_id'],
                'client_secret' => $_POST['edit_secret'],
                'redirect_url' => $_POST['edit_callback_url'],
                'corp_return_percent' => $_POST['edit_corp_return_percent'],
                'corp_top_ratter_count' => $_POST['edit_corp_top_ratter_count'],
                'show_top5_pvp' => $show_pvp,
                'show_chart1' => $show_chart1,
                'show_chart2' => $show_chart2,
                'show_npc_kills_by_systems_total' => $show_npc_kills_by_systems_total
            );
            $where = array(
                'id' => $_POST['edit_credentials_id']
            );
            $wpdb->update($wpdb->prefix . 'tr_sso_credentials', $data, $where);
            // echo $wpdb->last_error;
        }
    }

    /**
     * Prepares the fields for SSO application credentials insert
     *
     * This is one big ugly echo function with error checking
     *
     * @return void
     */
    public function render_SSO_credentials_mgmt()
    {
        global $wpdb;
        
        $sql = "SELECT * FROM `" . $wpdb->prefix . "tr_sso_credentials`";
        $credentials = $wpdb->get_row("$sql", ARRAY_A);
        if ($credentials['corporation_id'] == '') {
            echo $this->render_sso_login_stuff();
        }
        
        if (! $credentials) {
            echo '<h3> Add an SSO login credentials</h3>';
            
            echo '<form action="' . get_admin_url() . 'admin-post.php" method="post">';
            echo '<input type="hidden" name="action" value="SSO_action" />';
            echo '<input type="hidden" name="new_credentials_insert_atempt" value="1" />';
            
            echo '<div>';
            echo '<p>client ID</p>';
            
            if (($value = get_transient('new_client_id')) != null) {
                echo '<input type="text" name="new_client_id" style="border-color: red;"/>';
                delete_transient('new_client_id');
            } else {
                $new_key_id_v = get_transient('new_client_id_v');
                echo '<input type="text" name="new_client_id"  value="' . $new_key_id_v . '"/>';
            }
            
            echo '<p>Client Secret</p>';
            if (($value = get_transient('new_secret')) != null) {
                echo '<input type="text" name="new_secret" style="border-color: red;" />';
                delete_transient('new_secret');
            } else {
                $new_vcode_v = get_transient('new_secret_v');
                echo '<input type="text" name="new_secret" value="' . $new_vcode_v . '"/>';
            }
            
            echo '<p>Call Back URL</p>';
            
            if (isset($_SERVER['HTTPS'])) {
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
            } else {
                $protocol = 'http';
            }
            $callback_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . '/sso_callback/';
            
            if (($value = get_transient('new_callback_url')) != null) {
                echo '<input type="text" name="new_callback_url" style="border-color: red;" />';
                delete_transient('new_callback_url');
            } else {
                $new_corp_name_v = get_transient('new_callback_url_v');
                echo '<input type="text" name="new_callback_url" value="' . $callback_url . '"/>';
            }
            echo '<p>Return % of acquired taxes</p>';
            if (($value = get_transient('new_corp_return_percent')) != null) {
                echo '<input type="number" name="new_corp_return_percent" style="border-color: red;" />';
                delete_transient('new_corp_id');
            } else {
                $new_corp_return_percent_v = get_transient('new_corp_return_percent_v');
                echo '<input type="number" name="new_corp_return_percent" value="' . $new_corp_return_percent_v . '"/>';
            }
            
            echo '<p>How many top ratters should get rewards</p>';
            if (($value = get_transient('new_corp_top_ratter_count')) != null) {
                echo '<input type="number" name="new_corp_top_ratter_count" style="border-color: red;" />';
                delete_transient('new_corp_top_ratter_count');
            } else {
                $new_corp_top_ratter_count_v = get_transient('new_corp_top_ratter_count_v');
                echo '<input type="number" name="new_corp_top_ratter_count" value="' . $new_corp_top_ratter_count_v . '"/>';
            }
            
            echo '<br><input type="submit" value="ADD" />';
            
            echo '</div>';
            echo '</form>';
        } else {
            // give option to delete the credentials
            
            /*
             * @TODO MAKE THIS TABLE VERTICAL INSTEAD OF HORIZONTAL 35eye5urrey5yet4y54etwe
             */
            
            echo '<br><h3> Delete/Edit existing Third party SSO Credentials</h3>';
            
            echo '<table>';
            echo '<tr>';
            echo '<th></th>';
            echo '<th></th>';
            echo '<th>Client ID</th>';
            echo '<th>Client secret</th>';
            // echo'<th></th>';
            echo '<th>Corporation ID</th>';
            echo '<th>Callback URL</th>';
            echo '<th>% of taxes</th>';
            echo '<th>Top chars</th>';
            echo '<th></th>';
            echo '</tr>';
            
            // Delete button
            echo '<tr>';
            echo '<form action="' . get_admin_url() . 'admin-post.php" method="post">';
            echo '<input type="hidden" name="action" value="SSO_action" />';
            echo '<input type="hidden" name="delete_credentials_id" value="' . $credentials[id] . '" />';
            echo '<td><input type="submit" value="Delete" /></td>';
            echo '</form>';
            
            // edit button
            echo '<form action="' . get_admin_url() . 'admin-post.php" method="post">';
            echo '<input type="hidden" name="action" value="SSO_action" />';
            echo '<input type="hidden" name="edit_credentials_id" value="' . $credentials[id] . '" />';
            echo '<td><input type="submit" value="Update" /></td>';
            
            /*
             * Table contents
             */
            
            // client ID
            if (($value = get_transient('edit_client_id')) != null) {
                echo '<td><input type="text" name="edit_client_id" style="border-color: red;"/></td>';
                delete_transient('edit_client_id');
            } else {
                echo '<td><input type="text" name="edit_client_id" value="' . $credentials[client_id] . '" /></td>';
            }
            
            // Client Secret
            if (($value = get_transient('edit_secret')) != null) {
                echo '<td><input type="text" name="edit_secret" style="border-color: red;"/></td>';
                delete_transient('edit_secret');
            } else {
                echo '<td><input type="text" name="edit_secret" value="' . $credentials[client_secret] . '" /></td>';
            }
            
            // corporation id is changed programmaticaly according to SSO api
            echo '<td><input type="number" value="' . $credentials[corporation_id] . '" /></td>';
            
            // callback url
            if (($value = get_transient('edit_callback_url')) != null) {
                echo '<td><input type="text" name="edit_callback_url" style="border-color: red;"/></td>';
                delete_transient('edit_callback_url');
            } else {
                echo '<td><input type="text" name="edit_callback_url" value="' . $credentials[redirect_url] . '" /></td>';
                ;
            }
            
            // percent return of taxes
            if (($value = get_transient('edit_corp_return_percent')) != null) {
                echo '<td><input type="text" name="edit_corp_return_percent" style="border-color: red; width: 55px;"/></td>';
                delete_transient('edit_corp_return_percent');
            } else {
                echo '<td><input type="number" name="edit_corp_return_percent" value="' . $credentials[corp_return_percent] . '" style="width: 55px;" /></td>';
            }
            
            // top ratter count
            // percent return of taxes
            if (($value = get_transient('edit_corp_top_ratter_count')) != null) {
                echo '<td><input type="text" name="edit_corp_top_ratter_count" style="border-color: red; width: 55px;"/></td>';
                delete_transient('edit_corp_top_ratter_count');
            } else {
                echo '<td><input type="number" name="edit_corp_top_ratter_count" value="' . $credentials[corp_top_ratter_count] . '" style="width: 55px;" /></td>';
            }
            
            // checkbox for showing top 5 pvp table.
            $checked = '';
            if ($credentials[show_top5_pvp] == 1) {
                $checked = 'checked';
            }
            
            echo '<td><input type="checkbox" name="edit_show_top5_pvp" value="1" ' . $checked . '>Top 5 PVP characters</td>';
            // check for chart 1 controls
            $checked1 = '';
            if ($credentials[show_chart1] == 1) {
                $checked1 = 'checked';
            }
            echo '<td><input type="checkbox" name="edit_show_chart1" value="1" ' . $checked1 . '>Chart1</td>';
            // check for chart 2 controls
            $checked2 = '';
            if ($credentials[show_chart1] == 1) {
                $checked2 = 'checked';
            }
            echo '<td><input type="checkbox" name="edit_show_chart2" value="1" ' . $checked2 . '>Chart2</td>';
            // check for edit_show_npc_kills_by_systems_total controls
            $checked3 = '';
            if ($credentials[show_npc_kills_by_systems_total] == 1) {
                $checked3 = 'checked';
            }
            echo '<td><input type="checkbox" name="edit_show_npc_kills_by_systems_total" value="1" ' . $checked3 . '>Ratting systems</td>';
            echo '<td></td>';
            echo '</form>';
            
            echo '</tr>';
            
            echo '</table>';
        }
    }

    /**
     * Prepares html code with image and url for SSO login redirect
     *
     * http://eveonline-third-party-documentation.readthedocs.io/en/latest/crest/authentication.html
     *
     * @return $r html string
     */
    public function render_sso_login_stuff()
    {
        if (is_user_logged_in()) {
            global $wpdb;
            
            $sql = "SELECT * FROM `" . $wpdb->prefix . "tr_sso_credentials`";
            $credentials = $wpdb->get_row("$sql", ARRAY_A);
            
            if ($credentials) {
                $r .= '<form action="' . get_admin_url() . 'admin-post.php" method="post">';
                $r .= '<input type="hidden" name="action" value="SSO_action" />';
                $r .= '<input type="hidden" name="SSO_LOGIN_REDIRECT_1" value="true" />';
                $r .= '<input type="image" src="' . plugin_dir_url(dirname(__FILE__)) . 'img/EVE_SSO_Login_Buttons_Large_Black.png" border="0" alt="Submit" />';
                $r .= '</form>';
            } else {
                $r .= 'Please configure SSO application credentials under "Top Ratter" in administration menu.';
            }
            return $r;
        }
    }

    /**
     * Prepares html code to display all the tokens related chars for the selected user.
     *
     * User can choose to delete the token, but then the character becomes whos token is deleted becomes unassigned.
     *
     * @return $r html string
     */
    public function render_user_sso_token_mgmt()
    {
        /*
         * Prepare submit handle that will unassign the deleted char and token.
         */
        global $wpdb;
        $current_user = wp_get_current_user();
        
        $sql = "SELECT * FROM `" . $wpdb->prefix . "tr_users_chars` 
                JOIN " . $wpdb->prefix . "tr_characters ON " . $wpdb->prefix . "tr_users_chars.char_id=" . $wpdb->prefix . "tr_characters.id
                WHERE user_id='$current_user->ID';";
        $all_user_chars = $wpdb->get_results("$sql", ARRAY_A);
        if ($all_user_chars) {
            $r .= '<br><p> Your currently linked characters:</p>';
            $r .= '<table>';
            
            foreach ($all_user_chars as $character) {
                $r .= '<tr>';
                $r .= '<td>';
                
                $r .= '<form action="' . get_admin_url() . 'admin-post.php" method="post">';
                $r .= '<input type="hidden" name="action" value="SSO_action" />';
                $r .= '<input type="hidden" name="unlink_characteracter" value="' . $character['char_id'] . '" />';
                $r .= '<input type="submit" value="Unlink" /> ';
                $r .= $character['ownerName2'];
                $r .= '</form>';
                
                $r .= '</td>';
                $r .= '</tr>';
            }
            
            $r .= '</table>';
        }
        
        return $r;
    }

    /**
     * Renders page where sso callback is recieved and processed.
     *
     * @return void
     */
    public function render_sso_callback()
    {
        global $wpdb;
        
        if (isset($_GET['state'])) {
            
            // check if the stste is legit
            $state = $_GET['state'];
            // select state from database to see if it is a legit state that we requested.
            $sql = "SELECT * FROM `" . $wpdb->prefix . "tr_sso_auth_state` WHERE state='$state';";
            $statequery = $wpdb->get_results("$sql", ARRAY_A);
            
            if ($statequery) {
                // echo'we found records in database is legit answer';
                if (isset($_GET['code'])) {
                    $auth_code = $_GET['code'];
                    
                    // remove the database reords of this state so it can not be used anymore
                    $where = array(
                        'state' => $state
                    );
                    $wpdb->delete($wpdb->prefix . 'tr_sso_auth_state', $where);
                    
                    // do a curl call to exchange auth code with token.
                    $server_response = $this->basic_curl_eve_sso_auth_token($auth_code);
                    
                    date_default_timezone_set('UTC');
                    $laiks_tagad = date("Y-m-d H:i:s");
                    
                    // check if the token was submited by admin.
                    if ($state == "SSSStateClient_23egy34DFHJ5y") {
                        
                        $sql = "SELECT * FROM `" . $wpdb->prefix . "tr_sso_credentials`;";
                        $statequery = $wpdb->get_row("$sql", ARRAY_A);
                        if ($statequery['corporation_id'] == '') {
                            
                            $public_char_data = $this->token_to_char_id($server_response['access_token']);
                            $char_corp = $this->char_id_to_corp_id($public_char_data['CharacterID']);
                            
                            // update admin corp id.
                            $data = array(
                                'corporation_id' => $char_corp['corporation_id']
                            );
                            $where = array(
                                'id' => $statequery['id']
                            );
                            
                            $wpdb->update($wpdb->prefix . 'tr_sso_credentials', $data, $where);
                        }
                    }
                    
                    /*
                     * Call ESI api and check for public data.
                     */
                    $is_member = $this->ESI_api_background_check($server_response);
                    
                    if ($is_member['is_member'] == false) {
                        echo '<p>Well... you logged in, but you are not part of the corporation. so.... SPAI?</p>';
                        
                        // try to attach the character to current user.
                        $character_id = $this->character_atachment_to_user_mgmt($is_member);
                        
                        $data = array(
                            'access_token' => $server_response['access_token'],
                            'token_type' => $server_response['token_type'],
                            'expires_in' => $server_response['expires_in'],
                            'refresh_token' => $server_response['refresh_token'],
                            'ts' => $laiks_tagad,
                            'character_id' => $character_id,
                            'oficer_token' => 0
                        );
                        $wpdb->insert($wpdb->prefix . 'tr_sso_tokens', $data);
                    } else {
                        /*
                         * returns this if not false
                         * "{"CharacterID":92858642,"CharacterName":"Judge07","ExpiresOn":"2017-11-26T18:26:02","Scopes":"publicData","TokenType":"Character","CharacterOwnerHash":"z7rhzNy7ry+/TUYOegNzDF62/Cw=","IntellectualProperty":"EVE"}"
                         */
                        
                        // try to attach the character to current user.
                        $character_id = $this->character_atachment_to_user_mgmt($is_member);
                        
                        $officer_token = 0;
                        $is_officer = $this->is_character_director($is_member['CharacterID'], $server_response['access_token']);
                        
                        if ($is_officer) {
                            $officer_token = 1;
                        }
                        
                        if ($character_id == null) {
                            return;
                        }
                        
                        $data = array(
                            'access_token' => $server_response['access_token'],
                            'token_type' => $server_response['token_type'],
                            'expires_in' => $server_response['expires_in'],
                            'refresh_token' => $server_response['refresh_token'],
                            'ts' => $laiks_tagad,
                            'character_id' => $character_id,
                            'oficer_token' => $officer_token
                        );
                        $wpdb->insert($wpdb->prefix . 'tr_sso_tokens', $data);
                        
                        // echo'<p>Looks like the token was added successfuly.</p>';
                    }
                }
            } else {
                echo '<p>Token already acquired.</p>';
            }
        } else {
            // login failed try again or contact officer.
            echo '<p>Login failed! Try again or contact officer.</p>';
            set_site_transient('SSO_login_failed_notice', 'Sorry, SSO login failed.', 60 * 3);
        }
        
        if (isset($_SERVER['HTTPS'])) {
            $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
        } else {
            $protocol = 'http';
        }
        $callback_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . '/';
        echo '<br><a href="' . $callback_url . '"><button type="button">Back to begining</button></a>';
    }

    /**
     * Curl the EVE SSO auth to token exchange url with freshly acquired auth code
     *
     * inspiration from
     * http://codegist.net/snippet/php/basic_eve_ssophp_thejokr_php
     *
     * @param $auth_code authentication
     *            code recieved in previous steps
     *            
     * @return $server_output
     */
    public function basic_curl_eve_sso_auth_token($auth_code)
    {
        global $wpdb;
        
        $sql = "SELECT * FROM `" . $wpdb->prefix . "tr_sso_credentials`;";
        $credentials = $wpdb->get_row("$sql", ARRAY_A);
        
        $client_id = $credentials['client_id'];
        $client_secret = $credentials['client_secret'];
        
        $ssl = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === "on";
        
        $header = [
            "Authorization: Basic " . base64_encode("$client_id:$client_secret")
        ];
        $postdata = [
            'grant_type' => "authorization_code",
            'code' => $auth_code
        ];
        
        $curlhandle = curl_init("https://login.eveonline.com/oauth/token");
        curl_setopt($curlhandle, CURLOPT_USERAGENT, "Basic-EVE-SSO Agent");
        curl_setopt($curlhandle, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curlhandle, CURLOPT_POST, count($postdata));
        curl_setopt($curlhandle, CURLOPT_POSTFIELDS, http_build_query($postdata));
        curl_setopt($curlhandle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curlhandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlhandle, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($curlhandle);
        curl_close($curlhandle);
        
        if ($res === false) {
            exit("Error: No valid response");
        }
        $res = json_decode($res, true);
        return $res;
    }

    /**
     * Curl the EVE verify link to find out who this token belongs to
     *
     * @param $token token
     *            from db
     *            
     * @return $res
     */
    public function token_to_char_id($token)
    {
        global $wpdb;
        $ssl = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === "on";
        
        $header = [
            "Authorization: Bearer " . $token
        ];
        
        $curlhandle = curl_init("https://login.eveonline.com/oauth/verify");
        curl_setopt($curlhandle, CURLOPT_USERAGENT, "Basic-EVE-SSO Agent");
        curl_setopt($curlhandle, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curlhandle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curlhandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlhandle, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($curlhandle);
        curl_close($curlhandle);
        if ($res === false) {
            exit("Error: No valid response");
        }
        $res = json_decode($res, true);
        return $res;
    }

    /**
     * Exectues Curl call to ESI api to check if the character is director within the corporation.
     *
     * @param $owner_id int
     *            api character owner id from table kur_tr_characters
     * @param $token string
     *            valid working token.
     *            
     * @return bolean
     */
    public function is_character_director($owner_id, $token)
    {
        
        /*
         * https://esi.tech.ccp.is/latest/?datasource=tranquility#!/Character/get_characters_character_id_roles
         *
         *
         */
        global $wpdb;
        
        $header = array(
            "Authorization: Bearer " . $token
        );
        /*
         * base url for esi
         * https://esi.tech.ccp.is/latest/
         *
         * /v1/characters/{character_id}/roles/
         *
         * replace v1 with the base url.
         *
         * calls between the ESI requests has to be made with delay since it can not update on server that quick
         */
        
        $curlhandle = curl_init("https://esi.tech.ccp.is/latest/characters/$owner_id/roles/");
        curl_setopt($curlhandle, CURLOPT_USERAGENT, "Basic-EVE-SSO Agent");
        curl_setopt($curlhandle, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curlhandle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curlhandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlhandle, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($curlhandle);
        curl_close($curlhandle);
        if ($res === false) {
            exit("Error: No valid response");
        }
        
        $res = json_decode($res, true);
        
        if ($res) {
            
            if ($res['error']) {
                return false;
            }
            
            foreach ($res['roles'] as $role) {
                if ($role == 'Director') {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Curl the ESI endpoint to get corp id for the character id
     *
     * @param
     *            $char_id
     *            
     * @return $res
     */
    public function char_id_to_corp_id($char_id)
    {
        global $wpdb;
        $curlhandle = curl_init("https://esi.tech.ccp.is/latest/characters/$char_id/?datasource=tranquility");
        curl_setopt($curlhandle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curlhandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlhandle, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($curlhandle);
        curl_close($curlhandle);
        if ($res === false) {
            exit("Error: No valid response");
        }
        $res = json_decode($res, true);
        return $res;
    }

    /**
     * Returns valid token for char id from table
     *
     * Refreshes the token if token is expired and returns token.
     * if token is not valid then unlink characters and / or delte token.
     *
     * @param $char_id int
     *            id of the character from tr_characters table, NOT OWNER ID
     *            
     * @return $token string valid token to be used in api calls
     */
    public function get_fresh_token_by_char_id($char_id)
    {
        global $wpdb;
        date_default_timezone_set('UTC');
        $laiks_tagad = date("Y-m-d H:i:s");
        $user = wp_get_current_user();
        
        // get current token
        $sql = "SELECT * FROM `" . $wpdb->prefix . "tr_sso_tokens` WHERE character_id='$char_id';";
        $token_data = $wpdb->get_row("$sql", ARRAY_A);
        
        $exp_seconds = $token_data['expires_in'] - 10;
        $token_expiration = date('Y-m-d H:i:s', strtotime('+' . $exp_seconds . ' seconds', strtotime($token_data['ts'])));
        // check expiry for token
        if ($laiks_tagad > $token_expiration) {
            // token expired need to refresh
            
            $res = $this->refresh_token($token_data['refresh_token']);
            
            if ($res['error']) {
                // there was a problem with token refreshing.
                if ($res['error'] == 'invalid_grant') {
                    // echo'TRANSIENT- invalid grant:'.$res['error_description'];
                    set_site_transient('SSO_TOKEN_REFRESH_ERROR_eve', 'Invalid grant:' . $res['error_description'], 60 * 5);
                    
                    // unlink character
                    $wpdb->delete($wpdb->prefix . 'tr_users_chars', array(
                        'user_id' => $user->ID,
                        'char_id' => $token_data['character_id']
                    ));
                    
                    // delete the token.
                    $wpdb->delete($wpdb->prefix . 'tr_sso_tokens', array(
                        'character_id' => $token_data['character_id']
                    ));
                } else {
                    $sql = "SELECT * FROM `" . $wpdb->prefix . "tr_characters` WHERE id='$char_id';";
                    $char_data = $wpdb->get_row("$sql", ARRAY_A);
                    // echo'TRANSIENT- Problem with your character "'.$char_data['ownerName2'].'" token! Unlink the character and try linking it again. ERROR:'.$res['error_description'];
                    set_site_transient('SSO_TOKEN_REFRESH_ERROR_eve', 'Problem with your character "' . $char_data['ownerName2'] . '" token! Unlink the character and try linking it again. ERROR:' . $res['error_description'], 60 * 5);
                    
                    /*
                     * SET character corporation to 0 since token could not be refreshed and this char should not have access to the data.
                     *
                     * F_ID:nullthecharactercorpremoveaccessrights2435456
                     */
                    $data = array(
                        'corp_id' => 0
                    );
                    $where = array(
                        'id' => $char_id
                    );
                    $wpdb->update($wpdb->prefix . "tr_characters", $data, $where);
                }
                return null;
            } else {
                
                // get owner id
                $sql = "SELECT * FROM `" . $wpdb->prefix . "tr_characters` WHERE id='$char_id';";
                $owner_data = $wpdb->get_row("$sql", ARRAY_A);
                
                $is_officer = $this->is_character_director($owner_data['owner_id'], $res['access_token']);
                
                $officer_token = 0;
                
                if ($is_officer) {
                    $officer_token = 1;
                }
                
                // update the database table with refreshed token.
                $data = array(
                    'access_token' => $res['access_token'],
                    'refresh_token' => $res['refresh_token'],
                    'expires_in' => $res['expires_in'],
                    'ts' => $laiks_tagad,
                    'oficer_token' => $officer_token
                );
                
                $where = array(
                    'id' => $token_data['id']
                );
                $wpdb->update($wpdb->prefix . "tr_sso_tokens", $data, $where);
                
                return $res['access_token'];
            }
        } else {
            // echo 'token fresh';
            return $token_data['access_token'];
        }
        // if there is no char or some kind of error.
        return null;
    }

    /**
     * Returns response of a curl call on token refresh atempt
     *
     * @param $refresh_token string
     *            refresh token from table
     *            
     * @return $token_array array of token data response
     */
    public function refresh_token($refresh_token)
    {
        global $wpdb;
        
        $sql = "SELECT * FROM `" . $wpdb->prefix . "tr_sso_credentials`;";
        $credentials = $wpdb->get_row("$sql", ARRAY_A);
        
        $client_id = $credentials['client_id'];
        $client_secret = $credentials['client_secret'];
        
        $header = [
            "Authorization: Basic " . base64_encode("$client_id:$client_secret")
        ];
        $postdata = [
            'grant_type' => "refresh_token",
            'refresh_token' => $refresh_token
        ];
        
        $curlhandle = curl_init("https://login.eveonline.com/oauth/token");
        curl_setopt($curlhandle, CURLOPT_USERAGENT, "Basic-EVE-SSO Agent");
        curl_setopt($curlhandle, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curlhandle, CURLOPT_POST, count($postdata));
        curl_setopt($curlhandle, CURLOPT_POSTFIELDS, http_build_query($postdata));
        curl_setopt($curlhandle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curlhandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlhandle, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($curlhandle);
        curl_close($curlhandle);
        
        if ($res === false) {
            exit("Error: No valid response");
        }
        $token_array = json_decode($res, true);
        
        return $token_array;
    }

    /**
     * Returns response of a curl call on charid to char name
     *
     * @param $input_array char
     *            id
     *            
     * @return $output_array array of char id- name
     */
    public function owner_ids_to_names($input_array)
    {
        $char_ids;
        $count = count($input_array);
        $i = 1;
        
        foreach ($input_array as $id) {
            if ($i == $count) {
                $char_ids .= $id;
            } else {
                $char_ids .= $id . '%2C';
            }
            $i ++;
        }
        
        $curlhandle = curl_init("https://esi.tech.ccp.is/latest/characters/names/?character_ids=$char_ids&datasource=tranquility");
        curl_setopt($curlhandle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curlhandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlhandle, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($curlhandle);
        curl_close($curlhandle);
        if ($res === false) {
            exit("Error: No valid response");
        }
        $output_array = json_decode($res, true);
        return $output_array;
    }

    /**
     * Wrapper function for calling ESI API endpoints and checking if user is in right corporation.
     *
     * @param $data Array
     *            associative array containing response of the eve token exchange result.
     *            
     * @return null
     */
    public function ESI_api_background_check($data)
    {
        /*
         * Call ESI api and get public data to check if this character belongs to the corporation that is running the website.
         * https://developers.eveonline.com/blog/article/sso-to-authenticated-calls
         * #1 check who the token belongs to https://login.eveonline.com/oauth/verify
         *
         */
        global $wpdb;
        $public_char_data = $this->token_to_char_id($data['access_token']);
        /*
         * returns this
         * "{"CharacterID":92858642,"CharacterName":"Judge07","ExpiresOn":"2017-11-26T18:26:02","Scopes":"publicData","TokenType":"Character","CharacterOwnerHash":"z7rhzNy7ry+/TUYOegNzDF62/Cw=","IntellectualProperty":"EVE"}"
         */
        
        $char_name = $public_char_data['CharacterName'];
        
        // get the corporation id by curling this url https://esi.tech.ccp.is/latest/characters/92858642/?datasource=tranquility
        
        $char_corp = $this->char_id_to_corp_id($public_char_data['CharacterID']);
        /*
         * returns
         * { ["corporation_id"]=> int(98342863) ["birthday"]=> string(20) "2013-01-15T06:55:56Z" ["name"]=> string(7) "Judge07" ["gender"]=> string(4) "male" ["race_id"]=> int(1) ["bloodline_id"]=> int(11) ["description"]=> string(180) "" ["alliance_id"]=> int(1042504553) ["ancestry_id"]=> int(33) ["security_status"]=> float(-0.29927965578075) }
         */
        
        /*
         * check if the corp id matches.
         */
        $sql = "SELECT * FROM `" . $wpdb->prefix . "tr_sso_credentials`;";
        $statequery = $wpdb->get_row("$sql", ARRAY_A);
        
        if ($statequery['corporation_id'] == $char_corp['corporation_id']) {
            // is member of same corp
            $public_char_data['is_member'] = true;
        } else {
            // not
            $public_char_data['is_member'] = false;
        }
        
        return $public_char_data;
    }

    /**
     * Character relation checking function.
     *
     * @param $char_pub_data Array
     *            associative array containing data from character public data api.
     *            
     * @return $character_id int character_id from tr_characters
     */
    public function character_atachment_to_user_mgmt($char_pub_data)
    {
        global $wpdb;
        
        /*
         * returns this if not false
         * "{"CharacterID":92858642,"CharacterName":"Judge07","ExpiresOn":"2017-11-26T18:26:02","Scopes":"publicData","TokenType":"Character","CharacterOwnerHash":"z7rhzNy7ry+/TUYOegNzDF62/Cw=","IntellectualProperty":"EVE"}"
         */
        
        $sql = "SELECT * FROM `" . $wpdb->prefix . "tr_characters` WHERE owner_id='" . $char_pub_data['CharacterID'] . "';";
        $character_exist = $wpdb->get_row("$sql", ARRAY_A);
        
        $char_corp = $this->char_id_to_corp_id($char_pub_data['CharacterID']);
        
        if ($character_exist) {
            
            // check if this character is atached to some user
            $sql = "SELECT * FROM `" . $wpdb->prefix . "tr_users_chars` WHERE char_id='" . $character_exist['id'] . "';";
            $char_atachment = $wpdb->get_row("$sql", ARRAY_A);
            
            if ($char_atachment) {
                // get current user object
                $user = wp_get_current_user();
                $user->ID;
                if ($char_atachment['user_id'] == $user->ID) {
                    // if is atached to this user -> already atached.
                    Echo '<p> Character "' . $char_pub_data['CharacterName'] . '" is already attached to current user</p>';
                    $character_id = $character_exist['id'];
                } else {
                    // get user login from user id.
                    $user_owner = get_userdata($char_atachment['user_id']);
                    
                    Echo '<p> Could not attach character "' . $char_pub_data['CharacterName'] . '", because it is already attached to user: <b>' . $user_owner->user_login . '</b> </p>';
                    $character_id = null;
                }
            } else {
                // link this character to active user.
                $this->attach_character_to_user_SSO($character_exist['id']);
                $character_id = $character_exist['id'];
                /*
                 * update the character corporation id as well to reflect the access to the site.
                 */
                $data = array(
                    'corp_id' => $char_corp['corporation_id']
                );
                $where = array(
                    'owner_id' => $char_pub_data['CharacterID']
                );
                $wpdb->update($wpdb->prefix . 'tr_characters', $data, $where);
                
                Echo '<p> Character "' . $char_pub_data['CharacterName'] . '" has been attached to current user.</p>';
            }
        } else {
            
            // add character to databse.
            $data = array(
                'owner_id' => $char_pub_data['CharacterID'],
                'ownerName2' => $char_pub_data['CharacterName'],
                'corp_id' => $char_corp['corporation_id']
            );
            $wpdb->insert($wpdb->prefix . 'tr_characters', $data);
            
            $character_id = $wpdb->insert_id;
            // link this character to active user.
            $this->attach_character_to_user_SSO($character_id);
            
            Echo '<p> Character "' . $char_pub_data['CharacterName'] . '" successfuly attached to current user.</p>';
        }
        return $character_id;
    }

    /**
     * Attaches character to the logged in user.
     * Also checks if this is main or alt character.
     *
     * @param $char_id int
     *            id of the character from tr_characters table, NOT OWNER ID
     *            
     * @return null
     */
    public function attach_character_to_user_SSO($char_id)
    {
        global $wpdb;
        $user = wp_get_current_user();
        
        // check if user has main chars.
        $sql = "SELECT * FROM `" . $wpdb->prefix . "tr_users_chars` WHERE user_id='" . $user->ID . "' AND is_main_char='1';";
        $main_char_exist = $wpdb->get_row("$sql", ARRAY_A);
        
        if ($main_char_exist) {
            // add as slave
            $data = array(
                'user_id' => $user->ID,
                'char_id' => $char_id,
                'is_main_char' => 0
            );
            $wpdb->insert($wpdb->prefix . 'tr_users_chars', $data);
        } else {
            // add as main char
            $data = array(
                'user_id' => $user->ID,
                'char_id' => $char_id,
                'is_main_char' => 1
            );
            $wpdb->insert($wpdb->prefix . 'tr_users_chars', $data);
        }
    }

    /**
     * Function is hooked to login and checks if the user has access to the site
     *
     * function loops trough all user linked characters and checks character API
     * if the character is still in corporation do nothing.
     * if the token is bad or character not in corporation unlink the character and delete the token.
     * 
     * @todo do not unlink character since applicants can apply here
     *      
     * @return void
     */
    public function user_login_token_check()
    {
        global $wpdb;
//         $user = get_userdatabylogin($login);
        $current_user = wp_get_current_user();
        $current_user->ID;
        
        $sql = "SELECT * FROM `" . $wpdb->prefix . "tr_users_chars`
                JOIN " . $wpdb->prefix . "tr_characters ON " . $wpdb->prefix . "tr_users_chars.char_id=" . $wpdb->prefix . "tr_characters.id
                JOIN " . $wpdb->prefix . "tr_sso_tokens ON " . $wpdb->prefix . "tr_users_chars.char_id=" . $wpdb->prefix . "tr_sso_tokens.character_id
                WHERE user_id='$current_user->ID';";
        $users_chars = $wpdb->get_results("$sql", ARRAY_A);
        
        if ($users_chars) {
            foreach ($users_chars as $character) {
                // get fresh token and set corp 0 if there is problem.
                $token = $this->get_fresh_token_by_char_id($character['char_id']);
                
                // there is valid token
                if ($token) {
                    // check if it is still in the corporation
                    $char_corp = $this->char_id_to_corp_id($character['owner_id']);
                    /*
                     * simply update all character corporation ID's here
                     * F_ID:2435yg5y4hegr546heg5tefhgjkj
                     * 
                     * why is the hook not firing?
                     */
                    
                    $data = array(
                        'corp_id' => $char_corp['corporation_id']
                    );
                    $where = array(
                        'id' => $character['char_id']
                    );
                    $wpdb->update($wpdb->prefix . "tr_characters", $data, $where);
                }else{
                    //no valid token
                    $data = array(
                        'corp_id' => 0
                    );
                    $where = array(
                        'id' => $character['char_id']
                    );
                    $wpdb->update($wpdb->prefix . "tr_characters", $data, $where);
                }
            }
        }
    }

    /**
     * Main function to gather ratting data from new ESI api.
     *
     * does a api call to corporation wallet and gets the isk tax income.
     *
     * @return $temp_wallet_records array that contains all corp wallet transactions from last known entry to now.
     */
    public function esi_api_gather_ratted_isk_amount()
    {
        global $wpdb;
        
        // get all officer tokens
        $sql = "SELECT * FROM `" . $wpdb->prefix . "tr_sso_tokens` WHERE oficer_token='1';";
        $officer_tokens = $wpdb->get_results("$sql", ARRAY_A);
        
        $fresh_officer_token = null;
        if ($officer_tokens) {
            foreach ($officer_tokens as $token) {
                // check if can get fresh token,
                $fresh_token = $this->get_fresh_token_by_char_id($token['character_id']);
                
                if ($fresh_token) {
                    
                    $sql = "SELECT * FROM `" . $wpdb->prefix . "tr_characters` WHERE id='" . $token['character_id'] . "';";
                    $officer_character = $wpdb->get_row("$sql", ARRAY_A);
                    
                    // check if token belongs to director.
                    $is_director = $this->is_character_director($officer_character['owner_id'], $fresh_token);
                    
                    if ($is_director) {
                        // get token and exit the loop.
                        $fresh_officer_token = $fresh_token;
                        // $fresh_officer_token=$token;
                        break;
                    }
                }
            }
        } else {
            
            /*
             * There are no officer tokens make this noticable so that it can be fixed.
             */
            return 'no_officer_token';
        }
        
        if ($fresh_officer_token) {
            
            // get officer corporation id.
            $sql = "SELECT * FROM `" . $wpdb->prefix . "tr_sso_credentials`;";
            $corporation_credentials = $wpdb->get_row("$sql", ARRAY_A);
            $corp_id = $corporation_credentials['corporation_id'];
            
            // select the highest value from database
            $sql = "SELECT * FROM `" . $wpdb->prefix . "tr_ratting_data`  ORDER BY `ref_id` DESC;";
            $highest_ref = $wpdb->get_row("$sql", ARRAY_A);
            
            $killswitch = 0;
            $temp_wallet_records = array();
            $debug_counter = 0;
            $min_ref_id = null;
            do {
                $corporation_journal_data = $this->corp_wallet_journal_API_call($fresh_officer_token, $corp_id, $min_ref_id);
                // find min value from the response api
                if ($corporation_journal_data) {
                    // assign initial value.
                    $min_ref_id = $corporation_journal_data[0]['ref_id'];
                    foreach ($corporation_journal_data as $api_response) {
                        if ($api_response['ref_id'] < $min_ref_id) {
                            $min_ref_id = $api_response['ref_id'];
                        }
                        // add them to the same level array
                        $temp_wallet_records[] = $api_response;
                    }
                }
                
                // a kill switch condition
                if ($highest_ref['ref_id'] >= $min_ref_id) {
                    $killswitch = 1;
                }
                
                // debug kill just in case it runs wild or empty db scenario
                if ($debug_counter == 10) {
                    $killswitch = 1;
                }
                $debug_counter ++;
            } while ($killswitch == 0);
            
            return $temp_wallet_records;
        }
        return null;
    }

    /**
     * Executes API call to corporation wallet endpoint
     *
     * @param $fresh_officer_token string
     *            a valid officer token
     * @param $corp_id int
     *            corporation id.
     * @param $ref_id float
     *            reference id that is used to read older records of API
     *            
     * @return $corporation_journal_data Array that contains single API call response
     */
    public function corp_wallet_journal_API_call($fresh_officer_token, $corp_id, $ref_id = null)
    {
        $append_ref_id = '';
        
        if ($ref_id != null) {
            $append_ref_id = "&from_id=$ref_id";
        }
        
        $header = array(
            "Authorization: Bearer " . $fresh_officer_token
        );
        $curlhandle = curl_init("https://esi.tech.ccp.is/latest/corporations/$corp_id/wallets/1/journal/?datasource=tranquility$append_ref_id");
        curl_setopt($curlhandle, CURLOPT_USERAGENT, "Basic-EVE-SSO Agent");
        curl_setopt($curlhandle, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curlhandle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curlhandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlhandle, CURLOPT_RETURNTRANSFER, true);
        
        $res = curl_exec($curlhandle);
        curl_close($curlhandle);
        if ($res === false) {
            exit("Error: No valid response");
        }
        $res = json_decode($res, true);
        
        return $res;
    }
}
?>