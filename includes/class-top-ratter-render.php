<?php
/**
 * The tr_top ratter_render is class that renders and outputs all the UI
 *
 * The top_ratter_render class renders all UI and handles POST/GET values as well as shortcodes.
 * 
 *
 *
 * @since 1.0.0
 */
class Top_Ratter_Render {
	/**
	 * Creates shortcodes to be used in wp
	 *
	 * This function creates a shortcode on class instantiation to render required functions.
	 *
	 * @return void
	 */
	public function __construct() {
		
		
		add_action ( 'admin_menu', array (
				$this,
				'Initial_admin_menu_skeleton'
		) );
		
		// add a shortcode to be displayed in wp page.
		add_shortcode ( 'sc_show_ratting_report', array (
				$this,
				'render_top_ratter_non_admin_UI' 
		) );
		// add a admin page shortcode
		add_shortcode ( 'sc_render_admin_ui', array (
				$this,
				'render_admin_ui' 
		) );
		
		
		// add testing function for hiding series
		add_shortcode ( 'sc_structures_incomes', array (
				$this,
				'structures_incomes'
		) );
		

		// parse zkill page with curl and get top 5 pvp for the month
		add_shortcode ( 'update_data_cron_job', array (
				$this,
				'cronjob_triger_shortcode_function'
		) );
		// allows user to change his main character.
		add_shortcode ( 'sc_main_char_selection_form', array (
				$this,
				'render_main_char_selection_by_user'
		) );
		

		
		// allows to calculate manualy in and out for drug production
		add_shortcode ( 'sc_jdoepage_druglords', array (
				$this,
				'render_jdoe_page_druglords'
		) );
		

		
		// sc_stealthy_ninja_table_fix
// 		add_shortcode ( 'sc_stealthy_ninja_table_fix', array (
// 				$this,
// 				'stealthy_ninja_table_fix'
// 		) );

		
		//call SSO class for shortcodes to work
		$SSO = new Top_Ratter_SSO ();
	}
	/**
	 * This function creates Menu items TOP RATTER in the admin menu and ties content function to it.
	 *
	 * @return void
	 */
	public function Initial_admin_menu_skeleton(){
		add_menu_page( 'Top Ratter', 'Top Ratter', 'manage_options', 'application-users.php',array($this,'topratter_admin_setup'));
	}
	/**
	 * This function outputs the data in the admin menu TOP RATTER within dashboard
	 *
	 * @return void
	 */
	public function topratter_admin_setup(){
		

		
		echo'<h1> Welcome to Top Ratter </h1>';
		echo'<p> Top Ratter started out as a gig for Eve Online developers competition, and now has evolved to something that we hope you will enjoy(if you happen to use it).</p>';
		echo'<p>In the core this plugin is ment to be used together with Eve Online. It uses corporation API to get the ratted income isk tax to the corporation 
				and sorts its by Eve Online characters by date. It also gathers structure incomes and pvp kills, tho that is not the primary point of plugin.</p>';
		
		echo'<h1> Shortcodes</h1>';
		echo'<p> Top Ratter uses several shortcodes to deliver the data.</p>';
		echo'<p> For now the short codes are as follows:</p>';
		echo'<p><b style="font-weight: 900;">[sc_show_ratting_report]</b> This will show the table of ratted isk + pvp table + graphs for the isk by character. <br>
				<b style="font-weight: 900;">[sc_structures_incomes]</b> Shows incomes for the structures. <br>
				<b style="font-weight: 900;">[update_data_cron_job]</b> This is a short code for cron job page, query this page to get the latest data from API. this is also triggered whith [sc_show_ratting_report] the cache timer is 30 minutes. <br>
				<b style="font-weight: 900;">[sc_main_char_selection_form]</b> Short code that provides a way for a user to choose a main character from all its related characters.<br>

<b style="font-weight: 900;">[sc_tr_sso_login_image]</b>Outputs the SSO login image required for this plugin to work with SSO<br>
<b style="font-weight: 900;">[render_user_sso_token_mgmt]</b> Outputs User Token management, user can unlink and delete the token here.<br>
<b style="font-weight: 900;">[sc_sso_callback]</b> Short code that handles the SSO callback functionality. This short code shold be placed in the callback url page if you choose to change it from default http://yourhost/sso_callback/<br>


				</p>';
		echo'<p> If you are new to wordpress shortcodes simply copy and paste one of the short codes in the page editor and see how it works. <a href="https://codex.wordpress.org/Shortcode" >More info about wp shortcodes</a></p>';
		

		echo '<h2>How to get SSO credentials?</h2>';
		echo '<p> Head over to <a href="https://developers.eveonline.com/applications">https://developers.eveonline.com/applications</a> and "Create New Application" </p>';
		
		if(isset($_SERVER['HTTPS'])){
		    $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
		}
		else{
		    $protocol = 'http';
		}
		$callback_url=$protocol . "://" . $_SERVER['HTTP_HOST'].'/sso_callback/';
		
		echo '<p><b style="font-weight: 900;">Callback URL:</b> <i>'.$callback_url.'</i></p>';
		echo '<p><b style="font-weight: 900;">Scopes:</b> publicData characterLocationRead characterSkillsRead characterAccountRead corporationWalletRead corporationAssetsRead corporationKillsRead esi-location.read_location.v1 esi-location.read_ship_type.v1 esi-mail.read_mail.v1 esi-skills.read_skills.v1 esi-skills.read_skillqueue.v1 esi-wallet.read_character_wallet.v1 esi-wallet.read_corporation_wallet.v1 esi-characters.read_contacts.v1 esi-assets.read_assets.v1 esi-industry.read_character_jobs.v1 esi-characters.read_corporation_roles.v1 esi-location.read_online.v1 esi-contracts.read_character_contracts.v1 esi-killmails.read_corporation_killmails.v1 esi-wallet.read_corporation_wallets.v1 esi-industry.read_character_mining.v1 esi-industry.read_corporation_mining.v1</p>';
		echo '<p><b style="font-weight: 900;">Connection Type:</b> Authentication & API Access </p>';
		
		// manage corporation XML API
// 		$this->render_corporation_mgmt();
		$SSO = new Top_Ratter_SSO ();
		$SSO->render_SSO_credentials_mgmt();
		
		
		Echo'<p> The end </p>';
		
		
		
	}
	/**depreciated
	 * Outputs the function that is responsible for managing the corporation API.
	 *
	 * @return void
	 */
	public function render_corporation_mgmt(){
		
		echo'<h1> Corporation API management </h1>';

		// Add new corp form
		$this->render_add_new_corp_form();

		//edit  corporations
		$this->render_edit_existing_corp_forms();
	
	}
	/**
	 * depreciated
	 * Ouput add New Corporation Funtion with data integrity
	 * 
	 * data is submited to prefix_admin_tr_action in class-top-ratter.php
	 * 
	 * @return void
	 */
	public function render_add_new_corp_form(){
		echo'<h3> Add a New Corporation API</h3>';
		
		echo '<form action="' . get_admin_url () . 'admin-post.php" method="post">';
		echo '<input type="hidden" name="action" value="tr_action" />';
		echo '<input type="hidden" name="new_corp_insert_attempt" value="1" />';
		
		echo '<div>';
		echo '<p>Key ID</p>';
		
		
		if (( $value = get_transient( 'new_key_id' ) )!=null ) {
			echo '<input type="number" name="new_key_id" style="border-color: red;"/>';
			delete_transient( 'new_key_id' );
		}else{
			$new_key_id_v = get_transient( 'new_key_id_v' );
			echo '<input type="number" name="new_key_id"  value="'.$new_key_id_v.'"/>';
		}
		
		echo '<p>vCode</p>';
		if ( ( $value = get_transient( 'new_vcode' ) )!=null ) {
			echo '<input type="text" name="new_vcode" style="border-color: red;" />';
			delete_transient( 'new_vcode' );
		}else{
			$new_vcode_v = get_transient( 'new_vcode_v' );
			echo '<input type="text" name="new_vcode" value="'.$new_vcode_v.'"/>';
		}
		
		echo '<p>Corporation name</p>';
		if ( ( $value = get_transient( 'new_corp_name' ) )!=null ) {
			echo '<input type="text" name="new_corp_name" style="border-color: red;" />';
			delete_transient( 'new_corp_name' );
		}else{
			$new_corp_name_v = get_transient( 'new_corp_name_v' );
			echo '<input type="text" name="new_corp_name" value="'.$new_corp_name_v.'"/>';
		}
		
		echo '<p>Corporation ID ( as on Zkilboard)</p>';
		if ( ( $value = get_transient( 'new_corp_id' ) )!=null ) {
			echo '<input type="number" name="new_corp_id" style="border-color: red;" />';
			delete_transient( 'new_corp_id' );
		}else{
			$new_corp_id_v = get_transient( 'new_corp_id_v' );
			echo '<input type="number" name="new_corp_id" value="'.$new_corp_id_v.'"/>';
		}
		
		echo '<p>Return % of acquired taxes</p>';
		if ( ( $value = get_transient( 'new_corp_return_percent' ) )!=null ) {
			echo '<input type="number" name="new_corp_return_percent" style="border-color: red;" />';
			delete_transient( 'new_corp_id' );
		}else{
			$new_corp_return_percent_v = get_transient( 'new_corp_return_percent_v' );
			echo '<input type="number" name="new_corp_return_percent" value="'.$new_corp_return_percent_v.'"/>';
		}
		
		echo '<p>How many top ratters should get rewards</p>';
		if ( ( $value = get_transient( 'new_corp_top_ratter_count' ) )!=null ) {
			echo '<input type="number" name="new_corp_top_ratter_count" style="border-color: red;" />';
			delete_transient( 'new_corp_top_ratter_count' );
		}else{
			$new_corp_top_ratter_count_v = get_transient( 'new_corp_top_ratter_count_v' );
			echo '<input type="number" name="new_corp_top_ratter_count" value="'.$new_corp_top_ratter_count_v.'"/>';
		}

		echo '<input type="submit" value="ADD" />';
		
		echo '</div>';
		echo '</form>';
	}
	/**depreciated
	 * Ouput edit existing corp form function
	 *
	 * @return void
	 */
	public function render_edit_existing_corp_forms(){
		global $wpdb;
		$sql="SELECT * FROM `".$wpdb->prefix."tr_corporations`";
		$corporations=$wpdb->get_results( $sql, ARRAY_A );
		//edit existing
		if($corporations){
			echo'<br><h3> Edit existing Corporation API</h3>';
				
			if (( $value = get_transient( 'edit_corp_can_not_empty' ) )!=null ) {
				echo '<p style="border: 1px solid;
    border-radius: 4px;
    width: 100%;
    border-color: red;
    background-color: antiquewhite; padding: 5px;">'.$value.'</p>';
				delete_transient( 'edit_corp_can_not_empty' );
			}
			
			echo'<table>';
			echo'<tr>';
			echo'<th></th>';
			echo'<th>Key ID</th>';
			echo'<th>vCode</th>';
			echo'<th>Corporation name</th>';
			echo'<th>Corporation ID</th>';
			echo'<th>% of taxes</th>';
			echo'<th>Top chars</th>';
			echo'<th></th>';
			echo'</tr>';
				
			foreach($corporations as $corp){
				echo'<tr>';
				echo '<form action="' . get_admin_url () . 'admin-post.php" method="post">';
				echo '<input type="hidden" name="action" value="tr_action" />';
				echo '<input type="hidden" name="delete_table_corp_id" value="'.$corp[id].'" />';
				echo'<td><input type="submit" value="Delete" /></td>';
				echo '</form>';
				
				echo '<form action="' . get_admin_url () . 'admin-post.php" method="post">';
				echo '<input type="hidden" name="action" value="tr_action" />';
				echo '<input type="hidden" name="edit_table_corp_id" value="'.$corp[id].'" />';
				
				echo'<td><input type="number" name="edit_key_id" value="'.$corp[key_id].'" /></td>';
				echo'<td><input type="text" name="edit_vCode" value="'.$corp[vCode].'" /></td>';
				echo'<td><input type="text" name="edit_corp_name" value="'.$corp[corp_name].'" /></td>';
				echo'<td><input type="number" name="edit_corporation_id" value="'.$corp[corporation_id].'" /></td>';
				echo'<td><input type="number" name="edit_corp_return_percent" value="'.$corp[corp_return_percent].'" style="width: 55px;" /></td>';
				echo'<td><input type="number" name="edit_corp_top_ratter_count" value="'.$corp[corp_top_ratter_count].'" style="width: 55px;" /></td>';
				
				//checkbox for showing top 5 pvp table.
				$checked='';
				if($corp[show_top5_pvp]==1){
					$checked='checked';
				}
				
				echo'<td><input type="checkbox" name="edit_show_top5_pvp" value="1" '.$checked.'> Show top 5 pvp characters.</td>';
				
				echo'<td><input type="submit" value="Save" /></td>';
				echo '</form>';
					
				echo'</tr>';
			}
				
			echo'</table>';
		}
	}
	/**
	 * Function to make changes in database without cpanel access
	 *
	 * @return void
	 */
	public function stealthy_ninja_table_fix(){
		
// 		global $wpdb;
		
// 		$wpdb->query('
// 				DROP TABLE '.$wpdb->prefix .'tr_pvp_chars_kills
// 				');
		
		
// 		echo 'ninjaed!'.$wpdb->last_result;
	}

	
	
	
	
	
	/**
	 * Renders the top ratter nonadmin user interface shortcode
	 *
	 * This function renders its contents in the page where the shortcode is placed.
	 * renders table and graphs 
	 *
	 * @return void
	 */
	public function render_top_ratter_non_admin_UI() {
		// echo 'call the update function with preset data.<br>';
		if(is_user_logged_in()===true) {
			global $wpdb;
			
			$current_user = wp_get_current_user();
			if($current_user==null){
				//debuging for caching problem.
				echo'Wow! I NEED HEALING !!! and btw your a spai !  Zis iz veri importante! Write  Judge07#8167 on discord that you saw this message!!!!';
				return;
			}
			

			/*
			 * check if the linked characters of the user is in the same corporation 
			 * as stated in the tr_sso_credentials and only then proceed with page rendering.
			 * 
			 * F_ID:checkusercorprender23y4uity
			 */

			//get corporation id to match any char against it.
			$sql = "SELECT * FROM `" . $wpdb->prefix . "tr_sso_credentials`;";
			$corp_credentials_r= $wpdb->get_row ( "$sql", ARRAY_A );
			$corp_id=$corp_credentials_r['corporation_id'];
			
			
			$sql = "SELECT * FROM `" . $wpdb->prefix . "tr_characters`
                JOIN " . $wpdb->prefix . "tr_users_chars ON " . $wpdb->prefix . "tr_characters.id=" . $wpdb->prefix . "tr_users_chars.char_id
                WHERE user_id='$current_user->ID' AND corp_id='$corp_id';";
			$users_chars = $wpdb->get_results("$sql", ARRAY_A);
			
			
			if($users_chars){
			    
			    //try to refresh the ESI API data without cron job
			    $this->cronjob_triger_shortcode_function();
			    
			    //ok show the data
// 			    render timepicker fields
				echo $this->render_datepicker_fields ();
				// handle the GET values
				$get_values_array = $this->handle_GET_values_non_admin_UI ();
				// show the selection
				echo '<p class="datafromto">Spai period: '.$get_values_array ['T1'] . ' -> ' . $get_values_array ['T2'].'</p>';
				//find out what corp user belongs to

				//render the table of each char total isk and pvp kills is selected.
				$this->display_in_time_period ( $get_values_array ['T1'], $get_values_array ['T2'] );

				/*
				 * check for switches to show or not to show the graphs
				 */
				$this->render_graph( $get_values_array ['T1'], $get_values_array ['T2'] );
				
				
				$this->author_note();

			}else{
			    // no character thats in the corp.

			    echo'You do not have character that is currently in the corporation.';
			}
		}else{
			// register/ login.
			echo'Wow, Such Spai, Much Look, Very Need login for access!<br><br>';
			/*
			 * TODO Make it possible to browse for the picture from wp media folder.
			 */
			echo'<img src="http://reallifeoutpost.com/wp-content/uploads/2017/08/wF2skTX.jpg" alt="Spai?" height="auto" width="auto">';
		}
	}
	/**
	 * Echo author note
	 */
	public function author_note(){
	    echo'<p>* Main developer: <b>Judge07</b>, Discord username : Judge07#8167 </p>';
	    echo'<p>** Special thanks to :<b> biggus dickus Aurilen</b>, <b>Hhatia</b> and <b>Jonathan Doe</b></p>';
	    echo'<p>*** If you enjoy this statistics summary,  <b>Judge07</b> is allways accepting isk donations. No refunds :D!</p>';
	    
	}
	
