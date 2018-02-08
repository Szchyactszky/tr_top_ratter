<?php
/**
 * The tr_top ratter is a core plugin that handles and insantiates all other plugin files.
 *
 * The top_ratter includes  class-top_ratter_render class and top_ratter_xml_api class.
 * all calculations table cheks and any other kind of processing is done in this class file.
 *
 *
 */
class Top_Ratter {
	/**
	 * Starts up the plugin, enques styles,scripts and checks for tables.
	 *
	 * This function starts up the plugin. it checks for tables , enques styles and scripts that is used for this plugin.
	 * It also instantiates the render class for shortcodes to be available anywhere in the wp.
	 *
	 * @return void
	 */
	public function run() {
		// include other classes
		require_once plugin_dir_path ( __FILE__ ) . 'class-top-ratter-render.php';
		require_once plugin_dir_path ( __FILE__ ) . 'class-top-ratter-sso.php';
		
		// enque styles for this plugin
		add_action ( 'wp_enqueue_scripts', array (
				$this,
				'register_plugin_styles' 
		) );
		// enque jquery scripts for this plugin
		add_action ( 'wp_enqueue_scripts', array (
				$this,
				'register_plugin_script' 
		) );
		// add submit action form to redirect and catch from admin.php
		add_action ( 'admin_post_tr_action', array (
				$this,
				'prefix_admin_tr_action' 
		) );
		
		// check if plugin tables exist
		$this->table_check ();
		
		// instantiate the render class for shortcodes to work
		$shortcodes = new Top_Ratter_Render ();
	}
	/**
	 * Registers plugin scrips
	 *
	 * This function enques the scripts for wordpress the proper way
	 *
	 * @return void
	 *
	 */
	public function register_plugin_script() {
		wp_enqueue_script ( 'tr-execute', plugin_dir_url ( __FILE__ ) . '../js/execute.js', array (
				'jquery' 
		) );
		wp_enqueue_script ( 'tr-jquery-custom', plugin_dir_url ( __FILE__ ) . '../js/jquery-ui.min.js', array (
				'jquery' 
		) );
		// wp_enqueue_script ( 'tr-jquery-charts', plugin_dir_url ( __FILE__ ) . '../js/charts.js', array (
		// 'jquery'
		// ) );
		// include the new datachart file.
		// wp_enqueue_script ( 'tr-jquery-charts', plugin_dir_url ( __FILE__ ) . '../js/define_chart_code.js', array (
		// 'jquery'
		// ) );
		// enque the google jsapi from url
		// wp_enqueue_script ( 'kek-chart', 'https://www.google.com/jsapi');
		// wp_enqueue_script ( 'kek-chart2', 'https://www.gstatic.com/charts/loader.js');
	}
	/**
	 * Registers plugin styles
	 *
	 * This function enques the styles for wordpress the proper way
	 *
	 * @return void
	 */
	public function register_plugin_styles() {
		wp_register_style ( 'tr-plugin-styles', plugins_url ( 'tr_top_ratter/css/tr_top_ratter.css?v=' . microtime () ) );
		wp_enqueue_style ( 'tr-plugin-styles' );
		
		wp_register_style ( 'tr_jquery_custom_style', plugins_url ( 'tr_top_ratter/css/jquery-ui.min.css' ) );
		wp_enqueue_style ( 'tr_jquery_custom_style' );
	}
	/**
	 * Checks for plugin tables
	 *
	 * This function looks in the database and checks for required tables for this plugin.
	 * if tables are not found they are created.
	 *
	 * @return void
	 */
	public function table_check() {
	    // check if tables exist then create if not
	    global $wpdb;
	    
	    $required_tables = array (
	        "$wpdb->prefix" . "tr_ratting_data",
	        "$wpdb->prefix" . "tr_characters",
	        "$wpdb->prefix" . "tr_structures_income",
	        "$wpdb->prefix" . "tr_pvp_chars_kills",
	        "$wpdb->prefix" . "tr_users_chars",
	    )
	    
	    ;
	    foreach ( $required_tables as $table ) {
	        $val = $wpdb->get_var ( "SHOW TABLES LIKE '$table'" );
	        if ($val == $table) {
	            // exists//
	        } else {
	            // create non existing
	            $this->create_table ( $table );
	        }
	    }
	}
	/**
	 * Creates table
	 *
	 * This function is helper function table_check function.
	 * It creates tables
	 *
	 * @param string $table
	 *        	The name of the table to create
	 *        	
	 * @return void
	 *
	 */
	private function create_table($table) {
	    global $wpdb;
	    require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
	    switch ($table) {
	        
	        case "$wpdb->prefix" . "tr_ratting_data" :
	            $sql = "CREATE TABLE `" . $wpdb->prefix . "tr_ratting_data`(
						  `id` INT(200) NOT NULL AUTO_INCREMENT,
						  `owner_id` INT(100) NOT NULL COMMENT 'references a char from chars table',
						  `date_acquired` DATETIME NOT NULL COMMENT 'when char gets its ticks ingame',
						  `amount` FLOAT NOT NULL,
                          `system_id` INT(20) NULL,
                          `npc_kills` INT(20) NULL COMMENT 'total amount of npc kills within the tick',
                          `ref_id` VARCHAR(100) NOT NULL,
						  PRIMARY KEY(`id`),
                          UNIQUE (`ref_id`)
						) ENGINE = InnoDB;";
	            
	            dbDelta ( $sql );
	            break;
	        case "$wpdb->prefix" . "tr_characters" :
	            $sql = "CREATE TABLE `" . $wpdb->prefix . "tr_characters`(
						  `id` INT(200) NOT NULL AUTO_INCREMENT,
                          `corp_id` INT(10) NOT NULL,
						  `owner_id` INT(100) NOT NULL,
						  `ownerName2` VARCHAR(200) NOT NULL,
						  PRIMARY KEY(`id`),
						  UNIQUE (`owner_id`)
						) ENGINE = InnoDB;";
	            
