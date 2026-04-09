<?
set_time_limit(900);

// check if reports are called by user or service
if (!isset($_POST['schedule'])) {
	session_start();
}

include('../init.php');
include('fn_common.php');
include('fn_route.php');
include('../tools/gc_func.php');
include('../tools/email.php');
include('../tools/html2pdf.php');

// check if reports are called by user or service
if (isset($_POST['schedule'])) {
	$_SESSION = getUserData($_POST['user_id']);
	loadLanguage($_SESSION["language"], $_SESSION["units"]);
} else {
	checkUserSession();
	loadLanguage($_SESSION["language"], $_SESSION["units"]);
}

if (@$_POST['cmd'] == 'report') {
	// check privileges
	if ($_SESSION["privileges"] == 'subuser') {
		$user_id = $_SESSION["manager_id"];
	} else {
		$user_id = $_SESSION["user_id"];
	}

	// generate or send report to e-mail
	if (isset($_POST['schedule'])) {
		//check user usage
		if (!checkUserUsage($user_id, 'email')) die;

		reportsSend();
	} else {
		$report = reportsGenerate();

		if ($report != false) {
			echo $report;
		}
	}

	die;
}

function reportsSend()
{
	global $_POST, $la, $user_id;


	$template = getDefaultTemplate('schedule_reports', $_SESSION["language"]);

	$subject = $la['REPORT'] . ' - ' . $_POST['name'];
	$message = $template['message'];

	$filename = strtolower($_POST['name']) . '_' . $_POST['dtf'] . '_' . $_POST['dtt'] . '.' . $_POST['format'];
	$report = reportsGenerate();

	if ($report != false) {
		$result = sendEmailReport($_POST['email'], $subject, $message, true, $filename, $report);

		if ($result) {
			//update user usage
			updateUserUsage($user_id, false, $result, false, false, false);
		}
	}

	die;
}

function reportsGenerate()
{
	global $_POST, $ms, $gsValues, $user_id;

	$name = $_POST['name'];
	$type = $_POST['type'];
	$ignore_empty_reports = $_POST['ignore_empty_reports'];
	$format = $_POST['format'];
	$show_coordinates = $_POST['show_coordinates'];
	$show_addresses = $_POST['show_addresses'];
	$zones_addresses = $_POST['zones_addresses'];
	$stop_duration = $_POST['stop_duration'];
	$speed_limit = $_POST['speed_limit'];
	$imei = $_POST['imei'];
	$zone_ids = $_POST['zone_ids'];
	$sensor_names = $_POST['sensor_names'];
	$data_items = $_POST['data_items'];
	$other = $_POST['other'];
	$dtf = $_POST['dtf'];
	$dtt = $_POST['dtt'];

	// check if object is not removed from system and also if it is active
	$imeis = array();
	$imeis_ = explode(",", $imei);
	for ($i = 0; $i < count($imeis_); ++$i) {
		$imei = $imeis_[$i];

		if (checkObjectActive($imei)) {
			if ($_SESSION["privileges"] == 'subuser') {
				if (checkSubuserToObjectPrivileges($_SESSION["privileges_imei"], $imei)) {
					$imeis[] = $imei;
				}
			} else {
				if (checkUserToObjectPrivileges($user_id, $imei)) {
					$imeis[] = $imei;
				}
			}
		}
	}

	if (count($imeis) == 0) {
		return false;
	}

	$data_items = explode(',', $data_items);

	// other
	if ($type == 'travel_sheet_dn') {
		$default = array(
			'dn_starts_hour' => '22',
			'dn_starts_minute' => '00',
			'dn_ends_hour' => '06',
			'dn_ends_minute' => '00'
		);

		if (($other == '') || (json_decode(stripslashes($other), true) == null)) {
			$other = $default;
		} else {
			$other = json_decode(stripslashes($other), true);

			if (!isset($other["dn_starts_hour"])) {
				$other["dn_starts_hour"] = $default["dn_starts_hour"];
			}
			if (!isset($other["dn_starts_minute"])) {
				$other["dn_starts_minute"] = $default["dn_starts_minute"];
			}
			if (!isset($other["dn_ends_hour"])) {
				$other["dn_ends_hour"] = $default["dn_ends_hour"];
			}
			if (!isset($other["dn_ends_minute"])) {
				$other["dn_ends_minute"] = $default["dn_ends_minute"];
			}
		}
	} else if (($type == 'rag') || ($type == 'rag_driver')) {
		$default = array('low_score' => 0, 'high_score' => 5);

		if (($other == '') || (json_decode(stripslashes($other), true) == null)) {
			$other = $default;
		} else {
			$other = json_decode(stripslashes($other), true);

			if (!isset($other["low_score"])) {
				$other["low_score"] = $default["low_score"];
			}
			if (!isset($other["high_score"])) {
				$other["high_score"] = $default["high_score"];
			}
		}
	} else {
		$other = '';
	}

	$report_html = reportsAddHeaderStart($format);
	$report_html .= reportsAddStyle($type, $format);
	$report_html .= reportsAddJS($type);
	$report_html .= reportsAddHeaderEnd();

	if ($format == 'html') {
		$report_html .= '<img class="logo" src="' . $gsValues['URL_ROOT'] . '/img/' . $gsValues['LOGO'] . '" /><hr/>';
	} else if ($format == 'pdf') {
		$image_source = $gsValues['PATH_ROOT'] . '/img/' . $gsValues['LOGO'];
		$image = fopen($image_source, 'r');
		$image_string = fread($image, filesize($image_source));

		$img_base64 = base64_encode($image_string);

		$report_html .= '<img class="logo" src="data:image/jpg;base64,' . $img_base64 . '" /><hr/>';
	}

	$report_html .= reportsGenerateLoop($type, $imeis, $dtf, $dtt, $ignore_empty_reports, $speed_limit, $stop_duration, $show_coordinates, $show_addresses, $zones_addresses, $zone_ids, $sensor_names, $data_items, $other);
	$report_html .= '</body></html>';

	$report = $report_html;

	if ($format == 'pdf') {
		$report = html2pdf($report);
	}

	if (!isset($_POST['schedule'])) {
		$report = base64_encode($report);
	}

	// store generated report
	if ($zone_ids != '') {
		$zones = count(explode(",", $zone_ids));
	} else {
		$zones = 0;
	}

	if ($sensor_names != '') {
		$sensors = count(explode(",", $sensor_names));
	} else {
		$sensors = 0;
	}

	if (isset($_POST['schedule'])) {
		$schedule = 'true';
	} else {
		$schedule = 'false';
	}
	$filename = strtolower($name) . '_' . $dtf . '_' . $dtt;

	$report_file = $user_id . '_' . md5($type . $dtf . $dtt . gmdate("Y-m-d H:i:s") . rand());
	$file_path = $gsValues['PATH_ROOT'] . 'data/user/reports/' . $report_file;

	$report_html = base64_encode($report_html);

	$fp = fopen($file_path, 'wb');
	fwrite($fp, $report_html);
	fclose($fp);

	if (is_file($file_path)) {
		$q = "INSERT INTO `gs_user_reports_generated`(	`user_id`,
									`dt_report`,
									`name`,
									`type`,
									`format`,
									`objects`,
									`zones`,
									`sensors`,
									`schedule`,
									`filename`,
									`report_file`)
									VALUES
									('" . $user_id . "',
									'" . gmdate("Y-m-d H:i:s") . "',
									'" . $name . "',
									'" . $type . "',
									'" . $format . "',
									'" . count($imeis) . "',
									'" . $zones . "',
									'" . $sensors . "',
									'" . $schedule . "',
									'" . $filename . "',
									'" . $report_file . "')";
		$r = mysqli_query($ms, $q);
	}

	return $report;
}

function reportsGenerateLoop($type, $imeis, $dtf, $dtt, $ignore_empty_reports, $speed_limit, $stop_duration, $show_coordinates, $show_addresses, $zones_addresses, $zone_ids, $sensor_names, $data_items, $other)
{
	global $la;

	$result = '';

	for ($i = 0; $i < count($imeis); ++$i) {
		$imei = $imeis[$i];

		if ($type == "general") //GENERAL_INFO
		{
			$report = reportsGenerateGenInfo($imei, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $speed_limit, $stop_duration, $data_items);

			if ($report == false) {
				if ($ignore_empty_reports == 'false') {
					$result .= '<h3>' . $la['GENERAL_INFO'] . '</h3>';
					$result .= reportsAddReportHeader($imei, $dtf, $dtt);
					$result .= '<table><tr><td>' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr></table>';
					$result .= '<br/><hr/>';
				}
			} else {
				$result .= '<h3>' . $la['GENERAL_INFO'] . '</h3>';
				$result .= reportsAddReportHeader($imei, $dtf, $dtt);
				$result .= $report;
				$result .= '<br/><hr/>';
			}
		} elseif ($type == "drives_stops") //DRIVES_AND_STOPS
		{
			$report = reportsGenerateDrivesAndStops($imei, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $stop_duration, $show_coordinates, $show_addresses, $zones_addresses, $data_items);

			if ($report == false) {
				if ($ignore_empty_reports == 'false') {
					$result .= '<h3>' . $la['DRIVES_AND_STOPS'] . '</h3>';
					$result .= reportsAddReportHeader($imei, $dtf, $dtt);
					$result .= '<table><tr><td>' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr></table>';
					$result .= '<br/><hr/>';
				}
			} else {
				$result .= '<h3>' . $la['DRIVES_AND_STOPS'] . '</h3>';
				$result .= reportsAddReportHeader($imei, $dtf, $dtt);
				$result .= $report;
				$result .= '<br/><hr/>';
			}
		} elseif ($type == "drives_stops_sensors") //DRIVES_AND_STOPS_WITH_SENSORS
		{
			$sensors = getSensors($imei);
			$sensors_ = array();

			if (is_array($sensors)) {
				$sensor_names_ = explode(",", $sensor_names);
				for ($j = 0; $j < count($sensor_names_); ++$j) {
					for ($k = 0; $k < count($sensors); ++$k) {
						if (isset($sensors[$k]['name']) && $sensor_names_[$j] == $sensors[$k]['name']) {
							$sensors_[] = $sensors[$k];
						}
					}
				}
			}

			$report = reportsGenerateDrivesAndStopsSensors($imei, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $sensors_, $stop_duration, $show_coordinates, $show_addresses, $zones_addresses, $data_items);

			if ($report == false) {
				if ($ignore_empty_reports == 'false') {
					$result .= '<h3>' . $la['DRIVES_AND_STOPS_WITH_SENSORS'] . '</h3>';
					$result .= reportsAddReportHeader($imei, $dtf, $dtt);
					$result .= '<table><tr><td>' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr></table>';
					$result .= '<br/><hr/>';
				}
			} else {
				$result .= '<h3>' . $la['DRIVES_AND_STOPS_WITH_SENSORS'] . '</h3>';
				$result .= reportsAddReportHeader($imei, $dtf, $dtt);
				$result .= $report;
				$result .= '<br/><hr/>';
			}
		} elseif ($type == "drives_stops_logic") //DRIVES_AND_STOPS_WITH_LOGIC_SENSORS
		{
			$sensors = getSensors($imei);
			$sensors_ = array();

			if (is_array($sensors)) {
				$sensor_names_ = explode(",", $sensor_names);
				for ($j = 0; $j < count($sensor_names_); ++$j) {
					for ($k = 0; $k < count($sensors); ++$k) {
						if (isset($sensors[$k]['name']) && $sensor_names_[$j] == $sensors[$k]['name']) {
							$sensors_[] = $sensors[$k];
						}
					}
				}
			}

			$report = reportsGenerateDrivesAndStopsLogicSensors($imei, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $sensors_, $stop_duration, $show_coordinates, $show_addresses, $zones_addresses, $data_items);

			if ($report == false) {
				if ($ignore_empty_reports == 'false') {
					$result .= '<h3>' . $la['DRIVES_AND_STOPS_WITH_LOGIC_SENSORS'] . '</h3>';
					$result .= reportsAddReportHeader($imei, $dtf, $dtt);
					$result .= '<table><tr><td>' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr></table>';
					$result .= '<br/><hr/>';
				}
			} else {
				$result .= '<h3>' . $la['DRIVES_AND_STOPS_WITH_LOGIC_SENSORS'] . '</h3>';
				$result .= reportsAddReportHeader($imei, $dtf, $dtt);
				$result .= $report;
				$result .= '<br/><hr/>';
			}
		} else if ($type == "travel_sheet") //TRAVEL_SHEET
		{
			$report = reportsGenerateTravelSheet($imei, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $stop_duration, $show_coordinates, $show_addresses, $zones_addresses, $data_items);

			if ($report == false) {
				if ($ignore_empty_reports == 'false') {
					$result .= '<h3>' . $la['TRAVEL_SHEET'] . '</h3>';
					$result .= reportsAddReportHeader($imei, $dtf, $dtt);
					$result .= '<table><tr><td>' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr></table>';
					$result .= '<br/><hr/>';
				}
			} else {
				$result .= '<h3>' . $la['TRAVEL_SHEET'] . '</h3>';
				$result .= reportsAddReportHeader($imei, $dtf, $dtt);
				$result .= $report;
				$result .= '<br/><hr/>';
			}
		} else if ($type == "travel_sheet_dn") //TRAVEL_SHEET_DAY_NIGHT
		{
			$report = reportsGenerateTravelSheetDayNight($imei, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $stop_duration, $show_coordinates, $show_addresses, $zones_addresses, $data_items, $other);

			if ($report == false) {
				if ($ignore_empty_reports == 'false') {
					$result .= '<h3>' . $la['TRAVEL_SHEET_DAY_NIGHT'] . '</h3>';
					$result .= reportsAddReportHeader($imei, $dtf, $dtt);
					$result .= '<table><tr><td>' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr></table>';
					$result .= '<br/><hr/>';
				}
			} else {
				$result .= '<h3>' . $la['TRAVEL_SHEET_DAY_NIGHT'] . '</h3>';
				$result .= reportsAddReportHeader($imei, $dtf, $dtt);
				$result .= $report;
				$result .= '<br/><hr/>';
			}
		} else if ($type == "mileage_daily") //MILEAGE_DAILY
		{
			$report = reportsGenerateMileageDaily($imei, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $data_items);

			if ($report == false) {
				if ($ignore_empty_reports == 'false') {
					$result .= '<h3>' . $la['MILEAGE_DAILY'] . '</h3>';
					$result .= reportsAddReportHeader($imei, $dtf, $dtt);
					$result .= '<table><tr><td>' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr></table>';
					$result .= '<br/><hr/>';
				}
			} else {
				$result .= '<h3>' . $la['MILEAGE_DAILY'] . '</h3>';
				$result .= reportsAddReportHeader($imei, $dtf, $dtt);
				$result .= $report;
				$result .= '<br/><hr/>';
			}
		} else if ($type == "overspeed") //OVERSPEED
		{
			$report = reportsGenerateOverspeed($imei, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $speed_limit, $show_coordinates, $show_addresses, $zones_addresses, $data_items);

			if ($report == false) {
				if ($ignore_empty_reports == 'false') {
					$result .= '<h3>' . $la['OVERSPEEDS'] . '</h3>';
					$result .= reportsAddReportHeader($imei, $dtf, $dtt);
					$result .= '<table><tr><td>' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr></table>';
					$result .= '<br/><hr/>';
				}
			} else {
				$result .= '<h3>' . $la['OVERSPEEDS'] . '</h3>';
				$result .= reportsAddReportHeader($imei, $dtf, $dtt);
				$result .= $report;
				$result .= '<br/><hr/>';
			}
		} else if ($type == "underspeed") //UNDERSPEED
		{
			$report = reportsGenerateUnderspeed($imei, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $speed_limit, $show_coordinates, $show_addresses, $zones_addresses, $data_items);

			if ($report == false) {
				if ($ignore_empty_reports == 'false') {
					$result .= '<h3>' . $la['UNDERSPEEDS'] . '</h3>';
					$result .= reportsAddReportHeader($imei, $dtf, $dtt);
					$result .= '<table><tr><td>' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr></table>';
					$result .= '<br/><hr/>';
				}
			} else {
				$result .= '<h3>' . $la['UNDERSPEEDS'] . '</h3>';
				$result .= reportsAddReportHeader($imei, $dtf, $dtt);
				$result .= $report;
				$result .= '<br/><hr/>';
			}
		} else if ($type == "zone_in_out") //ZONE_IN_OUT
		{
			$report = reportsGenerateZoneInOut($imei, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $show_coordinates, $show_addresses, $zones_addresses, $zone_ids, $data_items);

			if ($report == false) {
				if ($ignore_empty_reports == 'false') {
					$result .= '<h3>' . $la['ZONE_IN_OUT'] . '</h3>';
					$result .= reportsAddReportHeader($imei, $dtf, $dtt);
					$result .= '<table><tr><td>' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr></table>';
					$result .= '<br/><hr/>';
				}
			} else {
				$result .= '<h3>' . $la['ZONE_IN_OUT'] . '</h3>';
				$result .= reportsAddReportHeader($imei, $dtf, $dtt);
				$result .= $report;
				$result .= '<br/><hr/>';
			}
		} else if ($type == "zone_in_out_general") //ZONE_IN_OUT_WITH_GEN_INFO
		{
			$report = reportsGenerateZoneInOutGenInfo($imei, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $speed_limit, $stop_duration, $show_coordinates, $show_addresses, $zones_addresses, $zone_ids, $data_items);

			if ($report == false) {
				if ($ignore_empty_reports == 'false') {
					$result .= '<h3>' . $la['ZONE_IN_OUT_WITH_GEN_INFORMATION'] . '</h3>';
					$result .= reportsAddReportHeader($imei, $dtf, $dtt);
					$result .= '<table><tr><td>' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr></table>';
					$result .= '<br/><hr/>';
				}
			} else {
				$result .= '<h3>' . $la['ZONE_IN_OUT_WITH_GEN_INFORMATION'] . '</h3>';
				$result .= reportsAddReportHeader($imei, $dtf, $dtt);
				$result .= $report;
				$result .= '<br/><hr/>';
			}
		} else if ($type == "events") //EVENTS
		{
			$report = reportsGenerateEvents($imei, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $show_coordinates, $show_addresses, $zones_addresses, $data_items);

			if ($report == false) {
				if ($ignore_empty_reports == 'false') {
					$result .= '<h3>' . $la['EVENTS'] . '</h3>';
					$result .= reportsAddReportHeader($imei, $dtf, $dtt);
					$result .= '<table><tr><td>' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr></table>';
					$result .= '<br/><hr/>';
				}
			} else {
				$result .= '<h3>' . $la['EVENTS'] . '</h3>';
				$result .= reportsAddReportHeader($imei, $dtf, $dtt);
				$result .= $report;
				$result .= '<br/><hr/>';
			}
		} else if ($type == "service") //SERVICE
		{
			$report = reportsGenerateService($imei, $data_items);

			if ($report == false) {
				if ($ignore_empty_reports == 'false') {
					$result .= '<h3>' . $la['SERVICE'] . '</h3>';
					$result .= reportsAddReportHeader($imei, $dtf, $dtt);
					$result .= '<table><tr><td>' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr></table>';
					$result .= '<br/><hr/>';
				}
			} else {
				$result .= '<h3>' . $la['SERVICE'] . '</h3>';
				$result .= reportsAddReportHeader($imei, $dtf, $dtt);
				$result .= $report;
				$result .= '<br/><hr/>';
			}
		} else if ($type == "fuelfillings") //FUEL_FILLINGS
		{
			$report = reportsGenerateFuelFillings($imei, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $show_coordinates, $show_addresses, $zones_addresses, $data_items);

			if ($report == false) {
				if ($ignore_empty_reports == 'false') {
					$result .= '<h3>' . $la['FUEL_FILLINGS'] . '</h3>';
					$result .= reportsAddReportHeader($imei, $dtf, $dtt);
					$result .= '<table><tr><td>' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr></table>';
					$result .= '<br/><hr/>';
				}
			} else {
				$result .= '<h3>' . $la['FUEL_FILLINGS'] . '</h3>';
				$result .= reportsAddReportHeader($imei, $dtf, $dtt);
				$result .= $report;
				$result .= '<br/><hr/>';
			}
		} else if ($type == "fuelthefts") //FUEL_THEFTS
		{
			$report = reportsGenerateFuelThefts($imei, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $show_coordinates, $show_addresses, $zones_addresses, $data_items);

			if ($report == false) {
				if ($ignore_empty_reports == 'false') {
					$result .= '<h3>' . $la['FUEL_THEFTS'] . '</h3>';
					$result .= reportsAddReportHeader($imei, $dtf, $dtt);
					$result .= '<table><tr><td>' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr></table>';
					$result .= '<br/><hr/>';
				}
			} else {
				$result .= '<h3>' . $la['FUEL_THEFTS'] . '</h3>';
				$result .= reportsAddReportHeader($imei, $dtf, $dtt);
				$result .= $report;
				$result .= '<br/><hr/>';
			}
		} else if ($type == "logic_sensors") //LOGIC_SENSORS
		{
			$sensors = getSensors($imei);
			$sensors_ = array();

			$sensor_names_ = explode(",", $sensor_names);
			for ($j = 0; $j < count($sensor_names_); ++$j) {
				for ($k = 0; $k < count($sensors); ++$k) {
					if ($sensors[$k]['result_type'] == 'logic') {
						if ($sensor_names_[$j] == $sensors[$k]['name']) {
							$sensors_[] = $sensors[$k];
						}
					}
				}
			}

			$report = reportsGenerateLogicSensorInfo($imei, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $sensors_, $show_coordinates, $show_addresses, $zones_addresses, $data_items);

			if ($report == false) {
				if ($ignore_empty_reports == 'false') {
					$result .= '<h3>' . $la['LOGIC_SENSORS'] . '</h3>';
					$result .= reportsAddReportHeader($imei, $dtf, $dtt);
					$result .= '<table><tr><td>' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr></table>';
					$result .= '<br/><hr/>';
				}
			} else {
				$result .= '<h3>' . $la['LOGIC_SENSORS'] . '</h3>';
				$result .= reportsAddReportHeader($imei, $dtf, $dtt);
				$result .= $report;
				$result .= '<br/><hr/>';
			}
		} else if ($type == "speed_graph") //SPEED
		{
			$sensors = array(array('name' => '', 'type' => 'speed', 'units' => $la["UNIT_SPEED"], 'result_type' => ''));

			$report = reportsGenerateGraph($imei, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $sensors);

			if ($report == false) {
				if ($ignore_empty_reports == 'false') {
					$result .= '<h3>' . $la['SPEED_GRAPH'] . '</h3>';
					$result .= reportsAddReportHeader($imei, $dtf, $dtt);
					$result .= '<table><tr><td>' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr></table>';
					$result .= '<br/><hr/>';
				}
			} else {
				$result .= '<h3>' . $la['SPEED_GRAPH'] . '</h3>';
				$result .= reportsAddReportHeader($imei, $dtf, $dtt);
				$result .= $report;
				$result .= '<br/><hr/>';
			}
		} else if ($type == "altitude_graph") //ALTITUDE
		{
			$sensors = array(array('name' => '', 'type' => 'altitude', 'units' => $la["UNIT_HEIGHT"], 'result_type' => ''));

			$report = reportsGenerateGraph($imei, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $sensors);

			if ($report == false) {
				if ($ignore_empty_reports == 'false') {
					$result .= '<h3>' . $la['ALTITUDE_GRAPH'] . '</h3>';
					$result .= reportsAddReportHeader($imei, $dtf, $dtt);
					$result .= '<table><tr><td>' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr></table>';
					$result .= '<br/><hr/>';
				}
			} else {
				$result .= '<h3>' . $la['ALTITUDE_GRAPH'] . '</h3>';
				$result .= reportsAddReportHeader($imei, $dtf, $dtt);
				$result .= $report;
				$result .= '<br/><hr/>';
			}
		} else if ($type == "acc_graph") //ACC
		{
			$sensors = getSensorFromType($imei, 'acc');

			$report = reportsGenerateGraph($imei, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $sensors);

			if ($report == false) {
				if ($ignore_empty_reports == 'false') {
					$result .= '<h3>' . $la['IGNITION_GRAPH'] . '</h3>';
					$result .= reportsAddReportHeader($imei, $dtf, $dtt);
					$result .= '<table><tr><td>' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr></table>';
					$result .= '<br/><hr/>';
				}
			} else {
				$result .= '<h3>' . $la['IGNITION_GRAPH'] . '</h3>';
				$result .= reportsAddReportHeader($imei, $dtf, $dtt);
				$result .= $report;
				$result .= '<br/><hr/>';
			}
		} else if ($type == "fuellevel_graph") //FUEL_LEVEL
		{
			$fuel_sensors = getSensorFromType($imei, 'fuel');
			$fuelsumup_sensor = getSensorFromType($imei, 'fuelsumup');

			if ($fuelsumup_sensor == false) {
				$sensors = $fuel_sensors;
			} else {
				$sensors = array_merge($fuel_sensors, $fuelsumup_sensor);
			}

			$report = reportsGenerateGraph($imei, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $sensors);

			if ($report == false) {
				if ($ignore_empty_reports == 'false') {
					$result .= '<h3>' . $la['FUEL_LEVEL_GRAPH'] . '</h3>';
					$result .= reportsAddReportHeader($imei, $dtf, $dtt);
					$result .= '<table><tr><td>' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr></table>';
					$result .= '<br/><hr/>';
				}
			} else {
				$result .= '<h3>' . $la['FUEL_LEVEL_GRAPH'] . '</h3>';
				$result .= reportsAddReportHeader($imei, $dtf, $dtt);
				$result .= $report;
				$result .= '<br/><hr/>';
			}
		} else if ($type == "temperature_graph") //TEMPERATURE
		{
			$sensors = getSensorFromType($imei, 'temp');

			$report = reportsGenerateGraph($imei, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $sensors);

			if ($report == false) {
				if ($ignore_empty_reports == 'false') {
					$result .= '<h3>' . $la['TEMPERATURE_GRAPH'] . '</h3>';
					$result .= reportsAddReportHeader($imei, $dtf, $dtt);
					$result .= '<table><tr><td>' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr></table>';
					$result .= '<br/><hr/>';
				}
			} else {
				$result .= '<h3>' . $la['TEMPERATURE_GRAPH'] . '</h3>';
				$result .= reportsAddReportHeader($imei, $dtf, $dtt);
				$result .= $report;
				$result .= '<br/><hr/>';
			}
		} else if ($type == "sensor_graph") //SENSOR
		{
			$sensors = getSensors($imei);
			$sensors_ = array();

			$sensor_names_ = explode(",", $sensor_names);
			for ($j = 0; $j < count($sensor_names_); ++$j) {
				for ($k = 0; $k < count($sensors); ++$k) {
					if ($sensor_names_[$j] == $sensors[$k]['name']) {
						$sensors_[] = $sensors[$k];
					}
				}
			}

			$report = reportsGenerateGraph($imei, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $sensors_);

			if ($report == false) {
				if ($ignore_empty_reports == 'false') {
					$result .= '<h3>' . $la['SENSOR_GRAPH'] . '</h3>';
					$result .= reportsAddReportHeader($imei, $dtf, $dtt);
					$result .= '<table><tr><td>' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr></table>';
					$result .= '<br/><hr/>';
				}
			} else {
				$result .= '<h3>' . $la['SENSOR_GRAPH'] . '</h3>';
				$result .= reportsAddReportHeader($imei, $dtf, $dtt);
				$result .= $report;
				$result .= '<br/><hr/>';
			}
		} elseif ($type == "routes") //ROUTES
		{
			$report = reportsGenerateRoutes($imei, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $speed_limit, $stop_duration, $data_items, false);

			if ($report == false) {
				if ($ignore_empty_reports == 'false') {
					if ($i == 0) {
						$result .= '<div style="height: 815px">';
					} else {
						$result .= '<div style="height: 900px">';
					}
					$result .= '<h3>' . $la['ROUTES'] . '</h3>';
					$result .= reportsAddReportHeader($imei, $dtf, $dtt);
					$result .= '<table><tr><td>' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr></table>';
					$result .= '<br/><hr/>';
					$result .= '</div>';
				}
			} else {
				if ($i == 0) {
					$result .= '<div style="height: 815px">';
				} else {
					$result .= '<div style="height: 900px">';
				}
				$result .= '<h3>' . $la['ROUTES'] . '</h3>';
				$result .= reportsAddReportHeader($imei, $dtf, $dtt);
				$result .= $report;
				$result .= '<br/><hr/>';
				$result .= '</div>';
			}
		} elseif ($type == "routes_stops") //ROUTES WITH STOPS
		{
			$report = reportsGenerateRoutes($imei, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $speed_limit, $stop_duration, $data_items, true);

			if ($report == false) {
				if ($ignore_empty_reports == 'false') {
					if ($i == 0) {
						$result .= '<div style="height: 815px">';
					} else {
						$result .= '<div style="height: 900px">';
					}
					$result .= '<h3>' . $la['ROUTES'] . '</h3>';
					$result .= reportsAddReportHeader($imei, $dtf, $dtt);
					$result .= '<table><tr><td>' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr></table>';
					$result .= '<br/><hr/>';
					$result .= '</div>';
				}
			} else {
				if ($i == 0) {
					$result .= '<div style="height: 815px">';
				} else {
					$result .= '<div style="height: 900px">';
				}
				$result .= '<h3>' . $la['ROUTES'] . '</h3>';
				$result .= reportsAddReportHeader($imei, $dtf, $dtt);
				$result .= $report;
				$result .= '<br/><hr/>';
				$result .= '</div>';
			}
		} elseif ($type == "image_gallery") //IMAGE_GALLERY
		{
			$report = reportsGenerateImageGallery($imei, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $show_coordinates, $show_addresses, $zones_addresses, $data_items);

			if ($report == false) {
				if ($ignore_empty_reports == 'false') {
					$result .= '<h3>' . $la['IMAGE_GALLERY'] . '</h3>';
					$result .= reportsAddReportHeader($imei, $dtf, $dtt);
					$result .= '<table><tr><td>' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr></table>';
					$result .= '<br/><hr/>';
				}
			} else {
				$result .= '<h3>' . $la['IMAGE_GALLERY'] . '</h3>';
				$result .= reportsAddReportHeader($imei, $dtf, $dtt);
				$result .= $report;
				$result .= '<br/><hr/>';
			}
		}
	}

	if ($type == "general_merged") //GENERAL_INFO_MERGED
	{
		$result .= '<h3>' . $la['GENERAL_INFO_MERGED'] . '</h3>';
		$result .= reportsAddReportHeader('', $dtf, $dtt);
		$result .= reportsGenerateGenInfoMerged($imeis, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $speed_limit, $stop_duration, $data_items);
		$result .= '<br/><hr/>';
	} elseif ($type == "object_info_ws") //OBJECT_INFO_WS
	{
		$result .= '<h3>' . $la['OBJECT_INFO_WS'] . ' -- ' . $_SESSION['username'] . '</h3>';
		$result .= reportsAddReportHeader('', $dtf, $dtt);
		$result .= reportsGenerateObjectInfoWs($imeis, $data_items);
		$result .= '<br/><hr/>';
	} elseif ($type == "object_info") //OBJECT_INFO
	{
		$result .= '<h3>' . $la['OBJECT_INFO'] . ', ' . $_SESSION['username'] . '</h3>';
		$result .= reportsAddReportHeader('', $dtf, $dtt);
		$result .= reportsGenerateObjectInfo($imeis, $data_items);
		$result .= '<br/><hr/>';
	} elseif ($type == "kilometers") //KILOMETERS_ENGINE_HOURS
	{
		$result .= '<h3>' . $la['KILOMETERS_ENGINE_HOURS'] . ' -- ' . $_SESSION['username'] . '</h3>';
		$result .= reportsAddReportHeaderKilometers($dtf, $dtt);
		$result .= reportsGenerateKilometers($imeis, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $speed_limit, $show_coordinates, $show_addresses, $zones_addresses, $data_items);
		$result .= '<br/><hr/>';
	} elseif ($type == "object_info_admin") //OBJECT_INFO_ADMIN
	{
		$result .= '<h3>' . $la['OBJECT_INFO_ADMIN'] . '</h3>';
		$result .= reportsAddReportHeader('', $dtf, $dtt);
		$result .= reportsGenerateObjectInfoAdmin($imeis, $show_coordinates, $show_addresses, $zones_addresses, $data_items);
		$result .= '<br/><hr/>';
	} else if ($type == "object_info_ventas") //OBJECT_INFO_VENTAS
	{
		$result .= '<h3>' . $la['OBJECT_INFO_VENTAS'] . '</h3>';
		$result .= reportsAddReportHeader('', $dtf, $dtt);
		$result .= reportsGenerateObjectInfoVentas($imeis, $data_items);
		$result .= '<br/><hr/>';
	} else if ($type == "object_info_sensors") //OBJECT_INFO_SENSORS
	{
		$result .= '<h3>' . $la['OBJECT_INFO_SENSORS'] . '</h3>';
		$result .= reportsAddReportHeader('', $dtf, $dtt);
		$result .= reportsGenerateObjectInfoSensors($imeis, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $data_items);
		$result .= '<br/><hr/>';
	} else if ($type == "object_info_sensors_client") //OBJECT_INFO_SENSORS_CLIENT
	{
		$sensors_ = getSensors($imei);
		$sensors = array();
		$sensor_names = $_POST['sensor_names'];

		if (is_array($sensors_)) {
			$sensor_names_ = explode(",", $sensor_names);
			for ($j = 0; $j < count($sensor_names_); ++$j) {
				for ($k = 0; $k < count($sensors_); ++$k) {
					if (isset($sensors_[$k]['name']) && $sensor_names_[$j] == $sensors_[$k]['name']) {
						$sensors[] = $sensors_[$k];
					}
				}
			}
		}
		$result .= '<h3>' . $la['OBJECT_INFO_SENSORS'] . '</h3>';
		$result .= reportsAddReportHeader('', $dtf, $dtt);
		$result .= reportsGenerateObjectInfoSensorsClient($imeis, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $sensors_, $stop_duration, $show_coordinates, $show_addresses, $zones_addresses, $data_items);
		$result .= '<br/><hr/>';
	} else if ($type == "current_position") //CURRENT POSITION
	{
		$result .= '<h3>' . $la['CURRENT_POSITION'] . '</h3>';
		$result .= reportsAddReportHeader('', $dtf, $dtt);
		$result .= reportsGenerateCurrentPosition($imeis, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $show_coordinates, $show_addresses, $zones_addresses, $data_items, false);
		$result .= '<br/><hr/>';
	} else if ($type == "current_position_off") //CURRENT POSITION OFFLINE
	{
		$result .= '<h3>' . $la['CURRENT_POSITION_OFFLINE'] . '</h3>';
		$result .= reportsAddReportHeader('', $dtf, $dtt);
		$result .= reportsGenerateCurrentPosition($imeis, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $show_coordinates, $show_addresses, $zones_addresses, $data_items, 'offline');
		$result .= '<br/><hr/>';
	} elseif ($type == "rag") //RAG BY OBJECT
	{
		$result .= '<h3>' . $la['DRIVER_BEHAVIOR_RAG'] . '</h3>';
		$result .= reportsAddReportHeader('', $dtf, $dtt);
		$result .= reportsGenerateRagByObject($imeis, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $speed_limit, $data_items, $other);
		$result .= '<br/><hr/>';
	} elseif ($type == "rag_driver") //RAG BY OBJECT
	{
		$result .= '<h3>' . $la['DRIVER_BEHAVIOR_RAG'] . '</h3>';
		$result .= reportsAddReportHeader('', $dtf, $dtt);
		$result .= reportsGenerateRagByDriver($imeis, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $speed_limit, $data_items, $other);
		$result .= '<br/><hr/>';
	} elseif ($type == "tasks") //TASKS
	{
		$result .= '<h3>' . $la['TASKS'] . '</h3>';
		$result .= reportsAddReportHeader('', $dtf, $dtt);
		$result .= reportsGenerateTasks($imeis, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $show_coordinates, $show_addresses, $zones_addresses, $data_items);
		$result .= '<br/><hr/>';
	} elseif ($type == "rilogbook") //RFID_AND_IBUTTON_LOGBOOK
	{
		$result .= '<h3>' . $la['RFID_AND_IBUTTON_LOGBOOK'] . '</h3>';
		$result .= reportsAddReportHeader('', $dtf, $dtt);
		$result .= reportsGenerateRiLogbook($imeis, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $show_coordinates, $show_addresses, $zones_addresses, $data_items);
		$result .= '<br/><hr/>';
	} elseif ($type == "dtc") //DIAGNOSTIC_TROUBLE_CODES
	{
		$result .= '<h3>' . $la['DIAGNOSTIC_TROUBLE_CODES'] . '</h3>';
		$result .= reportsAddReportHeader('', $dtf, $dtt);
		$result .= reportsGenerateDTC($imeis, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $show_coordinates, $show_addresses, $zones_addresses, $data_items);
		$result .= '<br/><hr/>';
	} elseif ($type == "expenses") //EXPENSES
	{
		$result .= '<h3>' . $la['EXPENSES'] . '</h3>';
		$result .= reportsAddReportHeader('', $dtf, $dtt);
		$result .= reportsGenerateExpenses($imeis, convUserUTCTimezone($dtf), convUserUTCTimezone($dtt), $data_items);
		$result .= '<br/><hr/>';
	}

	return $result;
}