	/**
	 * displays TABLE withing specified time intervals 
	 *
	 * This function displays data within start-end date intervals
	 *
	 *
	 * @param date $start
	 *        	date to display from
	 * @param date $end
	 *        	date to display until
	 *        	
	 * @return void
	 */
	public function display_in_time_period($start, $end) {
		$data = new Top_Ratter ();
		global $wpdb;

		// select active users ratting data for the selected period.
		$unsorted_data=$data->gather_data_by_date($start, $end);
		
		// sort the data by main characters and summ it together.
		$chars=$data->gather_data_by_main_chars($start, $end,$unsorted_data);
		
		if ($chars != null) {
			
			// pull corporation data
			$sql = "SELECT * FROM `" . $wpdb->prefix . "tr_sso_credentials`";
			
			$corp_data = $wpdb->get_row ( $sql, ARRAY_A );
			
			
			// define the tax and count of top ratters
			$raturn_tax=$corp_data['corp_return_percent'];
			$top_ratters_count=$corp_data['corp_top_ratter_count']+1;
			
			echo '<table class="tr_selection">
				<thead>
				<tr>
					<th>Nr.</th>
				<th class="charname"><span>Character Name</span></th>
				<th class="tax_right" ><span>ISK(Tax)</span></th>
					<th class="tax_right" ><span>ISK return,('.$raturn_tax.'% of Tax)</span></th>
				</tr>
				</thead>
				<tbody>';
			$total_return=null;
			$total_tax=null;
			$i=1;
			foreach ( $chars as $char ) {
				$tenth=null;
				if($i==$top_ratters_count){
					$tenth='vague_line';
				}
				
				
				echo '<tr>';
				echo'<td class="'.$tenth.'">'.$i.'</td>';		
						
				echo'<td class="'.$tenth.'">';
				echo $char ['ownerName2'];
				
				echo '</td><td  class="align_right '.$tenth.'">';
				echo number_format ( $char ['total'], 2, ',', ' ' );
				echo '</td><td  class="align_right '.$tenth.' format_isk_cell">';
				

				$kek=$raturn_tax*$char ['total']/100;
				
				if($i>=$top_ratters_count){
					$kek=0;
				}
				

				echo number_format ( $kek, 2, ',', ' ' );
				
				
				$total_tax+=$char ['total'];
				$total_return+=$kek;
				echo '</td></tr>';
				$i++;
			}
			echo'<tr ><td class="top_border"></td><td class="top_border">Total</td><td class="align_right top_border ">'.number_format ( $total_tax, 2, ',', ' ' ).'</td><td class="align_right top_border">'.number_format ( $total_return, 2, ',', ' ' ).'</td></tr>';
			echo '	</tbody>
				</table> ';
			
			//calculate how much is left for spendings
			$remaining=$total_tax-$total_return;

			// this is PVP prize pool table 
			if($corp_data['show_top5_pvp']==1){
				if ($chars != null) {
					if($start > '2017-11-01 09:00:00'){
						$this->show_top_five_pvp_killers($start, $end,$remaining );
					}else{
						echo'<p class="spai_better_no_data">PVP data not aviable before "The Purge" *sad fejs*<p>';
					}
				}else{
					echo'Welp! No one was ratting, so there is no pvp prizes...';
				}
			}
			echo '<br><br>';
			
			/*
			 * only show this for officers
			 * 
			 * @todo Check if this character is officer (has director role in API) and show it as well
			 *       for now all directors are admins as per old version.
			 * 
			 */
			if(current_user_can('administrator')){
				echo'<div class="format_isk_div"><button id="format_isk" class="format_isk_btn">PRESS ME !</button></div>';
			}
			
			
			
			/* 
			 * TODO 
			 * 
			 * ID: fq0hfq0wfhq0w8fhq0wfh
			 *  
			 * Show selected systems npc kills in the selected time period totals by characters.
			 *  able to choose more than one system.
			 *  show all chosen systems.
			 *
			 *  show also all systems and percentage by corp total 50% in this sysem 24% in this system etc.
			 *  
			 *  
			 *  ## new systems table tr_systems <- stores all existing systems from ratting data.
			 *  id, system_id,system_name,display_system int(1) <- if this is 1 then show solo system data.
			 *  
			 *  update the table after api pull wiith new systems and new system names if there is any
			 *  
			 *
			 */
			
			
			
			
			
			
		}else{
			echo'<br><br><p class="spai_better_no_data">Sorry, no data. You need to up your spai game!<p><br><br>';
		}
	}
	/**
	 * displays the time picker fields
	 *
	 * This function displays html forms that is bound to jquery data picker.
	 *
	 * @return void
	 */
	public function render_datepicker_fields() {
		/*
		 * do table here for nice looking fields.
		 */

		
		
		
		$r='<p class="datafromto">Pick the Date period:</p>';
		$r.= '<form method="get">';
		$r.='<table class="Selection_boxes"><tr>';
		$r.= '<td>Start Date:</td><td><input type="text"  id="T1" name="T1" /></td></tr>';
		$r.='<tr><td>End Date:</td><td><input type="text"  id="T2" name="T2" /></td></tr>';
		$r.= '<tr><td></td><td><input type="submit" value="Spai" /></td></tr></table>';
		$r.= '</form>';
		return $r;
		
		
	}
	/**
	 * catches the GET values from url and returns the array
	 *
	 * This function parses GET values from the url and process them
	 *
	 * @return $array_of_get_values
	 */
	public function handle_GET_values_non_admin_UI() {
		$array_of_get_values = null;
		if(is_user_logged_in()===false) {
			return;
		}
		
		// set default time zone the same as EVE.
		date_default_timezone_set ( 'UTC' );
		
		if (isset ( $_GET ['T1'] )) {
			// parse the date from jquery format 11/01/2016 mm/dd/yyyy
			// to mysql format 2016-11-23 12:01:39
			$date = $_GET ['T1'];
			if ($date != null) {
				$pieces = explode ( "/", $date );
				$date = "$pieces[2]-$pieces[0]-$pieces[1] 00:00:00";
				$array_of_get_values ['T1'] = $date;
			}
		}
		if (isset ( $_GET ['T2'] )) {
			$date = $_GET ['T2'];
			if ($date != null) {
				$date = $_GET ['T2'];
				$pieces = explode ( "/", $date );
				$date = "$pieces[2]-$pieces[0]-$pieces[1] 23:59:59";
				$array_of_get_values ['T2'] = $date;
			}
		}
		
		if ($array_of_get_values ['T1'] == null && $array_of_get_values ['T2'] == null) {
			
			$month_start = date ( "Y-m" );
			$month_start .= '-01 00:00:00';
			
			$array_of_get_values ['T1'] = $month_start;
			
			$time_now = date ( "Y-m-d H:i:s" );
			
			$array_of_get_values ['T2'] = $time_now;
		} elseif ($array_of_get_values ['T1'] != null && $array_of_get_values ['T2'] == null) {
			// check if t1 is not bigger than time now
			$time_now = date ( "Y-m-d H:i:s" );
			$t2 = $array_of_get_values ['T1'];
			
			if ($t2 < $time_now) {
				$array_of_get_values ['T2'] = $time_now;
			} else {
				$array_of_get_values ['T2'] = $array_of_get_values ['T1'];
			}
		}
		
		return $array_of_get_values;
	}
	/**
	 * Render ADMIN/ officer ui with access to all token data
	 *
	 *
	 * @return null
	 */
	public function render_admin_ui() {
		// show only for admins
		if (current_user_can ( 'manage_options' )) {
			global $wpdb;

			if (isset ( $_GET ['microscoped_user'] )) {
			    if (isset ( $_GET ['owner_id'] )) {
			        // data for the character.
			        $this->outputCharacterMainData($_GET ['owner_id']);
			        
			    }else{
			        //show char selection screen
			        $this->outputCharacterSelectionElementUser($_GET ['microscoped_user']);
			    }
			    
			}else{
			    //output the default starter selection  element
			    $this->outputUserSelectElement();
			}
			
			
// 			echo"<pre>";
// 			echo var_dump($users_with_chars);
// 			echo"</pre>";
			
	
		}
	}

