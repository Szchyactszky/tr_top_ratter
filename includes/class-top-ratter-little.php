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
		require_once plugin_dir_path ( __FILE__ ) . 'class-top-ratter-xml-api.php';
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
// 				"$wpdb->prefix" . "tr_corporations",
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
				// exists
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
						  `ref_id` INT(200) NOT NULL,
						  `date_acquired` DATETIME NOT NULL,
						  `amount` FLOAT NOT NULL,
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
			return;
		}

		$bounties_array = $this->filter_ratting_data ( $journal_records);
		$structures_array = $this->filter_ratting_data ( $journal_records,true);
		
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
				foreach ( $journal_records as $entry ) {
					if (in_array ( $entry ['ref_type'], $valid_refTypeID )) {
					    /*
					     * also cehck for negative values and only add if its positive.
					     */
					    if($entry['amount']>0){
					      $filtered_data [] = $entry;  
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
		echo $url . '<br><br>';
		$ch = curl_init ( $url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		// bad ssl disabled 
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		// ssl disable ends0
		
		curl_setopt($ch,CURLOPT_USERAGENT,'http://reallifeoutpost.com/ Maintainer: Judge07 pikkie747@gmail.com');
		
		$response = curl_exec ( $ch );
		
		if ($response === false) {
			echo 'Curl error: ' . curl_error ( $ch );
		}
		
		$data = json_decode ( $response, TRUE );
		return $data;
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
				$sql = "SELECT * FROM `" . $wpdb->prefix . "tr_corporations` WHERE `corporation_id`='$corp_id'";
				$api_page = $wpdb->get_row ( $sql, ARRAY_A );
				
				// if not record set 0
				if ($api_page ['z_kill_api_page'] != null) {
					$page = $api_page ['z_kill_api_page'];
				} else {
					$page = '0';
				}

				echo"<pre>Record Found: same month. Page Nr:$page</pre>";

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
				echo"<pre>Record Found: different month. Pulling  -25 days</pre>";
				$zkill_api_old=$this->get_zkill_api_data_all_pages($corp_id,$year_p,$month_p,$page_p);
				
				// process the gathered information
				$this->process_kills_and_insert ( $zkill_api_old, $corp_id );

				
				/*
				 * continue with current months api apull.
				 */
				echo"<pre>Record Found: different month. Pulling current month</pre>";
				$year = date ( 'Y', strtotime ( $laiks_tagad ) );
				$month = date ( 'm', strtotime ( $laiks_tagad ) );
				$page = '0';
			}
		} else {
			
			echo"<pre>No records found getting fresh data.</pre>";
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
		
		echo"<pre>";
		echo "EXECUTING API CALL! year:$year, month:$month,page:$page. ";
		echo"</pre>";
		
		do {
		
			//sleep for 5 sec because zkill api returns false if request too quick.

			sleep(3);
				
			$kill_data = $this->call_Zkill_api_curl ( $corp_id, $year, $month, $page );
		
			if ($kill_data != false) {
				echo"<pre> Got something from zkill api at page: $page </pre>";
		
				$zkill_api [] = $kill_data;
				$last_valid_page ++;
			} else {
				echo"<pre>Zkill api returns NULL at page: $page </pre>";
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
		
		echo"<pre>Inserting last valid page number in DB :$last_valid_page. </pre>";

		$wpdb->update ( $wpdb->prefix . 'tr_corporations', $data, $where );
		
		
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
			 * so prepare the sql for each char and put them all togehter to be executed at once at the end. put ; at the end of each char sql. for speed ofc
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