function reportsGenerateGenInfo($imei, $dtf, $dtt, $speed_limit, $stop_duration, $data_items) //GENERAL_INFO
{
	global $la, $user_id;

	$result = '';
	$data = getRoute($user_id, $imei, $dtf, $dtt, $stop_duration, true);

	if (empty($data['route'])) {
		return false;
	}


	if ($speed_limit > 0) {
		$overspeeds = getRouteOverspeeds($data['route'], $speed_limit);
		$overspeeds_count = count($overspeeds);
	} else {
		$overspeeds_count = 0;
	}

	$odometer = getObjectOdometer($imei);
	$odometer = floor(convDistanceUnits($odometer, 'km', $_SESSION["unit_distance"]));

	$result .= '<table>';
	if (in_array("route_start", $data_items)) {
		$result .= '<tr>
					<td><strong>' . $la['ROUTE_START'] . ':</strong></td>
					<td>' . $data['route'][0][0] . '</td>
				</tr>';
	}

	if (in_array("route_end", $data_items)) {
		$result .= '<tr>
					<td><strong>' . $la['ROUTE_END'] . ':</strong></td>
					<td>' . $data['route'][count($data['route']) - 1][0] . '</td>
				</tr>';
	}

	if (in_array("route_length", $data_items)) {
		$result .= '<tr>
					<td><strong>' . $la['ROUTE_LENGTH'] . ':</strong></td>
					<td>' . $data['route_length'] . ' ' . $la["UNIT_DISTANCE"] . '</td>
				</tr>';
	}

	if (in_array("move_duration", $data_items)) {
		$result .= '<tr>
					<td><strong>' . $la['MOVE_DURATION'] . ':</strong></td>
					<td>' . $data['drives_duration'] . '</td>
				</tr>';
	}

	if (in_array("stop_duration", $data_items)) {
		$result .= '<tr>
					<td><strong>' . $la['STOP_DURATION'] . ':</strong></td>
					<td>' . $data['stops_duration'] . '</td>
				</tr>';
	}

	if (in_array("stop_count", $data_items)) {
		$result .= '<tr>
					<td><strong>' . $la['STOP_COUNT'] . ':</strong></td>
					<td>' . count($data['stops']) . '</td>
				</tr>';
	}

	if (in_array("top_speed", $data_items)) {
		$result .= '<tr>
					<td><strong>' . $la['TOP_SPEED'] . ':</strong></td>
					<td>' . $data['top_speed'] . ' ' . $la["UNIT_SPEED"] . '</td>
				</tr>';
	}

	if (in_array("avg_speed", $data_items)) {
		$result .= '<tr>
					<td><strong>' . $la['AVG_SPEED'] . ':</strong></td>
					<td>' . $data['avg_speed'] . ' ' . $la["UNIT_SPEED"] . '</td>
				</tr>';
	}

	if (in_array("overspeed_count", $data_items)) {
		$result .= '<tr>
					<td><strong>' . $la['OVERSPEED_COUNT'] . ':</strong></td>
					<td>' . $overspeeds_count . '</td>
				</tr>';
	}

	if (in_array("fuel_consumption", $data_items)) {
		$result .= '<tr>
					<td><strong>' . $la['FUEL_CONSUMPTION'] . ':</strong></td>
					<td>' . $data['fuel_consumption'] . ' ' . $la["UNIT_CAPACITY"] . '</td>
				</tr>';
	}

	if (in_array("avg_fuel_consumption", $data_items)) {
		if ($_SESSION["unit_capacity"] == 'l') {
			$result .= '<tr>
						<td><strong>' . $la['AVG_FUEL_CONSUMPTION_100_KM'] . ':</strong></td>
						<td>' . $data['fuel_consumption_per_km'] . ' ' . $la["UNIT_CAPACITY"] . '</td>
					</tr>';
		} else {
			$result .= '<tr>
						<td><strong>' . $la['AVG_FUEL_CONSUMPTION_MPG'] . ':</strong></td>
						<td>' . $data['fuel_consumption_mpg'] . ' ' . $la["UNIT_MI"] . '</td>
					</tr>';
		}
	}

	if (in_array("fuel_cost", $data_items)) {
		$result .= '<tr>
					<td><strong>' . $la['FUEL_COST'] . ':</strong></td>
					<td>' . $data['fuel_cost'] . ' ' . $_SESSION["currency"] . '</td>
				</tr>';
	}

	if (in_array("engine_work", $data_items)) {
		$result .= '<tr>
					<td><strong>' . $la['ENGINE_WORK'] . ':</strong></td>
					<td>' . $data['engine_work'] . '</td>
				</tr>';
	}

	if (in_array("engine_idle", $data_items)) {
		$result .= '<tr>
					<td><strong>' . $la['ENGINE_IDLE'] . ':</strong></td>
					<td>' . $data['engine_idle'] . '</td>
				</tr>';
	}

	if (in_array("odometer", $data_items)) {
		$result .= '<tr>
					<td><strong>' . $la['ODOMETER'] . ':</strong></td>
					<td>' . $odometer . ' ' . $la["UNIT_DISTANCE"] . '</td>
				</tr>';
	}

	if (in_array("engine_hours", $data_items)) {
		$result .= '<tr>
					<td><strong>' . $la['ENGINE_HOURS'] . ':</strong></td>
					<td>' . getObjectEngineHours($imei, true) . '</td>
				</tr>';
	}

	if (in_array("driver", $data_items)) {
		$result .= '<tr>';

		$params = $data['route'][count($data['route']) - 1][6];

		$driver = getObjectDriver($user_id, $imei, $params);
		if ($driver == false) {
			$driver['driver_name'] = $la['NA'];
		}

		$result .= 	'<td><strong>' . $la['DRIVER'] . ':</strong></td>
					<td>' . $driver['driver_name'] . '</td>
					</tr>';
	}

	if (in_array("trailer", $data_items)) {
		$result .= '<tr>';

		$params = $data['route'][count($data['route']) - 1][6];
		$trailer = getObjectTrailer($user_id, $imei, $params);
		if ($trailer == false) {
			$trailer['trailer_name'] = $la['NA'];
		}

		$result .= 	'<td><strong>' . $la['TRAILER'] . ':</strong></td>
					<td>' . $trailer['trailer_name'] . '</td>
					</tr>';
	}

	$result .= '</table>';

	return $result;
}