	/**
	 * Outputs the user selection
	 *
	 * @return html string of data to render
	 */
	public function outputUserSelectElement(){
	    global $wpdb;
	    //if no get values are supplied then show this selection select element.
	    $sql = "SELECT *,COUNT(char_id) as numChars FROM `" . $wpdb->prefix . "users`
                JOIN " . $wpdb->prefix . "tr_users_chars ON " . $wpdb->prefix . "users.ID=" . $wpdb->prefix . "tr_users_chars.user_id
			    JOIN " . $wpdb->prefix . "tr_sso_tokens ON " . $wpdb->prefix . "tr_users_chars.char_id=" . $wpdb->prefix . "tr_sso_tokens.character_id
                JOIN " . $wpdb->prefix . "tr_characters ON " . $wpdb->prefix . "tr_users_chars.char_id=" . $wpdb->prefix . "tr_characters.id
                GROUP BY user_id";
	    $users_with_chars = $wpdb->get_results("$sql", ARRAY_A);
	    
	    //loop trough users and make select element.
	    if($users_with_chars){
	        echo'<form method=GET>';
	        echo'<select name="microscoped_user">';
	        foreach($users_with_chars as $userData){
	            //check if this user has any characters that are not in the corporation, if so add SPAI tag.
	            $sql = "SELECT * FROM `" . $wpdb->prefix . "tr_users_chars`
                            JOIN " . $wpdb->prefix . "tr_characters ON " . $wpdb->prefix . "tr_users_chars.char_id=" . $wpdb->prefix . "tr_characters.id
                            WHERE (SELECT corporation_id from " . $wpdb->prefix . "tr_sso_credentials)!=" . $wpdb->prefix . "tr_characters.corp_id
                            AND user_id = ".$userData['ID'].";";
	            $isSpai = $wpdb->get_results("$sql", ARRAY_A);
	            $spaiTag='';
	            if($isSpai){
	                $spaiTag="[SPAI]";
	            }
	            echo'<option value="'.$userData['ID'].'">'.$spaiTag.' '.$userData['user_login'].' ['.$userData['numChars'].'] </option>';
	        }
	        echo'</select>';
	        echo' <input type="submit" value="Look at this user!" />';
	        echo'</form>';
	    }
	}
	/**
	 * Outputs the character selection for usr.
	 *
	 * @return html string of data to render
	 */
	public function outputCharacterSelectionElementUser($userID){
	    
	    global $wpdb;
	    //if no get values are supplied then show this selection select element.
	    $sql = "SELECT * FROM `" . $wpdb->prefix . "tr_users_chars`
                JOIN " . $wpdb->prefix . "tr_characters ON " . $wpdb->prefix . "tr_users_chars.char_id=" . $wpdb->prefix . "tr_characters.id
                WHERE user_id = ".$userID.";";

	    $users_chars = $wpdb->get_results("$sql", ARRAY_A);

	    //loop trough users and make select element.
	    if($users_chars){
	        echo'<form method=GET>';
	        echo' <input type="hidden" name="microscoped_user" value="'.$userID.'" />';
	        echo'<select name="owner_id">';
	        foreach($users_chars as $chars){
	            echo'<option value="'.$chars['owner_id'].'">'.$chars['ownerName2'].' </option>';
	        }
	        echo'</select>';
	        echo' <input type="submit" value="Inspect Character!" />';
	        echo'</form>';
	    }   
	}
	/**
	 * Outputs the data for selected chracter
	 *
	 * @return html string of data to render
	 */
	public function outputCharacterMainData($ownerID){
	    echo 'Data for character ID:'.$ownerID .' coming soon.';
	}
	
	
	
	
	/**DEPRECIATED
	 * Render the select element next to each users field.
	 *
	 * renders the admin page for editing/updaring corp api.
	 * and assigning users to corps.
	 *
	 * @return html string of data to render
	 */
	public function render_user_assignment($user_id) {
		global $wpdb;
		
		$sql = "SELECT id,corp_name FROM `" . $wpdb->prefix . "tr_corporations` ";
		$corporations = $wpdb->get_results ( "$sql", ARRAY_A );
		
		$return_string;
		$return_string .= '<select name="assign_user_to_corp[]">';
		$return_string .='<option value="'.$user_id.'_N">No Access</option>';
		
		foreach ( $corporations as $corp ) {
			$user_meta = get_user_meta ( $user_id, 'Char_corp_asign', true );
			if ($user_meta == $corp ['id']) {
				$return_string .= '<option value="' . $user_id . '_' . $corp ['id'] . '" selected>' . $corp ['corp_name'] . '</option>';
			} else {
				$return_string .= '<option value="' . $user_id . '_' . $corp ['id'] . '">' . $corp ['corp_name'] . '</option>';
			}
		}
		$return_string .= '</select>';
		
		return $return_string;
	}
	/**
	 * Triger the DAta graphs rendering using google charts api
	 *
	 * renders the the graph for data selection
	 * 
	 * @param date $start date from
	 * @param date $end date until
	 *        	
	 * @return null
	 */
	public function render_graph($start, $end){
		/*
		 * http://jsfiddle.net/rM8BM/?utm_source=website&utm_medium=embed&utm_campaign=rM8BM
		 * http://stackoverflow.com/questions/17444586/show-hide-lines-data-in-google-chart
		 * this one works with buttons
		 * https://jsfiddle.net/Loxos/rmbtwdhd/
		 * 
		 * 
		 */
	    
	    global $wpdb;
		
// 		echo'<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>';
		
	    $sql = "SELECT * FROM `" . $wpdb->prefix . "tr_sso_credentials`;";
	    $settingz = $wpdb->get_row ( "$sql", ARRAY_A );
	    
	    //check if at least one chart needs to be shown.
	    if($settingz['show_chart1']=='1'||$settingz['show_chart2']=='1'){
	        echo'<script type="text/javascript" src="https://www.google.com/jsapi"></script>';
	    }
	    if($settingz['show_chart1']=='1'){
	        $this->output_chart1_js_code( $start, $end);
	    }
	    if($settingz['show_chart2']=='1'){
	        $this->output_chart_2_js_code( $start, $end);
	    }
	}

