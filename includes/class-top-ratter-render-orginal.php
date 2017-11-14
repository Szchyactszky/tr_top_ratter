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
		// add a shortcode to be displayed in wp page.
		add_shortcode ( 'sc_render_top_ratter_non_admin_UI', array (
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
		if(is_user_logged_in()==true) {
			global $wpdb;
			
			$user_id=get_current_user_id();
			$user_corp = get_user_meta ( $user_id, 'Char_corp_asign', true );
			if($user_corp!=null){
			// render timepicker fields
			echo $this->render_datepicker_fields ();
			// handle the GET values
			$get_values_array = $this->handle_GET_values_non_admin_UI ();
			// show the selection
			echo '<p class="datafromto">Spai period: '.$get_values_array ['T1'] . ' -> ' . $get_values_array ['T2'].'</p>';
			//find out what corp user belongs to
			
		
			$sql = "SELECT * FROM " . $wpdb->prefix . "tr_corporations WHERE id=$user_corp";
			// go trough array and find unique
			
			$corp_data_array = $wpdb->get_row ( "$sql", ARRAY_A );
			
			$xml = new Top_Ratter ();
				$xml->update_ratting_data ( $corp_data_array );
			
			//render the table of each char total isk
			$this->display_in_time_period ( $get_values_array ['T1'], $get_values_array ['T2'] );
			
			// render some kind of visual thing.
			$this->render_graph( $get_values_array ['T1'], $get_values_array ['T2'] );
			
			echo'**More functionality coming soon&trade;<br>';
			echo'*** Thanks to <b>Judge07</b>, <b>Jonathan Doe</b>, <b>biggus dickus Aurilen</b> and <b>Hhatia</b>';
			}else{
				echo'You are totally a spai, PM an officer ingame to confirm your spainess before you can proceed.';
			}
			
		}else{
			// register/ login.
			echo'Wow, Such Spai, Much Look, Very Need login for access!';
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
		$chars = $data->gather_data_by_date ( $start, $end );
		//get user 
		$current_user = wp_get_current_user();
		
		if ($chars != null) {
			echo '<table class="tr_selection">
				<thead>
				<tr>
					<th>Nr.</th>
				<th class="charname"><span>Character Name</span></th>
				<th class="tax_right" ><span>ISK(Tax)</span></th>
					<th class="tax_right" ><span>ISK return,(25% of Tax)</span></th>
				</tr>
				</thead>
				<tbody>';
			$total_return=null;
			$total_tax=null;
			$i=1;
			foreach ( $chars as $char ) {
				$tenth=null;
				if($i==11){
					$tenth='vague_line';
				}
				
				
				echo '<tr>';
				echo'<td class="'.$tenth.'">'.$i.'</td>';		
						
				echo'<td class="'.$tenth.'">';
				echo $char ['ownerName2'];
				
				echo '</td><td  class="align_right '.$tenth.'">';
				echo number_format ( $char ['total'], 2, ',', ' ' );
				echo '</td><td  class="align_right '.$tenth.'">';
				$kek=$char ['total']*0.25;
				//show payable amount without spaces for admin. for copy paste.
				if($current_user->user_login=='admin'){
					echo number_format ( $kek, 2, ',', '' );
				}else{
					echo number_format ( $kek, 2, ',', ' ' );
				}
				
				
				$total_tax+=$char ['total'];
				$total_return+=$kek;
				echo '</td></tr>';
				$i++;
			}
			echo'<tr ><td class="top_border"></td><td class="top_border">Total</td><td class="align_right top_border ">'.number_format ( $total_tax, 2, ',', ' ' ).'</td><td class="align_right top_border">'.number_format ( $total_return, 2, ',', ' ' ).'</td></tr>';
			echo '	</tbody>
				</table> ';
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
	public function render_graph($start=null, $end=null){
		/*
		 * http://jsfiddle.net/rM8BM/?utm_source=website&utm_medium=embed&utm_campaign=rM8BM
		 * http://stackoverflow.com/questions/17444586/show-hide-lines-data-in-google-chart
		 * this one works with buttons
		 * https://jsfiddle.net/Loxos/rmbtwdhd/
		 * 
		 * 
		 */

		
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
		var isk_day_char=[["Date","Elettra Blade","spreaders","SSoulSScream","biggus dickus Aurilen","Szchyactszky","Judge07","Medina Riper","anouk tolkien","Jonathan Doe","Windsigh","Cheese Nippels","Foxy Bushy","Seras Hightower","SSF ZiG","MattMan","lilbigbear","Annadrian","Deriger"],["2017-03-01",4373073,4209782,4439618,0,0,0,416851.1,0,0,0,26286180,20524463.9,11509867.5,1652022.5,0,0,0,0],["2017-03-02",482100,482100,482100,0,0,0,7034697,0,102276.6,0,0,13935228.7,0,0,0,111515,0,0],["2017-03-03",3257930,3257930,3257930,0,0,0,10159585,0,286782.7,0,2114383,13438791,0,0,0,4336781,0,0],["2017-03-04",4131752,4131752,4131752,0,0,0,1915880.8,0,0,0,0,2149488.36,0,0,0,32985.8,27225,0],["2017-03-05",1614472,1614472,1614472,0,11103430,0,10339218,0,0,0,13315549,669865.8,0,0,17819825,13478.3,0,0],["2017-03-06",0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],["2017-03-07",3077080,3077080,3077080,0,0,66600.7,19931.2,0,1572436.7,0,3042603,0,0,0,0,66600.7,0,0],["2017-03-08",2994531,5083678,5234235,681533,0,0,7307814,0,418548,0,0,0,4300541,0,6320160,0,0,0],["2017-03-09",11465228,10676374,11897869,3996309.7,0,0,0,3628848,0,0,8827750,17419.2,19893465,0,16262030,0,0,0],["2017-03-10",13340031,14008618,12930936,328640.4,0,0,0,0,0,0,1549692,0,10777375,0,31799304,0,43418,0],["2017-03-11",14650937,14739614,15498506,327758.5,0,10372.5,6141726,0,0,0,929946,0,15513210,0,24787179.1,10372.5,0,2560355],["2017-03-12",1017587,1017587,1027206,963638,12681950,329201.8,13111089,0,0,0,0,0,0,0,20090061,9797117.1,0,0],["2017-03-13",0,2125678,1598122,0,0,0,0,0,0,0,0,0,6483112,2746848,2394240,0,0,812902],["2017-03-14",303631,1844770,1669833,0,0,0,3042972,0,0,0,5537190,0,0,6810842,0,0,0,0],["2017-03-15",4383072,4363824,4730477,0,0,0,0,0,0,0,21205540,0,0,4506329,0,0,0,2646199],["2017-03-16",0,0,0,1322864.7,2302890,0,428137,0,0,37950.75,0,0,0,0,30901014,0,0,1673067],["2017-03-17",0,0,0,0,0,0,6620530,0,0,0,7916714,4494796,21626988,3941145,18719863,9687756,0,0],["2017-03-18",0,0,0,0,6903130,940351.73,0,0,0,20707,13197805,0,8845950,3080460.3,0,13328012,0,1945797.9],["2017-03-19",0,0,0,0,28907650,0,0,0,3303581.9,0,27279815,674194,10375922,6302158,2493592,18491765,0,1913718],["2017-03-20",0,0,0,0,0,0,0,24000,0,0,12798743.8,0,16814298,33468178.2,0,25536,0,234340],["2017-03-21",0,0,0,0,0,0,0,0,0,0,4123600,0,0,0,0,0,0,57895.6],["2017-03-22",0,0,0,672769.5,0,287239.9,0,0,0,0,0,0,0,0,15580280.5,0,0,0],["2017-03-23",0,0,0,658935.2,0,282367.5,0,59297.8,0,142167.4,0,0,0,9347951,15034440,6929683,0,818434],["2017-03-24",0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]]		
		</script>
	
EOD;
		
		
		
		$isk_day_per_char = <<<EOD
<script type="text/javascript">
				// this works with https://www.google.com/jsapi only (name colors are not hidden)
google.load("visualization", "1", {packages:["corechart"]});
				
				// this should works in plain html page aka hhatia code https://www.gstatic.com/charts/loader.js , but doesnt work in wp for some reason
// google.charts.load('current', {'packages':['corechart']});

google.setOnLoadCallback(drawChart);
		
  function drawChart() {
		
    var data = google.visualization.arrayToDataTable(isk_day_char);
		
    var options = {
		title: 'ISK/day per Character'
    };
		
    var chart = new google.visualization.LineChart(document.getElementById('isk_day_per_char'));
    chart.draw(data, options);
		
				//hhatia code begins
		
	            var columns = [];
		
            var defaultSeries = [];
            for (var k = 0; k <= data.getNumberOfColumns(); k++){
                defaultSeries.push(k);
                }
            var series ={};
            for (var i = 0; i < data.getNumberOfColumns(); i++){
                if (i == 0 || defaultSeries.indexOf(i) > -1){
		
                    columns.push(i);
                }
                else{
                    columns.push({
                        label: data.getColumnLabel(i),
                        type: data.getColumnType(i),
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
                title: 'ISK/day per Character',
     
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
                                label: data.getColumnLabel(src),
                                type: data.getColumnType(src),
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
                        var view = new google.visualization.DataView(data);
                        view.setColumns(columns);
                        chart.draw(view, options);
                    }
                }
            }
		
            google.visualization.events.addListener(chart, 'select', showHideSeries);
		
            // instantiate and draw new chart with new view and options
            var view = new google.visualization.DataView(data);
                  view.setColumns(columns);
                  chart.draw(view, options);
		
  }
		
</script>
EOD;
		
		echo $prepared_dummy_data1;
// 		$data = new Top_Ratter ();
		
// 		$chars = $data->prepare_data_for_chart_by_days ( $start, $end );
		
// 		$isk_day_char=json_encode($chars);
// 		echo'	<script type="text/javascript"> var isk_day_char='.$isk_day_char.'</script>';
		echo $isk_day_per_char;
		echo '<div id="isk_day_per_char"></div>';
	}
	/**
	 * Render the second chart day/isk/char accumulated
	 *
	 */
	public function output_chart_2_js_code($start, $end){
		
		$prepared_dummy_data2 = <<<EOD
 <script type="text/javascript">
<!--define the second array -->
var isk_day_char_acumulate=[["Date","Elettra Blade","spreaders","SSoulSScream","biggus dickus Aurilen","Szchyactszky","Judge07","Medina Riper","anouk tolkien","Jonathan Doe","Windsigh","Cheese Nippels","Foxy Bushy","Seras Hightower","SSF ZiG","MattMan","lilbigbear","Annadrian","Deriger"],["2017-03-01",4373073,4209782,4439618,0,0,0,416851.1,0,0,0,26286180,20524463.9,11509867.5,1652022.5,0,0,0,0],["2017-03-02",4855173,4691882,4921718,0,0,0,7451548.1,0,102276.6,0,26286180,34459692.6,11509867.5,1652022.5,0,111515,0,0],["2017-03-03",8113103,7949812,8179648,0,0,0,17611133.1,0,389059.3,0,28400563,47898483.6,11509867.5,1652022.5,0,4448296,0,0],["2017-03-04",12244855,12081564,12311400,0,0,0,19527013.9,0,389059.3,0,28400563,50047971.96,11509867.5,1652022.5,0,4481281.8,27225,0],["2017-03-05",13859327,13696036,13925872,0,11103430,0,29866231.9,0,389059.3,0,41716112,50717837.76,11509867.5,1652022.5,17819825,4494760.1,27225,0],["2017-03-06",13859327,13696036,13925872,0,11103430,0,29866231.9,0,389059.3,0,41716112,50717837.76,11509867.5,1652022.5,17819825,4494760.1,27225,0],["2017-03-07",16936407,16773116,17002952,0,11103430,66600.7,29886163.1,0,1961496,0,44758715,50717837.76,11509867.5,1652022.5,17819825,4561360.8,27225,0],["2017-03-08",19930938,21856794,22237187,681533,11103430,66600.7,37193977.1,0,2380044,0,44758715,50717837.76,15810408.5,1652022.5,24139985,4561360.8,27225,0],["2017-03-09",31396166,32533168,34135056,4677842.7,11103430,66600.7,37193977.1,3628848,2380044,0,53586465,50735256.96,35703873.5,1652022.5,40402015,4561360.8,27225,0],["2017-03-10",44736197,46541786,47065992,5006483.1,11103430,66600.7,37193977.1,3628848,2380044,0,55136157,50735256.96,46481248.5,1652022.5,72201319,4561360.8,70643,0],["2017-03-11",59387134,61281400,62564498,5334241.6,11103430,76973.2,43335703.1,3628848,2380044,0,56066103,50735256.96,61994458.5,1652022.5,96988498.1,4571733.3,70643,2560355],["2017-03-12",60404721,62298987,63591704,6297879.6,23785380,406175,56446792.1,3628848,2380044,0,56066103,50735256.96,61994458.5,1652022.5,117078559.1,14368850.4,70643,2560355],["2017-03-13",60404721,64424665,65189826,6297879.6,23785380,406175,56446792.1,3628848,2380044,0,56066103,50735256.96,68477570.5,4398870.5,119472799.1,14368850.4,70643,3373257],["2017-03-14",60708352,66269435,66859659,6297879.6,23785380,406175,59489764.1,3628848,2380044,0,61603293,50735256.96,68477570.5,11209712.5,119472799.1,14368850.4,70643,3373257],["2017-03-15",65091424,70633259,71590136,6297879.6,23785380,406175,59489764.1,3628848,2380044,0,82808833,50735256.96,68477570.5,15716041.5,119472799.1,14368850.4,70643,6019456],["2017-03-16",65091424,70633259,71590136,7620744.3,26088270,406175,59917901.1,3628848,2380044,37950.75,82808833,50735256.96,68477570.5,15716041.5,150373813.1,14368850.4,70643,7692523],["2017-03-17",65091424,70633259,71590136,7620744.3,26088270,406175,66538431.1,3628848,2380044,37950.75,90725547,55230052.96,90104558.5,19657186.5,169093676.1,24056606.4,70643,7692523],["2017-03-18",65091424,70633259,71590136,7620744.3,32991400,1346526.73,66538431.1,3628848,2380044,58657.75,103923352,55230052.96,98950508.5,22737646.8,169093676.1,37384618.4,70643,9638320.9],["2017-03-19",65091424,70633259,71590136,7620744.3,61899050,1346526.73,66538431.1,3628848,5683625.9,58657.75,131203167,55904246.96,109326430.5,29039804.8,171587268.1,55876383.4,70643,11552038.9],["2017-03-20",65091424,70633259,71590136,7620744.3,61899050,1346526.73,66538431.1,3652848,5683625.9,58657.75,144001910.8,55904246.96,126140728.5,62507983,171587268.1,55901919.4,70643,11786378.9],["2017-03-21",65091424,70633259,71590136,7620744.3,61899050,1346526.73,66538431.1,3652848,5683625.9,58657.75,148125510.8,55904246.96,126140728.5,62507983,171587268.1,55901919.4,70643,11844274.5],["2017-03-22",65091424,70633259,71590136,8293513.8,61899050,1633766.63,66538431.1,3652848,5683625.9,58657.75,148125510.8,55904246.96,126140728.5,62507983,187167548.6,55901919.4,70643,11844274.5],["2017-03-23",65091424,70633259,71590136,8952449,61899050,1916134.13,66538431.1,3712145.8,5683625.9,200825.15,148125510.8,55904246.96,126140728.5,71855934,202201988.6,62831602.4,70643,12662708.5],["2017-03-24",65091424,70633259,71590136,8952449,61899050,1916134.13,66538431.1,3712145.8,5683625.9,200825.15,148125510.8,55904246.96,126140728.5,71855934,202201988.6,62831602.4,70643,12662708.5]]
</script>	
EOD;
		
		
		
		
		$isk_day_per_char_acumulated = <<<EOD
<script type="text/javascript">
google.load("visualization", "1", {packages:["corechart"]});
// google.charts.load('current', {'packages':['corechart']});
google.setOnLoadCallback(drawChart);
		
  function drawChart() {
		
    var data = google.visualization.arrayToDataTable(isk_day_char_acumulate);
		
    var options = {
		title: 'ISK/day per Character Acumulated'
    };
		
    var chart = new google.visualization.LineChart(document.getElementById('isk_day_per_char_acumulate'));
    chart.draw(data, options);
		
	//hhatia code
		
		
		
	            var columns = [];
		
            var defaultSeries = [];
            for (var k = 0; k <= data.getNumberOfColumns(); k++){
                defaultSeries.push(k);
                }
            var series ={};
            for (var i = 0; i < data.getNumberOfColumns(); i++){
                if (i == 0 || defaultSeries.indexOf(i) > -1){
		
                    columns.push(i);
                }
                else{
                    columns.push({
                        label: data.getColumnLabel(i),
                        type: data.getColumnType(i),
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
                height: 900
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
                                label: data.getColumnLabel(src),
                                type: data.getColumnType(src),
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
                        var view = new google.visualization.DataView(data);
                        view.setColumns(columns);
                        chart.draw(view, options);
                    }
                }
            }
		
            google.visualization.events.addListener(chart, 'select', showHideSeries);
		
            // instantiate and draw new chart with new view and options
            var view = new google.visualization.DataView(data);
                  view.setColumns(columns);
                  chart.draw(view, options);
		
		
		
  }
		
		
		
</script>
EOD;
		
		
		
		echo $prepared_dummy_data2;
		
// 		$data = new Top_Ratter ();
// 		// 		echo '<p>ISK/day per Character Acumulated</p>';
		
// 		$chars = $data->prepare_data_for_chart_by_days_acumulated ( $start, $end );
		
// 		$isk_day_char_acumu=json_encode($chars);
// 		echo'	<script type="text/javascript"> var isk_day_char_acumulate='.$isk_day_char_acumu.'</script>';
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
}

?>