	            dbDelta ( $sql );
	            break;
	        case "$wpdb->prefix" . "tr_structures_income" :
	            $sql = "CREATE TABLE `" . $wpdb->prefix . "tr_structures_income`(
						  `id` INT(250) NOT NULL AUTO_INCREMENT,
						  `ref_id` VARCHAR(100) NOT NULL,
						  `date_acquired` DATETIME NOT NULL,
						  `amount` FLOAT NOT NULL,
                          `ref_type` VARCHAR(100) NOT NULL,
						  PRIMARY KEY(`id`)
						) ENGINE = InnoDB;";
	            
	            dbDelta ( $sql );
	            break;
	            
	        case "$wpdb->prefix" . "tr_pvp_chars_kills" :
	            $sql = "CREATE TABLE `" . $wpdb->prefix . "tr_pvp_chars_kills`(
						  `id` INT(250) NOT NULL AUTO_INCREMENT,
						  `char_id` VARCHAR(250) NOT NULL,
						  `corp_id` VARCHAR(250) NOT NULL,
						  `kill_id` VARCHAR(225) NOT NULL,
						  `timestamp` DATETIME NOT NULL,
						  PRIMARY KEY(`id`),
						 UNIQUE KEY `character_kills` (`char_id`,`kill_id`)
						) ENGINE = InnoDB;";
	            
	            dbDelta ( $sql );
	            break;
	        case "$wpdb->prefix" . "tr_users_chars" :
	            $sql = "CREATE TABLE `" . $wpdb->prefix . "tr_users_chars`(
						`uc_id` INT(10) NOT NULL AUTO_INCREMENT ,
						`user_id` INT(10) NOT NULL ,
						`char_id` INT(10) NOT NULL ,
						`is_main_char` INT(1) NOT NULL ,
						PRIMARY KEY (`uc_id`)
						) ENGINE = InnoDB;";
	            
	            dbDelta ( $sql );
	            break;
	    }
	}
	
	/**
	 * Updates the ratting +structures data table with newest entries from api.
	 *
	 * This function pulls new data from SSO api class and inserts it in to the table.
	 * It first checks for the user in tr_characters table and adds new user if it doesnt exist.
	 * then it processes the data from SSO api, by checking last inserted reftype_id and continue from there.
	 *
	 * @return void
	 */
	public function update_ratting_data() {
		global $wpdb;
		$sso = new Top_Ratter_SSO ();
		// get the ratting data from sso class
		$journal_records=$sso->esi_api_gather_ratted_isk_amount();
		
		if ($journal_records == null) {
		    echo'<p>Problem with API :(</p>';
			return;
		}
		if($journal_records=='no_officer_token'){
		    echo'<p>There is no officer token, inform officers. Pretty please?</p>';
		    return;
		}

		$bounties_array = $this->filter_ratting_data ( $journal_records);
		$structures_array = $this->filter_ratting_data ( $journal_records,true);
		
		/*
		 * Bounties akka ratting data to be inserted.
		 */
		if($bounties_array){
		    
		  $this->insert_new_characters_in_database($bounties_array);
		  
		  $bounties_to_insert=null;
		  
		  $sql_max_ref_id="SELECT MAX(`ref_id`) AS ref_id FROM `" . $wpdb->prefix . "tr_ratting_data`";
		  $max_ref_id=$wpdb->get_row($sql_max_ref_id,ARRAY_A);  
		  
		  if($max_ref_id['ref_id']!==NULL){
		      foreach($bounties_array as $bounty_record){
		          // select only those that is not in the db
		          if($bounty_record['ref_id']>(float)$max_ref_id['ref_id']){
		              //this is new record that is not in the db yet
		              $bounties_to_insert[]=$bounty_record;
		          }  
		      }
		  }else{
                // insert all since nothing in db
		      $bounties_to_insert=$bounties_array;
		  }
		  
		  if($bounties_to_insert){
		      $sql="INSERT INTO `" . $wpdb->prefix . "tr_ratting_data`(`owner_id`, `date_acquired`, `amount`, `system_id`, `npc_kills`, `ref_id`) VALUES";
		      $count=count($bounties_to_insert);
		      $i=1;
		      foreach($bounties_to_insert as $record){
		          $system_id=0;
		          if($record['extra_info']['system_id']){
		              $system_id=$record['extra_info']['system_id'];
		          }
		          $sql.='("'.$record['second_party_id'].'","'.$record['date'].'","'.$record['tax'].'","'.$system_id.'","'.$record['npc_kills'].'","'.$record['ref_id'].'")';
		          
		          if($i==$count){
		              $sql.='';
		          }else{
		              $sql.=',';
		          }
		          $i++;
		      }
		      $sql.=';';
		      $wpdb->query($sql);
		  } 
		}
		
		/*
		 * Structures data insert starts here
		 */
		if($structures_array){
		    $sql="INSERT INTO `" . $wpdb->prefix . "tr_structures_income`(`ref_id`, `date_acquired`, `amount`, `ref_type`) VALUES";
		    $count=count($structures_array);
		    $i=1;
		    foreach($structures_array as $record){
		  
		        $sql.='("'.$record['ref_id'].'","'.$record['date'].'","'.$record['amount'].'","'.$record['ref_type'].'")';
		        
		        if($i==$count){
		            $sql.='';
		        }else{
		            $sql.=',';
		        }
		        $i++;
		    }
		    $sql.=';';
		    $wpdb->query($sql); 
		}
		
		
		
		
		
		
		
	}
	/**
	 * Filters the data with valid refTypeID
	 *
	 * This function filters out the unnecessary refTypeID entries and return filtered array
	 * 
	 * @param string $journal_records data retrieved from API containing corporation journal records.
	 *        	
	 * @return $filtered_data associative array of filtered data containing only valid entries.
	 */
	public function filter_ratting_data($journal_records, $switch = null) {
		if ($switch == null) {
			// define only valid refTypeID in the ratting
		    if ($journal_records != null) {
				$valid_refTypeID = array (
						'bounty_prizes' 
				);
				$filtered_data = null;
				foreach ( $journal_records as $key=>$entry ) {
					if (in_array ( $entry ['ref_type'], $valid_refTypeID )) {
					    
					    //parse the npc kills and summ them then make new array record 'npc_kills'
					    $no_commas= explode(",", $entry['reason']);
					    $npc_kills=0;
					    if($no_commas){
					        foreach($no_commas as $value){
					            $no_seperator= explode(":", $value);
					            $npc_kills+=(int)$no_seperator[1];
					        }
					    }
					    $journal_records[$key]['npc_kills']=$npc_kills;
					    					    
					    /*
					     * change the date to mysql compatible date
					     */
					    $mysql_date = str_replace('T', ' ', $entry['date']);
					    $mysql_date = str_replace('Z', '', $mysql_date);
					    $journal_records[$key]['date']=$mysql_date;
					    
					    $filtered_data [] =  $journal_records[$key];
					}
				}
			}
			
			return $filtered_data;
			
		} elseif ($switch == 'structures') {
			// http://eveonline-third-party-documentation.readthedocs.io/en/latest/xmlapi/corporation/corp_walletjournal.html
		    if ($journal_records != null) {
				$valid_refTypeID = array (
						'docking_fee',
						'office_rental_fee',
    				    'factory_slot_rental_fee',
    				    'corporation_dividend_payment',
    				    'jump_clone_installation_fee',
    				    'manufacturing',
    				    'researching_technology',
    				    'researching_time_productivity',
    				    'researching_material_productivity',
    				    'copying',
    				    'reverse_engineering',
    				    'jump_clone_activation_fee',
    				    'reprocessing_tax',
    				    'industry_job_tax' 
				);
				$filtered_data = null;
				foreach ( $journal_records as $key=>$entry ) {
					if (in_array ( $entry ['ref_type'], $valid_refTypeID )) {
					    /*
					     * also cehck for negative values and only add if its positive.
					     */
					    if($entry['amount']>0){
					        
					        $mysql_date = str_replace('T', ' ', $entry['date']);
					        $mysql_date = str_replace('Z', '', $mysql_date);
					        $journal_records[$key]['date']=$mysql_date;
					        
					        
					        
					        $filtered_data [] =  $journal_records[$key];
					    }	
					}
				}
				
				// sort the array by date ascending starting from oldest first.
// 				usort ( $filtered_data, array (
// 						$this,
// 						"date_compare" 
// 				) );

			}
			
			return $filtered_data;
		}
	}
	/** Inserts new characters in to the database.
	 * @param array $bounties_array response from SSO api endpoint
	 *
	 * @return void
	 */
	public function insert_new_characters_in_database($bounties_array){
	    Global $wpdb;
	    $sso = new Top_Ratter_SSO ();
	    $unique_char_id=array();
	    foreach($bounties_array as $bounty){
	        if (in_array ( $bounty ['second_party_id'], $unique_char_id )) {
	        }else{
	            //add to array
	            $unique_char_id[]=$bounty ['second_party_id'];
	        }
	    }
	    
	    //select all characters from database
	    $sql = "SELECT `owner_id` FROM `" . $wpdb->prefix . "tr_characters`";
	    $existing_chars = $wpdb->get_results("$sql",ARRAY_A);
	    if($existing_chars){
	        //filter out those that is not in database
	        foreach($existing_chars as $char){
	            if (in_array ( $char['owner_id'], $unique_char_id )) {
	                if (($key = array_search($char['owner_id'], $unique_char_id)) !== false) {
	                    unset($unique_char_id[$key]);
	                }
	            }
	        }
	        
	        if($unique_char_id){
	            $new_character_data=$sso->owner_ids_to_names($unique_char_id);
	        }
	    }else{
	        if($unique_char_id)
	            $new_character_data=$sso->owner_ids_to_names($unique_char_id);
	    }
	    
	    if($new_character_data){
	        // insert in the database
	        $sql="INSERT INTO `" . $wpdb->prefix . "tr_characters`(`owner_id`, `ownerName2`) VALUES";
	        $count=count($new_character_data);
	        $i=1;
	        foreach($new_character_data as $record){
	            $sql.='("'.$record['character_id'].'","'.$record['character_name'].'")';
	            
	            if($i==$count){
	                $sql.='';
	            }else{
	                $sql.=',';
	            }
	            $i++;
	        }
	        $sql.=';';
	        
	        $wpdb->query($sql);
	    }
	}
	/**
	 * USORT custom helper function
	 *
	 * This function compares to data and return 0 negative or positive number for usort function
	 * more info http://stackoverflow.com/questions/6401714/php-order-array-by-date
	 * used as custom helper function in filter_ratting_data function.
	 *
	 * @param date $a
	 *        	date a
	 * @param date $b
	 *        	date b
	 *        	
	 * @return result of substracting one dae from another.
	 */
	public function date_compare($a, $b) {
		$t1 = strtotime ( $a ['date'] );
		$t2 = strtotime ( $b ['date'] );
		return $t1 - $t2;
	}
	/**
	 * Gathers data by specified date from the db
	 *
	 * This function gets all data from the tr_ratting_data for specified time period.
	 *
	 *
	 * @param date $start
	 *        	date to display from
	 * @param date $end
	 *        	date to display until
	 *        	
	 * @return $char_array array of totals for each character.
	 */
	public function gather_data_by_date($start, $end) {
		global $wpdb;
		
		
		

		// pull all characters
		$sql = "SELECT * FROM `" . $wpdb->prefix . "tr_characters`";
		
		$characters = $wpdb->get_results ( "$sql", ARRAY_A );
		
		if ($characters != null) {
			
			$char_array = null;
			
			foreach ( $characters as $char ) {
				// get characters total isk.
				$summ = $this->calculate_char_total_tax ( $start, $end, $char ['owner_id'] );
				
				// add to array if there is any value.
				if ($summ > 0) {
					$char_array [] = array (
							'owner_id' => $char ['owner_id'],
							'ownerName2' => $char ['ownerName2'],
							'total' => $summ 
					);
				}
			}
			
			if ($char_array != null) {
				// sort the array highest- lowest
				usort ( $char_array, array (
						$this,
						"isk_compare" 
				) );
			}
		}
		return $char_array;
	}
	/**
	 * USORT custom helper function
	 *
	 * This function compares to data and return 0 negative or positive number for usort function
	 * more info http://stackoverflow.com/questions/6401714/php-order-array-by-date
	 * used as custom helper function in filter_ratting_data function.
	 *
	 * @param float $a
	 *        	isk
	 * @param float $b
	 *        	isk
	 *        	
	 * @return result of substracting one dae from another.
	 */
	public function isk_compare($a, $b) {
		/*
		 * cast to int since it might be misbehaving with float
		 *
		 */
		$t1 = $a ['total'];
		$t2 = $b ['total'];
		
		return $t2 - $t1;
		// return $t2 - $t1;
	}
	/**
	 * Calculates character total tax isk withing time period
	 *
	 * This function reads database and selects data for specific character in specific time period.
	 *
	 * @param date $start
	 *        	date to display from
	 * @param date $end
	 *        	date to display until
	 * @param int $owner_id 
	 *        	
	 * @return $summ summ of isk
	 */
	public function calculate_char_total_tax($start, $end, $owner_id) {
		global $wpdb;
		$summ = null;
		
		$sql = "SELECT * FROM `" . $wpdb->prefix . "tr_ratting_data` WHERE `date_acquired` BETWEEN '" . $start . "' AND '" . $end . "' AND `owner_id`='" . $owner_id . "'";
		// echo $ql;
		$arry_of_ticks = $wpdb->get_results ( "$sql", ARRAY_A );
		
		// echo var_dump($arry_of_ticks);
		
		if (count ( $arry_of_ticks ) > 0) {
			
			foreach ( $arry_of_ticks as $tick ) {
				
				$summ += $tick ['amount'];
			}
		}
		return $summ;
	}
	/**
	 * HAndles Admin.php submit values
	 *
	 * This function catch all the values that is submited to admin.php
	 * Its a wordpress way of submiting values
	 *
	 *
	 * @return null
	 */
	public function prefix_admin_tr_action() {
		global $wpdb;
		
		// This is when user sets main char submit handle
		if (isset ( $_POST ['user_select_main_char'] )) {
			// update all chars as main char=0
			$data = array (
					'is_main_char' => '0' 
			);
			$where = array (
					'user_id' => $_POST ['user_select_main_char_u_id'] 
			);
			$wpdb->update ( $wpdb->prefix . 'tr_users_chars', $data, $where );
			
			// update the selected one as main char=1
			$data = array (
					'is_main_char' => '1' 
			);
			// where user is this and related char id is the new master
			$where = array (
					'user_id' => $_POST ['user_select_main_char_u_id'],
					'char_id' => $_POST ['user_select_main_char'] 
			);
			$wpdb->update ( $wpdb->prefix . 'tr_users_chars', $data, $where );
		}

		
		wp_redirect ( $_SERVER ['HTTP_REFERER'] );
		exit ();
	}
	/**
	 * Prepares data to be showin in graph
	 *
	 * This prepares the data to be displayed in google charts java aplet.
	 *
	 * @param date $start        	
	 *
	 * @param date $end        	
	 *
	 * @return $prepared_array
	 */
	public function prepare_data_for_chart_by_days($start, $end) {
		global $wpdb;
		

		$sql = "SELECT * FROM `" . $wpdb->prefix . "tr_characters`";
		$characters = $wpdb->get_results ( "$sql", ARRAY_A );
		
		if ($characters != null) {
			
			$chars_by_days = null;
			
			foreach ( $characters as $char ) {
				// run trough each char and create this array of date->amount for selected time period FOR EACH DAY WITHIN
				$character_data = $this->prepare_character_data_by_days_for_chart ( $start, $end, $char ['owner_id'] );
				
				
				
				if ($character_data != null) {
				    //BY CHAR ID NOT NAME
					$chars_by_days [$char ['ownerName2']] = $character_data;
				}
			}
			
			
			/*
			 * data should be sorted by main chars here.
			 */
			
			$chars_by_days = $this->calculate_chart_data_by_mains ( $chars_by_days );
			
			if ($chars_by_days == null) {
				return;
			}
			
			/*
			 * mold the array in a sutable format for google charts
			 */
			$prepared_array = null;
			// add first row of the array
			$prepared_array [0] [0] = 'Date';
			
			foreach ( $chars_by_days as $key => $value ) {
				// echo $key;
				$prepared_array [0] [] = $key;
			}
			
			
			$start2 = new DateTime ( $start );
			$end2 = date ( "Y-m-d H:i:s", strtotime ( $end . " +1 day" ) );
			$end3 = new DateTime ( $end2 );
			
			$interval = DateInterval::createFromDateString ( '1 day' );
			
			$period = new DatePeriod ( $start2, $interval, $end3 );
			
			$a = 1;
			// run trough all dates
			foreach ( $period as $dt ) {
				$date = $dt->format ( "Y-m-d" );
				// add a date in begining
				$prepared_array [$a] [] = $date;
				// run trough each char and add a value from current day
				foreach ( $chars_by_days as $char ) {
					$prepared_array [$a] [] = $char [$date];
				}
				
				$a ++;
			}
		}
		
		return $prepared_array;
	
	}
	/**
	 * Prepares data for single character isk per day no acumulating
	 *
	 * This function makes an array for each day within selected time frame for single character
	 *
	 * @param date $start        	
	 *
	 * @param date $end        	
	 *
	 * @param date $character_id        	
	 *
	 * @return $ready_array sorted data by days-> amount
	 */
	public function prepare_character_data_by_days_for_chart($start, $end, $character_id) {
		global $wpdb;
		// get data for character
		$sql = "SELECT * FROM `" . $wpdb->prefix . "tr_ratting_data` WHERE `date_acquired` BETWEEN '" . $start . "' AND '" . $end . "' AND `owner_id`='" . $character_id . "'";
		$character = $wpdb->get_results ( "$sql", ARRAY_A );
		
		// if there is no data
		if ($character == false) {
			return null;
		}
		
		// loop trough every day in the interval and createy empty array
		$start2 = new DateTime ( $start );
		
		// add 1 day to the end date.
		$end2 = date ( "Y-m-d H:i:s", strtotime ( $end . " +1 day" ) );
		$end3 = new DateTime ( $end2 );
		// define 1 day interval
		$interval = DateInterval::createFromDateString ( '1 day' );
		$period = new DatePeriod ( $start2, $interval, $end3 );
		// define empty char array
		$character_data = null;
		// loop trough the time period
		foreach ( $period as $dt ) {
			
			$character_data [$dt->format ( "Y-m-d" )] = 0;
		}
		$temp2 = $character_data;
		$total_if_was_ratting_at_all = 0;
		// now put the data from db in to those fields
		foreach ( $character as $tick ) {
			
			// run trough the dates for ech tick
			foreach ( $character_data as $key => $value ) {
				
				// get only date , we dont need hours nd such
				$pieces = explode ( " ", $tick ['date_acquired'] );
				
				// if the date is the same as the date from the date loop array add the isk
				if ($pieces [0] == $key) {
					$temp2 [$key] += $tick ['amount'];
					$total_if_was_ratting_at_all += $tick ['amount'];
				}
			}
		}
		
		// dont return if he was not ratting at all.
		if ($total_if_was_ratting_at_all > 0) {
			return $temp2;
		}
		return null;
	}
	/**
	 * Prepares data to be showin in graph with acumulation
	 *
	 * This prepares the data to be displayed in google charts java aplet.
	 *
	 * @param date $start        	
	 *
	 * @param date $end        	
	 *
	 * @return $prepared_array
	 */
	public function prepare_data_for_chart_by_days_acumulated($start, $end) {
		// sort and order the array here.
		global $wpdb;
		
		$sql = "SELECT * FROM `" . $wpdb->prefix . "tr_characters`;";
		
		$characters = $wpdb->get_results ( "$sql", ARRAY_A );
		
		if ($characters != null) {
			
			$chars_by_days = null;
			
			foreach ( $characters as $char ) {
				// run trough each char and create this array of date->amount for selected time period FOR EACH DAY WITHIN
				$character_data = $this->prepare_character_data_by_days_for_chart_acumulating ( $start, $end, $char ['owner_id'] );
				if ($character_data != null) {
					$chars_by_days [$char ['ownerName2']] = $character_data;
				}
			}
			
			if ($chars_by_days == false) {
				return;
			}
			
			/*
			 * data should be sorted by main chars here.
			 */
			$chars_by_days = $this->calculate_chart_data_by_mains ( $chars_by_days );
			
			$prepared_array = null;
			// add first row of the array
			$prepared_array [0] [0] = 'Date';
			foreach ( $chars_by_days as $key => $value ) {
				// echo $key;
				$prepared_array [0] [] = $key;
			}
			
			
			$start2 = new DateTime ( $start );
			$end2 = date ( "Y-m-d H:i:s", strtotime ( $end . " +1 day" ) );
			$end3 = new DateTime ( $end2 );
			
			$interval = DateInterval::createFromDateString ( '1 day' );
			
			$period = new DatePeriod ( $start2, $interval, $end3 );
			
			$a = 1;
			// run trough all dates
			foreach ( $period as $dt ) {
				$date = $dt->format ( "Y-m-d" );
				// add a date in begining
				$prepared_array [$a] [] = $date;
				// run trough each char and add a value from current day
				foreach ( $chars_by_days as $char ) {
					$prepared_array [$a] [] = $char [$date];
				}
				
				$a ++;
			}
		}
		
		return $prepared_array;
	}
	/**
	 * Prepares data for a single char _acumulating
	 *
	 * This function makes an array for each day within selected time frame for single character
	 *
	 * @param date $start        	
	 *
	 * @param date $end        	
	 *
	 * @param date $character_id        	
	 *
	 * @return $ready_array sorted data by days-> amount
	 */
	public function prepare_character_data_by_days_for_chart_acumulating($start, $end, $character_id) {
		global $wpdb;
		// get data for character
		$sql = "SELECT * FROM `" . $wpdb->prefix . "tr_ratting_data` WHERE `date_acquired` BETWEEN '" . $start . "' AND '" . $end . "' AND `owner_id`='" . $character_id . "'";
		$character = $wpdb->get_results ( "$sql", ARRAY_A );
		
		// loop trough every day in the interval and createy empty array
		$start2 = new DateTime ( $start );
		
		// add 1 day to the end date.
		$end2 = date ( "Y-m-d H:i:s", strtotime ( $end . " +1 day" ) );
		$end3 = new DateTime ( $end2 );
		// define 1 day intervaö
		$interval = DateInterval::createFromDateString ( '1 day' );
		$period = new DatePeriod ( $start2, $interval, $end3 );
		// define empty char array
		$character_data = null;
		// loop trough the time period
		foreach ( $period as $dt ) {
			
			$character_data [$dt->format ( "Y-m-d" )] = 0;
		}
		$temp2 = $character_data;
	
		// now put the data from db in to those fields
		foreach ( $character as $tick ) {
			
			
			// run trough the dates for ech tick
			foreach ( $character_data as $key => $value ) {
				
				// get only date , we dont need hours nd such
				$pieces = explode ( " ", $tick ['date_acquired'] );
				
				// if the date is the same as the date from the date loop array add the isk
				if ($pieces [0] == $key) {
					$temp2 [$key] += $tick ['amount'];
					
				}
			}
		}
		$ready_array = null;
		$total_if_was_ratting_at_all = 0;
		$previous_amount = 0;
		// populate the array count previous + next
		// temp 2 is date- value
		foreach ( $temp2 as $key => $value ) {
			// add to the previous value existing value and save.
			$ready_array [$key] = $value + $previous_amount;
			
			$total_if_was_ratting_at_all += $value;
			$previous_amount = $value + $previous_amount;
		}
		
		// dont return if he was not ratting at all.
		if ($total_if_was_ratting_at_all > 0) {
			return $ready_array;
		}
		return null;

	}
	/**
	 * inserts XML char response data in to table
	 *
	 * @param date $corp_data_array        	
	 *
	 * @return void
	 */
	public function update_corp_member_list_in_db($corp_data_array) {
		// pull data from api
		$xml = new Top_Ratter_Xml_Api ();
		$chars = $xml->update_corp_member_list ( $corp_data_array );
		global $wpdb;
		
		if ($chars) {
			$sql = "INSERT IGNORE INTO `" . $wpdb->prefix . "tr_characters`(`corp_id`, `owner_id`, `ownerName2`) VALUES ";
			$tot_count = count ( $chars );
			$count = 0;
			foreach ( $chars as $char ) {
				$count ++;
				$sql .= '("' . $corp_data_array ['id'] . '","' . $char ['characterID'] . '","' . $char ['ownerName2'] . '")';
				
				if ($count == $tot_count) {
					$sql .= ";";
				} else {
					$sql .= ",";
				}
			}
			
			$wpdb->query ( $sql );
		}

	}
	
	/**
	 * Sorts the ratting data by main chars if such is defined.
	 *
	 * This function takes all char data array and adds all related chars data to main char if such exist.
	 *
	 *
	 * @param date $start
	 *        	date to display from
	 * @param date $end
	 *        	date to display until
	 *        	
	 * @param array $data_array
	 *        	Must contain ["total"]=> int, ["ownerName2"]=> string ( char name)
	 *        	
	 * @return $soreted_main_char_array array of totals for each main character.
	 */
	public function gather_data_by_main_chars($start, $end, $data_array) {
		global $wpdb;
		$unsorted_data = $data_array;
		

		// get main chars
		$sql = "SELECT * FROM `" . $wpdb->prefix . "tr_users_chars`
				JOIN " . $wpdb->prefix . "tr_characters charz ON " . $wpdb->prefix . "tr_users_chars.char_id=charz.id
				WHERE`is_main_char` ='1'";
		$main_chars = $wpdb->get_results ( $sql, ARRAY_A );
		
		/*
		 * loop trough all main chars
		 * check if teh records match any of the chars for this user and if they do add them to main char array
		 * and remove from ratting array
		 */
		if ($unsorted_data) {
			$sorted_mains = null;
			if ($main_chars) {
				foreach ( $main_chars as $main_char ) {
					// acquire main char users chars for this corp.
					$sql = "SELECT * FROM `" . $wpdb->prefix . "tr_users_chars` 
							JOIN " . $wpdb->prefix . "tr_characters charz ON " . $wpdb->prefix . "tr_users_chars.char_id=charz.id 
							WHERE `user_id`='" . $main_char ['user_id'] . "'
					";
					$related_chars = $wpdb->get_results ( $sql, ARRAY_A );
					
					// define main char depository and display name
					$sorted_mains [$main_char ['ownerName2']] ['total'] = 0;
					$sorted_mains [$main_char ['ownerName2']] ['ownerName2'] = $main_char ['ownerName2'];
					// loop trough unsorted data and put all users chars under main.
					foreach ( $unsorted_data as $char_index => $unique_char ) {
						
						// for each unsorted record compare for user assigned chars.
						foreach ( $related_chars as $alt_toon ) {
							if ($unique_char ['ownerName2'] == $alt_toon ['ownerName2']) {
								// this is one of related chars.
								$sorted_mains [$main_char ['ownerName2']] ['total'] += $unique_char ['total'];
								
								
								// unset the value for that unique char
								unset ( $unsorted_data [$char_index] );
							}
						}
					}
					// if no data dont show
					if ($sorted_mains [$main_char ['ownerName2']] ['total'] == 0) {
						unset ( $sorted_mains [$main_char ['ownerName2']] );
					}
				}
				/*
				 * now u have $sorted_mains array that contains data acumulated by mains
				 * and $unsorted_data array that contains data where no mains or alts remain.
				 * combine the array and return it.
				 */
				
				if ($unsorted_data) {
					if ($sorted_mains) {
						
						// add the remaining free chars to return array
						foreach ( $unsorted_data as $unsorted_char ) {
							$sorted_mains [] = $unsorted_char;
						}
					} else {
						
						// return unsorted cus there is nothing to sort.
						usort ( $unsorted_data, array (
								$this,
								"isk_compare" 
						) );
						return $unsorted_data;
					}
				}
				
				if ($sorted_mains) {
					usort ( $sorted_mains, array (
							$this,
							"isk_compare" 
					) );
				}
				
			}
		}
		return $sorted_mains;
	}
	/**
	 * Sorts the PVP data by main chars if such is defined.
	 *
	 * @param date $start 
	 * @param date $end
	 * @param multiarray $data_array
	 * 
	 * @todo Make admins be able to choose how much pvp chars to show not hardcoded 5
	 *        	
	 * @return $soreted_main_char_array array of totals for each main character.
	 */
	public function get_top5_pvp_kills_by_main_characters($start, $end) {
		global $wpdb;
		
		// get all kills
		$sql = "SELECT * FROM `" . $wpdb->prefix . "tr_sso_credentials`";
		
		$corp_data = $wpdb->get_row ( $sql, ARRAY_A );
		
// 		$sql = "SELECT count(`kill_id`) as total, `char_id`as ownerName2 FROM `" . $wpdb->prefix . "tr_pvp_chars_kills`
// 		WHERE `timestamp` BETWEEN '$start' AND '$end'
// 		AND `corp_id`='" . $corp_data ['corporation_id'] . "' GROUP BY `ownerName2` ORDER BY total DESC";
		
		$sql = "SELECT count(`kill_id`) as total, `char_id`," . $wpdb->prefix . "tr_characters.ownerName2 FROM `" . $wpdb->prefix . "tr_pvp_chars_kills`
		JOIN " . $wpdb->prefix . "tr_characters ON " . $wpdb->prefix . "tr_pvp_chars_kills.char_id=" . $wpdb->prefix . "tr_characters.owner_id 
		WHERE `timestamp` BETWEEN '$start' AND '$end'
		AND " . $wpdb->prefix . "tr_pvp_chars_kills.corp_id='" . $corp_data ['corporation_id'] . "' GROUP BY `ownerName2` ORDER BY total DESC";
		
		
		$top_pvpers = $wpdb->get_results ( $sql, ARRAY_A );
		
		// sort them by main chars.
		
		$sorted_kills_by_main = $this->gather_data_by_main_chars ( $start, $end, $top_pvpers );
		
		// sort the array desc
		
		if ($sorted_kills_by_main) {
			usort ( $sorted_kills_by_main, array (
					$this,
					"isk_compare" 
			) );
			
			$limit = 5;
			$top_five = null;
			foreach ( $sorted_kills_by_main as $character ) {
				if ($limit <1) {
					break;
				}
				$top_five [] = $character;
				$limit --;
			}
		}
		
		return $top_five;
	}
	
	/**
	 * Collects Related characters for the user if there is any.
	 *
	 * @param int $user_id        	
	 *
	 * @return array containing chars and their id.
	 */
	public function get_character_assigned_chars($user_id) {
		/*
		 * collect chars for the user main will allways be first
		 * related chars will come after
		 * if there is no records return null
		 * return otwherwise char id from table and char name
		 */
		global $wpdb;
		
		$sql = "SELECT * FROM `" . $wpdb->prefix . "tr_users_chars` 
				JOIN " . $wpdb->prefix . "tr_characters as charz ON " . $wpdb->prefix . "tr_users_chars.char_id=charz.id 
				WHERE `user_id`='$user_id'";
		
		$related_chars = $wpdb->get_results ( $sql, ARRAY_A );
		
		return $related_chars;
	}
	/**
	 * Collects characters that are not assigned to any user by corp
	 * @param int $corp_id
	 * 
	 * @return array containing chars and their id.
	 */
	public function get_not_assigned_chars($corp_id) {
		
		/*
		 * select chars whos id is not found in the relation table.
		 * try with double left join
		 * http://stackoverflow.com/questions/10968767/mysql-select-rows-that-do-not-have-matching-column-in-other-table
		 * 
		 * SELECT `ownerName2`
		 * FROM wp_tr_characters
		 * LEFT JOIN wp_tr_characters_relations ON wp_tr_characters_relations.related_char_id = wp_tr_characters.id
		 * WHERE wp_tr_characters_relations.related_char_id IS NULL;
		 *
		 *
		 */
		global $wpdb;
		
		$sql = "SELECT `ownerName2`,`corp_id`,`id`
FROM  " . $wpdb->prefix . "tr_characters
LEFT JOIN " . $wpdb->prefix . "tr_users_chars ON " . $wpdb->prefix . "tr_users_chars.char_id = " . $wpdb->prefix . "tr_characters.id
WHERE " . $wpdb->prefix . "tr_users_chars.char_id IS NULL AND " . $wpdb->prefix . "tr_characters.corp_id='$corp_id' ORDER BY `ownerName2`;";
		
		$unrelated_chars = $wpdb->get_results ( $sql, ARRAY_A );
		
		if (count ( $unrelated_chars ) > 0) {
			return $unrelated_chars;
		}
		
		return null;
	}
	/**
	 * Assigns chars to user
	 *
	 * @param int $user_id
	 *        	to assign to
	 * @param array $array_of_chars
	 *        	chars to assign to this user.
	 *        	
	 * @return void
	 */
	public function assign_chars_to_user($user_id, $array_of_chars) {
		global $wpdb;
		if (count ( $array_of_chars ) > 0) {
			
			// has user any chars?
			$sql = "SELECT * FROM `" . $wpdb->prefix . "tr_users_chars` WHERE `user_id`='$user_id'";
			// we only care about one.
			$has_chars = $wpdb->get_row ( $sql, ARRAY_A );
			
			if ($has_chars) {
				// user has chars so just insert ignore them
				$this->insert_ignore_into_user_chars ( $user_id, $array_of_chars );
			} else {
				// user has no chars so make first one as main and insert rest.
				
				// insert 1st as main
				$data = array (
						'user_id' => $user_id,
						'char_id' => $array_of_chars [0],
						'is_main_char' => '1' 
				);
				$wpdb->insert ( $wpdb->prefix . 'tr_users_chars', $data );
				// remove the first element
				array_shift ( $array_of_chars );
				// insert rest as not main.
				$this->insert_ignore_into_user_chars ( $user_id, $array_of_chars );
			}

		}
	}
	/**
	 * Performs SQL insert ingnore query
	 *
	 * @param int $user_id        	
	 *
	 * @param int $data_array
	 *        	to insert
	 *        	
	 * @return void
	 */
	public function insert_ignore_into_user_chars($userr_id, $data_array) {
		global $wpdb;
		$sql = "INSERT IGNORE INTO " . $wpdb->prefix . "tr_users_chars (`user_id`,`char_id`,`is_main_char`) VALUES ";
		$char_count = count ( $data_array );
		$counter_1 = 0;
		foreach ( $data_array as $char_id ) {
			$counter_1 ++;
			
			$sql .= "('$userr_id','$char_id','0')";
			
			if ($counter_1 == $char_count) {
				$sql .= ';';
			} else {
				$sql .= ',';
			}
		}
		$wpdb->query ( $sql );
	}
	/**
	 * Detatches characters from user
	 *
	 * @param int $user_id        	
	 *
	 * @param int $data_array
	 *        	array of chars to detach from user
	 *        	
	 * @return void
	 */
	public function detach_chars_from_user($user_id, $data_array) {
		global $wpdb;
		if (count ( $data_array ) > 0) {
			
			/*
			 * target sql syntax
			 * DELETE FROM kur_tr_users_chars WHERE `char_id` IN (1,9,18,4,3,12) AND `user_id`='1'
			 */
			
			$sql = "DELETE FROM " . $wpdb->prefix . "tr_users_chars WHERE `char_id` IN (";
			$char_count = count ( $data_array );
			$counter_1 = 0;
			foreach ( $data_array as $char_id ) {
				$counter_1 ++;
				
				$sql .= "'$char_id'";
				
				if ($counter_1 == $char_count) {
					$sql .= ')';
				} else {
					$sql .= ',';
				}
			}
			$sql .= ' AND `user_id`=' . $user_id . ';';
			
			$wpdb->query ( $sql );
			
			/*
			 * check if there is left min char.
			 * -> FALSE -> if there is left chars at all
			 * --> false -> exit
			 * --> true set first char as main
			 * -> exit
			 */
			
			$sql = "SELECT * FROM `" . $wpdb->prefix . "tr_users_chars` WHERE `user_id`='$user_id' AND `is_main_char`='1'";
			$main_char = $wpdb->get_row ( $sql, ARRAY_A );
			
			if (! $main_char) {
				$sql = "SELECT * FROM `" . $wpdb->prefix . "tr_users_chars` WHERE `user_id`='$user_id'";
				// pick the first char
				//user can set its main chair on the page later.
				$has_chars_at_all = $wpdb->get_row ( $sql, ARRAY_A );
				if ($has_chars_at_all) {
					// set the first record as main.
					$data = array (
							'is_main_char' => '1' 
					);
					$where = array (
							'uc_id' => $has_chars_at_all ['uc_id'] 
					);
					$wpdb->update ( $wpdb->prefix . 'tr_users_chars', $data, $where );
					
					echo $wpdb->last_query;
				}
			}
		}
	}
	/**
	 * Changes the array so it only shows mains and those who are not assigned.
	 *
	 * @param array $data
	 *        	array of chars information by days.
	 *        	
	 * @return $main_char_data
	 */
	public function calculate_chart_data_by_mains($data) {
		global $wpdb;
		
		
		// acquire corporation id for the user that is doing the querry
// 		$user_id = get_current_user_id ();
// 		$user_meta_corp_id = get_user_meta ( $user_id, 'Char_corp_asign', true );
		
		// get main chars
		$sql = "SELECT * FROM `" . $wpdb->prefix . "tr_users_chars`
				JOIN " . $wpdb->prefix . "tr_characters charz ON " . $wpdb->prefix . "tr_users_chars.char_id=charz.id
						WHERE `is_main_char` ='1'";
		$main_chars = $wpdb->get_results ( $sql, ARRAY_A );
		
		if ($data) {
			$main_chars_data = null;
			
			if ($main_chars) {
				foreach ( $main_chars as $main_char ) {
					// acquire main char users chars for this corp.
					$sql = "SELECT * FROM `" . $wpdb->prefix . "tr_users_chars`
							JOIN " . $wpdb->prefix . "tr_characters charz ON " . $wpdb->prefix . "tr_users_chars.char_id=charz.id
									WHERE `user_id`='" . $main_char ['user_id'] . "'
					";
					$related_chars = $wpdb->get_results ( $sql, ARRAY_A );
					
					/*
					 * find the main char and set it as main, because data might be different.
					 * what if main char has no data?
					 * ->get first char data(to get the dates) assign it as main and set all values to 0
					 * ->loop trough the data and if found any use char set add the values for main
					 * ->remove the char from data array
					 */
	
					
					/*
					 * ->get first char data(to get the dates) assign it as main and set all values to 0
					 */
					foreach ( $data as $dates_preset ) {
						$preset_array = $dates_preset;
						$main_chars_data [$main_char ['ownerName2']] = array_fill_keys ( array_keys ( $preset_array ), 0 );
						break;
					}
					
					/*
					 * ->loop trough the data and if found any, use char set add the values for main
					 */
					foreach ( $related_chars as $r_char ) {
						foreach ( $data as $char_name => $char_dates ) {
							if ($char_name == $r_char ['ownerName2']) {

								/*
								 * this is one of related characters, now summ up the values in loop.
								 */
								
								foreach ( $char_dates as $date => $value ) {
									$main_chars_data [$main_char ['ownerName2']] [$date] += $value;
								}
								unset ( $data [$char_name] );
								
							}
						}
					}
				}
				
				/*
				 * thats it, add the remaining of the free chars to the main char array and return in.
				 */
				if (count ( $data ) > 0) {
					// add remaining not assigned
					foreach ( $data as $char_name_d => $char_dates_d ) {
						$main_chars_data [$char_name_d] = $char_dates_d;
					}
				}
			}
		}
		
		return $main_chars_data;
	}
	
	/**
	 * Performs Curl calls to Zkillboard api
	 *
	 * This function executes CURL call on zkillboard API for specified input
	 *
	 * @param int $corp_id        	
	 *
	 * @param int $year
	 *        	2017
	 *        	
	 * @param int $month
	 *        	double digits 01 -12
	 *        	
	 * @param int $page
	 *        	01-99...
	 *        	
	 * @return array $kills_array all the kills for the corporationby page.
	 */
	public function call_Zkill_api_curl($corp_id, $year, $month, $page) {
		if ($corp_id == null) {
			return 'Missing corp';
		}
		if ($year == null) {
			return 'Missing year';
		}
		if ($month == null) {
			return 'Missing month';
		}
		if ($page == null) {
			return 'Missing page';
		}
		
		$url = "https://zkillboard.com/api/kills/corporationID/$corp_id/year/$year/month/$month/page/$page/orderDirection/asc/";
		
		//for debuging purpouses and to see if it is even working because they change stuff often.
// 		echo $url . '<br><br>';
		$ch = curl_init ( $url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		// bad ssl disabled 
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		// ssl disable ends0
		
		/*
		 * TODO
		 *  use the actual webpage url.
		 *  use the first admin existing character name for maintainer.
		 *  use the admin login email.
		 */
		
		if(isset($_SERVER['HTTPS'])){
		    $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
		}
		else{
		    $protocol = 'http';
		}
		$site_url=$protocol . "://" . $_SERVER['HTTP_HOST'];
		
		
		$top_rat=new Top_Ratter();
		
		$ar=$top_rat->get_main_admin_character_in_corporation();
		
		curl_setopt($ch,CURLOPT_USERAGENT,''.$site_url.' Maintainer: '.$ar['name'].' '.$ar['email'].'');
		
		$response = curl_exec ( $ch );
		
		if ($response === false) {
			echo 'Curl error: ' . curl_error ( $ch );
		}
		
		$data = json_decode ( $response, TRUE );
		return $data;
	}
	/**
	 * Returns array of the first administrator main character name that is still in the corporation and user email associated to it.
	 * @return $Maintainer_name
	 */
	public function get_main_admin_character_in_corporation(){
	    global $wpdb;
	    //Get all users in the DB
	    $wp_user_search = $wpdb->get_results("SELECT ID, display_name FROM $wpdb->users ORDER BY ID");
	    $SSO=new Top_Ratter_SSO();

	    
	    $sql="SELECT * FROM `".$wpdb->prefix."tr_sso_credentials`";
	    $credentials=$wpdb->get_row($sql,ARRAY_A);
	    
	    
	    $return_array=array();
	    
	    //Loop through all users
	    foreach ( $wp_user_search as $userid ) {
	        //Current user ID we are looping through
	        $curID = $userid->ID;
	        //Grab the user info of current ID
	        $curuser = get_userdata($curID);
	        //Current user level
	        $user_level = $curuser->user_level;
	        //Only look for admins
	        if($user_level >= 8){//levels 8, 9 and 10 are admin
	            
	            //check if user has main chars related in user_chars table. 
	            $sql="SELECT * FROM `".$wpdb->prefix."tr_users_chars`
                      JOIN " . $wpdb->prefix . "tr_characters ON " . $wpdb->prefix . "tr_users_chars.char_id=" . $wpdb->prefix . "tr_characters.id
                      WHERE `user_id`='$curID' AND `is_main_char`='1'";
	            
	            $main_char=$wpdb->get_row($sql,ARRAY_A);
	           
	            if($main_char){
	                 $corp=$SSO->char_id_to_corp_id($main_char['owner_id']);
   
    	            if($corp['corporation_id']==$credentials['corporation_id']){
    	                
    	                $return_array['name']=$main_char['ownerName2'];
    	                $return_array['email']=$curuser->user_email;
    	                break;
    	            }
	            } 
	        }
	    }
	    return$return_array;   
	}
	
	/**
	 * Calls zkillboard api untill desired data is acquired, and inserts all new data in the table.
	 *
	 * @param int $corp_id        	
	 *
	 * @return void
	 */
	public function gather_zkill_data_for_corporation($corp_id) {
		
		global $wpdb;
		date_default_timezone_set ( 'UTC' );
		
		// get last kill time stamp
		$sql = "SELECT * FROM `" . $wpdb->prefix . "tr_pvp_chars_kills` WHERE `corp_id`='$corp_id' ORDER BY `timestamp` DESC";
		$last_kill = $wpdb->get_row ( $sql, ARRAY_A );
		
		
		$laiks_tagad = date ( "Y-m-d H:i:s" );
		
		if ($last_kill != null) {
			// not null, find year and month from the date.
			$year = date ( "Y", strtotime ( $last_kill ['timestamp'] ) );
			$month = date ( "m", strtotime ( $last_kill ['timestamp'] ) );
			
			// compare if this is current month.
			$month_now = date ( "m", strtotime ( $laiks_tagad ) );

			// if this is not the same month as records get last month and last year( if applicable)
			if ($month_now == $month) {
				
				// this is the same month so find out the page from the db to start checking from
				$sql = "SELECT * FROM `" . $wpdb->prefix . "tr_sso_credentials` WHERE `corporation_id`='$corp_id'";
				$api_page = $wpdb->get_row ( $sql, ARRAY_A );
				
				// if not record set 0
				if ($api_page ['z_kill_api_page'] != null) {
					$page = $api_page ['z_kill_api_page'];
				} else {
					$page = '0';
				}

// 				echo"<pre>Record Found: same month. Page Nr:$page</pre>";

			} else {
				
				// go back 25 days and get month number.
				$past_date = date ( "Y-m-d H:i:s", strtotime ( "-25 days", strtotime ( $laiks_tagad ) ) );
				
				// get previous months year and month
				$year_p = date ( "Y", strtotime ( $past_date ) );
				$month_p = date ( "m", strtotime ( $past_date ) );
				$page_p = '0';
				
				
				/*
				 * pull last months data
				 */
// 				echo"<pre>Record Found: different month. Pulling  -25 days</pre>";
				$zkill_api_old=$this->get_zkill_api_data_all_pages($corp_id,$year_p,$month_p,$page_p);
				
				// process the gathered information
				$this->process_kills_and_insert ( $zkill_api_old, $corp_id );

				
				/*
				 * continue with current months api apull.
				 */
// 				echo"<pre>Record Found: different month. Pulling current month</pre>";
				$year = date ( 'Y', strtotime ( $laiks_tagad ) );
				$month = date ( 'm', strtotime ( $laiks_tagad ) );
				$page = '0';
			}
		} else {
			
// 			echo"<pre>No records found getting fresh data.</pre>";
			// means no records at all get this months from 0 for this month
			$year = date ( 'Y', strtotime ( $laiks_tagad ) );
			$month = date ( 'm', strtotime ( $laiks_tagad ) );
			$page = '0';

		}
		
		$zkill_api=$this->get_zkill_api_data_all_pages($corp_id,$year,$month,$page);
		
		
		// process the gathered information
		$this->process_kills_and_insert ( $zkill_api, $corp_id );


	}
		/**
	 * Processes the data for the month and inserts in the db
	 *
	 * @param int $corp_id        	
	 *
	 * @param int $year
	 *        	2017
	 *        	
	 * @param int $month
	 *        	double digits 01 -12
	 *        	
	 * @param int $page
	 *        	01-99...       	
	 *
	 * @return $zkill_api array
	 */
	public function get_zkill_api_data_all_pages($corp_id,$year,$month,$page){
		global $wpdb;
		$zkill_api = null;
		$last_valid_page = $page;
		$stop = false;
		
		
		do {
		
			//sleep for 5 sec because zkill api returns false if request too quick.

			sleep(3);
				
			$kill_data = $this->call_Zkill_api_curl ( $corp_id, $year, $month, $page );
		
			if ($kill_data != false) {
// 				echo"<pre> Got something from zkill api at page: $page </pre>";
		
				$zkill_api [] = $kill_data;
				$last_valid_page ++;
			} else {
// 				echo"<pre>Zkill api returns NULL at page: $page </pre>";
				$stop = true;
			}
				
			$page ++;

		} while ( $stop == false );
		
		// insert last valid page in the db for further api pulls
		$data = array (
				'z_kill_api_page' => $last_valid_page
		);
		$where = array (
				'corporation_id' => $corp_id
		);
		
// 		echo"<pre>Inserting last valid page number in DB :$last_valid_page. </pre>";

		$wpdb->update ( $wpdb->prefix . 'tr_sso_credentials', $data, $where );
		
		
		return $zkill_api;
	}
	
	/**
	 * Processes the kills data for the month and inserts in the db
	 *
	 * @param int $array_kills        	
	 * @param int $corp_id        	
	 *
	 * @return void
	 */
	public function process_kills_and_insert($array_kills, $corp_id) {
		global $wpdb;
		
		if ($array_kills != false) {
			
			// define temp storage
			$temp_array = null;
			
			
			foreach ( $array_kills as $api_page ) {
				foreach ( $api_page as $kill_event ) {
					$kill_id = $kill_event ['killmail_id'];
					$kill_time = $kill_event ['killmail_time'];
					foreach ( $kill_event ['attackers'] as $attackers ) {
						/*
						 * look in the attackers array for each kill id
						 * compare the corp id if it matches add the name to the kill id array
						 */
						if ($attackers ['corporation_id'] == $corp_id) {
							if ($attackers ['character_id'] != null) {
								$temp_array [$attackers ['character_id']] [$kill_id] = $kill_time;
							}
				
						}
					}
				}
			}
			

			/*
			 * now i nsert in the table with sql that ignores the existing ones
			 * http://stackoverflow.com/questions/20928181/only-insert-into-table-if-item-does-not-exist
			 * ####sql will look smth like this
			 * INSERT IGNORE INTO wp_tr_pvp_chars_kills (`character`,`corp_id`,`kill_id`,`timestamp`)
			 * VALUES ('Szchyactszky','98342863','61740744','2017-04-22 09:16:43'),('Szchyactszky','98342863','61740746','2017-04-22 09:16:59')
			 *
			 * so prepare the sql for each char and put them all togehter to be executed at once at the end. put ; at the end of each char sql. for speed ofc and glory of Bob
			 */
			
			$complete_sql = null;
			
			
			$sql = "INSERT IGNORE INTO " . $wpdb->prefix . "tr_pvp_chars_kills (`char_id`,`corp_id`,`kill_id`,`timestamp`) VALUES ";
			
			$players_count = count ( $temp_array );
			$counter_1 = 0;
			foreach ( $temp_array as $player => $kill_ids ) {
				$kill_count = count ( $kill_ids );
				$counter_2 = 0;
				
				foreach ( $kill_ids as $kill_id => $kill_date ) {
					
					$counter_2 ++;
					$sql .= "('$player','$corp_id','$kill_id','$kill_date')";
					
					if ($counter_2 == $kill_count) {
						$sql .= '';
					} else {
						$sql .= ',';
					}
				}
				
				$counter_1 ++;
				if ($counter_1 == $players_count) {
					$sql .= ';';
				} else {
					$sql .= ',';
				}
			}
			$wpdb->query ( $sql );
			
		}
	}
}

?>