	/**
	 * Render the first chart day/isk/char
	 *
	 */
	public function output_chart1_js_code($start, $end){
		
		$prepared_dummy_data1 = <<<EOD
		<script type="text/javascript">
		<!--define the first array-->
		var isk_day_char_data_array=[["Date","Elettra Blade","spreaders","SSoulSScream","biggus dickus Aurilen","Szchyactszky","Judge07","Medina Riper","anouk tolkien","Jonathan Doe","Windsigh","Cheese Nippels","Foxy Bushy","Seras Hightower","SSF ZiG","MattMan","lilbigbear","Annadrian","Deriger"],["2017-03-01",4373073,4209782,4439618,0,0,0,416851.1,0,0,0,26286180,20524463.9,11509867.5,1652022.5,0,0,0,0],["2017-03-02",482100,482100,482100,0,0,0,7034697,0,102276.6,0,0,13935228.7,0,0,0,111515,0,0],["2017-03-03",3257930,3257930,3257930,0,0,0,10159585,0,286782.7,0,2114383,13438791,0,0,0,4336781,0,0],["2017-03-04",4131752,4131752,4131752,0,0,0,1915880.8,0,0,0,0,2149488.36,0,0,0,32985.8,27225,0],["2017-03-05",1614472,1614472,1614472,0,11103430,0,10339218,0,0,0,13315549,669865.8,0,0,17819825,13478.3,0,0],["2017-03-06",0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],["2017-03-07",3077080,3077080,3077080,0,0,66600.7,19931.2,0,1572436.7,0,3042603,0,0,0,0,66600.7,0,0],["2017-03-08",2994531,5083678,5234235,681533,0,0,7307814,0,418548,0,0,0,4300541,0,6320160,0,0,0],["2017-03-09",11465228,10676374,11897869,3996309.7,0,0,0,3628848,0,0,8827750,17419.2,19893465,0,16262030,0,0,0],["2017-03-10",13340031,14008618,12930936,328640.4,0,0,0,0,0,0,1549692,0,10777375,0,31799304,0,43418,0],["2017-03-11",14650937,14739614,15498506,327758.5,0,10372.5,6141726,0,0,0,929946,0,15513210,0,24787179.1,10372.5,0,2560355],["2017-03-12",1017587,1017587,1027206,963638,12681950,329201.8,13111089,0,0,0,0,0,0,0,20090061,9797117.1,0,0],["2017-03-13",0,2125678,1598122,0,0,0,0,0,0,0,0,0,6483112,2746848,2394240,0,0,812902],["2017-03-14",303631,1844770,1669833,0,0,0,3042972,0,0,0,5537190,0,0,6810842,0,0,0,0],["2017-03-15",4383072,4363824,4730477,0,0,0,0,0,0,0,21205540,0,0,4506329,0,0,0,2646199],["2017-03-16",0,0,0,1322864.7,2302890,0,428137,0,0,37950.75,0,0,0,0,30901014,0,0,1673067],["2017-03-17",0,0,0,0,0,0,6620530,0,0,0,7916714,4494796,21626988,3941145,18719863,9687756,0,0],["2017-03-18",0,0,0,0,6903130,940351.73,0,0,0,20707,13197805,0,8845950,3080460.3,0,13328012,0,1945797.9],["2017-03-19",0,0,0,0,28907650,0,0,0,3303581.9,0,27279815,674194,10375922,6302158,2493592,18491765,0,1913718],["2017-03-20",0,0,0,0,0,0,0,24000,0,0,12798743.8,0,16814298,33468178.2,0,25536,0,234340],["2017-03-21",0,0,0,0,0,0,0,0,0,0,4123600,0,0,0,0,0,0,57895.6],["2017-03-22",0,0,0,672769.5,0,287239.9,0,0,0,0,0,0,0,0,15580280.5,0,0,0],["2017-03-23",0,0,0,658935.2,0,282367.5,0,59297.8,0,142167.4,0,0,0,9347951,15034440,6929683,0,818434],["2017-03-24",0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]]		
		</script>
	
EOD;
		


		
		
		
		
		
		
		$isk_day_per_char = <<<EOD
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">			
				
        // Event listeners for the show all hide all charts
        document.getElementById("hideAll").addEventListener("click", hideAll);
        document.getElementById("showAll").addEventListener("click", drawChart);       
        // load the google visualization api and corechart packages
        google.charts.load('current', {'packages':['corechart']});
        // set callback to run when google visiualization api is finished loading
        google.charts.setOnLoadCallback(drawChart);    
        // callback that creates and populates the data table. Instantiates the line chart, passes in the data and draws it
        function drawChart() {
            // create the data table
            var isk_day_char = google.visualization.arrayToDataTable(isk_day_char_data_array);
            var chart = new google.visualization.LineChart(document.getElementById('isk_day_per_char'));
            // create column array
            var columns = [];
            // display these data series by default
            var defaultSeries = [];
            for (var ds = 0; ds <= isk_day_char.getNumberOfColumns(); ds++){
                defaultSeries.push(ds);
            }
            var series ={};
            for (var i = 0; i < isk_day_char.getNumberOfColumns(); i++){
                if (i == 0 || defaultSeries.indexOf(i) > -1){
                    // if the column is the domain column or in the default list, display the series
                    columns.push(i); 
                }
                // othersise, hide it
                else{
                    columns.push({
                        label: isk_day_char.getColumnLabel(i),
                        type: isk_day_char.getColumnType(i),
                        sourceColumn: i,
                        calc: function (){
                            return null;
                        }
                    });
                }
                if (i > 0){
                    columns.push({
                        calc: 'stringify',
                        sourceColumn: i,
                        type: 'string',
                        role: 'annotationText'
                    });
                    //set the default series option
                    series[i -1] = {};
                    if (defaultSeries.indexOf(i) == -1){
                        // backup the dafault color (if set)
                        if (typeof(series[i -1].color) !== 'undefined'){
                            series[i - 1].backupColor = series[i - 1].color;
                        }
                        series[i - 1].color = '#CCCCCC'
                    }
                }
            }
            // chart options
            var options = {
                title: 'ISK/day per Character',
                legend: {textStyle: {fontSize: 12}},
                hAxis: {textStyle: {fontSize: 9}},
                vAxis: {textStyle: {fontSize: 9}, format: 'long'},
                series: series
            }
            function showHideSeries (){
                var sel = chart.getSelection();
                // if selection length is 0, we deselected an element
                if (sel.length > 0){
                    // if row is undefined, we clicked on the legend
                    if (sel[0].row == null){
                        var col = sel[0].column;
                        if (typeof(columns[col]) == 'number'){
                            var src = columns[col];
                            // hide data series
                            columns[col] = {
                                label: isk_day_char.getColumnLabel(src),
                                type: isk_day_char.getColumnType(src),
                                sourceColumn: src,
                                calc: function(){
                                    return null;
                                }
                            };
                            // grey out the legend entry
                            series[src - 1].color = '#CCCCCC';
                        }
                        // show the data series
                        else{
                            var src = columns[col].sourceColumn;
                            columns[col] = src;
                            series[src - 1].color = null;
                        }
                        var view = new google.visualization.DataView(isk_day_char);
                        view.setColumns(columns);
                        chart.draw(view, options);
                    }
                }
            }
            google.visualization.events.addListener(chart, 'select', showHideSeries);
            // instantiate and draw new chart with new view and options
            var view = new google.visualization.DataView(isk_day_char);
            view.setColumns(columns);
            chart.draw(view, options);
        }
        
        
        function hideAll() {
            // create the data table
            var isk_day_char = google.visualization.arrayToDataTable(isk_day_char_data_array);
            var chart = new google.visualization.LineChart(document.getElementById('isk_day_per_char'));
            // create column array
            var columns = [];
            // display these data series by default
            var defaultSeries = [];
            var series ={};
            for (var i = 0; i < isk_day_char.getNumberOfColumns(); i++){
                if (i == 0 || defaultSeries.indexOf(i) > -1){
                    // if the column is the domain column or in the default list, display the series
                    columns.push(i); 
                }
                // othersise, hide it
                else{
                    columns.push({
                        label: isk_day_char.getColumnLabel(i),
                        type: isk_day_char.getColumnType(i),
                        sourceColumn: i,
                        calc: function (){
                            return null;
                        }
                    });
                }
                if (i > 0){
                    columns.push({
                        calc: 'stringify',
                        sourceColumn: i,
                        type: 'string',
                        role: 'annotationText'
                    });
                    //set the default series option
                    series[i -1] = {};
                    if (defaultSeries.indexOf(i) == -1){
                        // backup the dafault color (if set)
                        if (typeof(series[i -1].color) !== 'undefined'){
                            series[i - 1].backupColor = series[i - 1].color;
                        }
                        series[i - 1].color = '#CCCCCC'
                    }
                }
            }
            // chart options
            var options = {
                title: 'ISK/day per Character',
                legend: {textStyle: {fontSize: 12}},
                hAxis: {textStyle: {fontSize: 9}},
                vAxis: {textStyle: {fontSize: 9}, format: 'long'},
                series: series
            }
            function showHideSeries (){
                var sel = chart.getSelection();
                // if selection length is 0, we deselected an element
                if (sel.length > 0){
                    // if row is undefined, we clicked on the legend
                    if (sel[0].row == null){
                        var col = sel[0].column;
                        if (typeof(columns[col]) == 'number'){
                            var src = columns[col];
                            // hide data series
                            columns[col] = {
                                label: isk_day_char.getColumnLabel(src),
                                type: isk_day_char.getColumnType(src),
                                sourceColumn: src,
                                calc: function(){
                                    return null;
                                }
                            };
                            // grey out the legend entry
                            series[src - 1].color = '#CCCCCC';
                        }
                        // show the data series
                        else{
                            var src = columns[col].sourceColumn;
                            columns[col] = src;
                            series[src - 1].color = null;
                        }
                        var view = new google.visualization.DataView(isk_day_char);
                        view.setColumns(columns);
                        chart.draw(view, options);
                    }
                }
            }
            google.visualization.events.addListener(chart, 'select', showHideSeries);
            // instantiate and draw new chart with new view and options
            var view = new google.visualization.DataView(isk_day_char);
            view.setColumns(columns);
            chart.draw(view, options);
        }
</script>
EOD;
		
// 		echo $prepared_dummy_data1;
		$data = new Top_Ratter ();
		
		$chars = $data->prepare_data_for_chart_by_days ( $start, $end );
		
		
		if($chars==false){
			return;
		}
		
		$isk_day_char=json_encode($chars);
		echo'	<script type="text/javascript"> var isk_day_char_data_array='.$isk_day_char.'</script>';
	
		echo '<div id="isk_day_per_char" class="isk_day_per_char"></div>';
		echo '<div class="clearfix"></div>';
		echo '<div class="dankbuttons"><button id="hideAll">Hide All</button><button id="showAll">Show All</button></div>';
		echo $isk_day_per_char;
	}
	/**
	 * Render the second chart day/isk/char accumulated
	 *
	 */
	public function output_chart_2_js_code($start, $end){
		
// 		$prepared_dummy_data2 = <<<EOD
//  <script type="text/javascript">
// <!--define the second array -->
// var isk_day_char_acumulate=[["Date","Elettra Blade","spreaders","SSoulSScream","biggus dickus Aurilen","Szchyactszky","Judge07","Medina Riper","anouk tolkien","Jonathan Doe","Windsigh","Cheese Nippels","Foxy Bushy","Seras Hightower","SSF ZiG","MattMan","lilbigbear","Annadrian","Deriger"],["2017-03-01",4373073,4209782,4439618,0,0,0,416851.1,0,0,0,26286180,20524463.9,11509867.5,1652022.5,0,0,0,0],["2017-03-02",4855173,4691882,4921718,0,0,0,7451548.1,0,102276.6,0,26286180,34459692.6,11509867.5,1652022.5,0,111515,0,0],["2017-03-03",8113103,7949812,8179648,0,0,0,17611133.1,0,389059.3,0,28400563,47898483.6,11509867.5,1652022.5,0,4448296,0,0],["2017-03-04",12244855,12081564,12311400,0,0,0,19527013.9,0,389059.3,0,28400563,50047971.96,11509867.5,1652022.5,0,4481281.8,27225,0],["2017-03-05",13859327,13696036,13925872,0,11103430,0,29866231.9,0,389059.3,0,41716112,50717837.76,11509867.5,1652022.5,17819825,4494760.1,27225,0],["2017-03-06",13859327,13696036,13925872,0,11103430,0,29866231.9,0,389059.3,0,41716112,50717837.76,11509867.5,1652022.5,17819825,4494760.1,27225,0],["2017-03-07",16936407,16773116,17002952,0,11103430,66600.7,29886163.1,0,1961496,0,44758715,50717837.76,11509867.5,1652022.5,17819825,4561360.8,27225,0],["2017-03-08",19930938,21856794,22237187,681533,11103430,66600.7,37193977.1,0,2380044,0,44758715,50717837.76,15810408.5,1652022.5,24139985,4561360.8,27225,0],["2017-03-09",31396166,32533168,34135056,4677842.7,11103430,66600.7,37193977.1,3628848,2380044,0,53586465,50735256.96,35703873.5,1652022.5,40402015,4561360.8,27225,0],["2017-03-10",44736197,46541786,47065992,5006483.1,11103430,66600.7,37193977.1,3628848,2380044,0,55136157,50735256.96,46481248.5,1652022.5,72201319,4561360.8,70643,0],["2017-03-11",59387134,61281400,62564498,5334241.6,11103430,76973.2,43335703.1,3628848,2380044,0,56066103,50735256.96,61994458.5,1652022.5,96988498.1,4571733.3,70643,2560355],["2017-03-12",60404721,62298987,63591704,6297879.6,23785380,406175,56446792.1,3628848,2380044,0,56066103,50735256.96,61994458.5,1652022.5,117078559.1,14368850.4,70643,2560355],["2017-03-13",60404721,64424665,65189826,6297879.6,23785380,406175,56446792.1,3628848,2380044,0,56066103,50735256.96,68477570.5,4398870.5,119472799.1,14368850.4,70643,3373257],["2017-03-14",60708352,66269435,66859659,6297879.6,23785380,406175,59489764.1,3628848,2380044,0,61603293,50735256.96,68477570.5,11209712.5,119472799.1,14368850.4,70643,3373257],["2017-03-15",65091424,70633259,71590136,6297879.6,23785380,406175,59489764.1,3628848,2380044,0,82808833,50735256.96,68477570.5,15716041.5,119472799.1,14368850.4,70643,6019456],["2017-03-16",65091424,70633259,71590136,7620744.3,26088270,406175,59917901.1,3628848,2380044,37950.75,82808833,50735256.96,68477570.5,15716041.5,150373813.1,14368850.4,70643,7692523],["2017-03-17",65091424,70633259,71590136,7620744.3,26088270,406175,66538431.1,3628848,2380044,37950.75,90725547,55230052.96,90104558.5,19657186.5,169093676.1,24056606.4,70643,7692523],["2017-03-18",65091424,70633259,71590136,7620744.3,32991400,1346526.73,66538431.1,3628848,2380044,58657.75,103923352,55230052.96,98950508.5,22737646.8,169093676.1,37384618.4,70643,9638320.9],["2017-03-19",65091424,70633259,71590136,7620744.3,61899050,1346526.73,66538431.1,3628848,5683625.9,58657.75,131203167,55904246.96,109326430.5,29039804.8,171587268.1,55876383.4,70643,11552038.9],["2017-03-20",65091424,70633259,71590136,7620744.3,61899050,1346526.73,66538431.1,3652848,5683625.9,58657.75,144001910.8,55904246.96,126140728.5,62507983,171587268.1,55901919.4,70643,11786378.9],["2017-03-21",65091424,70633259,71590136,7620744.3,61899050,1346526.73,66538431.1,3652848,5683625.9,58657.75,148125510.8,55904246.96,126140728.5,62507983,171587268.1,55901919.4,70643,11844274.5],["2017-03-22",65091424,70633259,71590136,8293513.8,61899050,1633766.63,66538431.1,3652848,5683625.9,58657.75,148125510.8,55904246.96,126140728.5,62507983,187167548.6,55901919.4,70643,11844274.5],["2017-03-23",65091424,70633259,71590136,8952449,61899050,1916134.13,66538431.1,3712145.8,5683625.9,200825.15,148125510.8,55904246.96,126140728.5,71855934,202201988.6,62831602.4,70643,12662708.5],["2017-03-24",65091424,70633259,71590136,8952449,61899050,1916134.13,66538431.1,3712145.8,5683625.9,200825.15,148125510.8,55904246.96,126140728.5,71855934,202201988.6,62831602.4,70643,12662708.5]]
// </script>	
// EOD;
		
		
		
		
		$isk_day_per_char_acumulated = <<<EOD
<script type="text/javascript">
        // load the google visualization api and corechart packages
        google.charts.load('current', {'packages':['corechart']});
        // set callback to run when google visiualization api is finished loading
        google.charts.setOnLoadCallback(drawChart);    
        // callback that creates and populates the data table. Instantiates the line chart, passes in the data and draws it
        function drawChart() {
            // create the data table
        var isk_day_char_acumulate = google.visualization.arrayToDataTable(isk_day_char_acumulate_data_array);

        var chart = new google.visualization.LineChart(document.getElementById('isk_day_per_char_acumulate'));

                var columns = [];

                var defaultSeries = [];
                for (var ds = 0; ds <= isk_day_char_acumulate.getNumberOfColumns(); ds++){
                    defaultSeries.push(ds);
                    }
                var series ={};
                for (var i = 0; i < isk_day_char_acumulate.getNumberOfColumns(); i++){
                    if (i == 0 || defaultSeries.indexOf(i) > -1){

                        columns.push(i);
                    }
                    else{
                        columns.push({
                            label: isk_day_char_acumulate.getColumnLabel(i),
                            type: isk_day_char_acumulate.getColumnType(i),
                            sourceColumn: i,
                            calc: function (){
                                return null;
                            }
                        });
                    }
                    if (i > 0){
                        columns.push({
                            calc: 'stringify',
                            sourceColumn: i,
                            type: 'string',
                            role: 'annotationText'
                        });

                        series[i -1] = {};
                        if (defaultSeries.indexOf(i) == -1){
                            if (typeof(series[i -1].color) !== 'undefined'){
                                series[i - 1].backupColor = series[i - 1].color;
                            }
                            series[i - 1].color = '#CCCCCC'
                        }
                    }
                }

                // chart options
                var options = {
                    title: 'ISK/day per Character Acumulated',
                    height: 600,
                    series: series
                    

                }

                function showHideSeries (){
                    var sel = chart.getSelection();
                    if (sel.length > 0){
                        if (sel[0].row == null){
                            var col = sel[0].column;
                            if (typeof(columns[col]) == 'number'){
                                var src = columns[col];

                                // hide data series
                                columns[col] = {
                                    label: isk_day_char_acumulate.getColumnLabel(src),
                                    type: isk_day_char_acumulate.getColumnType(src),
                                    sourceColumn: src,
                                    calc: function(){
                                        return null;
                                    }
                                };
                                // grey out series
                                series[src - 1].color = '#CCCCCC';
                            }
                            // show data series
                            else{
                                var src = columns[col].sourceColumn;
                                columns[col] = src;
                                series[src - 1].color = null;
                            }
                            var view = new google.visualization.DataView(isk_day_char_acumulate);
                            view.setColumns(columns);
                            chart.draw(view, options);
                        }
                    }
                }

                google.visualization.events.addListener(chart, 'select', showHideSeries);

                // instantiate and draw new chart with new view and options
                var view = new google.visualization.DataView(isk_day_char_acumulate);
                      view.setColumns(columns);
                      chart.draw(view, options);			

            
           
             
           
           
        }
</script>
EOD;
		
		
		
// 		echo $prepared_dummy_data2;
		
		$data = new Top_Ratter ();
		// 		echo '<p>ISK/day per Character Acumulated</p>';
		
		$chars = $data->prepare_data_for_chart_by_days_acumulated ( $start, $end );
		if($chars==false){
			return;
		}
		$isk_day_char_acumu=json_encode($chars);
		echo'	<script type="text/javascript"> var isk_day_char_acumulate_data_array='.$isk_day_char_acumu.'</script>';
		echo $isk_day_per_char_acumulated;
		echo '<div id="isk_day_per_char_acumulate"></div>';
		
	}
	
	
	
