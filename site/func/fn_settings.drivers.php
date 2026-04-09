<?
session_start();
include('../init.php');
include('fn_common.php');
checkUserSession();

loadLanguage($_SESSION["language"], $_SESSION["units"]);

// check privileges
if ($_SESSION["privileges"] == 'subuser') {
	$user_id = $_SESSION["manager_id"];
} else {
	$user_id = $_SESSION["user_id"];
}

if (@$_POST['cmd'] == 'delete_object_driver') {
	$driver_id = $_POST["driver_id"];

	$q = "SELECT * FROM `gs_user_object_drivers` WHERE `driver_id`='" . $driver_id . "'";
	$r = mysqli_query($ms, $q);
	$row = mysqli_fetch_array($r);
	$driver_name = $row['driver_name'];

	$image_paths = [
		'data/user/drivers/' . $row['driver_img_file'],
		'data/user/licence/' . $row['licence_img_file'],
		'data/user/id/' . $row['id_img_file'],
		'data/user/nss/' . $row['nss_img_file'],
		'data/user/otro/' . $row['otro_img_file']
	];

	foreach ($image_paths as $path) {
		$img_file = $gsValues['PATH_ROOT'] . $path;
		if (is_file($img_file)) {
			@unlink($img_file);
		}
	}

	$q = "DELETE FROM `gs_user_object_drivers` WHERE `driver_id`='" . $driver_id . "'";
	$r = mysqli_query($ms, $q);


	addRowBinnacle($_SESSION["user_id"], 'Baja de conductor: ' . $driver_name, $q);

	// reset driver_id in objects
	$q = "UPDATE `gs_user_objects` SET `driver_id`='0' WHERE `driver_id`='" . $driver_id . "'";
	$r = mysqli_query($ms, $q);

	echo 'OK';
	die;
}

if (@$_POST['cmd'] == 'delete_selected_object_drivers') {
	$items = $_POST["items"];
	$deleted_driver_names = [];

	for ($i = 0; $i < count($items); ++$i) {
		$item = $items[$i];

		$q = "SELECT * FROM `gs_user_object_drivers` WHERE `driver_id`='" . $item . "'";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_array($r);
		$driver_name = $row['driver_name'];

		$deleted_driver_names[] = $driver_name;

		$image_paths = [
			'data/user/drivers/' . $row['driver_img_file'],
			'data/user/licence/' . $row['licence_img_file'],
			'data/user/id/' . $row['id_img_file'],
			'data/user/nss/' . $row['nss_img_file'],
			'data/user/otro/' . $row['otro_img_file']
		];

		foreach ($image_paths as $path) {
			$img_file = $gsValues['PATH_ROOT'] . $path;
			if (is_file($img_file)) {
				@unlink($img_file);
			}
		}

		$q = "DELETE FROM `gs_user_object_drivers` WHERE `driver_id`='" . $item . "'";
		$r = mysqli_query($ms, $q);

		// reset driver_id in objects
		$q = "UPDATE `gs_user_objects` SET `driver_id`='0' WHERE `driver_id`='" . $item . "'";
		$r = mysqli_query($ms, $q);
	}

	$driver_names_str = implode(', ', $deleted_driver_names);
	addRowBinnacle($_SESSION["user_id"], 'Baja de conductores: ' . $driver_names_str, $q);

	echo 'OK';
	die;
}



if (@$_POST['cmd'] == 'save_object_driver') {
	$driver_id = $_POST["driver_id"];
	$driver_name = $_POST["driver_name"];
	$driver_rfc = $_POST["driver_rfc"];
	$driver_nss = $_POST["driver_nss"];
	$driver_licence = $_POST["driver_licence"];
	$driver_licence_date = $_POST["driver_licence_date"];
	$driver_ine = $_POST["driver_ine"];
	$driver_ine_date = $_POST["driver_ine_date"];
	$driver_curp = $_POST["driver_curp"];
	$driver_assign_id = strtoupper($_POST["driver_assign_id"]);
	$driver_idn = $_POST["driver_idn"];
	$driver_address = $_POST["driver_address"];
	$driver_phone = $_POST["driver_phone"];
	$driver_email = $_POST["driver_email"];
	$driver_desc = $_POST["driver_desc"];
	$driver_img_file = $_POST["driver_img_file"];
	$licence_img_file = $_POST["licence_img_file"];
	$id_img_file = $_POST["id_img_file"];
	$nss_img_file = $_POST["nss_img_file"];
	$otro_img_file = $_POST["otro_img_file"];

	if ($driver_id == 'false') {
		$q = "INSERT INTO `gs_user_object_drivers`(	`user_id`,
									`driver_name`,
									`driver_rfc`,
									`driver_nss`,
									`driver_licence`,
									`driver_licence_date`,
									`driver_ine`,
									`driver_ine_date`,
									`driver_curp`,
									`driver_assign_id`,
									`driver_idn`,
									`driver_address`,
									`driver_phone`,
									`driver_email`,
									`driver_desc`,
									`licence_img_file`,
									`id_img_file`,
									`nss_img_file`,
									`otro_img_file`,
									`driver_img_file`)
									VALUES
									('" . $user_id . "',
									 '" . $driver_name . "',
									 '" . $driver_rfc . "',
									 '" . $driver_nss . "',
									 '" . $driver_licence . "',
									 '" . $driver_licence_date . "',
									 '" . $driver_ine . "',
									 '" . $driver_ine_date . "',
									 '" . $driver_curp . "',
									 '" . $driver_assign_id . "',
									 '" . $driver_idn . "',
									 '" . $driver_address . "',
									 '" . $driver_phone . "',
									 '" . $driver_email . "',
									 '" . $driver_desc . "',
									 '" . $licence_img_file . "',
									 '" . $id_img_file . "',
									 '" . $nss_img_file . "',
									 '" . $otro_img_file . "',
									 '" . $driver_img_file . "')";


		addRowBinnacle($_SESSION["user_id"], 'Alta de conductor: ' . $driver_name, $q);
	} else {
		$q = "UPDATE `gs_user_object_drivers` SET  	`driver_name`='" . $driver_name . "',
									`driver_rfc`='" . $driver_rfc . "',
									`driver_nss`='" . $driver_nss . "',
									`driver_licence`='" . $driver_licence . "',
									`driver_licence_date`='" . $driver_licence_date . "',
									`driver_ine`='" . $driver_ine . "',
									`driver_ine_date`='" . $driver_ine_date . "',
									`driver_curp`='" . $driver_curp . "',
									`driver_assign_id`='" . $driver_assign_id . "',
									`driver_idn`='" . $driver_idn . "',
									`driver_address`='" . $driver_address . "',
									`driver_phone`='" . $driver_phone . "',
									`driver_email`='" . $driver_email . "',
									`driver_desc`='" . $driver_desc . "'
									WHERE `driver_id`='" . $driver_id . "'";


		addRowBinnacle($_SESSION["user_id"], 'Edición de conductor: ' . $driver_name, $q);
	}

	$r = mysqli_query($ms, $q);

	echo 'OK';
	die;
}

