 <?php
 /**
  * handles ftp action 
  *
  * deletes or renames the file in the ftp server. Ftp user is pulled from MCP api
  * https://make.wordpress.org/core/handbook/best-practices/inline-documentation-standards/php/
  *
  * @since 4.6.1   <- actualy is wp version
  */
	class ftp {
		/**
		 * Creates ftp connection using data from mcp api.
		 *
		 * This function creates ftp connection to the specified application ftp account.
		 * Ftp application details are  acquired trough mcp api.
		 * Returns connection id that can be used in other functions of this class
		 *
		 * @param string $app_name The name of the application to make a ftp connection for.
		 *
		 * @return $conn_id
		 */
		public function connection($app_name) {
			$app_name = $app_name . '-vod';
			$mcp = dp_get_app_stats ( $app_name );
			$ftp_server = "46.254.15.179";
			$ftp_port = "21";
			// get app data from the api.
			$ftp_user = 'cast_' . $mcp ["serverData"] ["unique_id"] . '@46.254.15.179';
			$ftp_pass = $mcp ["serverData"] ["adminpassword"];
// 			echo"frp user $ftp_user  ftp pass  $ftp_pass ";
			$conn_id = ftp_connect ( $ftp_server, $ftp_port ) or die ( "Couldn't connect to $ftp_server" );
			
			$ftp_connection = ftp_login ( $conn_id, $ftp_user, $ftp_pass );
			
			return $conn_id;
		}
		/**
		 * Renames the file on the ftp server
		 *
		 * This function renames the file on the remote server
		 * 
		 *
		 * @param string $app_name The name of the application to make a ftp connection for.
		 * @param string $old_file old file name to rename.
		 * @param string $new_file new file name.
		 *
		 * @return void
		 */
		public function rename($app_name, $old_file, $new_file) {
			$conn_id = $this->connection ( $app_name );
			$old_file.='.mp4';
			$new_file.='.mp4';
			// try to rename $old_file to $new_file
			if (ftp_rename ( $conn_id, $old_file, $new_file )) {
// 				echo "Renamed $old_file to $new_file";
				$status	=true;
				
			} else {
// 				echo "Problem renaming $old_file to $new_file";
				$status	=false;
			}
			
			ftp_close ( $conn_id );
			return $status;
		}
		/**
		 * Deletes the file from the ftp server
		 *
		 * @param string $app_name app name to make ftp connection for, 
		 * @param string $file file to delete,    
		 *
		 * @return void
		 */
		public function delete($app_name, $file) {
			$conn_id = $this->connection ( $app_name );
			$file.='.mp4';
			if (ftp_delete ( $conn_id, $file )) {
// 				echo "$file deleted";
			} else {
// 				echo "Could not delete $file";
			}
			
			ftp_close ( $conn_id );
		}
	}
	
	?>