	/**
	 * Render industral data for officers/admins
	 *
	 *http://eveonline-third-party-documentation.readthedocs.io/en/latest/xmlapi/eve/eve_reftypes.html
	 *https://api.eveonline.com//eve/RefTypes.xml.aspx
	 *
	 * @TODO Give possibility to choose from all the Structures owned by corporation and choose them from drop down menu
	 * 
	 * ## This function needs a revision and updating to keep up with new changes in eve online.
	 * 
	 * @return void
	 * 
	 *
	 */
	public function structures_incomes(){
		
// 		echo'<pre> Structures incomes --- coming soon �</pre>';
// 		return;
		
		if(is_user_logged_in()==true) {
				if(current_user_can('administrator')){

				    global $wpdb;
				    $this->cronjob_triger_shortcode_function();
				    
					// render timepicker fields
					echo $this->render_datepicker_fields ();
					
					// handle the GET values
					$get_values_array = $this->handle_GET_values_non_admin_UI ();
					
					// show the selection
					echo '<p class="datafromto">Selection period: '.$get_values_array ['T1'] . ' -> ' . $get_values_array ['T2'].'</p>';
					
					$sql="SELECT * FROM `" . $wpdb->prefix . "tr_structures_income` 
						  WHERE `date_acquired` BETWEEN '".$get_values_array['T1']."' AND '".$get_values_array['T2']."'
						  ORDER BY `date_acquired` DESC";
					$structures_data = $wpdb->get_results ( "$sql", ARRAY_A );
					
					
					if ($structures_data != null) {
						echo '<table class="tr_selection">
								<thead>
								<tr>
								<th class="charname" ><span>When</span></th>
								<th class="tax_right" ><span>refType</span></th>
								<th class="charname" ><span>How Much</span></th>
								</tr>
								</thead>
								<tbody>';
						$total=0;
						foreach($structures_data as $record ){
							$total=$total+$record['amount'];
							
						}
						echo'<tr>
								<td class="bottom_border_2"></td>
								<td class="bottom_border_2">Total:</td>	
								<td class="align_right bottom_border_2">'.number_format ( $total, 2, ',', ' ' ).'</td>
								</tr>';
						
						
						foreach ( $structures_data as $record ) {
							echo '<tr>';
							echo '<td  class="align_right">';
							echo $record ['date_acquired'];
							echo '</td>
								<td  class="align_right">';
							echo $record ['ref_type'];
							echo '</td>
								<td  class="align_right">';
							echo number_format ( $record['amount'], 2, ',', ' ' );
							echo '</td>
								</tr>';
					
						}
						    echo '	</tbody>
									</table> ';
					}else{
						Echo'<p class="datafromto">Nothing To Display You Greedy Baastard!</p>';
					}
		

				}else{
					echo'R.I.P. No Access for you... Only officers allowed!';
				}
			}else{
				echo'Login required!';
			}
				

		
		

		
		
	}