function reportsGenerateGenInfoMerged($imeis, $dtf, $dtt, $speed_limit, $stop_duration, $data_items) //GENERAL_INFO_MERGED
{
	global $la, $user_id;

	$result = '<table class="report" width="100%"><tr align="center">';

	$result .= '<th>' . $la['OBJECT'] . '</th>';

	if (in_array("route_start", $data_items)) {
		$result .= '<th>' . $la['ROUTE_START'] . '</th>';
	}

	if (in_array("route_end", $data_items)) {
		$result .= '<th>' . $la['ROUTE_END'] . '</th>';
	}

	if (in_array("route_length", $data_items)) {
		$result .= '<th>' . $la['ROUTE_LENGTH'] . '</th>';
	}

	if (in_array("move_duration", $data_items)) {
		$result .= '<th>' . $la['MOVE_DURATION'] . '</th>';
	}

	if (in_array("stop_duration", $data_items)) {
		$result .= '<th>' . $la['STOP_DURATION'] . '</th>';
	}

	if (in_array("stop_count", $data_items)) {
		$result .= '<th>' . $la['STOP_COUNT'] . '</th>';
	}

	if (in_array("top_speed", $data_items)) {
		$result .= '<th>' . $la['TOP_SPEED'] . '</th>';
	}

	if (in_array("avg_speed", $data_items)) {
		$result .= '<th>' . $la['AVG_SPEED'] . '</th>';
	}

	if (in_array("overspeed_count", $data_items)) {
		$result .= '<th>' . $la['OVERSPEED_COUNT'] . '</th>';
	}

	if (in_array("fuel_consumption", $data_items)) {
		$result .= '<th>' . $la['FUEL_CONSUMPTION'] . '</th>';
	}

	if (in_array("avg_fuel_consumption", $data_items)) {
		if ($_SESSION["unit_capacity"] == 'l') {
			$result .= '<th>' . $la['AVG_FUEL_CONSUMPTION_100_KM'] . '</th>';
		} else {
			$result .= '<th>' . $la['AVG_FUEL_CONSUMPTION_MPG'] . '</th>';
		}
	}

	if (in_array("fuel_cost", $data_items)) {
		$result .= '<th>' . $la['FUEL_COST'] . '</th>';
	}

	if (in_array("engine_work", $data_items)) {
		$result .= '<th>' . $la['ENGINE_WORK'] . '</th>';
	}

	if (in_array("engine_idle", $data_items)) {
		$result .= '<th>' . $la['ENGINE_IDLE'] . '</th>';
	}

	if (in_array("odometer", $data_items)) {
		$result .= '<th>' . $la['ODOMETER'] . '</th>';
	}

	if (in_array("engine_hours", $data_items)) {
		$result .= '<th>' . $la['ENGINE_HOURS'] . '</th>';
	}

	if (in_array("driver", $data_items)) {
		$result .= '<th>' . $la['DRIVER'] . '</th>';
	}

	if (in_array("trailer", $data_items)) {
		$result .= '<th>' . $la['TRAILER'] . '</th>';
	}

	$result .= '</tr>';

	$total_route_length = 0;
	$total_drives_duration = 0;
	$total_stops_duration = 0;
	$total_stop_count = 0;
	$total_top_speed = 0;
	$total_avg_speed = 0;
	$total_overspeed_count = 0;
	$total_fuel_consumption = 0;
	$total_avg_fuel_consumption = 0;
	$total_avg_fuel_consumption_cnt = 0;
	$total_fuel_cost = 0;
	$total_engine_work = 0;
	$total_engine_idle = 0;
	$total_odometer = 0;
	$total_engine_hours = 0;

	$is_data = false;

	for ($i = 0; $i < count($imeis); ++$i) {
		$imei = $imeis[$i];

		$data = getRoute($user_id, $imei, $dtf, $dtt, $stop_duration, true);

		if ($data === null || !isset($data['route']) || count($data['route']) == 0) {
			$result .= '<tr align="center">';
			$result .= '<td>' . getObjectName($imei) . '</td>';
			$result .= '<td colspan="18">' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td>';
			$result .= '</tr>';
		} else {
			$is_data = true;

			if ($speed_limit > 0) {
				$overspeeds = getRouteOverspeeds($data['route'], $speed_limit);
				$overspeed_count = count($overspeeds);
			} else {
				$overspeed_count = 0;
			}

			$odometer = getObjectOdometer($imei);
			$odometer = floor(convDistanceUnits($odometer, 'km', $_SESSION["unit_distance"]));

			$result .= '<tr align="center">';

			$result .= '<td>' . getObjectName($imei) . '</td>';

			if (in_array("route_start", $data_items)) {
				$result .= '<td>' . $data['route'][0][0] . '</td>';
			}

			if (in_array("route_end", $data_items)) {
				$result .= '<td>' . $data['route'][count($data['route']) - 1][0] . '</td>';
			}

			if (in_array("route_length", $data_items)) {
				$result .= '<td>' . $data['route_length'] . ' ' . $la["UNIT_DISTANCE"] . '</td>';

				$total_route_length += $data['route_length'];
			}

			if (in_array("move_duration", $data_items)) {
				$result .= '<td>' . $data['drives_duration'] . '</td>';

				$total_drives_duration += $data['drives_duration_time'];
			}

			if (in_array("stop_duration", $data_items)) {
				$result .= '<td>' . $data['stops_duration'] . '</td>';

				$total_stops_duration += $data['stops_duration_time'];
			}

			if (in_array("stop_count", $data_items)) {
				$result .= '<td>' . count($data['stops']) . '</td>';

				$total_stop_count += count($data['stops']);
			}

			if (in_array("top_speed", $data_items)) {
				$result .= '<td>' . $data['top_speed'] . ' ' . $la["UNIT_SPEED"] . '</td>';
			}

			if (in_array("avg_speed", $data_items)) {
				$result .= '<td>' . $data['avg_speed'] . ' ' . $la["UNIT_SPEED"] . '</td>';
			}

			if (in_array("overspeed_count", $data_items)) {
				$result .= '<td>' . $overspeed_count . '</td>';

				$total_overspeed_count += $overspeed_count;
			}

			if (in_array("fuel_consumption", $data_items)) {
				$result .= '<td>' . $data['fuel_consumption'] . ' ' . $la["UNIT_CAPACITY"] . '</td>';

				$total_fuel_consumption += $data['fuel_consumption'];
			}

			if (in_array("avg_fuel_consumption", $data_items)) {
				if ($_SESSION["unit_capacity"] == 'l') {
					$result .= '<td>' . $data['fuel_consumption_per_km'] . ' ' . $la["UNIT_CAPACITY"] . '</td>';
					$total_avg_fuel_consumption += $data['fuel_consumption_per_km'];

					if ($data['fuel_consumption_per_km'] > 0) {
						$total_avg_fuel_consumption_cnt += 1;
					}
				} else {
					$result .= '<td>' . $data['fuel_consumption_mpg'] . ' ' . $la["UNIT_MI"] . '</td>';
					$total_avg_fuel_consumption += $data['fuel_consumption_mpg'];

					if ($data['fuel_consumption_mpg'] > 0) {
						$total_avg_fuel_consumption_cnt += 1;
					}
				}
			}

			if (in_array("fuel_cost", $data_items)) {
				$result .= '<td>' . $data['fuel_cost'] . ' ' . $_SESSION["currency"] . '</td>';

				$total_fuel_cost += $data['fuel_cost'];
			}

			if (in_array("engine_work", $data_items)) {
				$result .= '<td>' . $data['engine_work'] . '</td>';

				$total_engine_work += $data['engine_work_time'];
			}

			if (in_array("engine_idle", $data_items)) {
				$result .= '<td>' . $data['engine_idle'] . '</td>';

				$total_engine_idle += $data['engine_idle_time'];
			}

			if (in_array("odometer", $data_items)) {
				$result .= '<td>' . $odometer . ' ' . $la["UNIT_DISTANCE"] . '</td>';

				$total_odometer += $odometer;
			}

			if (in_array("engine_hours", $data_items)) {
				$engine_hours = getObjectEngineHours($imei, true);
				$engine_hours = preg_replace('/[^0-9]/', '', $engine_hours);

				$result .= '<td>' . $engine_hours . '</td>';

				$total_engine_hours += $engine_hours;
			}

			if (in_array("driver", $data_items)) {
				$params = $data['route'][count($data['route']) - 1][6];
				$driver = getObjectDriver($user_id, $imei, $params);
				if ($driver == false) {
					$driver['driver_name'] = $la['NA'];
				}

				$result .= '<td>' . $driver['driver_name'] . '</td>';
			}

			if (in_array("trailer", $data_items)) {
				$params = $data['route'][count($data['route']) - 1][6];
				$trailer = getObjectTrailer($user_id, $imei, $params);
				if ($trailer == false) {
					$trailer['trailer_name'] = $la['NA'];
				}

				$result .= '<td>' . $trailer['trailer_name'] . '</td>';
			}

			$result .= '</tr>';
		}
	}

	if (in_array("total", $data_items) && ($is_data == true)) {
		$result .= '<tr align="center">';

		$result .= '<td></td>';

		if (in_array("route_start", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("route_end", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("route_length", $data_items)) {
			$result .= '<td>' . $total_route_length . ' ' . $la["UNIT_DISTANCE"] . '</td>';
		}

		if (in_array("move_duration", $data_items)) {
			$result .= '<td>' . getTimeDetails($total_drives_duration, true) . '</td>';
		}

		if (in_array("stop_duration", $data_items)) {
			$result .= '<td>' . getTimeDetails($total_stops_duration, true) . '</td>';
		}

		if (in_array("stop_count", $data_items)) {
			$result .= '<td>' . $total_stop_count . '</td>';
		}

		if (in_array("top_speed", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("avg_speed", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("overspeed_count", $data_items)) {
			$result .= '<td>' . $total_overspeed_count . '</td>';
		}

		if (in_array("fuel_consumption", $data_items)) {
			$result .= '<td>' . $total_fuel_consumption . ' ' . $la["UNIT_CAPACITY"] . '</td>';
		}

		if (in_array("avg_fuel_consumption", $data_items)) {
			if (($total_avg_fuel_consumption > 0) && ($total_avg_fuel_consumption_cnt > 0)) {
				$total_avg_fuel_consumption = $total_avg_fuel_consumption / $total_avg_fuel_consumption_cnt;
				$total_avg_fuel_consumption = round($total_avg_fuel_consumption * 100) / 100;
			}

			if ($_SESSION["unit_capacity"] == 'l') {
				$result .= '<td>' . $total_avg_fuel_consumption . ' ' . $la["UNIT_CAPACITY"] . '</td>';
			} else {
				$result .= '<td>' . $total_avg_fuel_consumption . ' ' . $la["UNIT_MI"] . '</td>';
			}
		}

		if (in_array("fuel_cost", $data_items)) {
			$result .= '<td>' . $total_fuel_cost . ' ' . $_SESSION["currency"] . '</td>';
		}

		if (in_array("engine_work", $data_items)) {
			$result .= '<td>' . getTimeDetails($total_engine_work, true) . '</td>';
		}

		if (in_array("engine_idle", $data_items)) {
			$result .= '<td>' . getTimeDetails($total_engine_idle, true) . '</td>';
		}

		if (in_array("odometer", $data_items)) {
			$result .= '<td>' . $total_odometer . ' ' . $la["UNIT_DISTANCE"] . '</td>';
		}

		if (in_array("engine_hours", $data_items)) {
			$result .= '<td>' . $total_engine_hours . ' ' . $la["UNIT_H"] . '</td>';
		}

		if (in_array("driver", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("trailer", $data_items)) {
			$result .= '<td></td>';
		}

		$result .= '</tr>';
	}

	$result .= '</table>';

	return $result;
}

function reportsGenerateObjectInfo($imeis, $data_items)
{
	global $ms, $_SESSION, $la, $user_id;

	$result = '<table class="report" width="100%" ><tr align="center">';

	$result .= '<th>' . $la['OBJECT'] . '</th>';

	if (in_array("imei", $data_items)) {
		$result .= '<th>' . $la['IMEI'] . '</th>';
	}

	if (in_array("gps_device", $data_items)) {
		$result .= '<th>' . $la['GPS_DEVICE'] . '</th>';
	}

	if (in_array("acc", $data_items)) {
		$result .= '<th>' . $la['ACC'] . '</th>';
	}

	if (in_array("plate_number", $data_items)) {
		$result .= '<th>' . $la['PLATE_NUMBER'] . '</th>';
	}

	if (in_array("installation_date", $data_items)) {
		$result .= '<th>' . $la['INSTALLATION_DATE'] . '</th>';
	}

	if (in_array("rent", $data_items)) {
		$result .= '<th>' . $la['RENT'] . '</th>';
	}

	$result .= '</tr>';

	if ($user_id == '323' || $user_id == '367' || $user_id == '185') {
		$user_ids_to_query = array_diff(['323', '367', '185'], [$user_id]);

		foreach ($user_ids_to_query as $query_user_id) {
			$q = "SELECT DISTINCT imei FROM `gs_user_objects` WHERE `user_id`='" . $query_user_id . "'";
			$r = mysqli_query($ms, $q);

			while ($row = mysqli_fetch_assoc($r)) {
				$imeis[] = $row['imei'];
			}
		}
	}

	$processed_imeis = array();

	for ($i = 0; $i < count($imeis); ++$i) {
		$imei = $imeis[$i];
		if (!in_array($imei, $processed_imeis)) {
			$q = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "'";
			$r = mysqli_query($ms, $q);
			$row = mysqli_fetch_array($r);

			$odometer = getObjectOdometer($imei);
			$odometer = floor(convDistanceUnits($odometer, 'km', $_SESSION["unit_distance"]));

			$result .= '<tr align="center">';

			$result .= '<td>' . $row['name'] . '</td>';

			if (in_array("imei", $data_items)) {
				$result .= '<td>' . $row['imei'] . '</td>';
			}

			if (in_array("gps_device", $data_items)) {
				$result .= '<td>' . $row['device'] . '</td>';
			}

			if (in_array("acc", $data_items)) {
				$result .= '<td>' . $row['acc'] . '</td>';
			}

			if (in_array("plate_number", $data_items)) {
				$result .= '<td>' . $row['plate_number'] . '</td>';
			}

			if (in_array("installation_date", $data_items)) {

				$installation_data = getObjectInstalation($imei);
				$installation_date = $installation_data['fecha'];

				$result .= '<td>' . $installation_date . '</td>';
			}

			if (in_array("rent", $data_items)) {
				$result .= '<td>$' . getObjectRent($imei) . '</td>';
			}

			$result .= '</tr>';
		}
		$processed_imeis[] = $imei;
	}
	$result .= '</table>';

	return $result;
}

function reportsGenerateObjectInfoWs($imeis, $data_items)
{
	global $ms, $_SESSION, $la, $user_id;

	$result = '<table class="report" width="100%" ><tr align="center">';

	$result .= '<th>' . $la['OBJECT'] . '</th>';

	if (in_array("imei", $data_items)) {
		$result .= '<th>' . $la['IMEI'] . '</th>';
	}

	if (in_array("plate_number", $data_items)) {
		$result .= '<th>' . $la['PLATE_NUMBER'] . '</th>';
	}

	if (in_array("name_ws", $data_items)) {
		$result .= '<th>' . $la['NAME_WS'] . '</th>';
	}

	if (in_array("pass_ws", $data_items)) {
		$result .= '<th>' . $la['PASS_WS'] . '</th>';
	}

	if (in_array("response", $data_items)) {
		$result .= '<th>' . $la['RESPONSE'] . '</th>';
	}

	$result .= '</tr>';

	$processed_imeis = array();

	for ($i = 0; $i < count($imeis); ++$i) {
		$imei = $imeis[$i];
	
		if (!in_array($imei, $processed_imeis)) {
			// Obtener datos del objeto
			$q = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "'";
			$r = mysqli_query($ms, $q);
			$row = mysqli_fetch_array($r);
	
			$q_custom = "SELECT name, value FROM `gs_object_custom_fields` WHERE `imei`='" . $imei . "' AND `data_list`='true' AND `group_id`=''";
			$r_custom = mysqli_query($ms, $q_custom);
	
			$custom_data = [];
			while ($row_custom = mysqli_fetch_array($r_custom)) {
				$custom_data[] = $row_custom;
			}
	
			if (count($custom_data) > 1) {
				$result .= '<tr align="center" style="background-color: #f0f0f0; font-weight: bold;">
								<td colspan="6"></td>
							</tr>';
			}
	
			foreach ($custom_data as $row_custom) {
				$result .= '<tr align="center">';
	
				$result .= '<td>' . $row['name'] . '</td>';
	
				if (in_array("imei", $data_items)) {
					$result .= '<td>' . $row['imei'] . '</td>';
				}
	
				if (in_array("plate_number", $data_items)) {
					$result .= '<td>' . $row['plate_number'] . '</td>';
				}
	
				if (in_array("name_ws", $data_items)) {
					$result .= '<td>' . $row_custom['name'] . '</td>';
				}
	
				if (in_array("pass_ws", $data_items)) {
					$result .= '<td>' . $row_custom['value'] . '</td>';
				}
	
				if (in_array("response", $data_items)) {
					if ($row_custom['name'] == 'GUDA'){
						$ws='Guda';
					    $result .= '<td>' . getObjectResponseWs($imei, $ws) . '</td>';
					}
					if ($row_custom['name'] == 'RCONFIABLE'){
						$ws='Rconfiable';
					    $result .= '<td>' . getObjectResponseWs($imei, $ws) . '</td>';
					}
				}
	
				$result .= '</tr>';
			}
	
			$processed_imeis[] = $imei;
		}
	}
	
	$result .= '</table>';

	return $result;
}
function reportsGenerateObjectInfoSensors($imeis, $dtf, $dtt, $data_items)
{
	global $ms, $_SESSION, $la;
	$colspan = count($data_items);

	$excluded_ids = "(1, 171, 172, 290, 311, 316, 320, 345, 689, 621, 720, 723, 766, 767, 768, 769, 770, 772, 1024, 1046, 1049, 1050, 1051, 1052, 1053, 1054, 1059, 1060, 1066, 1067, 1140, 1441, 1150, 1167, 1171, 1219, 1599)";

	$q = "SELECT gs_objects.*, gs_user_objects.*, gs_users.username, gs_object_sensors.*
		FROM gs_objects
		INNER JOIN gs_user_objects ON gs_objects.imei = gs_user_objects.imei
		INNER JOIN gs_users ON gs_users.id = gs_user_objects.user_id
		INNER JOIN gs_object_sensors ON gs_objects.imei = gs_object_sensors.imei
		WHERE gs_objects.manager_id = '0' 
		AND (gs_object_sensors.type = 'fuel' OR gs_object_sensors.type = 'temp')
		AND gs_user_objects.user_id NOT IN $excluded_ids";

	$r = mysqli_query($ms, $q);

	$users = array();

	while ($row = mysqli_fetch_array($r)) {
		$user_id = $row['user_id'];
		$sensor_type = $row['type'];
		$imei = $row['imei'];

		if (!isset($users[$user_id])) {
			$users[$user_id] = array(
				'username' => $row['username'],
				'client_id' => $row['client_id'],
				'imei' => array(
					'fuel' => array(),
					'temp' => array()
				)
			);
		}

		if (!in_array($imei, $users[$user_id]['imei'][$sensor_type])) {
			$users[$user_id]['imei'][$sensor_type][] = $imei;
		}
	}





	$result = '<table class="report" width="100%"><tr align="center">';


	if (in_array("username", $data_items)) {
		$result .= '<th>' . $la['USER'] . '</th>';
	}
	if (in_array("name", $data_items)) {
		$result .= '<th>' . $la['OBJECT'] . '</th>';
	}
	if (in_array("imei", $data_items)) {
		$result .= '<th>' . $la['IMEI'] . '</th>';
	}
	if (in_array("sim_card_number", $data_items)) {
		$result .= '<th>' . $la['SIM_CARD_NUMBER'] . '</th>';
	}
	if (in_array("last_connection", $data_items)) {
		$result .= '<th>' . $la['LAST_CONNECTION'] . '</th>';
	}
	if (in_array("sensor_status", $data_items)) {
		$result .= '<th>' . $la['SENSOR_STATUS'] . '</th>';
	}
	if (in_array("gps_device", $data_items)) {
		$result .= '<th>' . $la['GPS_DEVICE'] . '</th>';
	}
	if (in_array("installation_date", $data_items)) {
		$result .= '<th>' . $la['INSTALLATION_DATE'] . '</th>';
	}


	$result .= '</tr>';

	$usernames = array_column($users, 'username');
	array_multisort($usernames, SORT_ASC, $users);
	$processed_imeis = array();
	$total_imeis = 0;
	$total_adc = 0;
	$total_temp = 0;
	$total_temp_ok = 0;
	$total_adc_ok = 0;
	$total_temp_falla = 0;
	$equipo_diesel_falla = 0;
	$diesel_variaciones = 0;
	$equipo_temp_falla = 0;
	$temp_falla = 0;

	foreach ($users as $user_id => $user_data) {
		$username = $user_data['username'];
		$imeis = $user_data['imei'];

		foreach ($imeis as $sensor_type => $imei_list) {
			foreach ($imei_list as $imei) {

				if (!in_array($imei, $processed_imeis)) {
					if ($sensor_type === 'fuel') {
						$fuel = getSensorsValue($imei, $dtf, $dtt);

						if (strpos($fuel, '"Diesel"') !== false) {
							$equipo_diesel_falla++;
						}
						if (strpos($fuel, 'Tanque') !== false) {
							$diesel_variaciones++;
						}
						if ($fuel === 'OK') {
							$total_adc_ok++;
						}
						$valor = $fuel;
						$total_adc++;
					} elseif ($sensor_type === 'temp') {
						$temp = getTempValue($imei, $dtf, $dtt);

						if (strpos($temp, '"temp"') !== false) {
							$equipo_temp_falla++;
						}
						if (strpos($temp, 'Temperatura') !== false) {
							$temp_falla++;
						}
						if ($temp === 'OK') {
							$total_temp_ok++;
						}
						$valor = $temp;
						$total_temp++;
					}

					$q_object_info = "SELECT * FROM `gs_objects` WHERE `imei`='$imei'";
					$r_object_info = mysqli_query($ms, $q_object_info);
					$row = mysqli_fetch_array($r_object_info);

					if ($row) {
						$result .= '<tr align="center">';

						if (in_array("username", $data_items)) {
							$username = getUserName_Report($imei);
							$result .= '<td>' . $username . '</td>';
						}
						if (in_array("name", $data_items)) {
							$result .= '<td>' . $row['name'] . '</td>';
						}
						if (in_array("imei", $data_items)) {
							$result .= '<td>' . $row['imei'] . '</td>';
							$total_imeis++;
						}
						if (in_array("sim_card_number", $data_items)) {
							$result .= '<td>' . $row['sim_number'] . '</td>';
						}
						if (in_array("last_connection", $data_items)) {
							$last_connection = convUserTimezone($row['dt_server']);
							$result .= '<td>' . $last_connection . '</td>';
						}
						if (in_array("sensor_status", $data_items)) {
							$result .= '<td>' . $valor . '</td>';
						}
						if (in_array("gps_device", $data_items)) {
							$result .= '<td>' . $row['device'] . '</td>';
						}
						if (in_array("installation_date", $data_items)) {

							$installation_data = getObjectInstalation($imei);
							$installation_date = $installation_data['fecha'];

							$result .= '<td>' . $installation_date . '</td>';
						}

						$result .= '</tr>';
					}
					$processed_imeis[] = $imei;
				}
			}
		}
		$result .= '<tr><td colspan="1"></td></tr>';
	}

	$result .= '<tr><td colspan="' . $colspan . '"</td></tr>';
	if ($total_imeis > 0) {
		$result .= '<tr><td colspan="1">Total De Unidades Con Sensores: ' . $total_imeis . '</td></tr>';
	}
	$result .= '<tr><td colspan="1"></td></tr>';


	if ($total_adc > 0) {
		$result .= '<tr><td colspan="1">Unidades Con Diesel: ' . $total_adc . '</td></tr>';
	}
	if ($total_adc_ok > 0) {
		$result .= '<tr><td colspan="1">Unidades Diesel OK: ' . $total_adc_ok . '</td></tr>';
	}
	if ($equipo_diesel_falla > 0) {
		$result .= '<tr><td colspan="1">Equipos Diesel Con Falla: ' . $equipo_diesel_falla . '</td></tr>';
	}
	if ($diesel_variaciones > 0) {
		$result .= '<tr><td colspan="1">Sensores Diesel Con Variaciones: ' . $diesel_variaciones . '</td></tr>';
	}
	$result .= '<tr><td colspan="1"></td></tr>';


	if ($total_temp > 0) {
		$result .= '<tr><td colspan="1">Unidades Con Temp: ' . $total_temp . '</td></tr>';
	}
	if ($total_temp_ok > 0) {
		$result .= '<tr><td colspan="1">Unidades Temp OK: ' . $total_temp_ok . '</td></tr>';
	}
	if ($equipo_temp_falla > 0) {
		$result .= '<tr><td colspan="1">Equipos Temp Con Falla: ' . $equipo_temp_falla . '</td></tr>';
	}
	if ($temp_falla > 0) {
		$result .= '<tr><td colspan="1">Sensores Temp Con Falla: ' . $temp_falla . '</td></tr>';
	}
	$result .= '<tr><td colspan="1"></td></tr>';
	$result .= '</table>';

	return $result;
}
function
reportsGenerateObjectInfoSensorsClient($imeis, $dtf, $dtt, $sensors, $stop_duration, $show_coordinates, $show_addresses, $zones_addresses, $data_items)
{
	global $ms, $_SESSION, $gsValues, $la, $user_id;
	$colspan = count($data_items);
	$result = '';

	$result = '<table class="report" width="100%"><tr align="center">';

	if (in_array("status", $data_items)) {
		$result .= '<th rowspan="2">' . $la['STATUS_'] . '</th>';
	}
	if (in_array("name", $data_items)) {
		$result .= '<th rowspan="2">' . $la['OBJECT'] . '</th>';
	}
	if (in_array("imei", $data_items)) {
		$result .= '<th rowspan="2">' . $la['IMEI'] . '</th>';
	}
	if (in_array("gps_device", $data_items)) {
		$result .= '<th rowspan="2">' . $la['GPS_DEVICE'] . '</th>';
	}
	if (in_array("last_connection", $data_items)) {
		$result .= '<th rowspan="2">' . $la['LAST_CONNECTION'] . '</th>';
	}
	if (in_array("position", $data_items)) {
		$result .= '<th colspan="3">' . $la['POSITION'] . '</th>';
	}
	for ($k = 0; $k < count($sensors); ++$k) {
		$sensor = $sensors[$k];
		$result .= '<th rowspan="2">' . $sensor['name'] . '</th>';
	}
	if (in_array("event", $data_items)) {
		$result .= '<th rowspan="2">' . $la['EVENT'] . '</th>';
	}


	$result .= '</tr>';
	$result .= '<tr align="center">
	<th>' . '' . '</th>
	<th>' . '' . '</th>
	<th>' . '' . '</th>
	</tr>';



	for ($i = 0; $i < count($imeis); ++$i) {
		$imei = $imeis[$i];

		$q = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "'";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_array($r);
		$lat = $row['lat'];
		$lng = $row['lng'];
		$params = $row['params'];
		$paramsArray = json_decode($params, true);


		if (in_array("status", $data_items)) {
			$connection_status = TimeReport($row['dt_server']);
			if ($connection_status == 'Sin reportar') {
				$result .= '<td style="text-align: center;"><span style="color: red;">' . $connection_status . '</span></td>';
			} else {
				$result .= '<td style="text-align: center;">' . $connection_status . '</td>';
			}
		}

		if (in_array("name", $data_items)) {
			$name = getObjectName($imei);
			$result .= '<td style="text-align: center;">' . $name . '</td>';
		}
		if (in_array("imei", $data_items)) {
			$result .= '<td style="text-align: center;">' . $imei . '</td>';
		}
		if (in_array("gps_device", $data_items)) {
			$device = getObjectDevice($imei);
			$result .= '<td style="text-align: center;">' . $device . '</td>';
		}
		if (in_array("last_connection", $data_items)) {
			$last_connection = getLastConection($imei);
			$result .= '<td style="text-align: center;">' . convUserTimezone($last_connection) . '</td>';
		}
		if (in_array("position", $data_items)) {
			$result .= '<td colspan="3" style="text-align: center;">' . reportsGetPossition($lat, $lng, $show_coordinates, $show_addresses, $zones_addresses) . '</td>';
		}
		for ($k = 0; $k < count($sensors); ++$k) {
			$paramsArray;
			if ($sensors[$k]['param'] == 'adc1') {
				$sensor_data1 = getSensorValue($paramsArray, $sensors[$k]);
			}
			if ($sensors[$k]['param'] == 'adc2') {
				$sensor_data2 = getSensorValue($paramsArray, $sensors[$k]);
			}
			$sensor_data = getSensorValue($paramsArray, $sensors[$k]);
			if ($sensors[$k]['type'] == 'fuelsumup') {
				$sensor_data1_value = isset($sensor_data1['value']) && is_numeric($sensor_data1['value']) ? $sensor_data1['value'] : 0;
				$sensor_data2_value = isset($sensor_data2['value']) && is_numeric($sensor_data2['value']) ? $sensor_data2['value'] : 0;
				$result .= '<td style="text-align: center;">' . ($sensor_data1_value + $sensor_data2_value) . ' Lts</td>';
			} else {
				$result .= '<td style="text-align: center;">' . $sensor_data['value_full'] . '</td>';
			}
		}
		if (in_array("event", $data_items)) {
			$fuel = getSensorsValue($imei, $dtf, $dtt);
			$valor = $fuel;
			if (strpos($fuel, '"falla"') !== false) {
				$result .= '<td style="text-align: center;"><span style="color: red;">' . $valor . '</span></td>';
			} else if (strpos($fuel, 'Tanque') !== false) {
				$result .= '<td style="text-align: center;"><span style="color: orange;">' . $valor . '</span></td>';
			} else if ($fuel === 'OK') {
				$result .= '<td style="text-align: center;"><span style="color: green;">' . $valor . '</span></td>';
			} else {
				$result .= '<td style="text-align: center;">' . $valor . '</td>';
			}
		}

		$result .= '</tr>';
	}


	$result .= '<tr><td colspan="' . $colspan . '"</td></tr>';
	$result .= '</table><br/>';

	return $result;
}


function reportsGenerateObjectInfoAdmin($imeis, $show_coordinates, $show_addresses, $zones_addresses, $data_items)
{
    global $ms, $_SESSION, $la;

    $excluded_ids = "(1, 171, 172, 290, 311, 316, 320, 345, 689, 621, 720, 723, 766, 767, 768, 769, 770, 772, 1024, 1046, 1049, 1050, 1051, 1052, 1053, 1054, 1059, 1060, 1066, 1067, 1140, 1441, 1150, 1167, 1171, 1219, 1599, 1669)";

    $q = "SELECT gs_objects.*, gs_user_objects.*, gs_users.id, gs_users.username
          FROM gs_objects
          INNER JOIN gs_user_objects ON gs_objects.imei = gs_user_objects.imei
          INNER JOIN gs_users ON gs_user_objects.user_id = gs_users.id
          WHERE gs_objects.manager_id = '0'
          AND gs_objects.sim_number != '0'
          AND gs_objects.sim_number != ''
          AND gs_user_objects.user_id NOT IN $excluded_ids
          ORDER BY gs_objects.dt_server DESC";

    $r = mysqli_query($ms, $q);
    if (!$r) {
        echo mysqli_error($ms);
        return '';
    }

    $users = [];
    $objectMap = [];

    while ($row = mysqli_fetch_array($r)) {
        $user_id = $row['user_id'];
        if (!isset($users[$user_id])) {
            $users[$user_id] = [
                'username' => $row['username'],
                'client_id' => $row['client_id'],
                'imei' => []
            ];
        }
        $users[$user_id]['imei'][] = $row['imei'];
        $objectMap[$row['imei']] = $row;
    }

    $result1 = '<table class="report" width="30%"><tr align="center">';
    $result1 .= '<th>' . $la['USER'] . '</th><th>' . $la['STATUS'] . '</th></tr>';

    foreach ($users as $user_data) {
        $username = $user_data['username'];
        if (!getUserActive($username)) {
            $result1 .= '<tr align="center"><td>' . $username . '</td><td>' . $la['INACTIVE'] . '</td></tr>';
        }
    }

    $result1 .= '<tr><td colspan="2"></td></tr>';

    $headers = ['USER', 'OBJECT'];
    $columns = [
        'imei' => 'IMEI',
        'sim_card_number' => 'SIM_CARD_NUMBER',
        'position' => 'POSITION',
        'event' => 'EVENT',
        'last_comment' => 'LAST_COMMENT',
        'last_connection' => 'LAST_CONNECTION',
        'gps_device' => 'GPS_DEVICE',
        'installation_date' => 'INSTALLATION_DATE',
        'status' => 'STATUS',
        'fall_month' => 'FALL_MONTH',
        'contact_type' => 'CONTACT_TYPE'
    ];

    $result = '<table class="report" width="100%"><tr align="center">';
    foreach ($headers as $header) {
        $result .= '<th>' . $la[$header] . '</th>';
    }
    foreach ($columns as $key => $label) {
        if (in_array($key, $data_items)) {
            $result .= '<th>' . $la[$label] . '</th>';
        }
    }
    $result .= '</tr>';

	
	$processed_imeis = [];	
	$dt_now = gmdate("Y-m-d H:i:s");
	
	$units_invalid = [];
	$units_24h = [];
	
	// Clasificar unidades
	foreach ($users as $user_data) {
		foreach (array_unique($user_data['imei']) as $imei) {
			if (!isset($objectMap[$imei])) continue;
	
			$object_info = $objectMap[$imei];
			$dt_server = $object_info['dt_server'] ?? '';
	
			if ($dt_server == '' || $dt_server == '0000-00-00 00:00:00') {
				$units_invalid[] = [
					'user_data' => $user_data,
					'imei' => $imei,
					'dt_server' => '0000-00-00 00:00:00'
				];
				continue;
			}
	
			$diff_secs = strtotime($dt_now) - strtotime($dt_server);
			if ($diff_secs >= 86400) {
				$units_24h[] = [
					'user_data' => $user_data,
					'imei' => $imei,
					'dt_server' => $dt_server,
					'diff' => $diff_secs
				];
			}
		}
	}
	
	usort($units_24h, function ($a, $b) {
		return strtotime($b['dt_server']) - strtotime($a['dt_server']);
	});
	
	$final_units = array_merge($units_invalid, $units_24h);
	
	foreach ($final_units as $unit) {
		$user_data = $unit['user_data'];
		$imei = $unit['imei'];
		$object_info = $objectMap[$imei];
	
		if (!isset($object_info)) continue;
		if (in_array($imei, $processed_imeis)) continue;
	
		$username = $user_data['username'];
	
		$r = mysqli_query($ms, "SELECT * FROM gs_object_data WHERE imei = '$imei'");
		if (!$r || mysqli_num_rows($r) == 0) continue;
		$object_data = mysqli_fetch_array($r);

		$diff = isset($unit['diff']) ? $unit['diff'] : null;
		$row_style = 'style="background-color: #b52100"';
		
		if ($diff !== null) {
			$days = round($diff / 86400, 1);
		
			if ($days >= 1 && $days < 2) {
				$row_style = 'style="background-color: #fce840"';
			} elseif ($days >= 2 && $days <= 5) {
				$row_style = 'style="background-color: #fcb700"';
			} elseif ($days > 5 && $days <= 10) {
				$row_style = 'style="background-color: #fc6f00"';
			} elseif ($days > 10) {
				$row_style = 'style="background-color: #df4004"';
			}
		}
	
		$result .= '<tr align="center" ' . $row_style . '>';
		$result .= '<td>' . $username . '</td>';
		$result .= '<td>' . getObjectName($imei) . '</td>';
	
		foreach ($columns as $key => $label) {
			if (!in_array($key, $data_items)) continue;
		
			switch ($key) {
				case 'imei':
					$result .= '<td>' . $imei . '</td>'; break;
				case 'sim_card_number':
					$result .= '<td>' . $object_info['sim_number'] . '</td>'; break;
				case 'position':
					$pos = reportsGetPossition($object_info['lat'], $object_info['lng'], $show_coordinates, $show_addresses, $zones_addresses);
					$result .= '<td>' . $pos . '</td>'; break;
				case 'event':
					$result .= '<td>' . getEvent($imei) . '</td>'; break;
				case 'last_comment':
					$result .= '<td>' . getComment($imei) . '</td>'; break;
				case 'last_connection':
					$result .= '<td>' . convUserTimezone($object_info['dt_server']) . '</td>'; break;
				case 'gps_device':
					$result .= '<td>' . $object_info['device'] . '</td>'; break;
				case 'installation_date':
					$install = getObjectInstalation($imei);
					$result .= '<td>' . ($install['fecha'] ?? 'N/A') . '</td>'; break;
				case 'status':
					$result .= '<td>' . getStatus($imei) . '</td>'; break;
				case 'fall_month':
					$result .= '<td>' . ($object_data['fall_month'] ?? '-') . '</td>'; break;
				case 'contact_type':
					$result .= '<td>' . ($object_data['contact_type'] ?? '-') . '</td>'; break;
			}
		}		
	
		$result .= '</tr>';
		$processed_imeis[] = $imei;
	}
	

    $result1 .= '</table>';
    $result .= '</table>';

    return $result1 . $result;
	
}

function reportsGenerateObjectInfoVentas($imeis, $data_items)
{
	global $ms, $_SESSION, $la;
	$colspan = count($data_items);

	$excluded_ids = "(1, 171, 172, 290, 311, 316, 320, 345, 689, 621, 720, 723, 766, 767, 768, 769, 770, 772, 1024, 1046, 1049, 1050, 1051, 1052, 1053, 1054, 1059, 1060, 1066, 1067, 1140, 1441, 1150, 1167, 1171, 1219, 1599, 1669)";

	$q = "SELECT gs_objects.*, gs_user_objects.*, gs_users.username
                    FROM gs_objects
                    INNER JOIN gs_user_objects ON gs_objects.imei = gs_user_objects.imei, gs_users
                    WHERE gs_objects.manager_id = '0' AND gs_user_objects.user_id AND gs_users.id = gs_user_objects.user_id AND gs_user_objects.user_id NOT IN " . $excluded_ids . "";

	$r = mysqli_query($ms, $q);

	$users = array();

	while ($row = mysqli_fetch_array($r)) {
		$user_id = $row['user_id'];

		if (!isset($users[$user_id])) {
			$users[$user_id] = array(
				'username' => $row['username'],
				'client_id' => $row['client_id'],
				'imei' => array()
			);
		}

		$users[$user_id]['imei'][] = $row['imei'];
	}




	$result = '<table class="report" width="100%"><tr align="center">';

	if (in_array("client_number", $data_items)) {
		$result .= '<th>' . $la['CLIENT_NUMBER'] . '</th>';
	}

	if (in_array("username", $data_items)) {
		$result .= '<th>' . $la['USER'] . '</th>';
	}

	if (in_array("name", $data_items)) {
		$result .= '<th>' . $la['OBJECT'] . '</th>';
	}

	if (in_array("plan", $data_items)) {
		$result .= '<th>' . $la['PLAN'] . '</th>';
	}

	if (in_array("imei", $data_items)) {
		$result .= '<th>' . $la['IMEI'] . '</th>';
	}

	if (in_array("sim_card_number", $data_items)) {
		$result .= '<th>' . $la['SIM_CARD_NUMBER'] . '</th>';
	}

	if (in_array("last_connection", $data_items)) {
		$result .= '<th>' . $la['LAST_CONNECTION'] . '</th>';
	}

	if (in_array("connection_status", $data_items)) {
		$result .= '<th>' . $la['CONNECTION_STATUS'] . '</th>';
	}

	if (in_array("gps_device", $data_items)) {
		$result .= '<th>' . $la['GPS_DEVICE'] . '</th>';
	}

	if (in_array("battery", $data_items)) {
		$result .= '<th>' . $la['BATTERY'] . '</th>';
	}

	if (in_array("alta_date", $data_items)) {
		$result .= '<th>' . $la['ALTA_DATE'] . '</th>';
	}

	if (in_array("maintenance", $data_items)) {
		$result .= '<th>' . $la['MAINTENANCE'] . '</th>';
	}

	if (in_array("maintenance_date", $data_items)) {
		$result .= '<th>' . $la['MAINTENANCE_DATE'] . '</th>';
	}

	if (in_array("installation_date", $data_items)) {
		$result .= '<th>' . $la['INSTALLATION_DATE'] . '</th>';
	}

	if (in_array("rent", $data_items)) {
		$result .= '<th>' . $la['RENT'] . '</th>';
	}

	if (in_array("seller", $data_items)) {
		$result .= '<th>' . $la['SELLER'] . '</th>';
	}


	$result .= '</tr>';

	$usernames = array_column($users, 'username');
	array_multisort($usernames, SORT_ASC, $users);
	$rent_prices = array();
	$processed_imeis = array();
	$imei_counter = 0;
	$total_imeis = 0;
	$imei_counter_demo = 0;
	$demos_courtesies_counter = 0;
	$imei_counter_new = 0;
	$imei_counter_replace = 0;
	$imei_counter_battery_replace = 0;
	$total_imei_counter_replace_battery = 0;
	$imei_counter_mtto = 0;
	$total_imei_counter_mtto = 0;
	$total_imei_counter_new = 0;
	$total_imei_counter_replace = 0;
	$current_date = new DateTime();
	$current_month = $current_date->format('m');
	$current_year = $current_date->format('Y');

	foreach ($users as $user_id => $user_data) {
		$username = $user_data['username'];
		$client_id = $user_data['client_id'];
		$imeis = $user_data['imei'];


		$q_imeis = "SELECT DISTINCT imei FROM gs_user_objects WHERE client_id='$client_id' AND client_id != 0";
		$r_imeis = mysqli_query($ms, $q_imeis);
		$rent_prices = [];

		while ($row_imeis = mysqli_fetch_assoc($r_imeis)) {
			$imei = $row_imeis['imei'];
			$rent_price = getObjectRent($imei);
			$rent_prices[$imei] = $rent_price;
		}

		asort($rent_prices);
		$sorted_imeis = array_keys($rent_prices);
		foreach ($sorted_imeis as $imei) {

			if (!in_array($imei, $processed_imeis)) {

				$q_object_info = "SELECT * FROM `gs_objects` WHERE `imei`='$imei'";
				$r_object_info = mysqli_query($ms, $q_object_info);
				$row = mysqli_fetch_array($r_object_info);

				if ($row) {
					$result .= '<tr align="center">';

					if (in_array("client_number", $data_items)) {
						if ($row['plan'] == 'demo') {
							$result .= '<td><span style="color: red;">' . $client_id . '</span></td>';
						} else {
							$result .= '<td>' . $client_id . '</td>';
						}
					}

					if (in_array("username", $data_items)) {
						$username = getUserName_Report($imei);
						if ($row['plan'] == 'demo') {
							$result .= '<td><span style="color: red;">' . $username . '</span></td>';
						} else {
							$result .= '<td>' . $username . '</td>';
						}
					}
					if (in_array("name", $data_items)) {
						if ($row['plan'] == 'demo') {
							$result .= '<td><span style="color: red;">' . $row['name'] . '</span></td>';
						} else {
							$result .= '<td>' . $row['name'] . '</td>';
						}
					}
					if (in_array("plan", $data_items)) {
						if ($row['plan'] == 'demo') {
							$result .= '<td><span style="color: red;">Demo,Cortesía</span></td>';
						} else {
							$result .= '<td>' . $row['plan'] . '</td>';
						}
					}
					if (in_array("imei", $data_items)) {
						if ($row['plan'] == 'demo') {
							$result .= '<td><span style="color: red;">' . $row['imei'] . '</span></td>';
							$demos_courtesies_counter++;
							$total_imeis++;
							$imei_counter_demo++;
						} else {
							$result .= '<td>' . $row['imei'] . '</td>';
							$imei_counter++;
							$total_imeis++;
						}
					}
					if (in_array("sim_card_number", $data_items)) {
						if ($row['plan'] == 'demo') {
							$result .= '<td><span style="color: red;">' . $row['sim_number'] . '</span></td>';
						} else {
							$result .= '<td>' . $row['sim_number'] . '</td>';
						}
					}
					if (in_array("last_connection", $data_items)) {
						$last_connection = convUserTimezone($row['dt_server']);
						if ($row['plan'] == 'demo') {
							$result .= '<td><span style="color: red;">' . $last_connection . '</span></td>';
						} else {
							$result .= '<td>' . $last_connection . '</td>';
						}
					}
					if (in_array("connection_status", $data_items)) {
						$connection_status = TimeReport($row['dt_server']);
						if ($row['plan'] == 'demo') {
							$result .= '<td><span style="color: red;">' . $connection_status . '</span></td>';
						} else {
							$result .= '<td>' . $connection_status . '</td>';
						}
					}
					if (in_array("gps_device", $data_items)) {
						$device = $row['device'];
						if ($device == 'concoxLL303') {
							$device = 'Solar-LL(4G)';
						} elseif ($device == 'topflytech') {
							$device = 'Solar-TF(4G)';
						}
						if ($row['plan'] == 'demo') {
							$result .= '<td><span style="color: red;">' . $device . '</span></td>';
						} else {
							$result .= '<td>' . $device . '</td>';
						}
					}
					if (in_array("battery", $data_items)) {
						$battery = getObjectBattery($imei);
						if ($row['plan'] == 'demo') {
							$result .= '<td><span style="color: red;">' . $battery . '</span></td>';
						} else {
							$result .= '<td>' . $battery . '</td>';
						}
					}
					if (in_array("alta_date", $data_items)) {
						$alta_date = getObjectAlta($imei);
						if ($row['plan'] == 'demo') {
							$result .= '<td><span style="color: red;">' . $alta_date . '</span></td>';
						} else {
							$result .= '<td>' . $alta_date . '</td>';
						}
					}
					if (in_array("maintenance", $data_items)) {
						$mtto_data = getObjectDateMtto($imei);
						$mtto_date = $mtto_data['fecha'];
						$mtto_month = date('m', strtotime($mtto_date));
						$mtto_year = date('Y', strtotime($mtto_date));
						$mtto = $mtto_data['mtto'];
						if ($mtto == 'Sin Cambios') {
							$mtto = 1;
						}
						if ($mtto == 'Remplazo de Equipo') {
							$mtto = 2;
						}
						if ($mtto == 'Instalación por Remplazo') {
							$mtto = 3;
						}
						if ($mtto == 'Instalación por Remplazo (Garantia)') {
							$mtto = 4;
						}
						if ($mtto == 'mtto') {
							$mtto = 5;
						}

						if ($row['plan'] == 'demo') {
							$result .= '<td><span style="color: red;">' . $mtto . '</span></td>';
						} elseif ($mtto == 5 && $mtto_year == $current_year && $mtto_month == $current_month) {
							$result .= '<td><span style="color: #28E0F9;">' . $mtto . '</span></td>';
						} elseif ($mtto == 1) {
							$mtto = 'Sin Cambios';
							$result .= '<td>' . $mtto . '</td>';
						} elseif ($mtto == 1) {
							$result .= '<td>' . $mtto . '</td>';
						} elseif ($mtto == 2) {
							$mtto = 'Remplazo de Equipo';
							$result .= '<td><span style="color: orange;">' . $mtto . '</span></td>';
						} elseif ($mtto == 3) {
							$mtto = 'Instalación por Remplazo';
							$result .= '<td><span style="color: orange;">' . $mtto . '</span></td>';
						} elseif ($mtto == 4) {
							$mtto = 'Instalación por Remplazo (Garantia)';
							$result .= '<td><span style="color: orange;">' . $mtto . '</span></td>';
						} elseif ($mtto != 1 && $mtto != 2 && $mtto != 3 && $mtto != 4 && $mtto != 5) {
							$result .= '<td>' . $mtto . '</td>';
						}
					}
					if (in_array("maintenance_date", $data_items)) {
						$mtto_data = getObjectDateMtto($imei);
						$mtto_date = $mtto_data['fecha'];
						$mtto_month = date('m', strtotime($mtto_date));
						$mtto_year = date('Y', strtotime($mtto_date));
						$mtto = $mtto_data['mtto'];
						if ($mtto == 'Sin Cambios') {
							$mtto = 1;
						}
						if ($mtto == 'Remplazo de Equipo') {
							$mtto = 2;
						}
						if ($mtto == 'Instalación por Remplazo') {
							$mtto = 3;
						}
						if ($mtto == 'Instalación por Remplazo (Garantia)') {
							$mtto = 4;
						}
						if ($mtto == 'mtto') {
							$mtto = 5;
						}

						if ($row['plan'] == 'demo') {
							$result .= '<td><span style="color: red;">' . $mtto_date . '</span></td>';
						} elseif ($mtto == 5 && $mtto_year == $current_year && $mtto_month == $current_month) {
							$result .= '<td><span style="color: #28E0F9;">' . $mtto_date . '</span></td>';
							$imei_counter_mtto++;
							$total_imei_counter_mtto++;
						} elseif ($mtto == 1) {
							$result .= '<td>' . $mtto_date . '</td>';
						} elseif ($mtto == 1) {
							$result .= '<td>' . $mtto_date . '</td>';
						} elseif ($mtto == 2) {
							$result .= '<td><span style="color: orange;">' . $mtto_date . '</span></td>';
						} elseif ($mtto == 3) {
							$result .= '<td><span style="color: orange;">' . $mtto_date . '</span></td>';
						} elseif ($mtto == 4) {
							$result .= '<td><span style="color: orange;">' . $mtto_date . '</span></td>';
						} elseif ($mtto != 1 && $mtto != 2 && $mtto != 3 && $mtto != 4 && $mtto != 5) {
							$result .= '<td>' . $mtto . '</td>';
						}
					}
					if (in_array("installation_date", $data_items)) {
						$installation_data = getObjectInstalation($imei);
					
						$installation_date = isset($installation_data['fecha']) ? $installation_data['fecha'] : '0000-00-00 00:00:00';
						$installation_month = $installation_date != '0000-00-00 00:00:00' ? date('m', strtotime($installation_date)) : '';
						$installation_year = $installation_date != '0000-00-00 00:00:00' ? date('Y', strtotime($installation_date)) : '';
						$remplazo = isset($installation_data['Remplazo']) ? $installation_data['Remplazo'] : false;
						$battery = isset($installation_data['Battery']) ? $installation_data['Battery'] : false;
						$instalacion = isset($installation_data['mtto']) ? $installation_data['mtto'] : '';
					
						if ($row['plan'] == 'demo') {
							$result .= '<td><span style="color: red;">' . $installation_date . '</span></td>';
						} else if ($remplazo && $installation_year == $current_year && $installation_month == $current_month) {
							$result .= '<td><span style="color: green;">' . $installation_date . '</span></td>';
							$imei_counter_replace++;
							$total_imei_counter_replace++;
						} else if ($battery && $installation_year == $current_year && $installation_month == $current_month) {
							$result .= '<td><span style="color: purple;">' . $installation_date . '</span></td>';
							$imei_counter_battery_replace++;
							$total_imei_counter_replace_battery++;
						} else if ($instalacion && $installation_year == $current_year && $installation_month == $current_month) {
							$result .= '<td><span style="color: green;">' . $installation_date . '</span></td>';
							$imei_counter_new++;
							$total_imei_counter_new++;
						} else {
							$result .= '<td>' . $installation_date . '</td>';
						}
					}
					
					if (in_array("rent", $data_items)) {
						$rent_price = $rent_prices[$imei];
						if ($row['plan'] == 'demo') {
							$result .= '<td><span style="color: red;">$' . $rent_price . '</span></td>';
						} else {
							$result .= '<td>$' . $rent_price . '</td>';
						}
					}
					if (in_array("seller", $data_items)) {
						$seller = getObjectSeller($imei);
						if ($row['plan'] == 'demo') {
							$result .= '<td><span style="color: red;">' . $seller . '</span></td>';
						} else {
							$result .= '<td>' . $seller . '</td>';
						}
					}
					$result .= '</tr>';
				}
				$processed_imeis[] = $imei;
			}
		}

		if ($imei_counter > 0) {
			$result .= '<tr><td colspan="1">Total: ' . $imei_counter . '</td></tr>';
			$imei_counter = 0;
		}
		if ($imei_counter_demo > 0) {
			$result .= '<tr><td colspan="1"><span style="color: red;">Total Demo/Cortecias: ' . $imei_counter_demo . '</span></td></tr>';
			$imei_counter_demo = 0;
		}
		if ($imei_counter_new > 0) {
			$result .= '<tr><td colspan="1"><span style="color: green;">Total Instalación Nueva: ' . $imei_counter_new . '</span></td></tr>';
			$imei_counter_new = 0;
		}
		if ($imei_counter_replace > 0) {
			$result .= '<tr><td colspan="1"><span style="color: orange;">Total Remplazos: ' . $imei_counter_replace . '</span></td></tr>';
			$imei_counter_replace = 0;
		}
		if ($imei_counter_battery_replace > 0) {
			$result .= '<tr><td colspan="1"><span style="color: purple;">Total Baterías Remplazadas: ' . $imei_counter_battery_replace . '</span></td></tr>';
			$imei_counter_battery_replace = 0;
		}
		if ($imei_counter_mtto > 0) {
			$result .= '<tr><td colspan="1"><span style="color: #28E0F9;">Total Rev/Mtto: ' . $imei_counter_mtto . '</span></td></tr>';
			$imei_counter_mtto = 0;
		}
	}
	$result .= '<tr><td colspan="' . $colspan . '"</td></tr>';

	if ($total_imeis > 0) {
		$result .= '<tr><td colspan="1">Total: ' . $total_imeis . '</td></tr>';
	}
	if ($demos_courtesies_counter > 0) {
		$result .= '<tr><td colspan="1"><span style="color: red;">Demos y Cortesías: ' . $demos_courtesies_counter . '</span></td></tr>';
	}
	if ($total_imei_counter_new > 0) {
		$result .= '<tr><td colspan="1"><span style="color: green;">Altas del Mes: ' . $total_imei_counter_new . '</span></td></tr>';
	}
	if ($total_imei_counter_replace > 0) {
		$result .= '<tr><td colspan="1"><span style="color: orange;">Remplazos del Mes: ' . $total_imei_counter_replace . '</span></td></tr>';
	}
	if ($total_imei_counter_replace_battery > 0) {
		$result .= '<tr><td colspan="1"><span style="color: purple;">Remplazos de Baterias ER-100 del Mes: ' . $total_imei_counter_replace_battery . '</span></td></tr>';
	}
	if ($total_imei_counter_mtto > 0) {
		$result .= '<tr><td colspan="1"><span style="color: #28E0F9;">Mtto/Revisiones del Mes: ' . $total_imei_counter_mtto . '</span></td></tr>';
	}
	$result .= '</table>';

	return $result;
}

function reportsGenerateCurrentPosition($imeis, $dtf, $dtt, $show_coordinates, $show_addresses, $zones_addresses, $data_items, $status)
{
	global $ms, $_SESSION, $gsValues, $la;

	$result = '';

	$result = '<table class="report" width="100%" ><tr align="center">';

	$result .= '<th>' . $la['OBJECT'] . '</th>';

	if (in_array("time", $data_items)) {
		$result .= '<th>' . $la['TIME'] . '</th>';
	}

	if (in_array("position", $data_items)) {
		$result .= '<th>' . $la['POSITION'] . '</th>';
	}

	if (in_array("speed", $data_items)) {
		$result .= '<th>' . $la['SPEED'] . '</th>';
	}

	if (in_array("altitude", $data_items)) {
		$result .= '<th>' . $la['ALTITUDE'] . '</th>';
	}

	if (in_array("angle", $data_items)) {
		$result .= '<th>' . $la['ANGLE'] . '</th>';
	}

	if (in_array("status", $data_items)) {
		$result .= '<th>' . $la['STATUS'] . '</th>';
	}

	if (in_array("odometer", $data_items)) {
		$result .= '<th>' . $la['ODOMETER'] . '</th>';
	}

	if (in_array("engine_hours", $data_items)) {
		$result .= '<th>' . $la['ENGINE_HOURS'] . '</th>';
	}

	$result .= '</tr>';

	for ($i = 0; $i < count($imeis); ++$i) {
		$imei = $imeis[$i];

		$q = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "'";
		$r = mysqli_query($ms, $q);

		while ($row = mysqli_fetch_array($r)) {
			$dt_server = $row['dt_server'];
			$dt_tracker = $row['dt_tracker'];
			$lat = $row['lat'];
			$lng = $row['lng'];
			$altitude = $row['altitude'];
			$angle = $row['angle'];
			$speed = $row['speed'];

			if (($lat != 0) && ($lng != 0)) {
				$speed = convSpeedUnits($speed, 'km', $_SESSION["unit_distance"]);
				$altitude = convAltitudeUnits($altitude, 'km', $_SESSION["unit_distance"]);

				// status
				$status_type = false;
				$status_str = '';
				$dt_last_stop = strtotime($row['dt_last_stop']);
				$dt_last_idle = strtotime($row['dt_last_idle']);
				$dt_last_move = strtotime($row['dt_last_move']);

				if (($dt_last_stop > 0) || ($dt_last_move > 0)) {
					// stopped and moving
					if ($dt_last_stop >= $dt_last_move) {
						$status_type = 'stopped';
						$status_str = $la['STOPPED'] . ' ' . getTimeDetails(strtotime(gmdate("Y-m-d H:i:s")) - $dt_last_stop, true);
					} else {
						$status_type = 'moving';
						$status_str = $la['MOVING'] . ' ' . getTimeDetails(strtotime(gmdate("Y-m-d H:i:s")) - $dt_last_move, true);
					}

					// idle
					if (($dt_last_stop <= $dt_last_idle) && ($dt_last_move <= $dt_last_idle)) {
						$status_type = 'idle';
						$status_str = $la['ENGINE_IDLE'] . ' ' . getTimeDetails(strtotime(gmdate("Y-m-d H:i:s")) - $dt_last_idle, true);
					}
				}

				// offline status
				$dt_now = gmdate("Y-m-d H:i:s");
				$dt_difference = strtotime($dt_now) - strtotime($dt_server);
				if ($dt_difference > $gsValues['CONNECTION_TIMEOUT'] * 60) {
					if (strtotime($dt_server) > 0) {
						$status_type = 'offline';
						$status_str = $la['OFFLINE'] . ' ' . getTimeDetails(strtotime(gmdate("Y-m-d H:i:s")) - strtotime($dt_server), true);
					}

					$speed = 0;
				}

				// filter status
				if (($status != false) && ($status != $status_type)) {
					continue;
				}

				$odometer = getObjectOdometer($imei);
				$odometer = floor(convDistanceUnits($odometer, 'km', $_SESSION["unit_distance"]));

				$result .= '<tr align="center">';

				$result .= '<td>' . getObjectName($imei) . '</td>';

				if (in_array("time", $data_items)) {
					$result .= '<td>' . convUserTimezone($dt_tracker) . '</td>';
				}

				if (in_array("position", $data_items)) {
					$result .= '<td>' . reportsGetPossition($lat, $lng, $show_coordinates, $show_addresses, $zones_addresses) . '</td>';
				}

				if (in_array("speed", $data_items)) {
					$result .= '<td>' . $speed . ' ' . $la["UNIT_SPEED"] . '</td>';
				}

				if (in_array("altitude", $data_items)) {
					$result .= '<td>' . $altitude . ' ' . $la["UNIT_HEIGHT"] . '</td>';
				}

				if (in_array("angle", $data_items)) {
					$result .= '<td>' . $angle . '</td>';
				}

				if (in_array("status", $data_items)) {
					$result .= '<td>' . $status_str . '</td>';
				}

				if (in_array("odometer", $data_items)) {
					$result .= '<td>' . $odometer . ' ' . $la["UNIT_DISTANCE"] . '</td>';
				}

				if (in_array("engine_hours", $data_items)) {
					$result .= '<td>' . getObjectEngineHours($imei, true) . '</td>';
				}

				$result .= '</tr>';
			} else {
				$result .= '<tr align="center">';
				$result .= '<td>' . getObjectName($imei) . '</td>';
				$result .= '<td colspan="9">' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td>';
				$result .= '</tr>';
			}
		}
	}

	$result .= '</table>';

	return $result;
}

function reportsGenerateDrivesAndStops($imei, $dtf, $dtt, $stop_duration, $show_coordinates, $show_addresses, $zones_addresses, $data_items) //DRIVES_AND_STOPS
{
	global $la, $user_id;

	$result = '';

	$data = getRoute($user_id, $imei, $dtf, $dtt, $stop_duration, true);

	if (count($data['route']) < 2) {
		return false;
	}

	$result = '<table class="report" width="100%"><tr align="center">';

	if (in_array("status", $data_items)) {
		$result .= '<th rowspan="2">' . $la['STATUS'] . '</th>';
	}

	if (in_array("start", $data_items)) {
		$result .= '<th rowspan="2">' . $la['START'] . '</th>';
	}

	if (in_array("end", $data_items)) {
		$result .= '<th rowspan="2">' . $la['END'] . '</th>';
	}

	if (in_array("duration", $data_items)) {
		$result .= '<th rowspan="2">' . $la['DURATION'] . '</th>';
	}

	$result .= '<th colspan="3">' . $la['STOP_POSITION'] . '</th>';

	if (in_array("fuel_consumption", $data_items)) {
		$result .= '<th rowspan="2">' . $la['FUEL_CONSUMPTION'] . '</th>';
	}

	if (in_array("avg_fuel_consumption", $data_items)) {
		if ($_SESSION["unit_capacity"] == 'l') {
			$result .= '<th rowspan="2">' . $la['AVG_FUEL_CONSUMPTION_100_KM'] . '</th>';
		} else {
			$result .= '<th rowspan="2">' . $la['AVG_FUEL_CONSUMPTION_MPG'] . '</th>';
		}
	}

	if (in_array("fuel_cost", $data_items)) {
		$result .= '<th rowspan="2">' . $la['FUEL_COST'] . '</th>';
	}

	if (in_array("engine_idle", $data_items)) {
		$result .= '<th rowspan="2">' . $la['ENGINE_IDLE'] . '</th>';
	}

	if (in_array("driver", $data_items)) {
		$result .= '<th rowspan="2">' . $la['DRIVER'] . '</th>';
	}

	if (in_array("trailer", $data_items)) {
		$result .= '<th rowspan="2">' . $la['TRAILER'] . '</th>';
	}

	$result .= '</tr>';

	$result .= '<tr align="center">
				<th>' . $la['LENGTH'] . '</th>
				<th>' . $la['TOP_SPEED'] . '</th>
				<th>' . $la['AVG_SPEED'] . '</th>
				</tr>';

	$dt_sort = array();
	for ($i = 0; $i < count($data['stops']); ++$i) {
		$dt_sort[] = $data['stops'][$i][6];
	}
	for ($i = 0; $i < count($data['drives']); ++$i) {
		$dt_sort[] = $data['drives'][$i][4];
	}
	sort($dt_sort);

	for ($i = 0; $i < count($dt_sort); ++$i) {
		for ($j = 0; $j < count($data['stops']); ++$j) {
			if ($data['stops'][$j][6] == $dt_sort[$i]) {
				$lat = sprintf("%01.6f", $data['stops'][$j][2]);
				$lng = sprintf("%01.6f", $data['stops'][$j][3]);

				$result .= '<tr align="center">';

				if (in_array("status", $data_items)) {
					$result .= '<td>' . $la['STOPPED'] . '</td>';
				}

				if (in_array("start", $data_items)) {
					$result .= '<td>' . $data['stops'][$j][6] . '</td>';
				}

				if (in_array("end", $data_items)) {
					$result .= '<td>' . $data['stops'][$j][7] . '</td>';
				}

				if (in_array("duration", $data_items)) {
					$result .= '<td>' . $data['stops'][$j][8] . '</td>';
				}

				$result .= '<td colspan="3">' . reportsGetPossition($lat, $lng, $show_coordinates, $show_addresses, $zones_addresses) . '</td>';

				if (in_array("fuel_consumption", $data_items)) {
					$result .= '<td>' . $data['stops'][$j][9] . ' ' . $la["UNIT_CAPACITY"] . '</td>';
				}

				if (in_array("avg_fuel_consumption", $data_items)) {
					$result .= '<td></td>';
				}

				if (in_array("fuel_cost", $data_items)) {
					$result .= '<td>' . $data['stops'][$j][10] . ' ' . $_SESSION["currency"] . '</td>';
				}

				if (in_array("engine_idle", $data_items)) {
					$result .= '<td>' . $data['stops'][$j][11] . '</td>';
				}

				if (in_array("driver", $data_items)) {
					$params = $data['route'][$data['stops'][$j][1]][6];
					$driver = getObjectDriver($user_id, $imei, $params);
					if ($driver == false) {
						$driver['driver_name'] = $la['NA'];
					}

					$result .= '<td>' . $driver['driver_name'] . '</td>';
				}

				if (in_array("trailer", $data_items)) {
					$params = $data['route'][$data['stops'][$j][1]][6];
					$trailer = getObjectTrailer($user_id, $imei, $params);
					if ($trailer == false) {
						$trailer['trailer_name'] = $la['NA'];
					}

					$result .= '<td>' . $trailer['trailer_name'] . '</td>';
				}

				$result .= '</tr>';
			}
		}
		for ($j = 0; $j < count($data['drives']); ++$j) {
			if ($data['drives'][$j][4] == $dt_sort[$i]) {
				$result .= '<tr align="center">';

				if (in_array("status", $data_items)) {
					$result .= '<td>' . $la['MOVING'] . '</td>';
				}

				if (in_array("start", $data_items)) {
					$result .= '<td>' . $data['drives'][$j][4] . '</td>';
				}

				if (in_array("end", $data_items)) {
					$result .= '<td>' . $data['drives'][$j][5] . '</td>';
				}

				if (in_array("duration", $data_items)) {
					$result .= '<td>' . $data['drives'][$j][6] . '</td>';
				}

				$result .= '<td>' . $data['drives'][$j][7] . ' ' . $la["UNIT_DISTANCE"] . '</td>
                    <td>' . $data['drives'][$j][8] . ' ' . $la["UNIT_SPEED"] . '</td>
                    <td>' . $data['drives'][$j][9] . ' ' . $la["UNIT_SPEED"] . '</td>';

				if (in_array("fuel_consumption", $data_items)) {
					$result .= '<td>' . $data['drives'][$j][10] . ' ' . $la["UNIT_CAPACITY"] . '</td>';
				}

				if (in_array("avg_fuel_consumption", $data_items)) {
					if ($_SESSION["unit_capacity"] == 'l') {
						$result .= '<td>' . $data['drives'][$j][13] . ' ' . $la["UNIT_CAPACITY"] . '</td>';
					} else {
						$result .= '<td>' . $data['drives'][$j][14] . ' ' . $la["UNIT_MI"] . '</td>';
					}
				}

				if (in_array("fuel_cost", $data_items)) {
					$result .= '<td>' . $data['drives'][$j][11] . ' ' . $_SESSION["currency"] . '</td>';
				}

				if (in_array("engine_idle", $data_items)) {
					$result .= '<td></td>';
				}

				if (in_array("driver", $data_items)) {
					$params = $data['route'][$data['drives'][$j][1]][6];
					$driver = getObjectDriver($user_id, $imei, $params);
					if ($driver == false) {
						$driver['driver_name'] = $la['NA'];
					}

					$result .= '<td>' . $driver['driver_name'] . '</td>';
				}

				if (in_array("trailer", $data_items)) {
					$params = $data['route'][$data['drives'][$j][1]][6];
					$trailer = getObjectTrailer($user_id, $imei, $params);
					if ($trailer == false) {
						$trailer['trailer_name'] = $la['NA'];
					}

					$result .= '<td>' . $trailer['trailer_name'] . '</td>';
				}
				$result .= '</tr>';
			}
		}
	}
	$result .= '</table><br/>';

	$result .= '<table>';

	if (in_array("move_duration", $data_items)) {
		$result .= '<tr>
						<td><strong>' . $la['MOVE_DURATION'] . ':</strong></td>
						<td>' . $data['drives_duration'] . '</td>
					</tr>';
	}

	if (in_array("stop_duration", $data_items)) {
		$result .= '<tr>
						<td><strong>' . $la['STOP_DURATION'] . ':</strong></td>
						<td>' . $data['stops_duration'] . '</td>
					</tr>';
	}

	if (in_array("route_length", $data_items)) {
		$result .= '<tr>
						<td><strong>' . $la['ROUTE_LENGTH'] . ':</strong></td>
						<td>' . $data['route_length'] . ' ' . $la["UNIT_DISTANCE"] . '</td>
					</tr>';
	}

	if (in_array("top_speed", $data_items)) {
		$result .= '<tr>
						<td><strong>' . $la['TOP_SPEED'] . ':</strong></td>
						<td>' . $data['top_speed'] . ' ' . $la["UNIT_SPEED"] . '</td>
					</tr>';
	}

	if (in_array("avg_speed", $data_items)) {
		$result .= '<tr>
						<td><strong>' . $la['AVG_SPEED'] . ':</strong></td>
						<td>' . $data['avg_speed'] . ' ' . $la["UNIT_SPEED"] . '</td>
					</tr>';
	}

	if (in_array("fuel_consumption", $data_items)) {
		$result .= '<tr>
						<td><strong>' . $la['FUEL_CONSUMPTION'] . ':</strong></td>
						<td>' . $data['fuel_consumption'] . ' ' . $la["UNIT_CAPACITY"] . '</td>
					</tr>';
	}

	if (in_array("avg_fuel_consumption", $data_items)) {
		if ($_SESSION["unit_capacity"] == 'l') {
			$result .= '<tr>
						<td><strong>' . $la['AVG_FUEL_CONSUMPTION_100_KM'] . ':</strong></td>
						<td>' . $data['fuel_consumption_per_km'] . ' ' . $la["UNIT_CAPACITY"] . '</td>
					</tr>';
		} else {
			$result .= '<tr>
						<td><strong>' . $la['AVG_FUEL_CONSUMPTION_MPG'] . ':</strong></td>
						<td>' . $data['fuel_consumption_mpg'] . ' ' . $la["UNIT_MI"] . '</td>
					</tr>';
		}
	}

	if (in_array("fuel_cost", $data_items)) {
		$result .= '<tr>
						<td><strong>' . $la['FUEL_COST'] . ':</strong></td>
						<td>' . $data['fuel_cost'] . ' ' . $_SESSION["currency"] . '</td>
					</tr>';
	}

	if (in_array("engine_work", $data_items)) {
		$result .= '<tr>
						<td><strong>' . $la['ENGINE_WORK'] . ':</strong></td>
						<td>' . $data['engine_work'] . '</td>
					</tr>';
	}

	if (in_array("engine_idle", $data_items)) {
		$result .= '<tr>
						<td><strong>' . $la['ENGINE_IDLE'] . ':</strong></td>
						<td>' . $data['engine_idle'] . '</td>
					</tr>';
	}

	$result .= '</table>';

	return $result;
}

function reportsGenerateDrivesAndStopsSensors($imei, $dtf, $dtt, $sensors, $stop_duration, $show_coordinates, $show_addresses, $zones_addresses, $data_items) //DRIVES_AND_STOPS_WITH_SENSORS
{
	global $la, $user_id;

	$colspan = count($data_items);
	$result = '';

	$data = getRoute($user_id, $imei, $dtf, $dtt, $stop_duration, true);

	if (count($data['route'] ?? []) < 2) {
		return false;
	}


	$result = '<table class="report" width="100%"><tr align="center">';

	if (in_array("status", $data_items)) {
		$result .= '<th rowspan="2">' . $la['STATUS'] . '</th>';
	}

	if (in_array("start", $data_items)) {
		$result .= '<th rowspan="2">' . $la['START'] . '</th>';
	}

	if (in_array("end", $data_items)) {
		$result .= '<th rowspan="2">' . $la['END'] . '</th>';
	}

	if (in_array("duration", $data_items)) {
		$result .= '<th rowspan="2">' . $la['DURATION'] . '</th>';
	}

	$result .= '<th colspan="3">' . $la['STOP_POSITION'] . '</th>';

	if (in_array("fuel_consumption", $data_items)) {
		$result .= '<th rowspan="2">' . $la['FUEL_CONSUMPTION'] . '</th>';
	}

	if (in_array("stolen", $data_items)) {
		$result .= '<th rowspan="2">' . $la['FUEL_DISCHARGE'] . '</th>';
	}

	if (in_array("avg_fuel_consumption", $data_items)) {
		if ($_SESSION["unit_capacity"] == 'l') {
			$result .= '<th rowspan="2">' . $la['AVG_FUEL_CONSUMPTION_KM'] . '</th>';
		} else {
			$result .= '<th rowspan="2">' . $la['AVG_FUEL_CONSUMPTION_MPG'] . '</th>';
		}
	}

	if (in_array("fuel_cost", $data_items)) {
		$result .= '<th rowspan="2">' . $la['FUEL_COST'] . '</th>';
	}

	if (in_array("engine_idle", $data_items)) {
		$result .= '<th rowspan="2">' . $la['ENGINE_IDLE'] . '</th>';
	}

	for ($k = 0; $k < count($sensors); ++$k) {
		$sensor = $sensors[$k];
		$result .= '<th rowspan="2">' . $sensor['name'] . '</th>';
	}

	if (in_array("driver", $data_items)) {
		$result .= '<th rowspan="2">' . $la['DRIVER'] . '</th>';
	}

	if (in_array("trailer", $data_items)) {
		$result .= '<th rowspan="2">' . $la['TRAILER'] . '</th>';
	}

	$result .= '</tr>';

	$result .= '<tr align="center">
				<th>' . $la['LENGTH'] . '</th>
				<th>' . $la['TOP_SPEED'] . '</th>
				<th>' . $la['AVG_SPEED'] . '</th>
				</tr>';

	$dt_sort = array();
	for ($i = 0; $i < count($data['stops']); ++$i) {
		$dt_sort[] = $data['stops'][$i][6];
	}
	for ($i = 0; $i < count($data['drives']); ++$i) {
		$dt_sort[] = $data['drives'][$i][4];
	}
	sort($dt_sort);

	$total_lts_km_array = []; // Inicializa un array vacío para almacenar las sumas
	$total_litros_robados = 0;
	$total_litros_drive = 0;
	$total_litros_stop = 0;
	for ($i = 0; $i < count($dt_sort); ++$i) {
		for ($j = 0; $j < count($data['stops']); ++$j) {
			if ($data['stops'][$j][6] == $dt_sort[$i]) {
				$lat = sprintf("%01.6f", $data['stops'][$j][2]);
				$lng = sprintf("%01.6f", $data['stops'][$j][3]);

				$result .= '<tr align="center">';

				if (in_array("status", $data_items)) {
					$result .= '<td>' . $la['STOPPED'] . '</td>';
				}

				if (in_array("start", $data_items)) {
					$result .= '<td>' . $data['stops'][$j][6] . '</td>';
				}

				if (in_array("end", $data_items)) {
					$result .= '<td>' . $data['stops'][$j][7] . '</td>';
				}

				if (in_array("duration", $data_items)) {
					$result .= '<td>' . $data['stops'][$j][8] . '</td>';
				}

				$result .= '<td colspan="3">' . reportsGetPossition($lat, $lng, $show_coordinates, $show_addresses, $zones_addresses) . '</td>';

				if (in_array("fuel_consumption", $data_items)) {
					$result .= '<td>' . $data['stops'][$j][9] . ' ' . $la["UNIT_CAPACITY"] . '</td>';
					$total_litros_stop += $data['stops'][$j][9];
				}
				if (in_array("stolen", $data_items)) {
					if ($data['stops'][$j][9] > 20) {
						$litros_por_minuto = 0.09;
						$tiempo_formato = $data['stops'][$j][11];
						preg_match('/(\d+) h/', $tiempo_formato, $matches);
						$horas = isset($matches[1]) ? intval($matches[1]) : 0;
						preg_match('/(\d+) min/', $tiempo_formato, $matches);
						$minutos = isset($matches[1]) ? intval($matches[1]) : 0;
						preg_match('/(\d+) s/', $tiempo_formato, $matches);
						$segundos = isset($matches[1]) ? intval($matches[1]) : 0;
						$segundos_en_minutos = $segundos / 60;
						$tiempo_total_minutos = $horas * 60 + $minutos + $segundos_en_minutos;
						$litros_estimados = $litros_por_minuto * $tiempo_total_minutos;

						$litros_robados = round($litros_estimados - $data['stops'][$j][9], 2);

						if ($litros_robados < 20) {
							$result .= '<td style="color: red;">' . $litros_robados . ' ' . $la["UNIT_CAPACITY"] . '</td>';
							$total_litros_robados += $litros_robados;
						} else {
							$result .= '<td></td>';
						}
					} else {
						$result .= '<td></td>';
					}
				}

				if (in_array("avg_fuel_consumption", $data_items)) {
					$result .= '<td></td>';
				}

				if (in_array("fuel_cost", $data_items)) {
					$result .= '<td>' . $data['stops'][$j][10] . ' ' . $_SESSION["currency"] . '</td>';
				}

				if (in_array("engine_idle", $data_items)) {
					$result .= '<td>' . $data['stops'][$j][11] . '</td>';
				}


				for ($k = 0; $k < count($sensors); ++$k) {
					$params = $data['stops'][$j][12];
					$sensor_data = getSensorValue($params, $sensors[$k]);
					$result .= '<td>' . $sensor_data['value_full'] . '</td>';

					if (!isset($total_lts_km_array[$j])) {
						$total_lts_km_array[$j] = 0;
					}
					$total_lts_km_array[$j] += $sensor_data['value'];
				}

				if (in_array("driver", $data_items)) {
					$params = $data['route'][$data['stops'][$j][1]][6];
					$driver = getObjectDriver($user_id, $imei, $params);
					if ($driver == false) {
						$driver['driver_name'] = $la['NA'];
					}

					$result .= '<td>' . $driver['driver_name'] . '</td>';
				}

				if (in_array("trailer", $data_items)) {
					$params = $data['route'][$data['stops'][$j][1]][6];
					$trailer = getObjectTrailer($user_id, $imei, $params);
					if ($trailer == false) {
						$trailer['trailer_name'] = $la['NA'];
					}

					$result .= '<td>' . $trailer['trailer_name'] . '</td>';
				}

				$result .= '</tr>';
			}
		}
		for ($j = 0; $j < count($data['drives']); ++$j) {
			if ($data['drives'][$j][4] == $dt_sort[$i]) {
				$result .= '<tr align="center">';

				if (in_array("status", $data_items)) {
					$result .= '<td>' . $la['MOVING'] . '</td>';
				}

				if (in_array("start", $data_items)) {
					$result .= '<td>' . $data['drives'][$j][4] . '</td>';
				}

				if (in_array("end", $data_items)) {
					$result .= '<td>' . $data['drives'][$j][5] . '</td>';
				}

				if (in_array("duration", $data_items)) {
					$result .= '<td>' . $data['drives'][$j][6] . '</td>';
				}

				$result .= '<td>' . $data['drives'][$j][7] . ' ' . $la["UNIT_DISTANCE"] . '</td>
							<td>' . $data['drives'][$j][8] . ' ' . $la["UNIT_SPEED"] . '</td>
							<td>' . $data['drives'][$j][9] . ' ' . $la["UNIT_SPEED"] . '</td>';

				if (in_array("fuel_consumption", $data_items)) {
					$result .= '<td>' . $data['drives'][$j][10] . ' ' . $la["UNIT_CAPACITY"] . '</td>';
					$total_litros_drive += $data['drives'][$j][10];
				}
				if (in_array("stolen", $data_items)) {
					if ($data['drives'][$j][10] > 20) {
						$litros_por_km = 0.40;
						$distancia = $data['drives'][$j][7];
						$litros_estimados = $litros_por_km * $distancia;

						$litros_robados = round($litros_estimados - $data['drives'][$j][10], 2);
						$litros_robados_ = abs($litros_robados);

						if ($litros_robados_ > 20) {
							$result .= '<td>Consumo Irregular</td>';
						} else {
							$result .= '<td></td>';
						}
					} else {
						$result .= '<td></td>';
					}
				}

				if (in_array("avg_fuel_consumption", $data_items)) {
					if ($_SESSION["unit_capacity"] == 'l') {
						$result .= '<td>' . $data['drives'][$j][13] . ' ' . $la["UNIT_CAPACITY"] . '</td>';
					} else {
						$result .= '<td>' . $data['drives'][$j][14] . ' ' . $la["UNIT_MI"] . '</td>';
					}
				}

				if (in_array("fuel_cost", $data_items)) {
					$result .= '<td>' . $data['drives'][$j][11] . ' ' . $_SESSION["currency"] . '</td>';
				}

				if (in_array("engine_idle", $data_items)) {
					$result .= '<td></td>';
				}

				for ($k = 0; $k < count($sensors); ++$k) {
					$result .= '<td></td>';
				}

				if (in_array("driver", $data_items)) {
					$params = $data['route'][$data['drives'][$j][1]][6];
					$driver = getObjectDriver($user_id, $imei, $params);
					if ($driver == false) {
						$driver['driver_name'] = $la['NA'];
					}

					$result .= '<td>' . $driver['driver_name'] . '</td>';
				}

				if (in_array("trailer", $data_items)) {
					$params = $data['route'][$data['drives'][$j][1]][6];
					$trailer = getObjectTrailer($user_id, $imei, $params);
					if ($trailer == false) {
						$trailer['trailer_name'] = $la['NA'];
					}

					$result .= '<td>' . $trailer['trailer_name'] . '</td>';
				}

				$result .= '</tr>';
			}
		}
	}

	$result .= '<tr><td colspan="' . $colspan . '"</td></tr>';
	$result .= '</table><br/>';

	$result .= '<table>';

	if (in_array("move_duration", $data_items)) {
		$result .= 	'<tr>
						<td><strong>' . $la['MOVE_DURATION'] . ':</strong></td>
						<td>' . $data['drives_duration'] . '</td>
					</tr>';
	}

	if (in_array("stop_duration", $data_items)) {
		$result .= 	'<tr>
						<td><strong>' . $la['STOP_DURATION'] . ':</strong></td>
						<td>' . $data['stops_duration'] . '</td>
					</tr>';
	}

	if (in_array("route_length", $data_items)) {
		$result .= 	'<tr>
						<td><strong>' . $la['ROUTE_LENGTH'] . ':</strong></td>
						<td>' . $data['route_length'] . ' ' . $la["UNIT_DISTANCE"] . '</td>
					</tr>';
	}

	if (in_array("top_speed", $data_items)) {
		$result .= 	'<tr>
						<td><strong>' . $la['TOP_SPEED'] . ':</strong></td>
						<td>' . $data['top_speed'] . ' ' . $la["UNIT_SPEED"] . '</td>
					</tr>';
	}

	if (in_array("avg_speed", $data_items)) {
		$result .= 	'<tr>
						<td><strong>' . $la['AVG_SPEED'] . ':</strong></td>
						<td>' . $data['avg_speed'] . ' ' . $la["UNIT_SPEED"] . '</td>
					</tr>';
	}

	if (in_array("fuel_consumption", $data_items)) {
		$result .= 	'<tr>
						<td><strong>' . $la['TOTAL_FUEL_CONSUMPTION'] . ':</strong></td>
						<td>' . $data['fuel_consumption'] . ' ' . $la["UNIT_CAPACITY"] . '</td>
					</tr>';
	}

	if (in_array("stolen", $data_items)) {
		$result .= 	'<tr>
						<td><strong>' . $la['DRIVE_FUEL_CONSUMPTION'] . ':</strong></td>
						<td>' . $total_litros_drive . '</td>
					</tr>';
	}

	if (in_array("stolen", $data_items)) {
		$result .= 	'<tr>
						<td><strong>' . $la['IDLE_FUEL_CONSUMPTION'] . ':</strong></td>
						<td>' . $total_litros_stop . '</td>
					</tr>';
	}

	if (in_array("stolen", $data_items)) {
		$result .= 	'<tr>
						<td><strong>' . $la['FUEL_DISCHARGE'] . ':</strong></td>
						<td>' . $total_litros_robados . '</td>
					</tr>';
	}

	if (in_array("avg_fuel_consumption", $data_items)) {
		if ($_SESSION["unit_capacity"] == 'l') {
			$primer_valor = reset($total_lts_km_array);
			$ultimo_valor = end($total_lts_km_array);
			$max_valor = max($primer_valor, $ultimo_valor);
			$litros_consumidos = $max_valor - min($primer_valor, $ultimo_valor);
			$max_litros_consumidos = max($litros_consumidos, 0);

			if ($data['route_length'] == 0 || $max_litros_consumidos == 0) {
				$km_por_litro = 0;
			} else {
				if ($max_litros_consumidos >= $data['route_length']) {
					$km_por_litro = $max_litros_consumidos / $data['route_length'];
				} else {
					$km_por_litro = $data['route_length'] / $max_litros_consumidos;
				}
			}

			$data['fuel_consumption_per_km'] = number_format($km_por_litro, 2);

			$result .= 	'<tr>
						<td><strong>' . $la['AVG_FUEL_CONSUMPTION_KM'] . ':</strong></td>
						<td>' . $data['fuel_consumption_per_km'] . ' ' . $la["UNIT_CAPACITY"] . '</td>
					</tr>';
		} else {
			$result .= 	'<tr>
						<td><strong>' . $la['AVG_FUEL_CONSUMPTION_MPG'] . ':</strong></td>
						<td>' . $data['fuel_consumption_mpg'] . ' ' . $la["UNIT_MI"] . '</td>
					</tr>';
		}
	}

	if (in_array("fuel_cost", $data_items)) {
		$result .= 	'<tr>
						<td><strong>' . $la['FUEL_COST'] . ':</strong></td>
						<td>' . $data['fuel_cost'] . ' ' . $_SESSION["currency"] . '</td>
					</tr>';
	}

	if (in_array("engine_work", $data_items)) {
		$result .= 	'<tr>
						<td><strong>' . $la['ENGINE_WORK'] . ':</strong></td>
						<td>' . $data['engine_work'] . '</td>
					</tr>';
	}

	if (in_array("engine_idle", $data_items)) {
		$result .= 	'<tr>
						<td><strong>' . $la['ENGINE_IDLE'] . ':</strong></td>
						<td>' . $data['engine_idle'] . '</td>
					</tr>';
	}

	$result .= '</table>';

	return $result;
}

function reportsGenerateDrivesAndStopsLogicSensors($imei, $dtf, $dtt, $sensors, $stop_duration, $show_coordinates, $show_addresses, $zones_addresses, $data_items) //DRIVES_AND_STOPS_WITH_LOGIC_SENSORS
{
	global $la, $user_id;

	$result = '';

	$data = getRoute($user_id, $imei, $dtf, $dtt, $stop_duration, true);

	if (count($data['route']) < 2) {
		return false;
	}

	$result = '<table class="report" width="100%"><tr align="center">';

	if (in_array("status", $data_items)) {
		$result .= '<th rowspan="2">' . $la['STATUS'] . '</th>';
	}

	if (in_array("start", $data_items)) {
		$result .= '<th rowspan="2">' . $la['START'] . '</th>';
	}

	if (in_array("end", $data_items)) {
		$result .= '<th rowspan="2">' . $la['END'] . '</th>';
	}

	if (in_array("duration", $data_items)) {
		$result .= '<th rowspan="2">' . $la['DURATION'] . '</th>';
	}

	$result .= '<th colspan="3">' . $la['STOP_POSITION'] . '</th>';

	if (in_array("fuel_consumption", $data_items)) {
		$result .= '<th rowspan="2">' . $la['FUEL_CONSUMPTION'] . '</th>';
	}

	if (in_array("avg_fuel_consumption", $data_items)) {
		if ($_SESSION["unit_capacity"] == 'l') {
			$result .= '<th rowspan="2">' . $la['AVG_FUEL_CONSUMPTION_100_KM'] . '</th>';
		} else {
			$result .= '<th rowspan="2">' . $la['AVG_FUEL_CONSUMPTION_MPG'] . '</th>';
		}
	}

	if (in_array("fuel_cost", $data_items)) {
		$result .= '<th rowspan="2">' . $la['FUEL_COST'] . '</th>';
	}

	if (in_array("engine_idle", $data_items)) {
		$result .= '<th rowspan="2">' . $la['ENGINE_IDLE'] . '</th>';
	}

	for ($k = 0; $k < count($sensors); ++$k) {
		$sensor = $sensors[$k];
		$result .= '<th rowspan="2">' . $sensor['name'] . '</th>';
	}

	if (in_array("driver", $data_items)) {
		$result .= '<th rowspan="2">' . $la['DRIVER'] . '</th>';
	}

	if (in_array("trailer", $data_items)) {
		$result .= '<th rowspan="2">' . $la['TRAILER'] . '</th>';
	}

	$result .= '</tr>';

	$result .= '<tr align="center">
				<th>' . $la['LENGTH'] . '</th>
				<th>' . $la['TOP_SPEED'] . '</th>
				<th>' . $la['AVG_SPEED'] . '</th>
				</tr>';

	$dt_sort = array();
	for ($i = 0; $i < count($data['stops']); ++$i) {
		$dt_sort[] = $data['stops'][$i][6];
	}
	for ($i = 0; $i < count($data['drives']); ++$i) {
		$dt_sort[] = $data['drives'][$i][4];
	}
	sort($dt_sort);

	for ($i = 0; $i < count($dt_sort); ++$i) {
		for ($j = 0; $j < count($data['stops']); ++$j) {
			if ($data['stops'][$j][6] == $dt_sort[$i]) {
				$lat = sprintf("%01.6f", $data['stops'][$j][2]);
				$lng = sprintf("%01.6f", $data['stops'][$j][3]);

				$result .= '<tr align="center">';

				if (in_array("status", $data_items)) {
					$result .= '<td>' . $la['STOPPED'] . '</td>';
				}

				if (in_array("start", $data_items)) {
					$result .= '<td>' . $data['stops'][$j][6] . '</td>';
				}

				if (in_array("end", $data_items)) {
					$result .= '<td>' . $data['stops'][$j][7] . '</td>';
				}

				if (in_array("duration", $data_items)) {
					$result .= '<td>' . $data['stops'][$j][8] . '</td>';
				}

				$result .= '<td colspan="3">' . reportsGetPossition($lat, $lng, $show_coordinates, $show_addresses, $zones_addresses) . '</td>';

				if (in_array("fuel_consumption", $data_items)) {
					$result .= '<td>' . $data['stops'][$j][9] . ' ' . $la["UNIT_CAPACITY"] . '</td>';
				}

				if (in_array("avg_fuel_consumption", $data_items)) {
					$result .= '<td></td>';
				}

				if (in_array("fuel_cost", $data_items)) {
					$result .= '<td>' . $data['stops'][$j][10] . ' ' . $_SESSION["currency"] . '</td>';
				}

				if (in_array("engine_idle", $data_items)) {
					$result .= '<td>' . $data['stops'][$j][11] . '</td>';
				}

				for ($k = 0; $k < count($sensors); ++$k) {
					$duration = 0;

					$status = false;
					$activation_time = '';
					$deactivation_time = '';

					$sensor = $sensors[$k];

					$id_start = $data['stops'][$j][0];
					$id_end = $data['stops'][$j][1];

					for ($l = $id_start; $l <= $id_end; ++$l) {
						$dt_tracker = $data['route'][$l][0];
						$params = $data['route'][$l][6];

						$param_value = getParamValue($params, $sensor['param']);

						if ($status == false) {
							if ($param_value == 1) {
								$activation_time = $dt_tracker;
								$status = true;
							}
						} else {
							if ($l == $id_end) {
								$deactivation_time = $dt_tracker;
								$duration += strtotime($deactivation_time) - strtotime($activation_time);
								$status = false;
							} else {
								if ($param_value == 0) {
									$duration += strtotime($deactivation_time) - strtotime($activation_time);
									$status = false;
								}
							}
						}

						$deactivation_time = $dt_tracker;
					}

					$result .= '<td>' . getTimeDetails($duration, true) . '</td>';
				}

				if (in_array("driver", $data_items)) {
					$params = $data['route'][$data['stops'][$j][1]][6];
					$driver = getObjectDriver($user_id, $imei, $params);
					if ($driver == false) {
						$driver['driver_name'] = $la['NA'];
					}

					$result .= '<td>' . $driver['driver_name'] . '</td>';
				}

				if (in_array("trailer", $data_items)) {
					$params = $data['route'][$data['stops'][$j][1]][6];
					$trailer = getObjectTrailer($user_id, $imei, $params);
					if ($trailer == false) {
						$trailer['trailer_name'] = $la['NA'];
					}

					$result .= '<td>' . $trailer['trailer_name'] . '</td>';
				}

				$result .= '</tr>';
			}
		}
		for ($j = 0; $j < count($data['drives']); ++$j) {
			if ($data['drives'][$j][4] == $dt_sort[$i]) {
				$result .= '<tr align="center">';

				if (in_array("status", $data_items)) {
					$result .= '<td>' . $la['MOVING'] . '</td>';
				}

				if (in_array("start", $data_items)) {
					$result .= '<td>' . $data['drives'][$j][4] . '</td>';
				}

				if (in_array("end", $data_items)) {
					$result .= '<td>' . $data['drives'][$j][5] . '</td>';
				}

				if (in_array("duration", $data_items)) {
					$result .= '<td>' . $data['drives'][$j][6] . '</td>';
				}

				$result .= '<td>' . $data['drives'][$j][7] . ' ' . $la["UNIT_DISTANCE"] . '</td>
							<td>' . $data['drives'][$j][8] . ' ' . $la["UNIT_SPEED"] . '</td>
							<td>' . $data['drives'][$j][9] . ' ' . $la["UNIT_SPEED"] . '</td>';

				if (in_array("fuel_consumption", $data_items)) {
					$result .= '<td>' . $data['drives'][$j][10] . ' ' . $la["UNIT_CAPACITY"] . '</td>';
				}

				if (in_array("avg_fuel_consumption", $data_items)) {
					if ($_SESSION["unit_capacity"] == 'l') {
						$result .= '<td>' . $data['drives'][$j][13] . ' ' . $la["UNIT_CAPACITY"] . '</td>';
					} else {
						$result .= '<td>' . $data['drives'][$j][14] . ' ' . $la["UNIT_MI"] . '</td>';
					}
				}

				if (in_array("fuel_cost", $data_items)) {
					$result .= '<td>' . $data['drives'][$j][11] . ' ' . $_SESSION["currency"] . '</td>';
				}

				if (in_array("engine_idle", $data_items)) {
					$result .= '<td></td>';
				}

				for ($k = 0; $k < count($sensors); ++$k) {
					$result .= '<td></td>';
				}

				if (in_array("driver", $data_items)) {
					$params = $data['route'][$data['drives'][$j][1]][6];
					$driver = getObjectDriver($user_id, $imei, $params);
					if ($driver == false) {
						$driver['driver_name'] = $la['NA'];
					}

					$result .= '<td>' . $driver['driver_name'] . '</td>';
				}

				if (in_array("trailer", $data_items)) {
					$params = $data['route'][$data['drives'][$j][1]][6];
					$trailer = getObjectTrailer($user_id, $imei, $params);
					if ($trailer == false) {
						$trailer['trailer_name'] = $la['NA'];
					}

					$result .= '<td>' . $trailer['trailer_name'] . '</td>';
				}

				$result .= '</tr>';
			}
		}
	}

	$result .= '</table><br/>';

	$result .= '<table>';

	if (in_array("move_duration", $data_items)) {
		$result .= 	'<tr>
						<td><strong>' . $la['MOVE_DURATION'] . ':</strong></td>
						<td>' . $data['drives_duration'] . '</td>
					</tr>';
	}

	if (in_array("stop_duration", $data_items)) {
		$result .= 	'<tr>
						<td><strong>' . $la['STOP_DURATION'] . ':</strong></td>
						<td>' . $data['stops_duration'] . '</td>
					</tr>';
	}

	if (in_array("route_length", $data_items)) {
		$result .= 	'<tr>
						<td><strong>' . $la['ROUTE_LENGTH'] . ':</strong></td>
						<td>' . $data['route_length'] . ' ' . $la["UNIT_DISTANCE"] . '</td>
					</tr>';
	}

	if (in_array("top_speed", $data_items)) {
		$result .= 	'<tr>
						<td><strong>' . $la['TOP_SPEED'] . ':</strong></td>
						<td>' . $data['top_speed'] . ' ' . $la["UNIT_SPEED"] . '</td>
					</tr>';
	}

	if (in_array("avg_speed", $data_items)) {
		$result .= 	'<tr>
						<td><strong>' . $la['AVG_SPEED'] . ':</strong></td>
						<td>' . $data['avg_speed'] . ' ' . $la["UNIT_SPEED"] . '</td>
					</tr>';
	}

	if (in_array("fuel_consumption", $data_items)) {
		$result .= 	'<tr>
						<td><strong>' . $la['FUEL_CONSUMPTION'] . ':</strong></td>
						<td>' . $data['fuel_consumption'] . ' ' . $la["UNIT_CAPACITY"] . '</td>
					</tr>';
	}

	if (in_array("avg_fuel_consumption", $data_items)) {
		if ($_SESSION["unit_capacity"] == 'l') {
			$result .= 	'<tr>
						<td><strong>' . $la['AVG_FUEL_CONSUMPTION_100_KM'] . ':</strong></td>
						<td>' . $data['fuel_consumption_per_100km'] . ' ' . $la["UNIT_CAPACITY"] . '</td>
					</tr>';
		} else {
			$result .= 	'<tr>
						<td><strong>' . $la['AVG_FUEL_CONSUMPTION_MPG'] . ':</strong></td>
						<td>' . $data['fuel_consumption_mpg'] . ' ' . $la["UNIT_MI"] . '</td>
					</tr>';
		}
	}

	if (in_array("fuel_cost", $data_items)) {
		$result .= 	'<tr>
						<td><strong>' . $la['FUEL_COST'] . ':</strong></td>
						<td>' . $data['fuel_cost'] . ' ' . $_SESSION["currency"] . '</td>
					</tr>';
	}

	if (in_array("engine_work", $data_items)) {
		$result .= 	'<tr>
						<td><strong>' . $la['ENGINE_WORK'] . ':</strong></td>
						<td>' . $data['engine_work'] . '</td>
					</tr>';
	}

	if (in_array("engine_idle", $data_items)) {
		$result .= 	'<tr>
						<td><strong>' . $la['ENGINE_IDLE'] . ':</strong></td>
						<td>' . $data['engine_idle'] . '</td>
					</tr>';
	}

	$result .= '</table>';

	return $result;
}
function reportsGenerateTravelSheet($imei, $dtf, $dtt, $stop_duration, $show_coordinates, $show_addresses, $zones_addresses, $data_items)
{
    global $la, $user_id;
    
    $result = '';
    $data = getRoute($user_id, $imei, $dtf, $dtt, $stop_duration, true);

    if (empty($data['drives'])) {
        return false;
    }

    $unit_capacity = $_SESSION["unit_capacity"];
    $currency = $_SESSION["currency"];

    // CABECERA
    $result .= '<table class="report" width="100%"><tr align="center">';
    $columns = [
        "time_a" => $la['TIME_A'],
        "position_a" => $la['POSITION_A'],
        "time_b" => $la['TIME_B'],
        "position_b" => $la['POSITION_B'],
        "duration" => $la['DURATION'],
        "route_length" => $la['LENGTH'],
        "fuel_consumption" => $la['FUEL_CONSUMPTION'],
        "avg_fuel_consumption" => ($unit_capacity == 'l') ? $la['AVG_FUEL_CONSUMPTION_100_KM'] : $la['AVG_FUEL_CONSUMPTION_MPG'],
        "fuel_cost" => $la['FUEL_COST'],
        "driver" => $la['DRIVER'],
        "trailer" => $la['TRAILER']
    ];
    
    foreach ($columns as $key => $label) {
        if (in_array($key, $data_items)) {
            $result .= "<th>{$label}</th>";
        }
    }
    $result .= '</tr>';

    $prev_position_b = null;

    foreach ($data['drives'] as $j => $drive) {
        $route_id_a = $drive[0];
        $route_id_b = $drive[2];
        $mid_index   = $drive[1];

        $route_a = $data['route'][$route_id_a];
        $route_b = $data['route'][$route_id_b];
        $mid_params = $data['route'][$mid_index][6];

        $lat1 = sprintf("%01.6f", $route_a[1]);
        $lng1 = sprintf("%01.6f", $route_a[2]);
        $lat2 = sprintf("%01.6f", $route_b[1]);
        $lng2 = sprintf("%01.6f", $route_b[2]);

        $time_a = $drive[4];
        $time_b = $drive[5];
        $duration = $drive[6];
        $route_length = $drive[7];

        // Posiciones con cache
        $position_a = $prev_position_b ?? reportsGetPossition($lat1, $lng1, $show_coordinates, $show_addresses, $zones_addresses);
        $position_b = reportsGetPossition($lat2, $lng2, $show_coordinates, $show_addresses, $zones_addresses);
        $prev_position_b = $position_b;

        // Combustible
        $fuel_consumption = round(($drive[10] + ($data['stops'][$j][9] ?? 0)) * 100) / 100;
        $fuel_cost = round(($drive[11] + ($data['stops'][$j][10] ?? 0)) * 100) / 100;
        $avg_fuel_consumption = ($unit_capacity == 'l') ? $drive[13] : $drive[14];

        // Conductor y remolque
        $driver_name = $trailer_name = $la['NA'];
        if (in_array("driver", $data_items)) {
            $driver = getObjectDriver($user_id, $imei, $mid_params);
            if ($driver !== false) $driver_name = $driver['driver_name'];
        }
        if (in_array("trailer", $data_items)) {
            $trailer = getObjectTrailer($user_id, $imei, $mid_params);
            if ($trailer !== false) $trailer_name = $trailer['trailer_name'];
        }

        // FILA
        $result .= '<tr align="center">';
        foreach ($data_items as $item) {
            switch ($item) {
                case "time_a": $result .= "<td>{$time_a}</td>"; break;
                case "position_a": $result .= "<td>{$position_a}</td>"; break;
                case "time_b": $result .= "<td>{$time_b}</td>"; break;
                case "position_b": $result .= "<td>{$position_b}</td>"; break;
                case "duration": $result .= "<td>{$duration}</td>"; break;
                case "route_length": $result .= "<td>{$route_length} {$la['UNIT_DISTANCE']}</td>"; break;
                case "fuel_consumption": $result .= "<td>{$fuel_consumption} {$la['UNIT_CAPACITY']}</td>"; break;
                case "avg_fuel_consumption":
                    $unit = ($unit_capacity == 'l') ? $la["UNIT_CAPACITY"] : $la["UNIT_MI"];
                    $result .= "<td>{$avg_fuel_consumption} {$unit}</td>";
                    break;
                case "fuel_cost": $result .= "<td>{$fuel_cost} {$currency}</td>"; break;
                case "driver": $result .= "<td>{$driver_name}</td>"; break;
                case "trailer": $result .= "<td>{$trailer_name}</td>"; break;
            }
        }
        $result .= '</tr>';
    }

    // TOTALES
    if (in_array("total", $data_items)) {
        $result .= '<tr align="center">';
        foreach ($data_items as $item) {
            switch ($item) {
                case "duration": $result .= "<td>{$data['drives_duration']}</td>"; break;
                case "route_length": $result .= "<td>{$data['route_length']} {$la['UNIT_DISTANCE']}</td>"; break;
                case "fuel_consumption": $result .= "<td>{$data['fuel_consumption']} {$la['UNIT_CAPACITY']}</td>"; break;
                case "avg_fuel_consumption":
                    $avg = ($unit_capacity == 'l') ? $data['fuel_consumption_per_100km'] : $data['fuel_consumption_mpg'];
                    $unit = ($unit_capacity == 'l') ? $la['UNIT_CAPACITY'] : $la['UNIT_MI'];
                    $result .= "<td>{$avg} {$unit}</td>";
                    break;
                case "fuel_cost": $result .= "<td>{$data['fuel_cost']} {$currency}</td>"; break;
                default: $result .= "<td></td>"; break;
            }
        }
        $result .= '</tr>';
    }

    $result .= '</table>';
    return $result;
}


function reportsGenerateTravelSheetDayNight($imei, $dtf, $dtt, $stop_duration, $show_coordinates, $show_addresses, $zones_addresses, $data_items, $other) //TRAVEL_SHEET_DAY_NIGHT
{
	global $la, $user_id;

	$result = '';
	$data = getRoute($user_id, $imei, $dtf, $dtt, $stop_duration, true);

	if (count($data['drives']) < 1) {
		return false;
	}

	$result = '<table class="report" width="100%"><tr align="center">';

	if (in_array("time_a", $data_items)) {
		$result .= '<th>' . $la['TIME_A'] . '</th>';
	}

	if (in_array("position_a", $data_items)) {
		$result .= '<th>' . $la['POSITION_A'] . '</th>';
	}

	if (in_array("time_b", $data_items)) {
		$result .= '<th>' . $la['TIME_B'] . '</th>';
	}

	if (in_array("position_b", $data_items)) {
		$result .= '<th>' . $la['POSITION_B'] . '</th>';
	}

	if (in_array("duration", $data_items)) {
		$result .= '<th>' . $la['DURATION'] . '</th>';
	}

	if (in_array("route_length", $data_items)) {
		$result .= '<th>' . $la['LENGTH'] . '</th>';
	}

	if (in_array("fuel_consumption", $data_items)) {
		$result .= '<th>' . $la['FUEL_CONSUMPTION'] . '</th>';
	}

	if (in_array("avg_fuel_consumption", $data_items)) {
		if ($_SESSION["unit_capacity"] == 'l') {
			$result .= '<th>' . $la['AVG_FUEL_CONSUMPTION_100_KM'] . '</th>';
		} else {
			$result .= '<th>' . $la['AVG_FUEL_CONSUMPTION_MPG'] . '</th>';
		}
	}

	if (in_array("fuel_cost", $data_items)) {
		$result .= '<th>' . $la['FUEL_COST'] . '</th>';
	}

	if (in_array("driver", $data_items)) {
		$result .= '<th>' . $la['DRIVER'] . '</th>';
	}

	if (in_array("trailer", $data_items)) {
		$result .= '<th>' . $la['TRAILER'] . '</th>';
	}

	$result .= '</tr>';

	$total_day_duration = 0;
	$total_day_route_length = 0;
	$total_day_fuel_consumption = 0;
	$total_day_avg_fuel_consumption = 0;
	$total_day_fuel_cost = 0;

	$total_night_duration = 0;
	$total_night_route_length = 0;
	$total_night_fuel_consumption = 0;
	$total_night_avg_fuel_consumption = 0;
	$total_night_fuel_cost = 0;

	for ($j = 0; $j < count($data['drives']); ++$j) {
		$route_id_a = $data['drives'][$j][0];
		$route_id_b = $data['drives'][$j][2];

		$lat1 = sprintf("%01.6f", $data['route'][$route_id_a][1]);
		$lng1 = sprintf("%01.6f", $data['route'][$route_id_a][2]);
		$lat2 = sprintf("%01.6f", $data['route'][$route_id_b][1]);
		$lng2 = sprintf("%01.6f", $data['route'][$route_id_b][2]);

		$time_a = $data['drives'][$j][4];

		$time_b = $data['drives'][$j][5];

		// day night
		$is_night = false;
		$dt_check = intval(date("Hi", strtotime($time_a)));
		$from = intval($other["dn_starts_hour"] . $other["dn_starts_minute"]);
		$to = intval($other["dn_ends_hour"] . $other["dn_ends_minute"]);

		if ($from > $to) {
			if (($from <= $dt_check) && (2400 >= $dt_check)) {
				$is_night = true;
			}

			if (($to >= $dt_check) && (0000 <= $dt_check)) {
				$is_night = true;
			}
		} else {
			if (($from <= $dt_check) && ($to >= $dt_check)) {
				$is_night = true;
			}
		}

		// this prevents double geocoder calling
		if (!isset($position_a)) {
			$position_a = reportsGetPossition($lat1, $lng1, $show_coordinates, $show_addresses, $zones_addresses);
		} else {
			$position_a = $position_b;
		}

		$position_b = reportsGetPossition($lat2, $lng2, $show_coordinates, $show_addresses, $zones_addresses);

		$duration = $data['drives'][$j][6];

		if ($is_night) {
			$diff = strtotime($time_b) - strtotime($time_a);
			$total_night_duration += $diff;
		} else {
			$diff = strtotime($time_b) - strtotime($time_a);
			$total_day_duration += $diff;
		}

		$route_length = $data['drives'][$j][7];

		if ($is_night) {
			$total_night_route_length += $route_length;
		} else {
			$total_day_route_length += $route_length;
		}

		$fuel_consumption = $data['drives'][$j][10];

		if (isset($data['stops'][$j])) {
			$fuel_consumption += $data['stops'][$j][9];
		}

		$fuel_consumption = round($fuel_consumption * 100) / 100;

		if ($is_night) {
			$total_night_fuel_consumption += $fuel_consumption;
		} else {
			$total_day_fuel_consumption += $fuel_consumption;
		}

		if ($_SESSION["unit_capacity"] == 'l') {
			$avg_fuel_consumption = $data['drives'][$j][13];
		} else {
			$avg_fuel_consumption = $data['drives'][$j][14];
		}

		if ($is_night) {
			$total_night_avg_fuel_consumption += $avg_fuel_consumption;
		} else {
			$total_day_avg_fuel_consumption += $avg_fuel_consumption;
		}

		$fuel_cost = $data['drives'][$j][11];

		if (isset($data['stops'][$j])) {
			$fuel_cost += $data['stops'][$j][10];
		}

		$fuel_cost = round($fuel_cost * 100) / 100;

		if ($is_night) {
			$total_night_fuel_cost += $fuel_cost;
		} else {
			$total_day_fuel_cost += $fuel_cost;
		}

		if ($is_night) {
			$result .= '<tr align="center" class="night">';
		} else {
			$result .= '<tr align="center">';
		}

		if (in_array("time_a", $data_items)) {
			$result .= '<td>' . $time_a . '</td>';
		}

		if (in_array("position_a", $data_items)) {
			$result .= '<td>' . $position_a . '</td>';
		}

		if (in_array("time_b", $data_items)) {
			$result .= '<td>' . $time_b . '</td>';
		}

		if (in_array("position_b", $data_items)) {
			$result .= '<td>' . $position_b . '</td>';
		}

		if (in_array("duration", $data_items)) {
			$result .= '<td>' . $duration . '</td>';
		}

		if (in_array("route_length", $data_items)) {
			$result .= '<td>' . $route_length . ' ' . $la["UNIT_DISTANCE"] . '</td>';
		}

		if (in_array("fuel_consumption", $data_items)) {
			$result .= '<td>' . $fuel_consumption . ' ' . $la["UNIT_CAPACITY"] . '</td>';
		}

		if (in_array("avg_fuel_consumption", $data_items)) {

			if ($_SESSION["unit_capacity"] == 'l') {
				$result .= '<td>' . $avg_fuel_consumption . ' ' . $la["UNIT_CAPACITY"] . '</td>';
			} else {
				$result .= '<td>' . $avg_fuel_consumption . ' ' . $la["UNIT_MI"] . '</td>';
			}
		}

		if (in_array("fuel_cost", $data_items)) {
			$result .= '<td>' . $fuel_cost . ' ' . $_SESSION["currency"] . '</td>';
		}

		if (in_array("driver", $data_items)) {
			$params = $data['route'][$data['drives'][$j][1]][6];
			$driver = getObjectDriver($user_id, $imei, $params);
			if ($driver == false) {
				$driver['driver_name'] = $la['NA'];
			}

			$result .= '<td>' . $driver['driver_name'] . '</td>';
		}

		if (in_array("trailer", $data_items)) {
			$params = $data['route'][$data['drives'][$j][1]][6];
			$trailer = getObjectTrailer($user_id, $imei, $params);
			if ($trailer == false) {
				$trailer['trailer_name'] = $la['NA'];
			}

			$result .= '<td>' . $trailer['trailer_name'] . '</td>';
		}

		$result .= '</tr>';
	}

	if (in_array("total", $data_items)) {
		$result .= '<tr align="center">';

		if (in_array("time_a", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("position_a", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("time_b", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("position_b", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("duration", $data_items)) {
			$total_day_duration = getTimeDetails($total_day_duration, true);
			$result .= '<td>' . $total_day_duration . '</td>';
		}

		if (in_array("route_length", $data_items)) {
			$result .= '<td>' . $total_day_route_length . ' ' . $la["UNIT_DISTANCE"] . '</td>';
		}

		if (in_array("fuel_consumption", $data_items)) {
			$result .= '<td>' . $total_day_fuel_consumption . ' ' . $la["UNIT_CAPACITY"] . '</td>';
		}

		if (in_array("avg_fuel_consumption", $data_items)) {
			if ($_SESSION["unit_capacity"] == 'l') {
				$result .= '<td>' . $total_day_avg_fuel_consumption . ' ' . $la["UNIT_CAPACITY"] . '</td>';
			} else {
				$result .= '<td>' . $total_day_avg_fuel_consumption . ' ' . $la["UNIT_MI"] . '</td>';
			}
		}

		if (in_array("fuel_cost", $data_items)) {
			$result .= '<td>' . $total_day_fuel_cost . ' ' . $_SESSION["currency"] . '</td>';
		}

		if (in_array("driver", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("trailer", $data_items)) {
			$result .= '<td></td>';
		}

		$result .= '</tr>';
	}

	if (in_array("total", $data_items)) {
		$result .= '<tr align="center">';

		if (in_array("time_a", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("position_a", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("time_b", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("position_b", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("duration", $data_items)) {
			$total_night_duration = getTimeDetails($total_night_duration, true);
			$result .= '<td class="night">' . $total_night_duration . '</td>';
		}

		if (in_array("route_length", $data_items)) {
			$result .= '<td class="night">' . $total_night_route_length . ' ' . $la["UNIT_DISTANCE"] . '</td>';
		}

		if (in_array("fuel_consumption", $data_items)) {
			$result .= '<td class="night">' . $total_night_fuel_consumption . ' ' . $la["UNIT_CAPACITY"] . '</td>';
		}

		if (in_array("avg_fuel_consumption", $data_items)) {
			if ($_SESSION["unit_capacity"] == 'l') {
				$result .= '<td class="night">' . $total_night_avg_fuel_consumption . ' ' . $la["UNIT_CAPACITY"] . '</td>';
			} else {
				$result .= '<td class="night">' . $total_night_avg_fuel_consumption . ' ' . $la["UNIT_MI"] . '</td>';
			}
		}

		if (in_array("fuel_cost", $data_items)) {
			$result .= '<td class="night">' . $total_night_fuel_cost . ' ' . $_SESSION["currency"] . '</td>';
		}

		if (in_array("driver", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("trailer", $data_items)) {
			$result .= '<td></td>';
		}

		$result .= '</tr>';
	}

	if (in_array("total", $data_items)) {
		$result .= '<tr align="center">';

		if (in_array("time_a", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("position_a", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("time_b", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("position_b", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("duration", $data_items)) {
			$result .= '<td>' . $data['drives_duration'] . '</td>';
		}

		if (in_array("route_length", $data_items)) {
			$result .= '<td>' . $data['route_length'] . ' ' . $la["UNIT_DISTANCE"] . '</td>';
		}

		if (in_array("fuel_consumption", $data_items)) {
			$result .= '<td>' . $data['fuel_consumption'] . ' ' . $la["UNIT_CAPACITY"] . '</td>';
		}

		if (in_array("avg_fuel_consumption", $data_items)) {
			if ($_SESSION["unit_capacity"] == 'l') {
				$result .= '<td>' . $data['fuel_consumption_per_100km'] . ' ' . $la["UNIT_CAPACITY"] . '</td>';
			} else {
				$result .= '<td>' . $data['fuel_consumption_mpg'] . ' ' . $la["UNIT_MI"] . '</td>';
			}
		}

		if (in_array("fuel_cost", $data_items)) {
			$result .= '<td>' . $data['fuel_cost'] . ' ' . $_SESSION["currency"] . '</td>';
		}

		if (in_array("driver", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("trailer", $data_items)) {
			$result .= '<td></td>';
		}

		$result .= '</tr>';
	}

	$result .= '</table>';

	return $result;
}

function reportsGenerateMileageDaily($imei, $dtf, $dtt, $data_items) //MILEAGE_DAILY
{
	global $la, $user_id;

	$result = '';

	// get date ranges
	$dates = array();
	$current = strtotime($dtf);
	$last = strtotime($dtt);

	while ($current < $last) {
		$date = gmdate('Y-m-d H:i:s', $current);

		if (count($dates) == 0) {
			$dates[] = $date;
		} else {
			$dates[] = convUserUTCTimezone(substr(convUserTimezone($date), 0, 10));
		}

		$current = strtotime('+1 day', $current);
	}

	array_push($dates, $dtt);

	$rows = '';

	$total_route_length = 0;
	$total_fuel_consumption = 0;
	$total_fuel_cost = 0;
	$total_engine_hours = 0;

	for ($i = 0; $i < count($dates) - 1; ++$i) {
		$result .= $dates[$i] . '</br>';

		$data = getRoute($user_id, $imei, $dates[$i], $dates[$i + 1], 1, true);

		if (!empty($data['route']) && is_array($data['route'])) {
			$rows .= '<tr align="center">';

			if (in_array("time", $data_items)) {
				$rows .= '<td>' . substr($data['route'][0][0], 0, 10) . '</td>';
			}

			if (in_array("start", $data_items)) {
				$rows .= '<td>' . $data['route'][0][0] . '</td>';
			}

			if (in_array("end", $data_items)) {
				$rows .= '<td>' . $data['route'][count($data['route']) - 1][0] . '</td>';
			}

			if (in_array("route_length", $data_items)) {
				$rows .= '<td>' . $data['route_length'] . ' ' . $la["UNIT_DISTANCE"] . '</td>';
				$total_route_length += $data['route_length'];
			}

			if (in_array("fuel_consumption", $data_items)) {
				$rows .= '<td>' . $data['fuel_consumption'] . ' ' . $la["UNIT_CAPACITY"] . '</td>';
				$total_fuel_consumption += $data['fuel_consumption'];
			}

			if (in_array("avg_fuel_consumption", $data_items)) {
				if ($_SESSION["unit_capacity"] == 'l') {
					$rows .= '<td>' . $data['fuel_consumption_per_km'] . ' ' . $la["UNIT_CAPACITY"] . '</td>';
				} else {
					$rows .= '<td>' . $data['fuel_consumption_mpg'] . ' ' . $la["UNIT_MI"] . '</td>';
				}
			}

			if (in_array("fuel_cost", $data_items)) {
				$rows .= '<td>' . $data['fuel_cost'] . ' ' . $_SESSION["currency"] . '</td>';
				$total_fuel_cost += $data['fuel_cost'];
			}

			if (in_array("engine_hours", $data_items)) {
				$rows .= '<td>' . getTimeDetails($data['engine_work_time'], true) . '</td>';
				$total_engine_hours += $data['engine_work_time'];
			}

			if (in_array("driver", $data_items)) {
				$params = $data['route'][0][6];
				$driver = getObjectDriver($user_id, $imei, $params);
				if ($driver == false) {
					$driver['driver_name'] = $la['NA'];
				}

				$rows .= '<td>' . $driver['driver_name'] . '</td>';
			}

			if (in_array("trailer", $data_items)) {
				$params = $data['route'][0][6];
				$trailer = getObjectTrailer($user_id, $imei, $params);
				if ($trailer == false) {
					$trailer['trailer_name'] = $la['NA'];
				}

				$rows .= '<td>' . $trailer['trailer_name'] . '</td>';
			}

			$rows .= '</tr>';
		}
	}

	if ($rows == '') {
		return false;
	} else {
		$result = '<table class="report" width="100%"><tr align="center">';

		if (in_array("time", $data_items)) {
			$result .= '<th>' . $la['TIME'] . '</th>';
		}

		if (in_array("start", $data_items)) {
			$result .= '<th>' . $la['START'] . '</th>';
		}

		if (in_array("end", $data_items)) {
			$result .= '<th>' . $la['END'] . '</th>';
		}

		if (in_array("route_length", $data_items)) {
			$result .= '<th>' . $la['LENGTH'] . '</th>';
		}

		if (in_array("fuel_consumption", $data_items)) {
			$result .= '<th>' . $la['FUEL_CONSUMPTION'] . '</th>';
		}

		if (in_array("avg_fuel_consumption", $data_items)) {
			if ($_SESSION["unit_capacity"] == 'l') {
				$result .= '<th>' . $la['AVG_FUEL_CONSUMPTION_100_KM'] . '</th>';
			} else {
				$result .= '<th>' . $la['AVG_FUEL_CONSUMPTION_MPG'] . '</th>';
			}
		}

		if (in_array("fuel_cost", $data_items)) {
			$result .= '<th>' . $la['FUEL_COST'] . '</th>';
		}

		if (in_array("engine_hours", $data_items)) {
			$result .= '<th>' . $la['ENGINE_HOURS'] . '</th>';
		}

		if (in_array("driver", $data_items)) {
			$result .= '<th>' . $la['DRIVER'] . '</th>';
		}

		if (in_array("trailer", $data_items)) {
			$result .= '<th>' . $la['TRAILER'] . '</th>';
		}

		$result .= '</tr>';

		$result .= $rows;

		if (in_array("total", $data_items)) {
			$result .= '<tr align="center">';

			if (in_array("time", $data_items)) {
				$result .= '<td></td>';
			}

			if (in_array("start", $data_items)) {
				$result .= '<td></td>';
			}

			if (in_array("end", $data_items)) {
				$result .= '<td></td>';
			}

			if (in_array("route_length", $data_items)) {
				$result .= '<td>' . $total_route_length . ' ' . $la["UNIT_DISTANCE"] . '</td>';
			}

			if (in_array("fuel_consumption", $data_items)) {
				$result .= '<td>' . $total_fuel_consumption . ' ' . $la["UNIT_CAPACITY"] . '</td>';
			}

			if (in_array("avg_fuel_consumption", $data_items)) {
				if ($_SESSION["unit_capacity"] == 'l') {
					$total_avg_fuel_consumption = 0;

					if (($total_fuel_consumption > 0) && ($total_route_length > 0)) {
						$total_avg_fuel_consumption = ($total_fuel_consumption / $total_route_length) * 100;
						$total_avg_fuel_consumption = round($total_avg_fuel_consumption * 100) / 100;
					}

					$result .= '<td>' . $total_avg_fuel_consumption . ' ' . $la["UNIT_CAPACITY"] . '</td>';
				} else {
					$total_avg_fuel_consumption = 0;

					if (($total_fuel_consumption > 0) && ($total_route_length > 0)) {
						$total_avg_fuel_consumption = ($total_route_length / $total_fuel_consumption);
						$total_avg_fuel_consumption = round($total_avg_fuel_consumption * 100) / 100;
					}

					$result .= '<td>' . $total_avg_fuel_consumption . ' ' . $la["UNIT_MI"] . '</td>';
				}
			}

			if (in_array("fuel_cost", $data_items)) {
				$result .= '<td>' . $total_fuel_cost . ' ' . $_SESSION["currency"] . '</td>';
			}

			if (in_array("engine_hours", $data_items)) {
				$result .= '<td>' . getTimeDetails($total_engine_hours, true) . '</td>';
			}

			if (in_array("driver", $data_items)) {
				$result .= '<td></td>';
			}

			if (in_array("trailer", $data_items)) {
				$result .= '<td></td>';
			}

			$result .= '</tr>';
		}

		$result .= '</table>';
	}

	return $result;
}

function reportsGenerateOverspeed($imei, $dtf, $dtt, $speed_limit, $show_coordinates, $show_addresses, $zones_addresses, $data_items) //OVERSPEED
{
	global $la, $user_id;

	$accuracy = getObjectAccuracy($imei);

	$route = getRouteRaw($imei, $accuracy, $dtf, $dtt);
	//$route = removeRouteFakeCoordinates($route, array());
	$overspeeds = getRouteOverspeeds($route, $speed_limit);

	if ((count($route) == 0) || (count($overspeeds) == 0)) {
		return false;
	}

	$result = '<table class="report" width="100%"><tr align="center">';

	if (in_array("start", $data_items)) {
		$result .= '<th>' . $la['START'] . '</th>';
	}

	if (in_array("end", $data_items)) {
		$result .= '<th>' . $la['END'] . '</th>';
	}

	if (in_array("duration", $data_items)) {
		$result .= '<th>' . $la['DURATION'] . '</th>';
	}

	if (in_array("top_speed", $data_items)) {
		$result .= '<th>' . $la['TOP_SPEED'] . '</th>';
	}

	if (in_array("avg_speed", $data_items)) {
		$result .= '<th>' . $la['AVG_SPEED'] . '</th>';
	}

	if (in_array("overspeed_position", $data_items)) {
		$result .= '<th>' . $la['OVERSPEED_POSITION'] . '</th>';
	}

	$result .= '</tr>';

	for ($i = 0; $i < count($overspeeds); ++$i) {
		$result .= '<tr align="center">';

		if (in_array("start", $data_items)) {
			$result .= '<td>' . $overspeeds[$i][0] . '</td>';
		}

		if (in_array("end", $data_items)) {
			$result .= '<td>' . $overspeeds[$i][1] . '</td>';
		}

		if (in_array("duration", $data_items)) {
			$result .= '<td>' . $overspeeds[$i][2] . '</td>';
		}

		if (in_array("top_speed", $data_items)) {
			$result .= '<td>' . $overspeeds[$i][3] . ' ' . $la["UNIT_SPEED"] . '</td>';
		}

		if (in_array("avg_speed", $data_items)) {
			$result .= '<td>' . $overspeeds[$i][4] . ' ' . $la["UNIT_SPEED"] . '</td>';
		}

		if (in_array("overspeed_position", $data_items)) {
			$result .= '<td>' . reportsGetPossition($overspeeds[$i][5], $overspeeds[$i][6], $show_coordinates, $show_addresses, $zones_addresses) . '</td>';
		}

		$result .= '</tr>';
	}

	$result .= '</table>';

	return $result;
}

function reportsGenerateKilometers($imeis, $dtf, $dtt, $speed_limit, $show_coordinates, $show_addresses, $zones_addresses, $data_items) //OVERSPEED
{
	global $ms, $_SESSION, $la, $user_id;

	$result = '<table class="report" width="100%" ><tr align="center">';

	if (in_array("object", $data_items)) {
	$result .= '<th>' . $la['OBJECT'] . '</th>';
	}

	if (in_array("distance_traveled", $data_items)) {
		$result .= '<th>' . $la['DISTANCE_TRAVELED'] . '</th>';
	}

	if (in_array("engine_hours", $data_items)) {
		$result .= '<th>' . $la['ENGINE_HOURS_DRIVER'] . '</th>';
	}

	if (in_array("engine_work", $data_items)) {
		$result .= '<th>' . $la['ENGINE_HOURS_DRIVER_STOP'] . '</th>';
	}

	if (in_array("engine_idle", $data_items)) {
		$result .= '<th>' . $la['ENGINE_IDLE_STOP'] . '</th>';
	}

	$result .= '</tr>';

	$processed_imeis = array();

	for ($i = 0; $i < count($imeis); ++$i) {
		$imei = $imeis[$i];
		if (!in_array($imei, $processed_imeis)) {
			$q = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "'";
			$r = mysqli_query($ms, $q);
			$row = mysqli_fetch_array($r);
		
			if (isset($dtf)) {
				$data = getRoute($user_id, $imei, $dtf, $dtt, 1, true);
			} else {
				$data = null;
			}
			$result .= '<tr align="center">';

			if (in_array("object", $data_items)) {
			$result .= '<td>' . $row['name'] . '</td>';
			}

			if (in_array("distance_traveled", $data_items)) {
				$result .= '<td>' . ($data['route_length'] ?? 'N/A') . ' ' . $la["UNIT_DISTANCE"] . '</td>';
			}
			
			if (in_array("engine_hours", $data_items)) {
				$result .= '<td>' . ($data['drives_duration'] ?? 'N/A') . '</td>';
			}
			
			if (in_array("engine_work", $data_items)) {
				$result .= '<td>' . ($data['engine_work'] ?? 'N/A') . '</td>';
			}
			
			if (in_array("engine_idle", $data_items)) {
				$result .= '<td>' . ($data['engine_idle'] ?? 'N/A') . '</td>';
			}			

			$result .= '</tr>';
		}
		$processed_imeis[] = $imei;
	}
	$result .= '</table>';

	return $result;
}
function reportsGenerateUnderspeed($imei, $dtf, $dtt, $speed_limit, $show_coordinates, $show_addresses, $zones_addresses, $data_items) //UNDERSPEED
{
	global $la, $user_id;

	$accuracy = getObjectAccuracy($imei);

	$route = getRouteRaw($imei, $accuracy, $dtf, $dtt);
	//$route = removeRouteFakeCoordinates($route, array());
	$underpeeds = getRouteUnderspeeds($route, $speed_limit);

	if ((count($route) == 0) || (count($underpeeds) == 0)) {
		return false;
	}

	$result = '<table class="report" width="100%"><tr align="center">';

	if (in_array("start", $data_items)) {
		$result .= '<th>' . $la['START'] . '</th>';
	}

	if (in_array("end", $data_items)) {
		$result .= '<th>' . $la['END'] . '</th>';
	}

	if (in_array("duration", $data_items)) {
		$result .= '<th>' . $la['DURATION'] . '</th>';
	}

	if (in_array("top_speed", $data_items)) {
		$result .= '<th>' . $la['TOP_SPEED'] . '</th>';
	}

	if (in_array("avg_speed", $data_items)) {
		$result .= '<th>' . $la['AVG_SPEED'] . '</th>';
	}

	if (in_array("underspeed_position", $data_items)) {
		$result .= '<th>' . $la['UNDERSPEED_POSITION'] . '</th>';
	}

	$result .= '</tr>';

	for ($i = 0; $i < count($underpeeds); ++$i) {
		$result .= '<tr align="center">';

		if (in_array("start", $data_items)) {
			$result .= '<td>' . $underpeeds[$i][0] . '</td>';
		}

		if (in_array("end", $data_items)) {
			$result .= '<td>' . $underpeeds[$i][1] . '</td>';
		}

		if (in_array("duration", $data_items)) {
			$result .= '<td>' . $underpeeds[$i][2] . '</td>';
		}

		if (in_array("top_speed", $data_items)) {
			$result .= '<td>' . $underpeeds[$i][3] . ' ' . $la["UNIT_SPEED"] . '</td>';
		}

		if (in_array("avg_speed", $data_items)) {
			$result .= '<td>' . $underpeeds[$i][4] . ' ' . $la["UNIT_SPEED"] . '</td>';
		}

		if (in_array("underspeed_position", $data_items)) {
			$result .= '<td>' . reportsGetPossition($underpeeds[$i][5], $underpeeds[$i][6], $show_coordinates, $show_addresses, $zones_addresses) . '</td>';
		}

		$result .= '</tr>';
	}

	$result .= '</table>';

	return $result;
}

function reportsGenerateZoneInOut($imei, $dtf, $dtt, $show_coordinates, $show_addresses, $zones_addresses, $zone_ids, $data_items) //ZONE_IN_OUT
{
	global $ms, $_SESSION, $la, $user_id;

	$zone_ids = explode(",", $zone_ids);

	$accuracy = getObjectAccuracy($imei);

	$route = getRouteRaw($imei, $accuracy, $dtf, $dtt);
	//$route = removeRouteFakeCoordinates($route, array());

	$q = "SELECT * FROM `gs_user_zones` WHERE `user_id`='" . $user_id . "'";
	$r = mysqli_query($ms, $q);
	$zones = array();

	while ($row = mysqli_fetch_array($r)) {
		if (in_array($row['zone_id'], $zone_ids)) {
			$zones[] = array($row['zone_id'], $row['zone_name'], $row['zone_vertices']);
		}
	}

	if ((count($route) == 0) || (count($zones) == 0)) {
		return false;
	}

	$in_zones = array();
	$in_zone = 0;
	$in_zone_route_length = 0;
	$in_zones_total = array();

	for ($i = 0; $i < count($route); ++$i) {
		$point_lat = $route[$i][1];
		$point_lng = $route[$i][2];

		for ($j = 0; $j < count($zones); ++$j) {
			$zone_id = $zones[$j][0];
			$zone_name = $zones[$j][1];
			$zone_vertices = $zones[$j][2];

			$isPointInPolygon = isPointInPolygon($zone_vertices, $point_lat, $point_lng);

			if ($isPointInPolygon) {
				if ($in_zone == 0) {
					$in_zone_start = $route[$i][0];
					$in_zone_name = $zone_name;
					$in_zone_lat = $point_lat;
					$in_zone_lng = $point_lng;
					$in_zone = $zone_id;
				}

				if (isset($prev_point_lat) && isset($prev_point_lng)) {
					$in_zone_route_length += getLengthBetweenCoordinates($prev_point_lat, $prev_point_lng, $point_lat, $point_lng);
				}
			} else {
				if ($in_zone == $zone_id) {
					$in_zone_end = $route[$i][0];
					$in_zone_duration = strtotime($in_zone_end) - strtotime($in_zone_start);

					$in_zone_route_length = convDistanceUnits($in_zone_route_length, 'km', $_SESSION["unit_distance"]);
					$in_zone_route_length = (round($in_zone_route_length * 100) / 100);

					$in_zones[] = array(
						$in_zone_start,
						$in_zone_end,
						$in_zone_duration,
						$in_zone_route_length,
						$in_zone_name,
						$in_zone_lat,
						$in_zone_lng
					);

					if (isset($in_zones_total[$zone_id])) {
						$in_zones_total[$zone_id]['duration'] += $in_zone_duration;
						$in_zones_total[$zone_id]['route_length'] += $in_zone_route_length;
					} else {
						$in_zones_total[$zone_id] = array('name' => $in_zone_name, 'duration' => $in_zone_duration, 'route_length' => $in_zone_route_length);
					}

					$in_zone = 0;
					$in_zone_route_length = 0;
				}
			}
		}

		$prev_point_lat = $point_lat;
		$prev_point_lng = $point_lng;
	}

	// add last zone record if it did not leave
	if ($in_zone != 0) {
		$in_zones[] = array(
			$in_zone_start,
			$la['NA'],
			$la['NA'],
			$la['NA'],
			$in_zone_name,
			$in_zone_lat,
			$in_zone_lng
		);
	}

	if (count($in_zones) == 0) {
		return false;
	}

	$result = '<table class="report" width="100%"><tr align="center">';

	if (in_array("zone_in", $data_items)) {
		$result .= '<th>' . $la['ZONE_IN'] . '</th>';
	}

	if (in_array("zone_out", $data_items)) {
		$result .= '<th>' . $la['ZONE_OUT'] . '</th>';
	}

	if (in_array("duration", $data_items)) {
		$result .= '<th>' . $la['DURATION'] . '</th>';
	}

	if (in_array("route_length", $data_items)) {
		$result .= '<th>' . $la['ROUTE_LENGTH'] . '</th>';
	}

	if (in_array("zone_name", $data_items)) {
		$result .= '<th>' . $la['ZONE_NAME'] . '</th>';
	}

	if (in_array("zone_position", $data_items)) {
		$result .= '<th>' . $la['ZONE_POSITION'] . '</th>';
	}

	$result .= '</tr>';

	for ($i = 0; $i < count($in_zones); ++$i) {
		$result .= '<tr align="center">';

		if (in_array("zone_in", $data_items)) {
			$result .= '<td>' . $in_zones[$i][0] . '</td>';
		}

		if (in_array("zone_out", $data_items)) {
			$result .= '<td>' . $in_zones[$i][1] . '</td>';
		}

		if (in_array("duration", $data_items)) {
			$result .= '<td>' . getTimeDetails($in_zones[$i][2], true) . '</td>';
		}

		if (in_array("route_length", $data_items)) {
			$result .= '<td>' . $in_zones[$i][3] . ' ' . $la['UNIT_DISTANCE'] . '</td>';
		}

		if (in_array("zone_name", $data_items)) {
			$result .= '<td>' . $in_zones[$i][4] . '</td>';
		}

		if (in_array("zone_position", $data_items)) {
			$result .= '<td>' . reportsGetPossition($in_zones[$i][5], $in_zones[$i][6], $show_coordinates, $show_addresses, $zones_addresses) . '</td>';
		}

		$result .= '</tr>';
	}

	$result .= '</table>';

	if (in_array("total", $data_items)) {

		$result .= '<br/>';

		usort($in_zones_total, function ($a, $b) {
			return strcmp($a["name"], $b["name"]);
		});

		$result .= '<table>';
		foreach ($in_zones_total as $key => $value) {
			$result .= '<tr>
					<td><strong>' . $value['name'] . ':</strong></td>
					<td>' . getTimeDetails($value['duration'], true) . '</td>
					<td>' . $value['route_length'] . ' ' . $la['UNIT_DISTANCE'] . '</td>
				</tr>';
		}
		$result .= '</table>';
	}

	return $result;
}

function parseDurationToSeconds($duration) {
	preg_match_all('/(\d+)\s*(h|min|s)/i', $duration, $matches, PREG_SET_ORDER);
	$seconds = 0;

	foreach ($matches as $match) {
		$value = (int)$match[1];
		$unit = strtolower($match[2]);

		if ($unit === 'h') {
			$seconds += $value * 3600;
		} elseif ($unit === 'min') {
			$seconds += $value * 60;
		} elseif ($unit === 's') {
			$seconds += $value;
		}
	}

	return $seconds;
}

function reportsGenerateZoneInOutGenInfo($imei, $dtf, $dtt, $speed_limit, $stop_duration, $show_coordinates, $show_addresses, $zones_addresses, $zone_ids, $data_items)
{
	global $ms, $_SESSION, $la, $user_id;

	$data = getRoute($user_id, $imei, $dtf, $dtt, $stop_duration, true);
	if (empty($data['route'])) return false;

	$grouped_days = [];
	foreach ($data['route'] as $point) {
		if (!isset($point[0])) continue;

		$timestamp = strtotime($point[0]);
		if (!$timestamp) continue;

		$date = gmdate('Y-m-d', $timestamp);
		$grouped_days[$date]['route'][] = $point;
	}
	foreach ($data['drives'] as $drive) {
		if (!isset($drive[4])) continue;

		$timestamp = strtotime($drive[4]);
		if (!$timestamp) continue;

		$date = gmdate('Y-m-d', $timestamp);
		$grouped_days[$date]['drives'][] = $drive;
	}
	foreach ($data['stops'] as $stop) {
		if (!isset($stop[7])) continue;

		$timestamp = strtotime($stop[7]);
		if (!$timestamp) continue;

		$date = gmdate('Y-m-d', $timestamp);
		$grouped_days[$date]['stops'][] = $stop;
	}
	$result = '';

	$object_name = getObjectName($imei);

	$result = '<p><strong>GPS:</strong> ' . $object_name . '</p>';

	$result .= '<table class="report" width="100%">';
	$result .= '<tr align="center">';
	$result .= '<th>' . $la['DATE'] . '</th>';

	$info_map_base = [
		"time_unlock"           => $la['TIME_UNLOCK'],
		"route_start"           => $la['ROUTE_START'],
		"route_end"             => $la['ROUTE_END'],
		"route_length"          => $la['ROUTE_LENGTH'],
		"move_duration"         => $la['MOVE_DURATION'],
		"stopped"               => $la['STOPPED'],
		"stop_duration_in_rute" => $la['STOP_DURATION_IN_RUTE'],
		"stop_count"            => $la['STOP_COUNT'],
		"top_speed"             => $la['TOP_SPEED'],
		"avg_speed"             => $la['AVG_SPEED'],
		"overspeed_count"       => $la['OVERSPEED_COUNT'],
		"engine_hours"          => $la['ENGINE_HOURS'],
		"driver"                => $la['DRIVER']
	];

	foreach ($info_map_base as $key => $label) {
		if (in_array($key, $data_items)) {
			$result .= '<th>' . $label . '</th>';
		}
	}
	$result .= '</tr>';

	$sum_route_length = 0;
	$sum_move_seconds = 0;
	$sum_stop_0_seconds = 0;
	$sum_stop_rest_seconds = 0;
	$sum_stop_count = 0;

	foreach ($grouped_days as $date => $day_data) {
		$route = $day_data['route'] ?? [];
		$drives = $day_data['drives'] ?? [];
		$stops = $day_data['stops'] ?? [];

		if (empty($route)) continue;

		$route_length = 0;
		foreach ($drives as $d) {
			if (isset($d[7]) && is_numeric($d[7])) {
				$route_length += $d[7];
			}
		}
		$route_length = round($route_length, 2);

		$total_stop_seconds_0 = 0;
		$total_stop_seconds_rest = 0;
		$total_drives_seconds_rest = 0;
		$engine_work_time = 0;
		$engine_idle_time = 0;

		foreach ($stops as $i => $stop) {
			$duration_str = $stop[8] ?? '0 s';
			$duration_sec = parseDurationToSeconds($duration_str);

			if ($i === 0) {
				$total_stop_seconds_0 = $duration_sec;
			} else {
				$total_stop_seconds_rest += $duration_sec;
			}
		}

		foreach ($drives as $i => $drive) {
			$duration_str = $drive[6] ?? '0 s';
			$duration_sec = parseDurationToSeconds($duration_str);
			$total_drives_seconds_rest += $duration_sec;

			if (isset($drive[12]) && is_numeric($drive[12])) {
				$engine_work_time += $drive[12];
			}
			if (isset($drive[13]) && is_numeric($drive[13])) {
				$engine_idle_time += $drive[13];
			}
		}

		$params = $route[count($route) - 1][6] ?? [];

		// Velocidades
		$total_speed = 0;
		$speed_count = 0;
		$top_speed_raw = 0;

		foreach ($drives as $d) {
			if (isset($d[8]) && is_numeric($d[8])) {
				$speed = $d[8];
				$total_speed += $speed;
				$speed_count++;
				if ($speed > $top_speed_raw) {
					$top_speed_raw = $speed;
				}
			}
		}
		$avg_speed_raw = $speed_count ? round($total_speed / $speed_count, 1) : 0;

		// Conversión final
		$route_start = !empty($drives) ? $drives[0][4] : $la['NA'];
		$route_end   = !empty($drives) ? end($drives)[5] : $la['NA'];
		$move_duration = getTimeDetails($total_drives_seconds_rest, true);
		$top_speed = $top_speed_raw . ' ' . $la["UNIT_SPEED"];
		$avg_speed = $avg_speed_raw . ' ' . $la["UNIT_SPEED"];
		$total_stop_time_0 = getTimeDetails($total_stop_seconds_0, true);
		$total_stop_time_rest = getTimeDetails($total_stop_seconds_rest, true);
		$engine_work = getTimeDetails($engine_work_time, true);
		$engine_idle = getTimeDetails($engine_idle_time, true);
		$overspeeds_count = ($speed_limit > 0) ? count(getRouteOverspeeds($route, $speed_limit)) : 0;
		$engine_hours = getObjectEngineHours($imei, true);
		$driver = getObjectDriver($user_id, $imei, $params)['driver_name'] ?? $la['NA'];
		$trailer = getObjectTrailer($user_id, $imei, $params)['trailer_name'] ?? $la['NA'];
		$hora_desbloqueo = null;

		$prev_out1 = null;
		foreach ($route as $point) {
			$timestamp = $point[0];
			$params = $point[6] ?? [];
			if (!isset($params['out1'])) continue;
			$current_out1 = (bool)$params['out1'];
			if ($prev_out1 === true && $current_out1 === false) {
				$hora_desbloqueo = $timestamp;
				break;
			}
			$prev_out1 = $current_out1 ?? null;
		}

		$info_map = [
			"time_unlock"            => $hora_desbloqueo,
			"route_start"            => $route_start,
			"route_end"              => $route_end,
			"route_length"           => $route_length . ' ' . $la["UNIT_DISTANCE"],
			"move_duration"          => $move_duration,
			"stopped"                => $total_stop_time_0,
			"stop_duration_in_rute" => $total_stop_time_rest,
			"stop_count"             => count($stops),
			"top_speed"              => $top_speed,
			"avg_speed"              => $avg_speed,
			"overspeed_count"        => $overspeeds_count,
			"fuel_consumption"       => 'N/A ' . $la["UNIT_CAPACITY"],
			"fuel_cost"              => 'N/A ' . $_SESSION["currency"],
			"engine_work"            => $engine_work,
			"engine_idle"            => $engine_idle,
			"engine_hours"           => $engine_hours,
			"driver"                 => $driver,
			"trailer"                => $trailer
		];

		// Acumuladores diarios
		$sum_route_length      += $route_length;
		$sum_move_seconds      += $total_drives_seconds_rest;
		$sum_stop_0_seconds    += $total_stop_seconds_0;
		$sum_stop_rest_seconds += $total_stop_seconds_rest;
		$sum_stop_count        += count($stops);

		// Generar fila del día
		$result .= '<tr align="center">';
		$result .= '<td>' . $date . '</td>';

		foreach ($info_map_base as $key => $label) {
			if (in_array($key, $data_items)) {
				$val = $info_map[$key];
				$result .= '<td>' . (is_numeric($val) && $val !== 'N/A' ? round($val, 2) : $val) . '</td>';
			}
		}
		$result .= '</tr>';
	}

	// ➕ Fila resumen final
	$result .= '<tr align="center" style="font-weight:bold; background:#f0f0f0;">';
	$result .= '<td>' . $la['TOTALS'] . '</td>';

	foreach ($info_map_base as $key => $label) {
		if (!in_array($key, $data_items)) continue;

		switch ($key) {
			case 'route_length':
				$result .= '<td>' . round($sum_route_length, 2) . ' ' . $la["UNIT_DISTANCE"] . '</td>';
				break;
			case 'move_duration':
				$result .= '<td>' . getTimeDetails($sum_move_seconds, true) . '</td>';
				break;
			case 'stopped':
				$result .= '<td>' . getTimeDetails($sum_stop_0_seconds, true) . '</td>';
				break;
			case 'stop_duration_in_rute':
				$result .= '<td>' . getTimeDetails($sum_stop_rest_seconds, true) . '</td>';
				break;
			case 'stop_count':
				$result .= '<td>' . $sum_stop_count . '</td>';
				break;
			default:
				$result .= '<td></td>';
		}
	}

	$result .= '</tr>';

	$result .= '</table><div style="margin-top:2px;"></div>';



	$zone_ids = array_filter(explode(',', $zone_ids));
	$q = "SELECT * FROM `gs_user_zones` WHERE `user_id`='$user_id'";
	$r = mysqli_query($ms, $q);
	$zones = [];

	while ($row = mysqli_fetch_assoc($r)) {
		if (in_array($row['zone_id'], $zone_ids)) {
			$zones[$row['zone_id']] = [
				'name' => $row['zone_name'],
				'vertices' => $row['zone_vertices']
			];
		}
	}
	// $inicio_mov = date('Y-m-d H:i:s', strtotime($data['drives'][0][4] . ' +6 hours'));

	// $route = getRouteRaw($imei, getObjectAccuracy($imei), $inicio_mov, $route_end);
	$route_ = $data['route'];
	if (empty($zones)) return false;
	
	$in_zones = [];
	$in_zones_total = [];
	$in_zone_id = null;
	$in_zone_route_length = 0;
	$was_inside = false;
	$prev_lat = $prev_lng = null;
	$last_zone_out_by_date = [];
	
	foreach ($route_ as $i => [$time, $lat, $lng]) {
		foreach ($zones as $zone_id => $zone) {
			$inside = isPointInPolygon($zone['vertices'], $lat, $lng);
			$time_ts = strtotime($time);
	
			if ($inside && (!$was_inside || $in_zone_id !== $zone_id)) {
				// Nueva entrada
				$was_inside = true;
				$in_zone_id = $zone_id;
				$in_zone_start = $time;
				$in_zone_start_ts = $time_ts;
				$in_zone_lat = $lat;
				$in_zone_lng = $lng;
				$in_zone_name = $zone['name'];
				$in_zone_route_length = 0;
	
				$fecha_actual = date('Y-m-d', $in_zone_start_ts);
				$matched_drive_start_ts = null;
	
				if (isset($last_zone_out_by_date[$fecha_actual])) {
					$matched_drive_start_ts = $last_zone_out_by_date[$fecha_actual];
				} else {
					foreach ($data['drives'] as $drive) {
						if (strpos($drive[4], $fecha_actual) === 0) {
							$matched_drive_start_ts = strtotime($drive[4]);
							break;
						}
					}
				}
	
				$drive_before_duration = $matched_drive_start_ts
					? getTimeDetails($in_zone_start_ts - $matched_drive_start_ts, true)
					: $la['NA'];
	
				$distance_before = 0;
				foreach ($data['drives'] as $drive) {
					$drive_start_ts = strtotime($drive[4]);
					if (
						date('Y-m-d', $drive_start_ts) === $fecha_actual &&
						$drive_start_ts >= $matched_drive_start_ts &&
						$drive_start_ts <= $in_zone_start_ts
					) {
						$distance_before += is_numeric($drive[7]) ? $drive[7] : 0;
					}
				}
				$distance_before = round(convDistanceUnits($distance_before, 'km', $_SESSION["unit_distance"]), 2);
			}
	
			elseif (!$inside && $was_inside && $in_zone_id === $zone_id) {
				$was_inside = false;
				$duration = $time_ts - $in_zone_start_ts; // Duración real dentro de la geocerca
				$movement = round(convDistanceUnits($in_zone_route_length, 'km', $_SESSION["unit_distance"]), 2);
			
				$in_zone_out_ts = $time_ts;
				$fecha_actual = date('Y-m-d', $in_zone_out_ts);
				$last_zone_out_by_date[$fecha_actual] = $in_zone_out_ts;
			
				$matched_drive_end_ts = null;
				$distance_after = 0;
				$next_zone_entry_ts = null;
			
				// Buscar si hay reentrada ese mismo día
				foreach ($route_ as $j => [$time_j, $lat_j, $lng_j]) {
					$entry_ts = strtotime($time_j);
					if ($entry_ts <= $in_zone_out_ts) continue;
					if (date('Y-m-d', $entry_ts) !== $fecha_actual) break;
			
					if (isPointInPolygon($zone['vertices'], $lat_j, $lng_j)) {
						$next_zone_entry_ts = $entry_ts;
						break;
					}
				}
			
				// Acumular distancia proporcional desde in_zone_out_ts
				foreach ($data['drives'] as $drive) {
					$start_ts = strtotime($drive[4]);
					$end_ts = strtotime($drive[5]);
					$drive_segment_duration = $end_ts - $start_ts;
					$km = isset($drive[7]) && is_numeric($drive[7]) ? $drive[7] : 0;
			
					if (date('Y-m-d', $start_ts) !== $fecha_actual) continue;
			
					if ($start_ts >= $in_zone_out_ts && (!$next_zone_entry_ts || $start_ts < $next_zone_entry_ts)) {
						$distance_after += $km;
						$matched_drive_end_ts = max($matched_drive_end_ts ?? 0, $end_ts);
					} elseif ($start_ts < $in_zone_out_ts && $end_ts > $in_zone_out_ts) {
						$drive_segment_partial = $end_ts - $in_zone_out_ts;
						$partial_km = ($drive_segment_partial / $drive_segment_duration) * $km;
			
						if (!$next_zone_entry_ts || $in_zone_out_ts < $next_zone_entry_ts) {
							$distance_after += $partial_km;
							$matched_drive_end_ts = max($matched_drive_end_ts ?? 0, $end_ts);
						}
					}
				}
			
				if ($matched_drive_end_ts) {
					$drive_after_duration = getTimeDetails($matched_drive_end_ts - $in_zone_out_ts, true);
					$distance_after = round(convDistanceUnits($distance_after, 'km', $_SESSION["unit_distance"]), 2);
					$drive_end_time = date('Y-m-d H:i:s', $matched_drive_end_ts);
				} else {
					$drive_after_duration = $la['NA'];
					$distance_after = $la['NA'];
					$drive_end_time = $la['NA'];
				}
			
				$in_zones[] = [
					'date' => date('Y-m-d', $in_zone_start_ts),
					'start_route' => isset($matched_drive_start_ts) ? date('Y-m-d H:i:s', $matched_drive_start_ts) : $la['NA'],
					'zone_in' => $in_zone_start,
					'zone_out' => $time,
					'time_before' => $drive_before_duration,
					'distance_before' => $distance_before . ' ' . $la["UNIT_DISTANCE"],
					'duration_in' => getTimeDetails($duration, true),
					'movement_in' => $movement . ' ' . $la["UNIT_DISTANCE"],
					'zone_name' => $in_zone_name,
					'zone_position' => reportsGetPossition($in_zone_lat, $in_zone_lng, $show_coordinates, $show_addresses, $zones_addresses),
					'time_after' => $drive_after_duration,
					'distance_after' => is_numeric($distance_after) ? $distance_after . ' ' . $la["UNIT_DISTANCE"] : $la['NA'],
					'end_route' => $drive_end_time
				];
			
				$in_zones_total[$zone_id]['duration'] = ($in_zones_total[$zone_id]['duration'] ?? 0) + $duration;
				$in_zones_total[$zone_id]['route_length'] = ($in_zones_total[$zone_id]['route_length'] ?? 0) + $movement;
			
				$in_zone_id = null;
				$in_zone_route_length = 0;
			}
			
			
		}
	
		if ($was_inside && isset($prev_lat, $prev_lng)) {
			$in_zone_route_length += getLengthBetweenCoordinates($prev_lat, $prev_lng, $lat, $lng);
		}
	
		$prev_lat = $lat;
		$prev_lng = $lng;
	}
	
	
	if (!empty($in_zones)) {
		$result .= '<br/><br/><table class="report" width="100%"><tr align="center">';
	
		$headers = [
			'Fecha', 'Geocerca', 'Inicio de Ruta', 'Entrada', 
			'Tiempo para Llegar', 'Distancia para Llegar',
			'Duracion', 'Movimiento', 'Dirección', 
			'Salida', 'Tiempo Después de Salir', 'Distancia Después de Salir', 'Fin de Ruta'
		];
		foreach ($headers as $h) {
			$result .= "<th>$h</th>";
		}
		$result .= '</tr>';
	
		foreach ($in_zones as $z) {
			$result .= '<tr align="center">';
			$result .= "<td>{$z['date']}</td>";
			$result .= "<td>{$z['zone_name']}</td>";
			$result .= "<td>{$z['start_route']}</td>";
			$result .= "<td>{$z['zone_in']}</td>";
			$result .= "<td>{$z['time_before']}</td>";
			$result .= "<td>{$z['distance_before']}</td>";
			$result .= "<td>{$z['duration_in']}</td>";
			$result .= "<td>{$z['movement_in']}</td>";
			$result .= "<td>{$z['zone_position']}</td>";
			$result .= "<td>{$z['zone_out']}</td>";
			$result .= "<td>{$z['time_after']}</td>";
			$result .= "<td>{$z['distance_after']}</td>";
			$result .= "<td>{$z['end_route']}</td>";
			$result .= '</tr>';
		}
	
		$result .= '</table>';
	}	
	
	return $result;
}

function reportsGenerateEvents($imei, $dtf, $dtt, $show_coordinates, $show_addresses, $zones_addresses, $data_items) //EVENTS
{
	global $ms, $_SESSION, $la, $user_id;

	$q = "SELECT * FROM `gs_user_events_data` WHERE `user_id`='" . $user_id . "' AND `imei`='" . $imei . "' AND dt_tracker BETWEEN '" . $dtf . "' AND '" . $dtt . "' ORDER BY dt_tracker ASC";
	$r = mysqli_query($ms, $q);
	$count = mysqli_num_rows($r);

	if ($count == 0) {
		return false;
	}

	$result = '<table class="report" width="100%"><tr align="center">';

	if (in_array("time", $data_items)) {
		$result .= '<th>' . $la['TIME'] . '</th>';
	}

	if (in_array("event", $data_items)) {
		$result .= '<th>' . $la['EVENT'] . '</th>';
	}

	if (in_array("driver", $data_items)) {
		$result .= '<th>' . $la['DRIVER'] . '</th>';
	}

	if (in_array("event_position", $data_items)) {
		$result .= '<th>' . $la['EVENT_POSITION'] . '</th>';
	}

	$result .= '</tr>';

	$total_events = array();

	while ($event_data = mysqli_fetch_array($r)) {
		$result .= '<tr align="center">';

		if (in_array("time", $data_items)) {
			$result .= '<td>' . convUserTimezone($event_data['dt_tracker']) . '</td>';
		}

		if (in_array("event", $data_items)) {
			$result .= '<td>' . $event_data['event_desc'] . '</td>';
		}

		if (in_array("driver", $data_items)) {
			$params = json_decode($event_data['params'], true);
			$driver = getObjectDriver($user_id, $imei, $params);
			if ($driver['driver_name'] == '') {
				$driver['driver_name'] = $la['NA'];
			}

			$result .= '<td>' . $driver['driver_name'] . '</td>';
		}

		if (in_array("event_position", $data_items)) {
			$result .= '<td>' . reportsGetPossition($event_data['lat'], $event_data['lng'], $show_coordinates, $show_addresses, $zones_addresses) . '</td>';
		}

		$result .= '</tr>';

		if (isset($total_events[$event_data['event_desc']])) {
			$total_events[$event_data['event_desc']]++;
		} else {
			$total_events[$event_data['event_desc']] = 1;
		}
	}

	$result .= '</table>';

	if (in_array("total", $data_items)) {
		$result .= '<br/>';

		ksort($total_events);

		$result .= '<table>';
		foreach ($total_events as $key => $value) {
			$result .= '<tr>
					<td><strong>' . $key . ':</strong></td>
					<td>' . $value . '</td>
				</tr>';
		}
		$result .= '</table>';
	}

	return $result;
}

function reportsGenerateService($imei, $data_items) //SERVICE
{
	global $ms, $_SESSION, $la, $user_id;

	$result = '';

	$q = "SELECT * FROM `gs_object_services` WHERE `imei`='" . $imei . "' ORDER BY name asc";
	$r = mysqli_query($ms, $q);
	$count = mysqli_num_rows($r);

	if ($count == 0) {
		return false;
	}

	$result = '<table class="report" width="100%"><tr align="center">';

	if (in_array("service", $data_items)) {
		$result .= '<th width="20%">' . $la['SERVICE'] . '</th>';
	}

	if (in_array("last_service", $data_items)) {
		$result .= 	'<th width="15%">' . $la['LAST_SERVICE'] . ' (' . $la["UNIT_DISTANCE"] . ')</th>
					<th width="15%">' . $la['LAST_SERVICE'] . ' (' . $la["UNIT_H"] . ')</th>
					<th width="15%">' . $la['LAST_SERVICE'] . '</th>';
	}

	if (in_array("status", $data_items)) {
		$result .= '<th width="35%">' . $la['STATUS'] . '</th>';
	}

	$result .= '</tr>';

	// get real odometer and engine hours
	$odometer = getObjectOdometer($imei);
	$odometer = floor(convDistanceUnits($odometer, 'km', $_SESSION["unit_distance"]));

	$engine_hours = getObjectEngineHours($imei, false);

	while ($row = mysqli_fetch_array($r)) {
		$service_id = $row["service_id"];
		$name = $row['name'];
		$odo_last = $la['NA'];
		$engh_last = $la['NA'];
		$days_last = $la['NA'];

		$status_arr = array();

		if ($row['odo'] == 'true') {
			$row['odo_interval'] = floor(convDistanceUnits($row['odo_interval'], 'km', $_SESSION["unit_distance"]));
			$row['odo_last'] = floor(convDistanceUnits($row['odo_last'], 'km', $_SESSION["unit_distance"]));

			$odo_diff = $odometer - $row['odo_last'];
			$odo_diff = $row['odo_interval'] - $odo_diff;

			if ($odo_diff <= 0) {
				$odo_diff = abs($odo_diff);
				$status_arr[] = '<font color="red">' . $la['ODOMETER_EXPIRED'] . ' (' . $odo_diff . ' ' . $la["UNIT_DISTANCE"] . ')</font>';
			} else {
				$status_arr[] = $la['ODOMETER_LEFT'] . ' (' . $odo_diff . ' ' . $la["UNIT_DISTANCE"] . ')';
			}

			$odo_last = $row['odo_last'];
		}

		if ($row['engh'] == 'true') {
			$engh_diff = $engine_hours - $row['engh_last'];
			$engh_diff = $row['engh_interval'] - $engh_diff;

			if ($engh_diff <= 0) {
				$engh_diff = abs($engh_diff);
				$status_arr[] = '<font color="red">' . $la['ENGINE_HOURS_EXPIRED'] . ' (' . $engh_diff . ' ' . $la["UNIT_H"] . ')</font>';
			} else {
				$status_arr[] = $la['ENGINE_HOURS_LEFT'] . ' (' . $engh_diff . ' ' . $la["UNIT_H"] . ')';
			}

			$engh_last = $row['engh_last'];
		}

		if ($row['days'] == 'true') {
			$days_diff = strtotime(gmdate("M d Y ")) - (strtotime($row['days_last']));
			$days_diff = floor($days_diff / 3600 / 24);
			$days_diff = $row['days_interval'] - $days_diff;

			if ($days_diff <= 0) {
				$days_diff = abs($days_diff);
				$status_arr[] = '<font color="red">' . $la['DAYS_EXPIRED'] . ' (' . $days_diff . ')</font>';
			} else {
				$status_arr[] = $la['DAYS_LEFT'] . ' (' . $days_diff . ')';
			}

			$days_last = $row['days_last'];
		}

		if (in_array("service", $data_items)) {
			$result .= '<tr><td>' . $name . '</td>';
		}

		if (in_array("last_service", $data_items)) {
			$result .= '<td align="center">' . $odo_last . '</td>
					<td align="center">' . $engh_last . '</td>
					<td align="center">' . $days_last . '</td>';
		}

		if (in_array("status", $data_items)) {
			$status = strtolower(implode(", ", $status_arr));
			$result .= '<td>' . $status . '</td>';
		}

		$result .= '</tr>';
	}

	$result .= '</table>';

	return $result;
}

function reportsGenerateFuelFillings($imei, $dtf, $dtt, $show_coordinates, $show_addresses, $zones_addresses, $data_items) //FUEL_FILLINGS
{
	global $la, $user_id;

	$result = '';

	$accuracy = getObjectAccuracy($imei);
	$fuel_sensors = getSensorFromType($imei, 'fuel');

	$route = getRouteRaw($imei, $accuracy, $dtf, $dtt);
	$ff = getRouteFuelFillings($route, $accuracy, $fuel_sensors);

	if ((count($route) == 0) || (count($ff['fillings']) == 0)) {
		return false;
	}

	$result = '<table class="report" width="100%"><tr align="center">';

	if (in_array("time", $data_items)) {
		$result .= '<th>' . $la['TIME'] . '</th>';
	}

	if (in_array("position", $data_items)) {
		$result .= '<th>' . $la['POSITION'] . '</th>';
	}

	if (in_array("before", $data_items)) {
		$result .= '<th>' . $la['BEFORE'] . '</th>';
	}

	if (in_array("after", $data_items)) {
		$result .= '<th>' . $la['AFTER'] . '</th>';
	}

	if (in_array("filled", $data_items)) {
		$result .= '<th>' . $la['FILLED'] . '</th>';
	}

	if (in_array("sensor", $data_items)) {
		$result .= '<th>' . $la['SENSOR'] . '</th>';
	}

	if (in_array("driver", $data_items)) {
		$result .= '<th>' . $la['DRIVER'] . '</th>';
	}

	$result .= '</tr>';

	for ($i = 0; $i < count($ff['fillings']); ++$i) {
		$lat = $ff['fillings'][$i][1];
		$lng = $ff['fillings'][$i][2];

		$params = $ff['fillings'][$i][8];
		$driver = getObjectDriver($user_id, $imei, $params);
		if ($driver['driver_name'] == '') {
			$driver['driver_name'] = $la['NA'];
		}

		$result .= '<tr align="center">';

		if (in_array("time", $data_items)) {
			$result .= '<td>' . $ff['fillings'][$i][0] . '</td>';
		}

		if (in_array("position", $data_items)) {
			$result .= '<td>' . reportsGetPossition($lat, $lng, $show_coordinates, $show_addresses, $zones_addresses) . '</td>';
		}

		if (in_array("before", $data_items)) {
			$result .= '<td>' . $ff['fillings'][$i][3] . ' ' . $ff['fillings'][$i][7] . '</td>';
		}

		if (in_array("after", $data_items)) {
			$result .= '<td>' . $ff['fillings'][$i][4] . ' ' . $ff['fillings'][$i][7] . '</td>';
		}

		if (in_array("filled", $data_items)) {
			$result .= '<td>' . $ff['fillings'][$i][5] . ' ' . $ff['fillings'][$i][7] . '</td>';
		}

		if (in_array("sensor", $data_items)) {
			$result .= '<td>' . $ff['fillings'][$i][6] . '</td>';
		}

		if (in_array("driver", $data_items)) {
			$result .= '<td>' . $driver['driver_name'] . '</td>';
		}

		$result .= '</tr>';
	}

	$result .= '</table>';

	if (in_array("total", $data_items)) {
		$result .= '<br/>';
		$result .= '<table>';
		$result .= '<tr><td><strong>' . $la['FILLED'] . ':</strong></td><td>' . $ff['total_filled'] . '</td></tr>';
		$result .= '</table>';
	}

	return $result;
}

function reportsGenerateFuelThefts($imei, $dtf, $dtt, $show_coordinates, $show_addresses, $zones_addresses, $data_items) //FUEL_THEFTS
{
	global $la, $user_id;

	$result = '';

	$accuracy = getObjectAccuracy($imei);
	$fuel_sensors = getSensorFromType($imei, 'fuel');

	$route = getRouteRaw($imei, $accuracy, $dtf, $dtt);
	$ft = getRouteFuelThefts($route, $accuracy, $fuel_sensors);

	if ((count($route) == 0) || (count($ft['thefts']) == 0)) {
		return false;
	}

	$result = '<table class="report" width="100%"><tr align="center">';

	if (in_array("time", $data_items)) {
		$result .= '<th>' . $la['TIME'] . '</th>';
	}

	if (in_array("position", $data_items)) {
		$result .= '<th>' . $la['POSITION'] . '</th>';
	}

	if (in_array("before", $data_items)) {
		$result .= '<th>' . $la['BEFORE'] . '</th>';
	}

	if (in_array("after", $data_items)) {
		$result .= '<th>' . $la['AFTER'] . '</th>';
	}

	if (in_array("stolen", $data_items)) {
		$result .= '<th>' . $la['STOLEN'] . '</th>';
	}

	if (in_array("sensor", $data_items)) {
		$result .= '<th>' . $la['SENSOR'] . '</th>';
	}

	if (in_array("driver", $data_items)) {
		$result .= '<th>' . $la['DRIVER'] . '</th>';
	}

	$result .= '</tr>';

	for ($i = 0; $i < count($ft['thefts']); ++$i) {
		$lat = $ft['thefts'][$i][1];
		$lng = $ft['thefts'][$i][2];

		if (isset($ft['thefts'][$i]) && is_array($ft['thefts'][$i]) && isset($ft['thefts'][$i][8])) {
			$params = $ft['thefts'][$i][8];
			$driver = getObjectDriver($user_id, $imei, $params);

			if ($driver && isset($driver['driver_name']) && $driver['driver_name'] == '') {
				$driver['driver_name'] = $la['NA'];
			}
		} else {
			$driver = ['driver_name' => $la['NA']];
		}


		$result .= '<tr align="center">';

		if (in_array("time", $data_items)) {
			$result .= '<td>' . $ft['thefts'][$i][0] . '</td>';
		}

		if (in_array("position", $data_items)) {
			$result .= '<td>' . reportsGetPossition($lat, $lng, $show_coordinates, $show_addresses, $zones_addresses) . '</td>';
		}

		if (in_array("before", $data_items)) {
			$result .= '<td>' . $ft['thefts'][$i][3] . ' ' . $ft['thefts'][$i][7] . '</td>';
		}

		if (in_array("after", $data_items)) {
			$result .= '<td>' . $ft['thefts'][$i][4] . ' ' . $ft['thefts'][$i][7] . '</td>';
		}

		if (in_array("stolen", $data_items)) {
			$result .= '<td>' . $ft['thefts'][$i][5] . ' ' . $ft['thefts'][$i][7] . '</td>';
		}

		if (in_array("sensor", $data_items)) {
			$result .= '<td>' . $ft['thefts'][$i][6] . '</td>';
		}

		if (in_array("driver", $data_items)) {
			if (is_array($driver) && isset($driver['driver_name'])) {
				$result .= '<td>' . $driver['driver_name'] . '</td>';
			} else {
				$result .= '<td>Sin conductor</td>';
			}
		}

		$result .= '</tr>';
	}

	$result .= '</table>';

	if (in_array("total", $data_items)) {
		$result .= '<br/>';
		$result .= '<table>';
		$result .= '<tr><td><strong>' . $la['STOLEN'] . ':</strong></td><td>' . $ft['total_stolen'] . '</td></tr>';
		$result .= '</table>';
	}

	return $result;
}

function reportsGenerateLogicSensorInfo($imei, $dtf, $dtt, $sensors, $show_coordinates, $show_addresses, $zones_addresses, $data_items) //LOGIC_SENSORS
{
	global $gsValues, $la, $user_id;

	$accuracy = getObjectAccuracy($imei);
	$route = getRouteRaw($imei, $accuracy, $dtf, $dtt);
	$lsi = getRouteLogicSensorInfo($route, $accuracy, $sensors);

	if ((count($route) == 0) || (count($lsi) == 0) || ($sensors == false)) {
		return false;
	}

	$result = '<table class="report" width="100%"><tr align="center">';

	if (in_array("sensor", $data_items)) {
		$result .= '<th>' . $la['SENSOR'] . '</th>';
	}

	if (in_array("activation_time", $data_items)) {
		$result .= '<th>' . $la['ACTIVATION_TIME'] . '</th>';
	}

	if (in_array("deactivation_time", $data_items)) {
		$result .= '<th>' . $la['DEACTIVATION_TIME'] . '</th>';
	}

	if (in_array("duration", $data_items)) {
		$result .= '<th>' . $la['DURATION'] . '</th>';
	}

	if (in_array("activation_position", $data_items)) {
		$result .= '<th>' . $la['ACTIVATION_POSITION'] . '</th>';
	}

	if (in_array("deactivation_position", $data_items)) {
		$result .= '<th>' . $la['DEACTIVATION_POSITION'] . '</th>';
	}

	$result .= '</tr>';

	for ($i = 0; $i < count($lsi); ++$i) {
		$sensor_name = $lsi[$i][0];
		$lsi_activation_time = $lsi[$i][1];
		$lsi_deactivation_time = $lsi[$i][2];
		$lsi_duration = $lsi[$i][3];
		$lsi_activation_lat = $lsi[$i][4];
		$lsi_activation_lng = $lsi[$i][5];
		$lsi_deactivation_lat = $lsi[$i][6];
		$lsi_deactivation_lng = $lsi[$i][7];

		$result .= '<tr align="center">';

		if (in_array("sensor", $data_items)) {
			$result .= '<td>' . $sensor_name . '</td>';
		}

		if (in_array("activation_time", $data_items)) {
			$result .= '<td>' . $lsi_activation_time . '</td>';
		}

		if (in_array("deactivation_time", $data_items)) {
			$result .= '<td>' . $lsi_deactivation_time . '</td>';
		}

		if (in_array("duration", $data_items)) {
			$result .= '<td>' . $lsi_duration . '</td>';
		}

		if (in_array("activation_position", $data_items)) {
			$result .= '<td>' . reportsGetPossition($lsi_activation_lat, $lsi_activation_lng, $show_coordinates, $show_addresses, $zones_addresses) . '</td>';
		}

		if (in_array("deactivation_position", $data_items)) {
			$result .= '<td>' . reportsGetPossition($lsi_deactivation_lat, $lsi_deactivation_lng, $show_coordinates, $show_addresses, $zones_addresses) . '</td>';
		}

		$result .= '</tr>';
	}

	$result .= '</table>';

	return $result;
}

function reportsGenerateRagByObject($imeis, $dtf, $dtt, $speed_limit, $data_items, $other)
{
	global $ms, $_SESSION, $la, $user_id;

	$result = '<table class="report" width="100%" ><tr align="center">';

	$result .= '<th>' . $la['DRIVER'] . '</th>';
	$result .= '<th>' . $la['OBJECT'] . '</th>';
	$result .= '<th>' . $la['ROUTE_LENGTH'] . '</th>';

	if (in_array("overspeed_score", $data_items)) {
		$result .= '<th>' . $la['OVERSPEED_DURATION'] . '</th>';
		$result .= '<th>' . $la['OVERSPEED_SCORE'] . '</th>';
	}

	if (in_array("harsh_acceleration_score", $data_items)) {
		$result .= '<th>' . $la['HARSH_ACCELERATION_COUNT'] . '</th>';
		$result .= '<th>' . $la['HARSH_ACCELERATION_SCORE'] . '</th>';
	}

	if (in_array("harsh_braking_score", $data_items)) {
		$result .= '<th>' . $la['HARSH_BRAKING_COUNT'] . '</th>';
		$result .= '<th>' . $la['HARSH_BRAKING_SCORE'] . '</th>';
	}

	if (in_array("harsh_cornering_score", $data_items)) {
		$result .= '<th>' . $la['HARSH_CORNERING_COUNT'] . '</th>';
		$result .= '<th>' . $la['HARSH_CORNERING_SCORE'] . '</th>';
	}

	$result .= '<th>' . $la['RAG'] . '</th>';
	$result .= '</tr>';

	$rag = array();

	for ($i = 0; $i < count($imeis); ++$i) {
		$imei = $imeis[$i];

		$data = getRoute($user_id, $imei, $dtf, $dtt, 1, true);
		if (!isset($data['route']) || !is_array($data['route']) || count($data['route']) == 0) {
			continue;
		}		

		$haccel_count = 0;
		$hbrake_count = 0;
		$hcorn_count = 0;

		$q = "SELECT * FROM `gs_user_events_data` WHERE `user_id`='" . $user_id . "' AND `imei`='" . $imei . "'
			AND `type`='haccel' AND dt_tracker BETWEEN '" . $dtf . "' AND '" . $dtt . "' ORDER BY dt_tracker ASC";
		$r = mysqli_query($ms, $q);

		$haccel_count = mysqli_num_rows($r);

		$q = "SELECT * FROM `gs_user_events_data` WHERE `user_id`='" . $user_id . "' AND `imei`='" . $imei . "'
			AND `type`='hbrake' AND dt_tracker BETWEEN '" . $dtf . "' AND '" . $dtt . "' ORDER BY dt_tracker ASC";
		$r = mysqli_query($ms, $q);

		$hbrake_count = mysqli_num_rows($r);

		$q = "SELECT * FROM `gs_user_events_data` WHERE `user_id`='" . $user_id . "' AND `imei`='" . $imei . "'
			AND `type`='hcorn' AND dt_tracker BETWEEN '" . $dtf . "' AND '" . $dtt . "' ORDER BY dt_tracker ASC";
		$r = mysqli_query($ms, $q);

		$hcorn_count = mysqli_num_rows($r);

		$q = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "'";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_array($r);
		$params = json_decode($row['params'], true);
		$driver = getObjectDriver($user_id, $imei, $params);

		if ($driver == false) {
			continue;
		}

		$route_length = $data['route_length'];

		$overspeed_duration = 0;
		$overspeed = 0;

		for ($j = 0; $j < count($data['route']); ++$j) {
			$speed = $data['route'][$j][5];

			if ($speed > $speed_limit) {
				if ($overspeed == 0) {
					$overspeed_start = $data['route'][$j][0];
					$overspeed = 1;
				}
			} else {
				if ($overspeed == 1) {
					$overspeed_end = $data['route'][$j][0];
					$overspeed_duration += strtotime($overspeed_end) - strtotime($overspeed_start);
					$overspeed = 0;
				}
			}
		}

		if ($route_length > 0) {
			$overspeed_score = $overspeed_duration / 10 / $route_length * 100;
			$overspeed_score = round($overspeed_score * 100) / 100;

			$haccel_score = $haccel_count / $route_length * 100;
			$haccel_score = round($haccel_score * 100) / 100;

			$hbrake_score = $hbrake_count / $route_length * 100;
			$hbrake_score = round($hbrake_score * 100) / 100;

			$hcorn_score = $hcorn_count / $route_length * 100;
			$hcorn_score = round($hcorn_score * 100) / 100;
		} else {
			$overspeed_score = 0;
			$haccel_score = 0;
			$hbrake_score = 0;
			$hcorn_score = 0;
		}

		$rag_score = 0;

		if (in_array("overspeed_score", $data_items)) {
			$rag_score += $overspeed_score;
		}

		if (in_array("harsh_acceleration_score", $data_items)) {
			$rag_score += $haccel_score;
		}

		if (in_array("harsh_braking_score", $data_items)) {
			$rag_score += $hbrake_score;
		}

		if (in_array("harsh_cornering_score", $data_items)) {
			$rag_score += $hcorn_score;
		}

		$rag_score = round($rag_score * 100) / 100;

		$rag[] = array(
			'driver_name' => $driver['driver_name'],
			'object_name' => getObjectName($imei),
			'route_length' => $route_length,
			'overspeed_duration' => $overspeed_duration,
			'overspeed_score' => $overspeed_score,
			'haccel_count' => $haccel_count,
			'haccel_score' => $haccel_score,
			'hbrake_count' => $hbrake_count,
			'hbrake_score' => $hbrake_score,
			'hcorn_count' => $hcorn_count,
			'hcorn_score' => $hcorn_score,
			'rag_score' => $rag_score
		);
	}

	usort($rag, function ($a, $b) {
		return strcmp($a["driver_name"], $b["driver_name"]);
	});

	if (count($rag) == 0) {
		$result .= '<tr><td align="center" colspan="12">' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr>';
	}

	// list all drivers
	for ($i = 0; $i < count($rag); ++$i) {
		$result .= '<tr align="center">';

		$result .= '<td>' . $rag[$i]['driver_name'] . '</td>';
		$result .= '<td>' . $rag[$i]['object_name'] . '</td>';
		$result .= '<td>' . $rag[$i]['route_length'] . ' ' . $la['UNIT_DISTANCE'] . '</td>';

		if (in_array("overspeed_score", $data_items)) {
			$result .= '<td>' . getTimeDetails($rag[$i]['overspeed_duration'], true) . '</td>';
			$result .= '<td>' . $rag[$i]['overspeed_score'] . '</td>';
		}

		if (in_array("harsh_acceleration_score", $data_items)) {
			$result .= '<td>' . $rag[$i]['haccel_count'] . '</td>';
			$result .= '<td>' . $rag[$i]['haccel_score'] . '</td>';
		}

		if (in_array("harsh_braking_score", $data_items)) {
			$result .= '<td>' . $rag[$i]['hbrake_count'] . '</td>';
			$result .= '<td>' . $rag[$i]['hbrake_score'] . '</td>';
		}

		if (in_array("harsh_cornering_score", $data_items)) {
			$result .= '<td>' . $rag[$i]['hcorn_count'] . '</td>';
			$result .= '<td>' . $rag[$i]['hcorn_score'] . '</td>';
		}

		if ($rag[$i]['rag_score'] <= $other['high_score'] / 2) {
			$rag_color = '#00FF00';
		} else if (($rag[$i]['rag_score'] > $other['high_score'] / 2) && ($rag[$i]['rag_score'] <= $other['high_score'])) {
			$rag_color = '#FFFF00';
		} else if ($rag[$i]['rag_score'] > $other['high_score']) {
			$rag_color = '#FF0000';
		}

		//if ($rag[$i]['rag_score'] <= 1)
		//{
		//	$rag_color = '#00FF00';
		//}
		//else if (($rag[$i]['rag_score'] > 1) && ($rag[$i]['rag_score'] <= 3))
		//{
		//	$rag_color = '#FFFF00';
		//}
		//else if ($rag[$i]['rag_score'] > 3)
		//{
		//	$rag_color = '#FF0000';
		//}

		$result .= '<td bgcolor="' . $rag_color . '">' . $rag[$i]['rag_score'] . '</td>';

		$result .= '</tr>';
	}

	$result .= '</table>';

	return $result;
}

function reportsGenerateRagByDriver($imeis, $dtf, $dtt, $speed_limit, $data_items, $other)
{
	global $ms, $_SESSION, $la, $user_id;

	$result = '<table class="report" width="100%" ><tr align="center">';

	$result .= '<th>' . $la['DRIVER'] . '</th>';
	$result .= '<th>' . $la['ROUTE_LENGTH'] . '</th>';

	if (in_array("overspeed_score", $data_items)) {
		$result .= '<th>' . $la['OVERSPEED_DURATION'] . '</th>';
		$result .= '<th>' . $la['OVERSPEED_SCORE'] . '</th>';
	}

	if (in_array("harsh_acceleration_score", $data_items)) {
		$result .= '<th>' . $la['HARSH_ACCELERATION_COUNT'] . '</th>';
		$result .= '<th>' . $la['HARSH_ACCELERATION_SCORE'] . '</th>';
	}

	if (in_array("harsh_braking_score", $data_items)) {
		$result .= '<th>' . $la['HARSH_BRAKING_COUNT'] . '</th>';
		$result .= '<th>' . $la['HARSH_BRAKING_SCORE'] . '</th>';
	}

	if (in_array("harsh_cornering_score", $data_items)) {
		$result .= '<th>' . $la['HARSH_CORNERING_COUNT'] . '</th>';
		$result .= '<th>' . $la['HARSH_CORNERING_SCORE'] . '</th>';
	}

	$result .= '<th>' . $la['RAG'] . '</th>';
	$result .= '</tr>';

	$driver_routes = array();
	$driver_events = array();
	$rag = array();

	for ($i = 0; $i < count($imeis); ++$i) {
		$imei = $imeis[$i];

		$sensor = getSensorFromType($imei, 'da');

		if ($sensor) {
			// routes
			$accuracy = getObjectAccuracy($imei);

			$route = getRouteRaw($imei, $accuracy, $dtf, $dtt);

			if (count($route) == 0) {
				continue;
			}

			// filter jumping cordinates
			$route = removeRouteJunkPoints($route, $accuracy);

			$driver_assign_id = false;
			$driver_assign_id_prev =  false;
			$overspeed = 0;

			for ($j = 0; $j < count($route) - 1; ++$j) {
				$sensor_data = getSensorValue($route[$j][6], $sensor[0]);
				$driver_assign_id = $sensor_data['value'];

				if (($driver_assign_id_prev == false) || ($driver_assign_id_prev == $driver_assign_id)) {
					if (!isset($driver_routes[$driver_assign_id])) {
						$driver_routes[$driver_assign_id] = array();
						$driver_routes[$driver_assign_id]['route_length'] = 0;
						$driver_routes[$driver_assign_id]['overspeed_duration'] = 0;
					}

					// route length
					$route_length = getLengthBetweenCoordinates($route[$j][1], $route[$j][2], $route[$j + 1][1], $route[$j + 1][2]);
					$route_length = convDistanceUnits($route_length, 'km', $_SESSION["unit_distance"]);

					$driver_routes[$driver_assign_id]['route_length'] += $route_length;

					// overspeeds				
					if ($route[$j][5] > $speed_limit) {
						if ($overspeed == 0) {
							$overspeed_start = $route[$j][0];
							$overspeed = 1;
						}
					} else {
						if ($overspeed == 1) {
							$overspeed_end = $route[$j][0];
							$driver_routes[$driver_assign_id]['overspeed_duration'] += strtotime($overspeed_end) - strtotime($overspeed_start);
							$overspeed = 0;
						}
					}
				}

				$driver_assign_id_prev = $driver_assign_id;
			}

			// events
			$q = "SELECT * FROM `gs_user_events_data` WHERE `user_id`='" . $user_id . "' AND `imei`='" . $imei . "' AND dt_tracker BETWEEN '" . $dtf . "' AND '" . $dtt . "' ORDER BY dt_tracker ASC";
			$r = mysqli_query($ms, $q);

			while ($event = mysqli_fetch_array($r)) {
				$params = json_decode($event['params'], true);

				$sensor_data = getSensorValue($params, $sensor[0]);
				$driver_assign_id = $sensor_data['value'];

				if (!isset($driver_events[$driver_assign_id])) {
					$driver_events[$driver_assign_id] = array();
					$driver_events[$driver_assign_id]['haccel_count'] = 0;
					$driver_events[$driver_assign_id]['hbrake_count'] = 0;
					$driver_events[$driver_assign_id]['hcorn_count'] = 0;
				}

				if ($event['type'] == 'haccel') {
					$driver_events[$driver_assign_id]['haccel_count'] += 1;
				}

				if ($event['type'] == 'hbrake') {
					$driver_events[$driver_assign_id]['hbrake_count'] += 1;
				}

				if ($event['type'] == 'hcorn') {
					$driver_events[$driver_assign_id]['hcorn_count'] += 1;
				}
			}
		}
	}

	// merge
	$rag = $driver_routes;

	foreach ($rag as $key => $value) {
		if (isset($driver_events[$key])) {
			$rag[$key] = array_merge($rag[$key], $driver_events[$key]);
		} else {
			$rag[$key]['haccel_count'] = 0;
			$rag[$key]['hbrake_count'] = 0;
			$rag[$key]['hcorn_count'] = 0;
		}
	}

	// rag
	foreach ($rag as $key => $value) {
		$q = "SELECT * FROM `gs_user_object_drivers` WHERE UPPER(`driver_assign_id`)='" . strtoupper($key) . "' AND `user_id`='" . $user_id . "'";
		$r = mysqli_query($ms, $q);
		$driver = mysqli_fetch_array($r);

		if ($driver) {
			$rag[$key]['driver_name'] = $driver['driver_name'];
		} else {
			$rag[$key]['driver_name'] = strtoupper($key);
		}

		$rag[$key]['route_length'] = sprintf('%0.2f', $rag[$key]['route_length']);

		if ($rag[$key]['route_length'] > 0) {
			$rag[$key]['overspeed_score'] = $rag[$key]['overspeed_duration'] / 10 / $rag[$key]['route_length'] * 100;
			$rag[$key]['overspeed_score'] = round($rag[$key]['overspeed_score'] * 100) / 100;

			$rag[$key]['haccel_score'] = $rag[$key]['haccel_count'] / $rag[$key]['route_length'] * 100;
			$rag[$key]['haccel_score'] = round($rag[$key]['haccel_score'] * 100) / 100;

			$rag[$key]['hbrake_score'] = $rag[$key]['hbrake_count'] / $rag[$key]['route_length'] * 100;
			$rag[$key]['hbrake_score'] = round($rag[$key]['hbrake_score'] * 100) / 100;

			$rag[$key]['hcorn_score'] = $rag[$key]['hcorn_count'] / $rag[$key]['route_length'] * 100;
			$rag[$key]['hcorn_score'] = round($rag[$key]['hcorn_score'] * 100) / 100;
		} else {
			$rag[$key]['overspeed_score'] = 0;
			$rag[$key]['haccel_score'] = 0;
			$rag[$key]['hbrake_score'] = 0;
			$rag[$key]['hcorn_score'] = 0;
		}

		$rag_score = 0;

		if (in_array("overspeed_score", $data_items)) {
			$rag_score += $rag[$key]['overspeed_score'];
		}

		if (in_array("harsh_acceleration_score", $data_items)) {
			$rag_score += $rag[$key]['haccel_score'];
		}

		if (in_array("harsh_braking_score", $data_items)) {
			$rag_score += $rag[$key]['hbrake_score'];
		}

		if (in_array("harsh_cornering_score", $data_items)) {
			$rag_score += $rag[$key]['hcorn_score'];
		}

		$rag[$key]['rag_score'] = round($rag_score * 100) / 100;
	}

	usort($rag, function ($a, $b) {
		return strcmp($a["driver_name"], $b["driver_name"]);
	});

	if (count($rag) == 0) {
		$result .= '<tr><td align="center" colspan="11">' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr>';
	}

	// list all drivers
	for ($i = 0; $i < count($rag); ++$i) {
		$result .= '<tr align="center">';

		$result .= '<td>' . $rag[$i]['driver_name'] . '</td>';
		$result .= '<td>' . $rag[$i]['route_length'] . ' ' . $la['UNIT_DISTANCE'] . '</td>';

		if (in_array("overspeed_score", $data_items)) {
			$result .= '<td>' . getTimeDetails($rag[$i]['overspeed_duration'], true) . '</td>';
			$result .= '<td>' . $rag[$i]['overspeed_score'] . '</td>';
		}

		if (in_array("harsh_acceleration_score", $data_items)) {
			$result .= '<td>' . $rag[$i]['haccel_count'] . '</td>';
			$result .= '<td>' . $rag[$i]['haccel_score'] . '</td>';
		}

		if (in_array("harsh_braking_score", $data_items)) {
			$result .= '<td>' . $rag[$i]['hbrake_count'] . '</td>';
			$result .= '<td>' . $rag[$i]['hbrake_score'] . '</td>';
		}

		if (in_array("harsh_cornering_score", $data_items)) {
			$result .= '<td>' . $rag[$i]['hcorn_count'] . '</td>';
			$result .= '<td>' . $rag[$i]['hcorn_score'] . '</td>';
		}

		if ($rag[$i]['rag_score'] <= $other['high_score'] / 2) {
			$rag_color = '#00FF00';
		} else if (($rag[$i]['rag_score'] > $other['high_score'] / 2) && ($rag[$i]['rag_score'] <= $other['high_score'])) {
			$rag_color = '#FFFF00';
		} else if ($rag[$i]['rag_score'] > $other['high_score']) {
			$rag_color = '#FF0000';
		}

		//if ($rag[$i]['rag_score'] <= 1)
		//{
		//	$rag_color = '#00FF00';
		//}
		//else if (($rag[$i]['rag_score'] > 1) && ($rag[$i]['rag_score'] <= 3))
		//{
		//	$rag_color = '#FFFF00';
		//}
		//else if ($rag[$i]['rag_score'] > 3)
		//{
		//	$rag_color = '#FF0000';
		//}

		$result .= '<td bgcolor="' . $rag_color . '">' . $rag[$i]['rag_score'] . '</td>';

		$result .= '</tr>';
	}

	$result .= '</table>';

	return $result;
}

function reportsGenerateTasks($imeis, $dtf, $dtt, $show_coordinates, $show_addresses, $zones_addresses, $data_items)
{
	global $ms, $_SESSION, $la, $user_id;

	$result = '<table class="report" width="100%" ><tr align="center">';

	$result .= '<th>' . $la['TIME'] . '</th>';

	if (in_array("name", $data_items)) {
		$result .= '<th>' . $la['NAME'] . '</th>';
	}

	if (in_array("description", $data_items)) {
		$result .= '<th>' . $la['DESCRIPTION'] . '</th>';
	}

	$result .= '<th>' . $la['OBJECT'] . '</th>';

	if (in_array("from", $data_items)) {
		$result .= '<th>' . $la['FROM'] . '</th>';
	}

	if (in_array("start_time", $data_items)) {
		$result .= '<th>' . $la['START_TIME'] . '</th>';
	}

	if (in_array("to", $data_items)) {
		$result .= '<th>' . $la['TO'] . '</th>';
	}

	if (in_array("end_time", $data_items)) {
		$result .= '<th>' . $la['END_TIME'] . '</th>';
	}

	if (in_array("priority", $data_items)) {
		$result .= '<th>' . $la['PRIORITY'] . '</th>';
	}

	if (in_array("status", $data_items)) {
		$result .= '<th>' . $la['STATUS'] . '</th>';
	}

	$result .= '</tr>';

	$imeis_str = '';
	for ($i = 0; $i < count($imeis); ++$i) {
		$imeis_str .= '"' . $imeis[$i] . '",';
	}
	$imeis_str = rtrim($imeis_str, ',');

	$q = "SELECT * FROM `gs_object_tasks` WHERE `imei` IN (" . $imeis_str . ") AND dt_task BETWEEN '" . $dtf . "' AND '" . $dtt . "' ORDER BY dt_task DESC";
	$r = mysqli_query($ms, $q);
	$count = mysqli_num_rows($r);

	if ($count == 0) {
		$result .= '<tr><td align="center" colspan="4">' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr>';
	}

	while ($row = mysqli_fetch_array($r)) {
		$dt_task = convUserTimezone($row['dt_task']);
		$name = $row['name'];
		$desc = $row['desc'];
		$imei = $row['imei'];
		$priority = $row["priority"];
		$status = $row["status"];

		$object_name = getObjectName($imei);

		$result .= '<tr align="center">';

		$result .= '<td>' . $dt_task . '</td>';

		if (in_array("name", $data_items)) {
			$result .= '<td>' . $name . '</td>';
		}

		if (in_array("description", $data_items)) {
			$result .= '<td style="max-width: 250px;">' . $desc . '</td>';
		}

		$result .= '<td>' . $object_name . '</td>';

		if (in_array("from", $data_items)) {
			$result .= '<td>' . $row["start_address"] . '</td>';
		}

		if (in_array("start_time", $data_items)) {
			$result .= '<td>' . $row["start_from_dt"] . ' - ' . $row["start_to_dt"] . '</td>';
		}

		if (in_array("to", $data_items)) {
			$result .= '<td>' . $row["end_address"] . '</td>';
		}

		if (in_array("end_time", $data_items)) {
			$result .= '<td>' . $row["end_from_dt"] . ' - ' . $row["end_to_dt"] . '</td>';
		}

		if (in_array("priority", $data_items)) {
			if ($priority == 'low') {
				$priority = $la['LOW'];
			} else if ($priority == 'normal') {
				$priority = $la['NORMAL'];
			} else if ($priority == 'high') {
				$priority = $la['HIGH'];
			}

			$result .= '<td>' . $priority . '</td>';
		}

		if (in_array("status", $data_items)) {
			if ($status == 0) {
				$status = $la['NEW'];
			} else if ($status == 1) {
				$status = $la['IN_PROGRESS'];
			} else if ($status == 2) {
				$status = $la['COMPLETED'];
			} else if ($status == 3) {
				$status = $la['FAILED'];
			} else if ($status == 4) {
				$status = $la['WITH_DELAY_2'];
			} else if ($status == 5) {
				$status = $la['WITH_DELAY_3'];
			} else if ($status == 6) {
				$status = $la['WITH_DELAY_4'];
			} else if ($status == 7) {
				$status = $la['WITH_DELAY_7'];
			}

			$result .= '<td>' . $status . '</td>';
		}

		$result .= '</tr>';
	}

	$result .= '</table>';

	return $result;
}

function reportsGenerateRiLogbook($imeis, $dtf, $dtt, $show_coordinates, $show_addresses, $zones_addresses, $data_items)
{
	global $ms, $_SESSION, $la, $user_id;

	$result = '<table class="report" width="100%" ><tr align="center">';

	$result .= '<th>' . $la['TIME'] . '</th>';
	$result .= '<th>' . $la['OBJECT'] . '</th>';

	if (in_array("group", $data_items)) {
		$result .= '<th>' . $la['GROUP'] . '</th>';
	}

	if (in_array("name", $data_items)) {
		$result .= '<th>' . $la['NAME'] . '</th>';
	}

	if (in_array("position", $data_items)) {
		$result .= '<th>' . $la['POSITION'] . '</th>';
	}

	$result .= '</tr>';

	$imeis_str = '';
	for ($i = 0; $i < count($imeis); ++$i) {
		$imeis_str .= '"' . $imeis[$i] . '",';
	}
	$imeis_str = rtrim($imeis_str, ',');

	$q = "SELECT * FROM `gs_rilogbook_data` WHERE `imei` IN (" . $imeis_str . ") AND dt_server BETWEEN '" . $dtf . "' AND '" . $dtt . "' ORDER BY dt_server DESC";
	$r = mysqli_query($ms, $q);
	$count = mysqli_num_rows($r);

	if ($count == 0) {
		$result .= '<tr><td align="center" colspan="5">' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr>';
	}

	while ($row = mysqli_fetch_array($r)) {
		$dt_tracker = convUserTimezone($row['dt_tracker']);
		$imei = $row['imei'];
		$group = $row["group"];
		$assign_id = strtoupper($row["assign_id"]);
		$lat = $row["lat"];
		$lng = $row["lng"];

		$object_name = getObjectName($imei);

		if ($group == 'da') {
			$q2 = "SELECT * FROM `gs_user_object_drivers` WHERE `user_id`='" . $user_id . "' AND `driver_assign_id`='" . $assign_id . "'";
			$r2 = mysqli_query($ms, $q2);
			$row2 = mysqli_fetch_array($r2);

			if ($row2) {
				$assign_id = $row2["driver_name"];
			}

			$group = $la['DRIVER'];
		} else if ($group == 'pa') {
			$q2 = "SELECT * FROM `gs_user_object_passengers` WHERE `user_id`='" . $user_id . "' AND `passenger_assign_id`='" . $assign_id . "'";
			$r2 = mysqli_query($ms, $q2);
			$row2 = mysqli_fetch_array($r2);

			if ($row2) {
				$assign_id = $row2["passenger_name"];
			}

			$group = $la['PASSENGER'];
		} else if ($group == 'ta') {
			$q2 = "SELECT * FROM `gs_user_object_trailers` WHERE `user_id`='" . $user_id . "' AND `trailer_assign_id`='" . $assign_id . "'";
			$r2 = mysqli_query($ms, $q2);
			$row2 = mysqli_fetch_array($r2);

			if ($row2) {
				$assign_id = $row2["trailer_name"];
			}

			$group = $la['TRAILER'];
		}

		$result .= '<tr align="center">';

		$result .= '<td>' . $dt_tracker . '</td>';
		$result .= '<td>' . $object_name . '</td>';

		if (in_array("group", $data_items)) {
			$result .= '<td>' . $group . '</td>';
		}

		if (in_array("name", $data_items)) {
			$result .= '<td>' . $assign_id . '</td>';
		}

		if (in_array("position", $data_items)) {
			$position = reportsGetPossition($lat, $lng, $show_coordinates, $show_addresses, $zones_addresses);
			$result .= '<td>' . $position . '</td>';
		}

		$result .= '</tr>';
	}

	$result .= '</table>';

	return $result;
}

function reportsGenerateDTC($imeis, $dtf, $dtt, $show_coordinates, $show_addresses, $zones_addresses, $data_items)
{
	global $ms, $_SESSION, $la, $user_id;

	$result = '<table class="report" width="100%" ><tr align="center">';

	$result .= '<th>' . $la['TIME'] . '</th>';
	$result .= '<th>' . $la['OBJECT'] . '</th>';

	if (in_array("code", $data_items)) {
		$result .= '<th>' . $la['CODE'] . '</th>';
	}

	if (in_array("position", $data_items)) {
		$result .= '<th>' . $la['POSITION'] . '</th>';
	}

	$result .= '</tr>';

	$imeis_str = '';
	for ($i = 0; $i < count($imeis); ++$i) {
		$imeis_str .= '"' . $imeis[$i] . '",';
	}
	$imeis_str = rtrim($imeis_str, ',');

	$q = "SELECT * FROM `gs_dtc_data` WHERE `imei` IN (" . $imeis_str . ") AND dt_server BETWEEN '" . $dtf . "' AND '" . $dtt . "' ORDER BY dt_server DESC";
	$r = mysqli_query($ms, $q);
	$count = mysqli_num_rows($r);

	if ($count == 0) {
		$result .= '<tr><td align="center" colspan="4">' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr>';
	}

	while ($row = mysqli_fetch_array($r)) {
		$dt_tracker = convUserTimezone($row['dt_tracker']);
		$imei = $row['imei'];
		$code = strtoupper($row["code"]);
		$lat = $row["lat"];
		$lng = $row["lng"];

		$object_name = getObjectName($imei);

		$result .= '<tr align="center">';

		$result .= '<td>' . $dt_tracker . '</td>';
		$result .= '<td>' . $object_name . '</td>';

		if (in_array("code", $data_items)) {
			$result .= '<td>' . $code . '</td>';
		}

		if (in_array("position", $data_items)) {
			$position = reportsGetPossition($lat, $lng, $show_coordinates, $show_addresses, $zones_addresses);
			$result .= '<td>' . $position . '</td>';
		}

		$result .= '</tr>';
	}

	$result .= '</table>';

	return $result;
}

function reportsGenerateExpenses($imeis, $dtf, $dtt, $data_items) //EXPENSES
{
	global $ms, $_SESSION, $la, $user_id;

	$result = '';

	$result = '<table class="report" width="100%" ><tr align="center">';

	if (in_array("date", $data_items)) {
		$result .= '<th>' . $la['DATE'] . '</th>';
	}

	if (in_array("name", $data_items)) {
		$result .= '<th>' . $la['NAME'] . '</th>';
	}

	if (in_array("object", $data_items)) {
		$result .= '<th>' . $la['OBJECT'] . '</th>';
	}

	if (in_array("quantity", $data_items)) {
		$result .= '<th>' . $la['QUANTITY'] . '</th>';
	}

	if (in_array("cost", $data_items)) {
		$result .= '<th>' . $la['COST'] . '</th>';
	}

	if (in_array("supplier", $data_items)) {
		$result .= '<th>' . $la['SUPPLIER'] . '</th>';
	}

	if (in_array("buyer", $data_items)) {
		$result .= '<th>' . $la['BUYER'] . '</th>';
	}

	if (in_array("odometer", $data_items)) {
		$result .= '<th>' . $la['ODOMETER'] . '</th>';
	}

	if (in_array("engine_hours", $data_items)) {
		$result .= '<th>' . $la['ENGINE_HOURS'] . '</th>';
	}

	if (in_array("description", $data_items)) {
		$result .= '<th>' . $la['DESCRIPTION'] . '</th>';
	}

	$result .= '</tr>';

	$imeis_str = '';
	for ($i = 0; $i < count($imeis); ++$i) {
		$imeis_str .= '"' . $imeis[$i] . '",';
	}
	$imeis_str = rtrim($imeis_str, ',');

	$q = "SELECT * FROM `gs_user_expenses` WHERE `imei` IN (" . $imeis_str . ") AND dt_expense BETWEEN '" . $dtf . "' AND '" . $dtt . "' ORDER BY dt_expense DESC";
	$r = mysqli_query($ms, $q);
	$count = mysqli_num_rows($r);

	if ($count == 0) {
		$result .= '<tr><td align="center" colspan="10">' . $la['NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST'] . '</td></tr>';
	}

	$total_quantity = 0;
	$total_cost = 0;

	while ($row = mysqli_fetch_array($r)) {
		$dt_expense = convUserTimezone($row['dt_expense']);
		$name = $row['name'];
		$imei = $row['imei'];
		$quantity = $row["quantity"];
		$cost = $quantity * $row["cost"] . ' ' . $_SESSION["currency"];
		$supplier = $row["supplier"];
		$buyer = $row["buyer"];
		$desc = $row["desc"];

		// odometer and engine hours
		$odometer = floor(convDistanceUnits($row['odometer'], 'km', $_SESSION["unit_distance"]));

		$engine_hours = floor($row['engine_hours'] / 60 / 60);

		$total_quantity += $quantity;
		$total_cost += $cost;

		$object_name = getObjectName($imei);

		$result .= '<tr align="center">';

		if (in_array("date", $data_items)) {
			$result .= '<td>' . $dt_expense . '</td>';
		}

		if (in_array("name", $data_items)) {
			$result .= '<td>' . $name . '</td>';
		}

		if (in_array("object", $data_items)) {
			$result .= '<td>' . $object_name . '</td>';
		}

		if (in_array("quantity", $data_items)) {
			$result .= '<td>' . $quantity . '</td>';
		}

		if (in_array("cost", $data_items)) {
			$result .= '<td>' . $cost . '</td>';
		}

		if (in_array("supplier", $data_items)) {
			$result .= '<td>' . $supplier . '</td>';
		}

		if (in_array("buyer", $data_items)) {
			$result .= '<td>' . $buyer . '</td>';
		}

		if (in_array("odometer", $data_items)) {
			$result .= '<td>' . $odometer . ' ' . $la["UNIT_DISTANCE"] . '</td>';
		}

		if (in_array("engine_hours", $data_items)) {
			$result .= '<td>' . $engine_hours . ' ' . $la["UNIT_H"] . '</td>';
		}

		if (in_array("description", $data_items)) {
			$result .= '<td style="max-width: 250px;">' . $desc . '</td>';
		}

		$result .= '</tr>';
	}

	if (in_array("total", $data_items)) {
		$result .= '<tr align="center">';

		if (in_array("date", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("name", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("object", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("quantity", $data_items)) {
			$result .= '<td>' . $total_quantity . '</td>';
		}

		if (in_array("cost", $data_items)) {
			$result .= '<td>' . $total_cost . ' ' . $_SESSION["currency"] . '</td>';
		}

		if (in_array("supplier", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("buyer", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("odometer", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("engine_hours", $data_items)) {
			$result .= '<td></td>';
		}

		if (in_array("description", $data_items)) {
			$result .= '<td></td>';
		}
	}

	$result .= '</table>';

	return $result;
}

function reportsGenerateGraph($imei, $dtf, $dtt, $sensors) //SENSOR GRAPH        
{
	global $gsValues, $la, $user_id;

	$result = '';

	$accuracy = getObjectAccuracy($imei);

	$route = getRouteRaw($imei, $accuracy, $dtf, $dtt);

	if ((count($route) == 0) || ($sensors == false)) {
		return false;
	}

	usort($sensors, function ($a, $b) {
		return strcmp($a["name"], $b["name"]);
	});

	// loop per sensors
	for ($i = 0; $i < count($sensors); ++$i) {
		$graph = array();
		$graph['data'] = array();
		$graph['data_index'] = array();

		// prepare graph plot id
		$graph_plot_id = $imei . '_' . $i;

		// prepare data
		$sensor = $sensors[$i];

		for ($j = 0; $j < count($route); ++$j) {
			$dt_tracker = $route[$j][0];
			$dt_tracker_timestamp = strtotime($dt_tracker) * 1000;

			if ($sensor['type'] == 'speed') {
				$value = $route[$j][5];
			} else if ($sensor['type'] == 'altitude') {
				$value = $route[$j][3];
			} else {
				if ($sensor['type'] == 'fuelsumup') {
					$data = array();
					$data['value'] = 0;
					$data['value_full'] = '';

					if (!isset($fuel_sensors)) {
						$fuel_sensors = getSensorFromType($imei, 'fuel');
					}

					for ($k = 0; $k < count($fuel_sensors); ++$k) {
						if ($fuel_sensors[$k]['result_type'] == 'value') {
							$sensor_data = getSensorValue($route[$j][6], $fuel_sensors[$k]);
							if (is_numeric($sensor_data['value']))
								$data['value'] += $sensor_data['value'];
						}
					}

					$data['value'] =  round($data['value'] * 100) / 100;

					$data['value_full'] = $sensor['units'];
					$data['value_full'] .= ' ' . $sensor['units'];
				} else {
					$data = getSensorValue($route[$j][6], $sensor);
				}

				if ($sensor['type'] == 'engh') {
					$data['value'] = $data['value'] / 60 / 60;
					$data['value'] = round($data['value'] * 100) / 100;
				}

				$value = $data['value'];
			}

			$graph['data'][] = array($dt_tracker_timestamp, $value);
			$graph['data_index'][$dt_tracker_timestamp] = $j;
		}

		// set units
		if ($sensor['type'] == 'odo') {
			$graph['units'] = $la['UNIT_DISTANCE'];
			$graph['result_type'] = $sensor['result_type'];
		} else if ($sensor['type'] == 'engh') {
			$graph['units'] = $la['UNIT_H'];
			$graph['result_type'] = $sensor['result_type'];
		} else {
			$graph['units'] = $sensor['units'];
			$graph['result_type'] = $sensor['result_type'];
		}

		$result .= '<script type="text/javascript">$(document).ready(function () {var graph = ' . json_encode($graph) . ';initGraph("' . $graph_plot_id . '", graph);})</script>';

		$result .= '<div class="graph-controls">';

		if (($sensor['type'] != 'speed') && ($sensor['type'] != 'altitude')) {
			$result .= '<div class="graph-controls-left"><b>' . $la['SENSOR'] . ':</b> ' . $sensor['name'] . '</div>';
		}

		$result .= '<div class="graph-controls-right">
					<div id="graph_label_' . $graph_plot_id . '" class="graph-label"></div>
					
					<a href="#" onclick="graphPanLeft(\'' . $graph_plot_id . '\');">
						<div class="panel-button" title="' . $la['PAN_LEFT'] . '">
							<img src="' . $gsValues['URL_ROOT'] . '/theme/images/arrow-left.svg" width="12px" border="0"/>
						</div>
					</a>
					
					<a href="#" onclick="graphPanRight(\'' . $graph_plot_id . '\');">
						<div class="panel-button" title="' . $la['PAN_RIGHT'] . '">
							<img src="' . $gsValues['URL_ROOT'] . '/theme/images/arrow-right.svg" width="12px" border="0"/>
						</div>
					</a>
					  
					<a href="#" onclick="graphZoomIn(\'' . $graph_plot_id . '\');">
						<div class="panel-button" title="' . $la['ZOOM_IN'] . '">
							<img src="' . $gsValues['URL_ROOT'] . '/theme/images/plus.svg" width="12px" border="0"/>
						</div>
					</a>
					
					<a href="#" onclick="graphZoomOut(\'' . $graph_plot_id . '\');">
						<div class="panel-button" title="' . $la['ZOOM_OUT'] . '">
							<img src="' . $gsValues['URL_ROOT'] . '/theme/images/minus.svg" width="12px" border="0"/>
						</div>
					</a>
				</div>
			</div>
			<div id="graph_plot_' . $graph_plot_id . '" style="height: 150px; width:100%;"></div>';
	}

	return $result;
}

function reportsGenerateRoutes($imei, $dtf, $dtt, $speed_limit, $stop_duration, $data_items, $stops) //ROUTES
{
	global $la, $user_id;

	$result = '';
	$data = getRoute($user_id, $imei, $dtf, $dtt, $stop_duration, true);

    if (!isset($data['route']) || !is_array($data['route']) || count($data['route']) == 0) {
		return false;
	}

	$result .= '<script type="text/javascript">
				$(document).ready(function ()
				{
					initMap("' . $imei . '");
					
					var route = transformToHistoryRoute(' . json_encode($data) . ');
					
					showRoute("' . $imei . '", route, ' . $stops . ');
				})
			</script>';

	$result .= '<div id="map_' . $imei . '" class="map"></div>';

	if ($speed_limit > 0) {
		$overspeeds = getRouteOverspeeds($data['route'], $speed_limit);
		$overspeeds_count = count($overspeeds);
	} else {
		$overspeeds_count = 0;
	}

	$odometer = getObjectOdometer($imei);
	$odometer = floor(convDistanceUnits($odometer, 'km', $_SESSION["unit_distance"]));

	$table = array();

	if (in_array("route_start", $data_items)) {
		$table[] = array('name' => $la['ROUTE_START'], 'value' => $data['route'][0][0]);
	}

	if (in_array("route_end", $data_items)) {
		$table[] = array('name' => $la['ROUTE_END'], 'value' => $data['route'][count($data['route']) - 1][0]);
	}

	if (in_array("route_length", $data_items)) {
		$table[] = array('name' => $la['ROUTE_LENGTH'], 'value' => $data['route_length'] . ' ' . $la["UNIT_DISTANCE"]);
	}

	if (in_array("move_duration", $data_items)) {
		$table[] = array('name' => $la['MOVE_DURATION'], 'value' => $data['drives_duration']);
	}

	if (in_array("stop_duration", $data_items)) {
		$table[] = array('name' => $la['STOP_DURATION'], 'value' => $data['stops_duration']);
	}

	if (in_array("stop_count", $data_items)) {
		$table[] = array('name' => $la['STOP_COUNT'], 'value' => count($data['stops']));
	}

	if (in_array("top_speed", $data_items)) {
		$table[] = array('name' => $la['TOP_SPEED'], 'value' => $data['top_speed'] . ' ' . $la["UNIT_SPEED"]);
	}

	if (in_array("avg_speed", $data_items)) {
		$table[] = array('name' => $la['AVG_SPEED'], 'value' => $data['avg_speed'] . ' ' . $la["UNIT_SPEED"]);
	}

	if (in_array("overspeed_count", $data_items)) {
		$table[] = array('name' => $la['OVERSPEED_COUNT'], 'value' => $overspeeds_count);
	}

	if (in_array("fuel_consumption", $data_items)) {
		$table[] = array('name' => $la['FUEL_CONSUMPTION'], 'value' => $data['fuel_consumption'] . ' ' . $la["UNIT_CAPACITY"]);
	}

	if (in_array("avg_fuel_consumption", $data_items)) {
		if ($_SESSION["unit_capacity"] == 'l') {
			$table[] = array('name' => $la['AVG_FUEL_CONSUMPTION_100_KM'], 'value' => $data['fuel_consumption_per_km'] . ' ' . $la["UNIT_CAPACITY"]);
		} else {
			$table[] = array('name' => $la['AVG_FUEL_CONSUMPTION_MPG'], 'value' => $data['fuel_consumption_mpg'] . ' ' . $la["UNIT_MI"]);
		}
	}

	if (in_array("fuel_cost", $data_items)) {
		$table[] = array('name' => $la['FUEL_COST'], 'value' => $data['fuel_cost'] . ' ' . $_SESSION["currency"]);
	}

	if (in_array("engine_work", $data_items)) {
		$table[] = array('name' => $la['ENGINE_WORK'], 'value' => $data['engine_work']);
	}

	if (in_array("engine_idle", $data_items)) {
		$table[] = array('name' => $la['ENGINE_IDLE'], 'value' => $data['engine_idle']);
	}

	if (in_array("odometer", $data_items)) {
		$table[] = array('name' => $la['ODOMETER'], 'value' => $odometer . ' ' . $la["UNIT_DISTANCE"]);
	}

	if (in_array("engine_hours", $data_items)) {
		$table[] = array('name' => $la['ENGINE_HOURS'], 'value' => getObjectEngineHours($imei, true));
	}

	if (in_array("driver", $data_items)) {
		$result .= '<tr>';

		$params = $data['route'][count($data['route']) - 1][6];

		$driver = getObjectDriver($user_id, $imei, $params);
		if ($driver == false) {
			$driver['driver_name'] = $la['NA'];
		}

		$table[] = array('name' => $la['DRIVER'], 'value' => $driver['driver_name']);
	}

	if (in_array("trailer", $data_items)) {
		$result .= '<tr>';

		$params = $data['route'][count($data['route']) - 1][6];
		$trailer = getObjectTrailer($user_id, $imei, $params);
		if ($trailer == false) {
			$trailer['trailer_name'] = $la['NA'];
		}

		$table[] = array('name' => $la['TRAILER'], 'value' => $trailer['trailer_name']);
	}

	$rows = '';

	$table_half_cnt = round(count($table) / 2);

	for ($i = 0; $i < $table_half_cnt; ++$i) {
		$rows .= '<tr>
				<td><strong>' . $table[$i]['name'] . ':</strong></td>
				<td>' . $table[$i]['value'] . '</td>';

		if (isset($table[$table_half_cnt + $i])) {
			$rows .= '<td><strong>' . $table[$table_half_cnt + $i]['name'] . ':</strong></td>
					<td>' . $table[$table_half_cnt + $i]['value'] . '</td>';
		}

		$rows .= '</tr>';
	}

	if ($rows != '') {
		$result .= '</br>';
		$result .= '<table>';
		$result .= $rows;
		$result .= '</table>';
	}

	return $result;
}

function reportsGenerateImageGallery($imei, $dtf, $dtt, $show_coordinates, $show_addresses, $zones_addresses, $data_items) //IMAGE_GALLERY
{
	global $ms, $gsValues, $la, $user_id;

	$result = '';

	$q = "SELECT * FROM `gs_object_img` WHERE `imei`='" . $imei . "' AND dt_server BETWEEN '" . $dtf . "' AND '" . $dtt . "'";
	$r = mysqli_query($ms, $q);
	$count = mysqli_num_rows($r);

	if ($count == 0) {
		return false;
	}

	while ($row = mysqli_fetch_array($r)) {
		$img_file = $gsValues['URL_ROOT'] . '/data/img/' . $row['img_file'];

		$result .= '<table>';
		$result .= '<tr><td colspan="2"><img style="image-orientation: from-image; height: 480px;" src="' . $img_file . '"></td></tr>';

		if (in_array("time", $data_items)) {
			$result .= '<tr>
						<td><strong>' . $la['TIME'] . ':</strong></td>
						<td>' . convUserTimezone($row['dt_tracker']) . '</td>
					</tr>';
		}

		if (in_array("position", $data_items)) {
			$result .= '<tr>
						<td><strong>' . $la['POSITION'] . ':</strong></td>
						<td>' . reportsGetPossition($row['lat'], $row['lng'], $show_coordinates, $show_addresses, $zones_addresses) . '</td>
					</tr>';
		}

		$result .= '</table>';
		$result .= '</br>';
	}

	return $result;
}

$zones_addr = array();
$zones_addr_loaded = false;

function reportsGetPossition($lat, $lng, $show_coordinates, $show_addresses, $zones_addresses)
{
	global $ms, $user_id, $zones_addr, $zones_addr_loaded;

	$lat = sprintf('%0.6f', $lat);
	$lng = sprintf('%0.6f', $lng);

	if ($show_coordinates == 'true') {
		$position = '<a href="https://maps.google.com/maps?q=' . $lat . ',' . $lng . '&t=m" target="_blank">' . $lat . ' &deg;, ' . $lng . ' &deg;</a>';
	} else {
		$position = '';
	}

	if ($zones_addresses == 'true') {
		if ($zones_addr_loaded == false) {
			$q_zones = "SELECT * FROM `gs_user_zones` WHERE `user_id`='$user_id'";
			$q_markers = "SELECT * FROM `gs_user_markers` WHERE `user_id`='$user_id'";

			$r_zones = mysqli_query($ms, $q_zones);
			$r_markers = mysqli_query($ms, $q_markers);

			$min_distance_zone = 200;
			$min_distance_marker = 200;
			$distance_marker = 200;
			$nearest_zone = null;
			$nearest_marker = null;

			while ($row = mysqli_fetch_array($r_zones)) {
				$zone_vertices = $row['zone_vertices'];
				$isPointInPolygon = isPointInPolygon($zone_vertices, $lat, $lng);

				if ($isPointInPolygon) {
					$nearest_zone = $row['zone_name'];
					$min_distance_zone = 0;
					break;
				}
			}

			while ($row = mysqli_fetch_array($r_markers)) {
				$marker_lat = $row['marker_lat'];
				$marker_lng = $row['marker_lng'];
				$distance = calculateDistance($lat, $lng, $marker_lat, $marker_lng);

				if ($distance < $min_distance_marker) {
					$distance_marker = $distance;
					$nearest_marker = $row['marker_name'];
				}
			}

			if ($min_distance_zone < $min_distance_marker) {
				$position .= ' - (Geocerca) ' . $nearest_zone;
				if ($distance_marker < $min_distance_marker) {
					$position .= ' - (Marcador) ' . $nearest_marker;
				}
			}
			if ($min_distance_zone > $distance_marker) {
				$position .= ' - (Marcador) ' . $nearest_marker;
			}
		}
	}

	if ($show_addresses == 'true') {
		$address = geocoderGetAddress($lat, $lng);

		if ($address != '') {
			if ($position == '') {
				$position = $address;
			} else {
				$position .= ' - ' . $address;
			}
		}
	}

	return $position;
}

function reportsAddReportHeader($imei, $dtf = false, $dtt = false)
{
	global $la, $user_id;

	$result = '<table>';

	if ($imei != "") {
		$result .= '<tr><td><strong>' . $la['OBJECT'] . ':</strong></td><td>' . getObjectName($imei) . '</td></tr>';
	}

	if (($dtf != false) && ($dtt != false)) {
		$result .= '<tr><td><strong>' . $la['PERIOD'] . ':</strong></td><td>' . $dtf . ' - ' . $dtt . '</td></tr>';
	}

	$result .= '</table><br/>';

	return $result;
}

function reportsAddReportHeaderKilometers($dtf = false, $dtt = false)
{
	global $la, $user_id;

	$result = '<table>';


	if (($dtf != false) && ($dtt != false)) {
		$result .= '<tr><td><strong>' . $la['PERIOD'] . ':</strong></td><td>' . $dtf . ' - ' . $dtt . '</td></tr>';
	}

	$result .= '</table><br/>';

	return $result;
}

function reportsAddHeaderStart($format)
{
	global $ms, $gsValues;

	$result = '';

	if (($format == 'html') || ($format == 'pdf')) {
		$result = 	'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
						<html>
						<head>
						<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
						<title>' . $gsValues['NAME'] . ' ' . $gsValues['VERSION'] . '</title>';

		if (file_exists('../favicon.png')) {
			$result .= '<link rel="shortcut icon" href="' . $gsValues['URL_ROOT'] . '/favicon.png" type="image/x-icon">';
		} else {
			$result .= '<link rel="shortcut icon" href="' . $gsValues['URL_ROOT'] . '/favicon.ico" type="image/x-icon">';
		}
	} else if ($format == 'xls') {
		$result = 	'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
						<html xmlns="http://www.w3.org/1999/xhtml">
						<head>
						<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
						<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7">
						<title></title>';
	}

	return $result;
}

function reportsAddHeaderEnd()
{
	$result = '</head><body>';

	return $result;
}

function reportsAddStyle($type, $format)
{
	global $gsValues;

	$result = "<style type='text/css'>";

	if ($format == 'html') {
		$result .= "@import url(https://fonts.googleapis.com/css?family=Open+Sans:400,600,300,700&subset=latin,greek,greek-ext,cyrillic,cyrillic-ext,latin-ext,vietnamese);
				
				html, body {
					text-align: left; 
					margin: 10px;
					padding: 0px;
					font-size: 11px;
					font-family: 'open sans';
					color: #444444;
				}";
	} else if ($format == 'pdf') {
		$result .= "	html, body {
					text-align: left; 
					margin: 10px;
					padding: 0px;
					font-size: 11px;
					font-family: 'DejaVu Sans';
					color: #444444;
				}";
	} else if ($format == 'xls') {
		$result .= "	html, body {
					text-align: left; 
					margin: 10px;
					padding: 0px;
					font-size: 11px;
					color: #444444;
				}";
	}

	$result .= ".logo { border:0px; width:250px; height:56px; }
		
				h3 { 
					font-size: 13px;
					font-weight: 600;
				}
				
				hr {
					border-color: #eeeeee;
					border-style: solid none none;
					border-width: 1px 0 0;
					height: 1px;
					margin-left: 1px;
					margin-right: 1px;
				}
				
				a,
				a:hover { text-decoration: none; color: #2b82d4; }
				b, strong{ font-weight: 600; }
				
				.graph-controls
				{
					margin-bottom: 10px;
					display: table;
					width: 100%;
				}
				.graph-controls div
				{
					display: inline-block;
					vertical-align: middle;
					font-size: 11px;
				}
				.graph-controls-left
				{
					float: left;
					margin-top: 5px;
				}
				.graph-controls-right
				{
					float: right;
				}
				.graph-label
				{
					line-height: 24px;
					margin-right: 5px;
				}
				.panel-button img {
					display: block;
					padding: 6px;
					background: #f5f5f5;
				}				
				.panel-button img:hover {
					background: #ffffff;
				}
				
				caption,
				th,
				td { vertical-align: middle; }
				
				table.report {
					border: 1px solid #eeeeee;
					border-collapse: collapse;
				}
				
				table.report th {
					font-weight: 600;
					padding: 2px;
					border: 1px solid #eeeeee;
					background-color: #eeeeee;
				}
				
				table.report td {
					padding: 2px;
					border: 1px solid #eeeeee;
				}
				
				table.report td.night {
					background-color: #f5f5f5;
				}
				
				table.report tr.night {
					background-color: #f5f5f5;
				}
				
				table.report tr:hover { background-color: #f8f8f8; }
				
				td { mso-number-format:'\@';/*force text*/ }
				
			</style>";


	if (($type == 'routes') || ($type == 'routes_stops')) {
		$result .= '<link type="text/css" href="' . $gsValues['URL_ROOT'] . '/theme/leaflet/leaflet.css" rel="Stylesheet" />';

		$result .= "<style type='text/css'>			
					.map {
						width: 640px;
						height: 480px;
					}
				</style>";
	}

	return $result;
}

function reportsAddJS($type)
{
	global $gsValues;

	$result = '';

	if (($type == 'speed_graph') || ($type == 'altitude_graph') || ($type == 'acc_graph') || ($type == 'fuellevel_graph') || ($type == 'temperature_graph') || ($type == 'sensor_graph')) {
		$result .= '<script type="text/javascript" src="' . $gsValues['URL_ROOT'] . '/js/jquery-2.1.4.min.js"></script>
				<script type="text/javascript" src="' . $gsValues['URL_ROOT'] . '/js/jquery-migrate-1.2.1.min.js"></script>
				<script type="text/javascript" src="' . $gsValues['URL_ROOT'] . '/js/jquery.flot.min.js"></script>
				<script type="text/javascript" src="' . $gsValues['URL_ROOT'] . '/js/jquery.flot.crosshair.min.js"></script>
				<script type="text/javascript" src="' . $gsValues['URL_ROOT'] . '/js/jquery.flot.navigate.min.js"></script>
				<script type="text/javascript" src="' . $gsValues['URL_ROOT'] . '/js/jquery.flot.selection.min.js"></script>
				<script type="text/javascript" src="' . $gsValues['URL_ROOT'] . '/js/jquery.flot.time.min.js"></script>
				<script type="text/javascript" src="' . $gsValues['URL_ROOT'] . '/js/jquery.flot.resize.min.js"></script>
				<script type="text/javascript" src="' . $gsValues['URL_ROOT'] . '/js/gs.common.js"></script>
					
				<script type="text/javascript">
					var graphPlot = new Array();
					
					function initGraph(id, graph)
					{
						if (!graph)
						{
							var data = []; // if no data, just create array for empty graph
							var units = "";
							var steps_flag = false;
							var points_flag = false;
						} 
						else
						{
							var data = graph["data"];
							var units = graph["units"];
							
							if (graph["result_type"] == "logic")
							{
								var steps_flag = true;
								var points_flag = false;
							}
							else
							{
								var steps_flag = false;
								var points_flag = false;
							}
						}
						
						var minzoomRange = 30000;//	min zoom in is within 1 minute range (1*60*1000 = 60000)
						var maxzoomRange = 30 * 86400000;//	max zoom out is 5 times greater then chosen period (default is equal to 30 days 30 * 24*60*60*1000 = 86400000 )
						
						var options = {
							xaxis: {
								mode: "time", 
								zoomRange: [minzoomRange, maxzoomRange]
								},
							yaxis: {
								//min: 0, 
								tickFormatter: function (v) {
										var result = "";
										if (graph)
										{
											result = Math.round(v * 100)/100  + " " + units;
										}
										return result;
									}, 
								zoomRange: [0, 0], 
								panRange: false
								},
							selection: {mode: "x"},
							crosshair: {mode: "x"},
							lines: {show: true, lineWidth: 1, fill: true, fillColor: "rgba(43,130,212,0.3)", steps: steps_flag},
							series: {lines: {show: true} , points: { show: points_flag, radius: 1 }},
							colors: ["#2b82d4"],
							grid: {hoverable: true, autoHighlight: true, clickable: true},
							zoom: {
								//interactive: true,
								animate: true,
								trigger: "dblclick", // or "click" for single click
								amount: 3         // 2 = 200% (zoom in), 0.5 = 50% (zoom out)
							},
							pan: {interactive: false, animate: true}
						};
						
						graphPlot[id] = $.plot($("#graph_plot_"+id), [data], options);
					
						$("#graph_plot_"+id).unbind("plothover");
						$("#graph_plot_"+id).bind("plothover", function (event, pos, item) {
							if (item)
							{
								var dt_tracker = getDatetimeFromTimestamp(item.datapoint[0]);
								
								var value = item.datapoint[1];
								document.getElementById("graph_label_"+id).innerHTML = value + " " + units + " - " + dt_tracker;			
							}
						});
						
						$("#graph_plot_"+id).unbind("plotpan");
						$("#graph_plot_"+id).bind("plotpan", function (event, plot) {							
							var scrollTop = document.documentElement.scrollTop;							
							setTimeout(function(){
								document.documentElement.scrollTop = scrollTop;
							}, 1);
						});
						
						$("#graph_plot_"+id).unbind("plotzoom");
						$("#graph_plot_"+id).bind("plotzoom", function (event, plot) {							
							var scrollTop = document.documentElement.scrollTop;
							
							setTimeout(function(){
								document.documentElement.scrollTop = scrollTop;
							}, 1);
						});
						
						$("#graph_plot_"+id).unbind("plotselected");
						$("#graph_plot_"+id).bind("plotselected", function (event, ranges) {
							graphPlot[id] = $.plot($("#graph_plot_"+id), 
							[data],
							$.extend(true, {}, options, {
								xaxis: { min: ranges.xaxis.from, max: ranges.xaxis.to }
							}));
							
							// dont fire event on the overview to prevent eternal loop
							overview.setSelection(ranges, true);
						});
					}
					function graphPanLeft(id)
					{						
						graphPlot[id].pan({left: -100});						
					}
					
					function graphPanRight(id)
					{
						graphPlot[id].pan({left: +100});
					}
					
					function graphZoomIn(id)
					{
						graphPlot[id].zoom();
					}
					
					function graphZoomOut(id)
					{
						graphPlot[id].zoomOut();
					}
				</script>';
	} else if (($type == 'routes') || ($type == 'routes_stops')) {
		$result .= '<script type="text/javascript" src="' . $gsValues['URL_ROOT'] . '/js/jquery-2.1.4.min.js"></script>
				<script type="text/javascript" src="' . $gsValues['URL_ROOT'] . '/js/leaflet/leaflet.js"></script>
				<script type="text/javascript" src="' . $gsValues['URL_ROOT'] . '/js/gs.common.js"></script>
				
				<script type="text/javascript">				
					var map = new Array();
					var zoom = 1;
					var icon_size_x = 28 * zoom;
					var icon_size_y = 28 * zoom;
					var icon_anc_x = 14 * zoom;
					var icon_anc_y = 28 * zoom;
					
					var routeStartMarkerIcon = L.icon({
						iconUrl: "' . $gsValues['URL_ROOT'] . '/img/markers/route-start.svg",
						iconSize:     [icon_size_x, icon_size_y],
						iconAnchor:   [icon_anc_x, icon_anc_y],
						popupAnchor:  [0, 0]
					});
					    
					var routeEndMarkerIcon = L.icon({
						iconUrl: "' . $gsValues['URL_ROOT'] . '/img/markers/route-end.svg",
						iconSize:     [icon_size_x, icon_size_y],
						iconAnchor:   [icon_anc_x, icon_anc_y],
						popupAnchor:  [0, 0]
					});
					
					var routeStopMarkerIcon = L.icon({
						iconUrl: "' . $gsValues['URL_ROOT'] . '/img/markers/route-stop.svg",
						iconSize:     [icon_size_x, icon_size_y],
						iconAnchor:   [icon_anc_x, icon_anc_y],
						popupAnchor:  [0, 0]
					});
					
					function initMap(imei)
					{
						map[imei] = L.map("map_"+imei, {minZoom: 3, maxZoom: 18, editable: false, zoomControl: false});
						
						var mapLayer = new L.TileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {attribution: "&copy; <a href=\"http://osm.org/copyright\">OpenStreetMap</a> contributors"});
						
						map[imei].addLayer(mapLayer);
					}
					
					function showRoute(imei, data, stops)
					{
						// prepare points
						var route_points = new Array();
						for (i = 0; i<data["route"].length; i++)
						{
							var lat = data["route"][i]["lat"];
							var lng = data["route"][i]["lng"];
							
							route_points.push(L.latLng(lat, lng));
						}
						
						// draw route
						var routeLayer = L.polyline(route_points, {color: "' . $_SESSION['map_rc'] . '", opacity: 0.8, weight: 3});
						map[imei].addLayer(routeLayer);
						
						// add route start marker
						var lng = data["route"][0]["lng"];
						var lat = data["route"][0]["lat"];						
						var routeStartMarker = L.marker([lat, lng], {icon: routeStartMarkerIcon});						
						map[imei].addLayer(routeStartMarker);
						
						// add route end marker
						var lng = data["route"][data["route"].length-1]["lng"];
						var lat = data["route"][data["route"].length-1]["lat"];						
						var routeEndMarker = L.marker([lat, lng], {icon: routeEndMarkerIcon});						
						map[imei].addLayer(routeEndMarker);
						
						// put stop markers
						if (stops)
						{
							for (i=0;i<data["stops"].length;i++)
							{
								var lng = data["stops"][i]["lng"];
								var lat = data["stops"][i]["lat"];
								
								var routeStopMarker = L.marker([lat, lng], {icon: routeStopMarkerIcon});
								
								map[imei].addLayer(routeStopMarker);
							}	
						}
						
						// zoom to fit route
						var bounds = routeLayer.getBounds();
						map[imei].fitBounds(bounds);
					}
				</script>';
	}

	return $result;
}
