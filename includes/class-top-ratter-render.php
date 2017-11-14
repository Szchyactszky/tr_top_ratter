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
		add_shortcode ( 'kek_testing_random_javascript', array (
				$this,
				'testing_random_javascript' 
		) );
		
		// add testing function for hiding series
		add_shortcode ( 'sc_structures_incomes', array (
				$this,
				'structures_incomes'
		) );
		
		// add testing function for hiding series
		add_shortcode ( 'sc_render_graph', array (
				$this,
				'render_graph'
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
		
		// allows user to change his main character.
		add_shortcode ( 'sc_assign_related_chars', array (
				$this,
				'render_admin_assign_related_characters'
		) );
		
		// allows to calculate manualy in and out for drug production
		add_shortcode ( 'sc_jdoepage_druglords', array (
				$this,
				'render_jdoe_page_druglords'
		) );
		
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
		
		// sc_stealthy_ninja_table_fix
		add_shortcode ( 'sc_stealthy_ninja_table_fix', array (
				$this,
				'stealthy_ninja_table_fix'
		) );
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
		
		/*
		 * # Introducttion and what it does
		 * # All the shortcodes and whats the purpouse of them
		 * #
		 * ### Corporation set up page add, edit, delete
		 * ### Additional values like tax% and how much etc.
		 * #### Assign user to corporation
		 * #### Assign users chars to user
		 */
		
		echo'<h1> Welcome to Top Ratter </h1>';
		echo'<p> Top Ratter started out as a gig for Eve Online developers competition, and now has evolved to something that we hope you will enjoy(if you happen to use it).</p>';
		echo'<p>In the core this plugin is ment to be used together with Eve Online. It uses corporation API to get the ratted income isk tax to the corporation 
				and sorts its by Eve Online characters by date. It also gathers structure incomes and pvp kills, tho that is not the primary point of plugin.</p>';
		
		echo'<h1> Shortcodes</h1>';
		echo'<p> Top Ratter uses several shortcodes to deliver the data.</p>';
		echo'<p> For now the short codes are as follows:</p>';
		echo'<p><b style="font-weight: 900;">[sc_show_ratting_report]</b> This will show the table of ratted isk + pvp table + graphs for the isk by character. <br>
				<b style="font-weight: 900;">[sc_structures_incomes]</b> Shows incomes for the structures. <br>
				<b style="font-weight: 900;">[sc_render_admin_ui]</b> Page where wp users are assigned to a corporation( or removed). <br>
				<b style="font-weight: 900;">[update_data_cron_job]</b> This is a short code for cron job page, query this page to get the latest data from API. Update at least once a week for complete data coverage. <br>
				<b style="font-weight: 900;">[sc_main_char_selection_form]</b> Short code that provides a way for a user to choose a main character from all its related characters.<br>
				<b style="font-weight: 900;">[sc_assign_related_chars]</b> Short code that allows officers to assign characters to a specific wp users.<br>
				</p>';
		echo'<p> If you are new to wordpress shortcodes simply copy and paste one of the short codes in the page editor and see how it works. <a href="https://codex.wordpress.org/Shortcode" >More info about wp shortcodes</a></p>';
		
		Echo '<h4>NOTE:</h4><p>Even tho i made it with multiple corporations in mind, it might not work as it has not been tested in multiple corporation environment.</p>';

		echo '<h2>How to get API?</h2>';
		echo '<p>This plugin needs only <b>"Account and Market"</b> API section. Api should be corporation API, character who is an officer or higher in corporation can make corporation API.</p>';
		echo '<p> More in formation at <a href="https://community.eveonline.com/support/api-key"> https://community.eveonline.com/support/api-key</a>';
		// manage corporation API
		$this->render_corporation_mgmt();
		
		
		
		Echo'<p> The end </p>';
		
		
		
	}
	/**
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
		
		echo '<div>';
		echo '</form>';
	}
	/**
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
	 *
	 * @return void
	 */
	public function render_top_ratter_non_admin_UI() {
		// echo 'call the update function with preset data.<br>';
		if(is_user_logged_in()===true) {
			global $wpdb;
			
			$user_id=get_current_user_id();
			if($user_id==null){
				//debuging for caching problem.
				echo'Wow! I NEED HEALING !!! and btw your a spai !  Zis iz veri importante! Write Judge07 ingame mail that you saw this message!!!!';
				return;
			}
			
			$user_corp = get_user_meta ( $user_id, 'Char_corp_asign', true );
			if($user_corp!=null){
				// render timepicker fields
				echo $this->render_datepicker_fields ();
				// handle the GET values
				$get_values_array = $this->handle_GET_values_non_admin_UI ();
				// show the selection
				echo '<p class="datafromto">Spai period: '.$get_values_array ['T1'] . ' -> ' . $get_values_array ['T2'].'</p>';
				//find out what corp user belongs to
				
			
// 				$sql = "SELECT * FROM " . $wpdb->prefix . "tr_corporations WHERE id=$user_corp";
				// go trough array and find unique
				
// 				$corp_data_array = $wpdb->get_row ( "$sql", ARRAY_A );
				
// 				echo'corp data array<pre>';
// 				echo var_dump($corp_data_array);
// 				echo'</pre>';

				
				//render the table of each char total isk
				$this->display_in_time_period ( $get_values_array ['T1'], $get_values_array ['T2'] );
				
				// render some kind of visual thing.
				$this->render_graph( $get_values_array ['T1'], $get_values_array ['T2'] );
			
// 				echo'*Data is refreshed every 30 min.<br>';
// 				echo'**More functionality coming soon&trade;<br>';
// 				echo'*** Thanks to <b>Judge07</b>, <b>Jonathan Doe</b>, <b>biggus dickus Aurilen</b> and <b>Hhatia</b>';
			}else{
				echo'You are totally a spai, PM an officer ingame to confirm your spainess before you can proceed.';
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
			
			// get the user id
			$user_id = get_current_user_id ();
			$user_meta = get_user_meta ( $user_id, 'Char_corp_asign', true );
	
			// pull only this users corporation data
			$sql = "SELECT * FROM `" . $wpdb->prefix . "tr_corporations` WHERE id='$user_meta';";
			
			$corp_data = $wpdb->get_row ( $sql, ARRAY_A );
			
			
			// define the tax and count of top ratters
			$raturn_tax=$corp_data['corp_return_percent'];
			$top_ratters_count=$corp_data['corp_top_ratter_count'];
			
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
			$i=0;
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
				
// 				$kek=$char ['total']*0.25;
				$kek=$raturn_tax*$char ['total']/100;
				
				if($i>=$top_ratters_count){
					$kek=0;
				}
				
				//show payable amount without spaces for admin. for copy paste.
// 				if($current_user->user_login=='admin'){
// 					echo number_format ( $kek, 2, ',', '' );
// 				}else{
					
// 				}
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
			
			/*
			 * TODO 
			 * #show/hide the top 5 pvp persons.
			 * #get start date dynamicaly.
			 */
			if($corp_data['show_top5_pvp']==1){
				if ($chars != null) {
					if($start > '2017-10-01 09:00:00'){
						$this->show_top_five_pvp_killers($start, $end,$remaining );
					}else{
						echo'<p class="spai_better_no_data">PVP data not aviable before "The Purge" *sad fejs*<p>';
					}
				}else{
					echo'Welp! no one was ratting, so there is no pvp prizes...';
				}
			}
			echo '<br><br>';
			
			/*
			 * only show this for officers
			 */
			if(current_user_can('administrator')){
				echo'<div class="format_isk_div"><button id="format_isk" class="format_isk_btn">PRESS ME !</button></div>';
			}
			
			
			
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
// 		echo '<form method="get">';
// 		echo '<p> from <input type="text"  id="T1" name="T1" /></p>';
// 		echo '<p> to <input type="text"  id="T2" name="T2" /></p>';
// 		echo '<p><input type="submit" value="show" /></p>';
// 		echo '</form>';
// 		echo '<div class="clearfix"></div>';
		
		
		
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
	 * Render ADMIN/ officer ui for editing corp api data
	 *
	 * renders the admin page for editing/updaring corp api.
	 * and assigning users to corps.
	 *
	 * @return null
	 */
	public function render_admin_ui() {
		// show only for admins
		if (current_user_can ( 'manage_options' )) {
			global $wpdb;
			
			echo'<p>This is where you assign users visibility for corporation.If in doubt PM Judge07 ingame</p>';
			echo'<p>Information format:<br>';
			echo'ID-login-> coporation</p>';
			
			
			
			$sql = "SELECT ID,user_nicename FROM `" . $wpdb->prefix . "users` ";
			$all_users = $wpdb->get_results ( "$sql", ARRAY_A );
			
// 			echo '<form method="POST">';
			echo '<form action="' . get_admin_url () . 'admin-post.php" method="post">';
			echo '<input type="hidden" name="action" value="tr_action" />';
			echo '<div class="users_corps">';
			
			foreach ( $all_users as $user ) {
				echo '<p>' . $user ['ID'] . '-' . $user ['user_nicename'] . ' -> ' . $this->render_user_assignment ( $user ['ID'] ) . '</p>';
			}
			echo'</div>';
			echo '<input type="submit" value="save" />';
			echo '</form>';
		}
	}
	/**
	 * Render the select element next to each users field.
	 *
	 * renders the admin page for editing/updaring corp api.
	 * and assigning users to corps.
	 *
	 * @return null
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
	 * Render Graph for data selection
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

// 		echo $start.'****'.$end;
		
// 		echo'<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>';
		
		echo'<script type="text/javascript" src="https://www.google.com/jsapi"></script>';
		
		
		$this->output_chart1_js_code($start, $end);
		
		$this->output_chart_2_js_code($start, $end);
		

	}
	/**
	 * testing click on  series to hide it.
	 * https://codepen.io/anon/pen/jVaEqW
	 * 
	 * make it in to seperate file 
	 * http://stackoverflow.com/questions/13219015/google-charts-external-javascript-issue
	 * another way to load with extrenal file
	 * https://groups.google.com/forum/#!topic/google-visualization-api/F0NcpEgt2TA
	 * //how to do it win wordpress
	 * https://www.worldoweb.co.uk/2014/adding-google-charts-wordpress-blog-part-1
	 * 
	 * 
	 */
	public function testing_random_javascript(){
		
		
		
		
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
		
/*
 * 1354
 * 103 = names
 * 1152 = chart +title - names
 * 1255
 *  difference = 99 px 
 *  
 */

		
		
		
		
		
		
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
	 */
	public function structures_incomes(){
		
		echo'<pre> Structures incomes ---</pre>';
		
		if(is_user_logged_in()==true) {
			global $wpdb;
				
			$user_id=get_current_user_id();
			$user_corp = get_user_meta ( $user_id, 'Char_corp_asign', true );
			if($user_corp!=null){
				if(current_user_can('administrator')){
					
					$sql = "SELECT * FROM " . $wpdb->prefix . "tr_corporations WHERE id=$user_corp";
					// go trough array and find unique
						
					$corp_data_array = $wpdb->get_row ( "$sql", ARRAY_A );
			
					$xml = new Top_Ratter ();
					$xml->update_ratting_data ( $corp_data_array );
					
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
// 					echo $sql;
					
					
					if ($structures_data != null) {
						echo '<table class="tr_selection">
								<thead>
								<tr>
								<th class="charname"><span>Who</span></th>
								<th class="charname" ><span>When</span></th>
								<th class="tax_right" ><span>refTypeID</span></th>
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
								<td class="bottom_border_2"></td>
								<td class="bottom_border_2">Total:</td>	
								<td class="align_right bottom_border_2">'.number_format ( $total, 2, ',', ' ' ).'</td>
								</tr>';
						
						foreach ( $structures_data as $record ) {
							echo '<tr>';
							echo'<td>';
							echo $record ['who_used'];
							echo '</td>
								<td  class="align_right">';
							echo $record ['date_acquired'];
							echo '</td>
								<td  class="align_right">';
							if($record ['refTypeID']=='120'){
								echo'Industry Job Tax';
								
							}elseif($record ['refTypeID']=='128'){
								echo'Jump Clone Activation Fee';
							}else{
								echo'Jump Clone Installation Fee';
							}
// 							echo $record ['refTypeID'];
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
		
// 					echo"<br>------vardump------<br><pre>";
// 					echo var_dump($structures_data);
// 					echo"</pre><br>----------<br>";

				}else{
					echo'R.I.P. No Access for you...';
				}
			}else{
				echo'Officers only biach!';
			}
				
		}else{
			// register/ login.
			echo'Wow, Such Spai, Much Look, Very Need login for access!';
		}
		
		

		
		
	}

	/**
	 * Parse the Zkill page and get top 5 kills for the current month
	 * 
	 * https://zkillboard.com/corporation/98342863/top/
	 * 
	 * @ return void
	 */
	public function cronjob_triger_shortcode_function(){
		
// 		ini_set('memory_limit','250M');
		Global $wpdb;
		
// 		echo'<pre>shortcode triger function</pre>';
		
		//get all corps
		$sql="SELECT * FROM `" . $wpdb->prefix . "tr_corporations`";
		$corp_table=$wpdb->get_results($sql,ARRAY_A);
		
		if($corp_table!=false){
			$top_rat=new Top_Ratter();
			//run for each corp
			foreach($corp_table as $corporation){
				
				
// 				echo'corporation var<pre>';
// 				echo var_dump($corporation);
// 				echo'</pre>';
				
// 				echo'<pre>shortcode triger function</pre>';

				/*
				 * Redo the gather mechanism because this will run the xml call again
				 * but the xml timer has been set already so it will return null as to not hammer the xml endpoint
				 * 
				 * So for now just use one corp.
				 * 
				 */
				
				//update ratting data
				$top_rat->update_ratting_data ( $corporation );
				
				
				//update zkill data
				$top_rat->gather_zkill_data_for_corporation($corporation['corporation_id']);
				
				//update user list.
				$top_rat->update_corp_member_list_in_db ( $corporation );
// 						echo"<pre>";
// 						echo var_dump($corporation);
// 						echo"</pre>";
				
			}
			
			
			
		}
		
		
		
		


		
		
		
// 		echo"<pre>";
// 		echo var_dump($data);
// 		echo"</pre>";
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
	}
	
	/**
	 * Renders Top 5 pvp killers
	 * 
	 * @param $start
	 * @param $end
	 * 
	 * @ return void
	 */
	public function show_top_five_pvp_killers($start, $end,$remaining){
		Global $wpdb;
		

// 		echo'<p class="spai_better_no_data">Thanks to Zkillboard team changing their API from black to white with orange spots
// 					that sometimes are inside squares but sometimes are pink there is no kills on this page. You
// 					are welcome to check the Zkill on your own. Sorry folks for the PVP reward fuck up, blame Zkill :D<br> P.S. Our IT team is working vigorously to fight this nonsence and deliver you the most l33t pvpers!<p><br>';
// 		return;
		
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
// 		echo'<pre>';
// 		echo var_dump($top_pvpers);
// 		echo'</pre>';
		
		
		
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
// 			for($i=1;$i<=5;$i++){
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
// 			echo'<p class="spai_better_no_data">Thanks to Zkillboard team chaning their API from black to white with black spots 
// 					that sometimes are inside squares but sometimes are pink there is no temporary kills on this page. You 
// 					are welcome to check the Zkill on your own. Sorry folks for the PVP reward fuck up, blame Zkill :D<p><br>';
		}
		
// 		$corp_gets=$remaining-$total_pvp_prizes;
// 		echo 'Remaining:'. number_format ( $corp_gets, 2, ',', ' ' );
		
	}
	
	/**
	 * Renders Main Char selection drob box in 'myAccount'
	 *
	 * Allows loged in user to select his main char from his assigned chars.
	 *
	 * @return void
	 */
	public function render_main_char_selection_by_user(){
	
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

	/**
	 * Assign characters to the user.
	 *
	 * If there is no chars yet, first assigned character will be the main.
	 * Already assigned characters do not show up in the aviable chars.
	 *
	 * @return void
	 */
	public function render_admin_assign_related_characters(){
		global $wpdb;
		/*
		 * select all chars from wp_tr_characters
		 * 
		 * select all chars from relation table 
		 * 
		 * remove chars that is in the relation table from all the chars 
		 * 
		 * show a number of assigned chars to the username [3] including main.
		 * 
		 * show all chars that is not refferenced in the  wp_tr_characters_relations on the RIGH
		 *  
		 * show all the referenced characters on the LEFT
		 */
		
		
		
		if ( current_user_can( 'manage_options' ) ) {
			/* A user with admin privileges */
			
			//get user corp data
			$admin_user_id=get_current_user_id();
			$user_corp = get_user_meta ( $admin_user_id, 'Char_corp_asign', true );
			$sql = "SELECT * FROM " . $wpdb->prefix . "tr_corporations WHERE id=$user_corp";
			// go trough array and find unique
			
			$corp_data_array = $wpdb->get_row ( "$sql", ARRAY_A );
			$xml = new Top_Ratter ();
			
			//run the updater
// 			echo'update chars for corp [uncoment]';
// 			$xml->update_corp_member_list_in_db ( $corp_data_array );
			
			
			
// 			$sql="SELECT * FROM `" . $wpdb->prefix . "tr_characters`";
// 			$all_chars=get_results($sql,ARRAY_A);
		
// 			$sql="SELECT * FROM `" . $wpdb->prefix . "tr_characters_relations`";
// 			$related_chars=get_results($sql,ARRAY_A);
			
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
				
// 				$user_id=$_GET ['aacfu'];
				
// 				echo'show stuff for user';
				
				$tr=new Top_Ratter();
				
				$related_chars=$tr->get_character_assigned_chars($user_id);

				//select only chars for this corp.
				$not_related_chars=$tr->get_not_assigned_chars($user_corp);
				
				/*
				 * MAKE THAT IT STAYS ON THE USER BY GET VALUE.
				 */
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

// 			echo'related chars <pre>';
// 			echo var_dump($related_chars);
// 			echo'</pre>';
			
// 			echo' NOT related chars <pre>';
// 			echo var_dump($not_related_chars);
// 			echo'</pre>';

		}	
	}
	
	/**
	 * Renders page where user can manage isk for druglords reactions pos.
	 *
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
		
		Echo'Here be Jdoe druglords stuff';
	}
	
	
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