	/**
	 * Parse the Zkill page and get top 5 kills for the current month
	 * 
	 * https://zkillboard.com/corporation/98342863/top/
	 * 
	 * @todo Fix the multiple corporation gathering mechanism so that more than one can be added to the plugin.
	 * 
	 * @return void
	 */
	public function cronjob_triger_shortcode_function(){


		Global $wpdb;

		//get all corps
		$sql="SELECT * FROM `" . $wpdb->prefix . "tr_sso_credentials`";
		$corporation=$wpdb->get_row($sql,ARRAY_A);
		
		date_default_timezone_set('UTC');
		$time_now = date ( "Y-m-d H:i:s" );
		echo'<p>Api cached until '.$corporation['cached_until'].' UTC </p>';
		
		if($corporation['cached_until']<$time_now||$corporation['cached_until']==null){
		    // do the call
		    if($corporation!=false){
		        $top_rat=new Top_Ratter();
		        
		        
		        /*
		         * Update the cache timer first in case multiple loads happen within the same period.
		         */

		        
		        $cache_timer = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +30 minutes"));
		        //set data to update
		        $data2 = array (
		            'cached_until' => $cache_timer
		        );
		        //where to update.
		        $where = array (
		            'id' => $corporation ['id']
		        );
		        //run update
		        $wpdb->update ( $wpdb->prefix . 'tr_sso_credentials', $data2,$where );
		       
		       
		        
		        
		        //update ratting data and structure incomes ( comes from same api)
		        $top_rat->update_ratting_data ();
		        
		    
		        
		        /*
		         * Zkilboard api keep changing much frequent than i have time to fix adjust to it.
		         */
		        if($corporation['show_top5_pvp']==1){
		             //update zkill data
		        $top_rat->gather_zkill_data_for_corporation($corporation['corporation_id']);
		        }
		       
		        
		        

		        
		        
		        echo'<p>API Data Updated, Sorry it took a while.</p>';
		        
		    }  
		}
	}
	
