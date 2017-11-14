<?php
/**
 * The tr_top ratter_xml_api is class that acquires the data from EvE Online xml api endpoint.
 *
 * The top_ratter_xml api class contains functions to gather data from eve online xml api endpoint and present it in requierd format.
 * https://api.eveonline.com//corp/WalletJournal.xml.aspx?
 * Returns acquired data to caller function.
 * 
 *
 *
 * @since 1.0.0
 */
class Top_Ratter_Xml_Api {
	/**
	 * Executes api calling with curl
	 *
	 * This function calls xml api endpoint with curl and returns SimpleXMLElement object
	 *
	 * @param string $url
	 *        	URL api endpoint to call for
	 *        	
	 * @return $xml SimpleXMLElement response
	 */
	public function apicall($url) {
		$ch = curl_init ( $url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		// bad ssl disabled ÖD
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		// ssl disable ends
		$response = curl_exec ( $ch );
		if ($response === false) {
			echo 'Curl error: ' . curl_error ( $ch );
			echo ' Refresh page.';
// 			die ();
			return false;
		} else {
			
// 						echo 'ze fukin problem<pre>';
// 						echo var_dump($response);
// 						echo '</pre>';
			
			$xml = new SimpleXMLElement ( $response );
			

			return $xml;
		}
	}
	/**
	 * Calls for api endpoint with specified parameters
	 *
	 * This function querries the eve online api endpoint for corporation wallet.
	 * The coporation keyid and vcode is passed in when calling the function.
	 * It then processes the data in asociative array and returns it
	 *
	 * @param string $corp_data_array
	 *        	an array containg Vcode and Key_id for required API call.
	 *        	
	 * @return $filtered_data associative array of filtered data containing only valid entries.
	 */
	public function get_ratting_data($corp_data_array,$switch=null) {
		
// 		echo 'https://api.eveonline.com//corp/WalletJournal.xml.aspx?keyID=' . $corp_data_array ['key_id'] . '&vCode=' . $corp_data_array ['vCode'] . '&accountKey=' . $corp_data_array ['accountKey'] . '&rowCount=2560';
		
		
		date_default_timezone_set('UTC');
		global $wpdb;
	
		$time_now = date ( "Y-m-d H:i:s" );
// 		echo 'cached time';
// 		echo $corp_data_array['cached_until'];
		
		//if the cahce timer is bigger than now.
		if($corp_data_array['cached_until']<$time_now||$corp_data_array['cached_until']==null){
			$ticks = $this->apicall ( 'https://api.eveonline.com//corp/WalletJournal.xml.aspx?keyID=' . $corp_data_array ['key_id'] . '&vCode=' . $corp_data_array ['vCode'] . '&accountKey=' . $corp_data_array ['accountKey'] . '&rowCount=2560' );
// 			echo 'https://api.eveonline.com//corp/WalletJournal.xml.aspx?keyID=' . $corp_data_array ['key_id'] . '&vCode=' . $corp_data_array ['vCode'] . '&accountKey=' . $corp_data_array ['accountKey'] . '&rowCount=2560';
				
			$xml_array [] = null;
			
			// loop trough the relevant data
// 			echo var_dump($ticks);
			if ($ticks != null) {
				//add new cache timer to the corporations db
				$cache_timer = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +10 minutes"));
// 				$cache_timer = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." 0 minutes"));
				//set data to update
				$data2 = array (
						'cached_until' => $cache_timer
				);
				//where to update.
				$where = array (
						'id' => $corp_data_array ['id']
				);
				//run update
				$wpdb->update ( $wpdb->prefix . 'tr_corporations', $data2,$where );
				
				
				foreach ( $ticks->result->rowset->row as $row ) {
					if($switch==null){
						$xml_array [] = array (
								"date" => ( string ) $row ['date'],
								"refID" => ( int ) $row ['refID'],
								"refTypeID" => ( int ) $row ['refTypeID'],
								"ownerName2" => ( string ) $row ['ownerName2'],
								"ownerID2" => ( int ) $row ['ownerID2'],
								"amount" => ( float ) $row ['amount'],
								"ownerName1" => ( string ) $row ['ownerName1']
						);
					}
				}
			}elseif($ticks==false){
				return false;
			}
		}
// 		echo"<br>------xml------<br><pre>";
// 		echo var_dump($ticks);
// 		echo"</pre><br>----------<br>";
		
		return $xml_array;
	}
	/**
	 * Executes curl call to get all corporation members.
	 *
	 * @param array $corp_data_array
	 *
	 * @return $xml_array array of characters from api.
	 */
	public function update_corp_member_list($corp_data_array){
		
		/*
		 * honor the cache, but try to use it every time somone enters the page
		 * insert records from api to wp_tr_characters if not already
		 * 
		 * 
		 */
		date_default_timezone_set('UTC');
		global $wpdb;
		
		$time_now = date ( "Y-m-d H:i:s" );
// 				echo 'cached time';
// 				echo $corp_data_array['cached_until'];
// 					echo'corp data array<pre>';
// 					echo var_dump($corp_data_array);
// 					echo'</pre>';
		//if the cahce timer is bigger than now.
		$xml_array=null;
// 		if($corp_data_array['cached_until']<$time_now||$corp_data_array['cached_until']==null){
			$members = $this->apicall ( 'https://api.eveonline.com/corp/MemberTracking.xml.aspx?keyID=' . $corp_data_array ['key_id'] . '&vCode=' . $corp_data_array ['vCode']  );
			
// 								echo'members<pre>';
// 								echo var_dump($members);
// 								echo'</pre>';
								
// 								return;
			
			foreach ( $members->result->rowset->row as $row ) {
				if($switch==null){
					$xml_array [] = array (
							"characterID" => ( string ) $row ['characterID'],
							"ownerName2" => ( string ) $row ['name'],
					);
				}
			}
			
			
// 		}
		
		
		return $xml_array;
		
		
	}
	
	
	
	
	
}

?>