if (@$_GET['cmd'] == 'load_object_driver_list') {
	$page = $_GET['page']; // get the requested page
	$limit = $_GET['rows']; // get how many rows we want to have into the grid
	$sidx = $_GET['sidx']; // get index row - i.e. user click to sort
	$sord = $_GET['sord']; // get the direction
	$search = caseToUpper(@$_GET['s']); // get search

	if (!$sidx)
		$sidx = 1;

	$q = "SELECT * FROM `gs_user_object_drivers` WHERE `user_id`='" . $user_id . "'";

	if ($search != '') {
		$q .= " AND (UPPER(`driver_name`) LIKE '%$search%')";
	}

	$r = mysqli_query($ms, $q);
	$count = mysqli_num_rows($r);

	if ($count > 0) {
		$total_pages = ceil($count / $limit);
	} else {
		$total_pages = 1;
	}

	if ($page > $total_pages)
		$page = $total_pages;
	$start = $limit * $page - $limit; // do not put $limit*($page - 1)

	$q .= " ORDER BY $sidx $sord LIMIT $start, $limit";
	$r = mysqli_query($ms, $q);

	$response = new stdClass();
	$response->page = $page;
	$response->total = $total_pages;
	$response->records = $count;

	if ($r) {
		$i = 0;
		while ($row = mysqli_fetch_array($r)) {
			$driver_id = $row['driver_id'];
			$driver_name = $row['driver_name'];
			$driver_idn = $row["driver_idn"];
			$driver_desc = $row['driver_desc'];

			// set modify buttons
			$modify = '<a href="#" onclick="settingsObjectDriverProperties(\'' . $driver_id . '\');"  title="' . $la['EDIT'] . '"><img src="theme/images/edit.svg" /></a>';
			$modify .= '<a href="#" onclick="settingsObjectDriverDelete(\'' . $driver_id . '\');"  title="' . $la['DELETE'] . '"><img src="theme/images/remove3.svg" /></a>';
			// set row
			$response->rows[$i]['id'] = $driver_id;
			$response->rows[$i]['cell'] = array($driver_name, $driver_idn, $driver_desc, $modify);
			$i++;
		}
	}

	header('Content-type: application/json');
	echo json_encode($response);
	die;
}

if (@$_POST['cmd'] == 'load_object_driver_data') {
	$q = "SELECT * FROM `gs_user_object_drivers` WHERE `user_id`='" . $user_id . "' ORDER BY `driver_name` ASC";
	$r = mysqli_query($ms, $q);

	$result = array();

	while ($row = mysqli_fetch_array($r)) {
		$driver_id = $row['driver_id'];
		$result[$driver_id] = array(
			'name' => $row['driver_name'],
			'rfc' => $row['driver_rfc'],
			'nss' => $row['driver_nss'],
			'licence' => $row['driver_licence'],
			'date_licence' => $row['driver_licence_date'],
			'ine' => $row['driver_ine'],
			'date_ine' => $row['driver_ine_date'],
			'curp' => $row['driver_curp'],
			'assign_id' => $row['driver_assign_id'],
			'idn' => $row['driver_idn'],
			'address' => $row['driver_address'],
			'phone' => $row['driver_phone'],
			'email' => $row['driver_email'],
			'desc' => $row['driver_desc'],
			'licence_img_file' => $row['licence_img_file'],
			'id_img_file' => $row['id_img_file'],
			'nss_img_file' => $row['nss_img_file'],
			'otro_img_file' => $row['otro_img_file'],
			'img' => $row['driver_img_file']
		);
	}
	echo json_encode($result);
	die;
}