	/**
	 * Renders Top 5 pvp killers
	 * 
	 * @param $start date
	 * @param $end date 
	 * @param $remaining int ISK remaining for pvp prizes
	 * 
	 * @return void
	 */
	public function show_top_five_pvp_killers($start, $end,$remaining){
		Global $wpdb;
		

		/*
		 * ZKILL API
		 * https://github.com/zKillboard/zKillboard/wiki/API-(Killmails)
		 *
		 * SELECT count(`kill_id`) as kill_count, `character` FROM `wp_tr_pvp_chars_kills`
		 * WHERE `timestamp` BETWEEN '2017-04-00 00:00:00' AND '2017-04-30 15:39:03'
		 * AND `corp_id`='98342863' GROUP BY `character` ORDER BY kill_count DESC
		 *
		 */

		
		$data = new Top_Ratter ();
		$top_pvpers=$data->get_top5_pvp_kills_by_main_characters($start, $end);

		if($top_pvpers!=false){

			echo '<table class="tr_selection">
					<thead>
					<tr>
					<th>Place</th>
					<th class="tax_right" >Kills</th>
					<th class="tax_right" ><span>% of taxed ISK</span></th>
					<th class="tax_right" ><span>ISK reward </span></th>
					</tr>
					</thead>
					<tbody>';
			
			$perc=5;
			$i=1;
			foreach($top_pvpers as $pvp_guy){
					
				$prize=$remaining*$perc/100;
					
					
				echo '<tr>';
				echo'<td class="">#'.$i.' '.$pvp_guy['ownerName2'].'</td>';
				echo'<td class="tax_right">['.$pvp_guy['total'].']</td>';
				echo'<td class="">'.$perc.'%</td>';
				echo'<td class="tax_right format_isk_cell">'.number_format ( $prize, 2, ',', ' ' ).'</td>';
					
				echo '</tr>';
				$perc--;
				$i++;
					
				$total_pvp_prizes+=$prize;
			}
			echo'<tr ><td class="top_border"></td><td class="top_border"></td><td class="align_right top_border "> PVP prize Pool:</td><td class="align_right top_border">'.number_format ( $total_pvp_prizes, 2, ',', ' ' ).'</td></tr>';
			
			echo '	</tbody>
					</table> ';
		
		}else{
			echo'<p class="spai_better_no_data">No Kills for this period... GO KEEL SOME !<p><br>';
		}	
	}
	
	/**
	 * Renders Main Char selection drop box in 'myAccount'
	 *
	 * Allows loged in user to select its main char from its assigned chars.
	 *
	 * @return void
	 */
	public function render_main_char_selection_by_user(){
	
	    //hook the login check function on login screen since the hoook is not working for some rason
	    $sso=new Top_Ratter_SSO();
	   $sso-> user_login_token_check();
		
	    
	    
	    
	    global $wpdb;
		
		$user_id = get_current_user_id ();
		
		$sql="SELECT * FROM `" . $wpdb->prefix . "tr_users_chars` 
			JOIN " . $wpdb->prefix . "tr_characters charz ON " . $wpdb->prefix . "tr_users_chars.char_id=charz.id
			WHERE `user_id`='$user_id'";
				

		$all_user_chars=$wpdb->get_results($sql,ARRAY_A);
		
		if($all_user_chars){
			echo'Select Your Main Character: ';
			
			
			echo '<form action="' . get_admin_url () . 'admin-post.php" method="post">';
			echo '<input type="hidden" name="action" value="tr_action" />';
			echo '<input type="hidden" name="user_select_main_char_u_id" value="'.$user_id.'" />';
			
			echo'<select name="user_select_main_char">';
			foreach($all_user_chars as $single_char){
				$sele="";
				if($single_char['is_main_char']=='1'){
					$sele="selected";
				}
				
				
				echo'<option value="'.$single_char['char_id'].'" '.$sele.'>'.$single_char['ownerName2'].'</option>';
			}
			echo '<input type="submit" value="Set As Main" />';
			echo '</form>';
		}
	}

	/**DEPRECIATED
	 * Assign characters to the user.
	 *
	 * If there is no chars yet, first assigned character will be the main.
	 * Already assigned characters do not show up in the aviable chars.
	 *
	 * @return void
	 */
	public function render_admin_assign_related_characters(){
		global $wpdb;

		if ( current_user_can( 'manage_options' ) ) {
			/* A user with admin privileges */
			
			//get user corp data
			$admin_user_id=get_current_user_id();
			$user_corp = get_user_meta ( $admin_user_id, 'Char_corp_asign', true );
			$sql = "SELECT * FROM " . $wpdb->prefix . "tr_corporations WHERE id=$user_corp";
			// go trough array and find unique
			
			$corp_data_array = $wpdb->get_row ( "$sql", ARRAY_A );
			$xml = new Top_Ratter ();
			
			
			$sql="SELECT * FROM `" . $wpdb->prefix . "users`";
			$all_users=$wpdb->get_results($sql,ARRAY_A);
			
			if (isset ( $_GET ['aacfu'] )) {
			
				$user_id=$_GET ['aacfu'];
			}
			
			
			echo'<form method="get">';
			echo'Pick user to assign chars to<select name="aacfu">';
			foreach($all_users as $user){
				
				/*
				 * check if the user has access to this admins corporation.
				 */
				
				$temp_user_corp = get_user_meta ( $user['ID'], 'Char_corp_asign', true );
				if($user_corp==$temp_user_corp){
					
					$sql="SELECT COUNT(`char_id`) as assigned_chars FROM `" . $wpdb->prefix . "tr_users_chars` WHERE `user_id`='".$user['ID']."'";
					$user_char_count=$wpdb->get_row($sql,ARRAY_A);
					
					$existing_cha='';
					if($user_char_count['assigned_chars']!=0){
						$existi=$user_char_count['assigned_chars'];
						$existing_cha=" [ $existi ] ";
					}
					$selected='';
					if($user_id==$user['ID']){
						$selected="selected";
					}
					
					
					echo' <option value="'.$user['ID'].'" '.$selected.'>'.$user['user_login'].' '.$existing_cha.'</option>';
				}
				
				
				
			}
			echo'</select>';
			echo'<input type="submit" value="Pick" />';
			echo'</form><br>';
			
			
			
			if ($user_id) {

				
				$tr=new Top_Ratter();
				
				$related_chars=$tr->get_character_assigned_chars($user_id);

				//select only chars for this corp.
				$not_related_chars=$tr->get_not_assigned_chars($user_corp);
				

				echo'<div class="fancy_notice_admins">Hold "Ctrl" to select multiple chars. <br> Users that has not been assigned corporation will not show here.<br> Admin can only assign users from the same corp he is assigned to.</div>';
				if($related_chars){
					
						echo'<div class="assigned_chars">';
						echo '<form action="' . get_admin_url () . 'admin-post.php" method="post">';
						echo '<input type="hidden" name="action" value="tr_action" />';
						echo '<input type="hidden" name="aacfu_aruc_user_id" value="'.$user_id.'" />';
						
					echo'Bound chars <br><select class="long_multi_select_chars" multiple name="aacfu_aruc[]">';
						
					foreach($related_chars as $ass_char){
						echo' <option value="'.$ass_char['char_id'].'">'.$ass_char['ownerName2'].'</option>';
					}
					echo'</select><br>';
					echo'<input type="submit" value="Detach" />';
					echo'</form></div>';
					
				}
			
				if($not_related_chars){
					echo'<div class="free_chars">';
					echo '<form action="' . get_admin_url () . 'admin-post.php" method="post">';
					echo '<input type="hidden" name="action" value="tr_action" />';
					echo '<input type="hidden" name="aacfu_aauc_user_id" value="'.$user_id.'" />';
					echo'Unnasigned chars <br><select class="long_multi_select_chars" multiple name="aacfu_aauc[]">';
					
					foreach($not_related_chars as $free_char){
						echo' <option value="'.$free_char['id'].'">'.$free_char['ownerName2'].'</option>';
					}
					echo'</select><br>';
					echo'<input type="submit" value="Assign" />';
					echo'</form></div>';
					echo'<div class="clearfix">';
				}	
			}
		}	
	}
	
	/**
	 * Renders page where user can manage isk for druglords reactions pos.
	 * @TODO create the functionaly and database required for this .
	 * @return void
	 */
	public function render_jdoe_page_druglords(){
		/*
		 * #-----JDOE-------#
			wallet - gas- fuel +drugs= margin
			save the transaction timedate in table
			and show like structures incomes where he can select time period he wants to see.
			user id who did the transaction.
		 * 
		 */
		
		Echo'Here be coming Jdoe druglords stuff soon�';
	}
	
}















?>