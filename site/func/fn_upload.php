<?
session_start();
include('../init.php');
include('fn_common.php');
checkUserSession();

if (@$_GET['file'] == 'logo_png') {
	if ($_SESSION["cpanel_privileges"] != 'super_admin') {
		die;
	}

	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		$file_path = $gsValues['PATH_ROOT'] . 'img/logo.png';

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);

		$file_url = $gsValues['URL_ROOT'] . '/img/logo.png';
		echo $file_url;
	}
}

if (@$_GET['file'] == 'logo_svg') {
	if ($_SESSION["cpanel_privileges"] != 'super_admin') {
		die;
	}

	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		$file_path = $gsValues['PATH_ROOT'] . 'img/logo.svg';

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);

		$file_url = $gsValues['URL_ROOT'] . '/img/logo.svg';
		echo $file_url;
	}
}

if (@$_GET['file'] == 'logo_small_png') {
	if ($_SESSION["cpanel_privileges"] != 'super_admin') {
		die;
	}

	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		$file_path = $gsValues['PATH_ROOT'] . 'img/logo_small.png';

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);

		$file_url = $gsValues['URL_ROOT'] . '/img/logo_small.png';
		echo $file_url;
	}
}

if (@$_GET['file'] == 'logo_small_svg') {
	if ($_SESSION["cpanel_privileges"] != 'super_admin') {
		die;
	}

	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		$file_path = $gsValues['PATH_ROOT'] . 'img/logo_small.svg';

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);

		$file_url = $gsValues['URL_ROOT'] . '/img/logo_small.svg';
		echo $file_url;
	}
}

if (@$_GET['file'] == 'favicon_png') {
	if ($_SESSION["cpanel_privileges"] != 'super_admin') {
		die;
	}

	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		$file_path = $gsValues['PATH_ROOT'] . 'favicon.png';

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);

		$file_url = $gsValues['URL_ROOT'] . '/favicon.png';
		echo $file_url;
	}
}

if (@$_GET['file'] == 'favicon_ico') {
	if ($_SESSION["cpanel_privileges"] != 'super_admin') {
		die;
	}

	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		$file_path = $gsValues['PATH_ROOT'] . 'favicon.ico';

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);

		$file_url = $gsValues['URL_ROOT'] . '/favicon.ico';
		echo $file_url;
	}
}

if (@$_GET['file'] == 'login_background') {
	if ($_SESSION["cpanel_privileges"] != 'super_admin') {
		die;
	}

	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		$file_path = $gsValues['PATH_ROOT'] . 'img/login-background.jpg';

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);

		$file_url = $gsValues['URL_ROOT'] . '/img/login-background.jpg';
		echo $file_url;
	}
}

if (@$_GET['file'] == 'driver_photo') {
	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		if (intval($_GET['driver_id']) == 0) {
			$current_file = 'driver_id_photo_' . time() . '.' . $_GET['type_img'];
		} else {
			$q = "SELECT `driver_img_file` FROM `gs_user_object_drivers` WHERE `driver_id` = " . $_GET['driver_id'];
			$r = mysqli_query($ms, $q);
			$row = mysqli_fetch_assoc($r);

			$driver_img_file = $row['driver_img_file'];

			$file_path = $gsValues['PATH_ROOT'] . 'data/user/drivers/' . $driver_img_file;
			@unlink($file_path);

			$current_file = 'driver_id_photo_' . $_GET['driver_id'] . '.' . $_GET['type_img'];
			$q = "UPDATE `gs_user_object_drivers` SET `driver_img_file`='" . $current_file . "' WHERE `driver_id`='" . intval($_GET['driver_id']) . "'";
			$r = mysqli_query($ms, $q);
		}

		$file_path = $gsValues['PATH_ROOT'] . 'data/user/drivers/' . $current_file;
		//$file_url = $gsValues['URL_ROOT'] . '/data/user/drivers/' . $_SESSION["user_id"] . '_temp.png';

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);

		echo $current_file;
	}
}

if (@$_GET['file'] == 'Drop_Driver_Photo') {
	$driver_id = $_GET['driver_id'];

	if (intval($driver_id) > 0) {
		//recover licence_img_file from table gs_user_object_drivers
		$q = "SELECT `driver_img_file` FROM `gs_user_object_drivers` WHERE `driver_id` = " . $driver_id;
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_assoc($r);

		$driver_img_file = $row['driver_img_file'];

		$file_path = $gsValues['PATH_ROOT'] . 'data/user/drivers/' . $driver_img_file;
		unlink($file_path);
		$q = "UPDATE `gs_user_object_drivers` SET `driver_img_file` = '' WHERE `driver_id` = " . $driver_id;
		$r = mysqli_query($ms, $q);
	}
}

if (@$_GET['file'] == 'Download_Driver_Photo') {
	$driver_id = $_GET['driver_id'];
	$q = "SELECT driver_name, `driver_img_file` FROM `gs_user_object_drivers` WHERE `driver_id` = " . $driver_id;
	$r = mysqli_query($ms, $q);
	$row = mysqli_fetch_assoc($r);

	$driver_img_file = $row['driver_img_file'];
	$file_path = $gsValues['PATH_ROOT'] . 'data/user/drivers/' . $driver_img_file;

	if (file_exists($file_path)) {
		$fileData = base64_encode(file_get_contents($file_path));
		$filename = $row['driver_name'] . '_driver.png';
		$response = [
			'fileName' => $filename,
			'fileData' => $fileData
		];
		header('Content-Type: application/json');
		echo json_encode($response);
	} else {
		echo json_encode(['error' => 'File not found.']);
	}
	exit;
}

if (@$_GET['file'] == 'Licence_Photo') {
	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		if (intval($_GET['driver_id']) == 0) {
			$current_file = 'driver_id_licence_' . time() . '.' . $_GET['type_img'];
		} else {
			$q = "SELECT `licence_img_file` FROM `gs_user_object_drivers` WHERE `driver_id` = " . $_GET['driver_id'];
			$r = mysqli_query($ms, $q);
			$row = mysqli_fetch_assoc($r);

			$licence_img_file = $row['licence_img_file'];

			$file_path = $gsValues['PATH_ROOT'] . 'data/user/drivers/' . $licence_img_file;
			@unlink($file_path);

			$current_file = 'driver_id_licence_' . $_GET['driver_id'] . '.' . $_GET['type_img'];
			$q = "UPDATE `gs_user_object_drivers` SET `licence_img_file`='" . $current_file . "' WHERE `driver_id`='" . intval($_GET['driver_id']) . "'";
			$r = mysqli_query($ms, $q);
		}

		$file_path = $gsValues['PATH_ROOT'] . 'data/user/licence/' . $current_file;
		//$file_url = $gsValues['URL_ROOT'] . '/data/user/licence/' . $_SESSION["user_id"] . '_temp.png';

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);

		echo $current_file;
	}
}

if (@$_GET['file'] == 'Drop_Licence_Photo') {
	$driver_id = $_GET['driver_id'];

	if (intval($driver_id) > 0) {
		//recover licence_img_file from table gs_user_object_drivers
		$q = "SELECT `licence_img_file` FROM `gs_user_object_drivers` WHERE `driver_id` = " . $driver_id;
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_assoc($r);

		$licence_img_file = $row['licence_img_file'];

		$file_path = $gsValues['PATH_ROOT'] . 'data/user/licence/' . $row['licence_img_file'];
		unlink($file_path);
		$q = "UPDATE `gs_user_object_drivers` SET `licence_img_file` = '' WHERE `driver_id` = " . $driver_id;
		$r = mysqli_query($ms, $q);
	}
}

if (@$_GET['file'] == 'Download_Licence_Photo') {
	$driver_id = $_GET['driver_id'];
	$q = "SELECT driver_name, `licence_img_file` FROM `gs_user_object_drivers` WHERE `driver_id` = " . $driver_id;
	$r = mysqli_query($ms, $q);
	$row = mysqli_fetch_assoc($r);

	$licence_img_file = $row['licence_img_file'];
	$file_path = $gsValues['PATH_ROOT'] . 'data/user/licence/' . $row['licence_img_file'];

	if (file_exists($file_path)) {
		$fileData = base64_encode(file_get_contents($file_path));
		$filename = $row['driver_name'] . '_licence.png';
		$response = [
			'fileName' => $filename,
			'fileData' => $fileData
		];
		header('Content-Type: application/json');
		echo json_encode($response);
	} else {
		echo json_encode(['error' => 'File not found.']);
	}
	exit;
}


if (@$_GET['file'] == 'Id_Photo') {
	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		if (intval($_GET['driver_id']) == 0) {
			$current_file = 'driver_id_photo_' . time() . '.' . $_GET['type_img'];
		} else {
			$q = "SELECT `id_img_file` FROM `gs_user_object_drivers` WHERE `driver_id` = " . $_GET['driver_id'];
			$r = mysqli_query($ms, $q);
			$row = mysqli_fetch_assoc($r);

			$id_img_file = $row['id_img_file'];

			$file_path = $gsValues['PATH_ROOT'] . 'data/user/id/' . $id_img_file;
			@unlink($file_path);

			$current_file = 'driver_id_photo_' . $_GET['driver_id'] . '.' . $_GET['type_img'];
			$q = "UPDATE `gs_user_object_drivers` SET `id_img_file`='" . $current_file . "' WHERE `driver_id`='" . intval($_GET['driver_id']) . "'";
			$r = mysqli_query($ms, $q);
		}


		$file_path = $gsValues['PATH_ROOT'] . 'data/user/id/' . $current_file;
		//$file_url = $gsValues['URL_ROOT'] . '/data/user/id/' . $_SESSION["user_id"] . '_temp.png';

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);

		echo $current_file;
	}
}

if (@$_GET['file'] == 'Drop_Id_Photo') {
	$driver_id = $_GET['driver_id'];

	if (intval($driver_id) > 0) {
		//recover licence_img_file from table gs_user_object_drivers
		$q = "SELECT `id_img_file` FROM `gs_user_object_drivers` WHERE `driver_id` = " . $driver_id;
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_assoc($r);

		$id_img_file = $row['id_img_file'];

		$file_path = $gsValues['PATH_ROOT'] . 'data/user/id/' . $id_img_file;
		unlink($file_path);
		$q = "UPDATE `gs_user_object_drivers` SET `id_img_file` = '' WHERE `driver_id` = " . $driver_id;
		$r = mysqli_query($ms, $q);
	}
}

if (@$_GET['file'] == 'Download_Id_Photo') {
	$driver_id = $_GET['driver_id'];
	$q = "SELECT driver_name, `id_img_file` FROM `gs_user_object_drivers` WHERE `driver_id` = " . $driver_id;
	$r = mysqli_query($ms, $q);
	$row = mysqli_fetch_assoc($r);

	$id_img_file = $row['id_img_file'];
	$file_path = $gsValues['PATH_ROOT'] . 'data/user/id/' . $id_img_file;

	if (file_exists($file_path)) {
		$fileData = base64_encode(file_get_contents($file_path));
		$filename = $row['driver_name'] . '_id.png';
		$response = [
			'fileName' => $filename,
			'fileData' => $fileData
		];
		header('Content-Type: application/json');
		echo json_encode($response);
	} else {
		echo json_encode(['error' => 'File not found.']);
	}
	exit;
}

if (@$_GET['file'] == 'Nss_Photo') {
	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$pdfData = $postdata;
		$filteredData = substr($pdfData, strpos($pdfData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		if (intval($_GET['driver_id']) == 0) {
			$current_file = 'driver_id_nss_' . time() . '.pdf';
		} else {
			$current_file = 'driver_id_nss_' . $_GET['driver_id'] . '.pdf';
			$q = "UPDATE `gs_user_object_drivers` SET `nss_img_file`='" . $current_file . "' WHERE `driver_id`='" . intval($_GET['driver_id']) . "'";
			$r = mysqli_query($ms, $q);
		}

		$file_path = $gsValues['PATH_ROOT'] . 'data/user/nss/' . $current_file;
		//$file_url = $gsValues['URL_ROOT'] . '/data/user/nss/' . $_SESSION["user_id"] . '_temp.pdf';

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);

		echo $current_file;
	}
}

if (@$_GET['file'] == 'Drop_Nss_Photo') {
	$driver_id = $_GET['driver_id'];

	if (intval($driver_id) > 0) {
		$q = "SELECT `nss_img_file` FROM `gs_user_object_drivers` WHERE `driver_id` = " . $driver_id;
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_assoc($r);

		$nss_img_file = $row['nss_img_file'];

		$file_path = $gsValues['PATH_ROOT'] . 'data/user/nss/' . $nss_img_file;
		unlink($file_path);
		$q = "UPDATE `gs_user_object_drivers` SET `nss_img_file` = '' WHERE `driver_id` = " . $driver_id;
		$r = mysqli_query($ms, $q);
		echo '';
	}
}

if (@$_GET['file'] == 'Download_Nss_Photo') {
	$driver_id = $_GET['driver_id'];
	$q = "SELECT driver_name, `nss_img_file` FROM `gs_user_object_drivers` WHERE `driver_id` = " . $driver_id;
	$r = mysqli_query($ms, $q);
	$row = mysqli_fetch_assoc($r);

	$id_nss_file = $row['nss_img_file'];
	$file_path = $gsValues['PATH_ROOT'] . 'data/user/nss/' . $id_nss_file;

	if (file_exists($file_path)) {
		$fileData = base64_encode(file_get_contents($file_path));
		$filename = $row['driver_name'] . '_nss.pdf';
		$response = [
			'fileName' => $filename,
			'fileData' => $fileData
		];
		header('Content-Type: application/json');
		echo json_encode($response);
	} else {
		echo json_encode(['error' => 'File not found.']);
	}
	exit;
}

if (@$_GET['file'] == 'Otro_Photo') {
	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		if (intval($_GET['driver_id']) == 0) {
			$current_file = 'driver_id_otro_' . time() . '.' . $_GET['type_img'];
		} else {
			$q = "SELECT `otro_img_file` FROM `gs_user_object_drivers` WHERE `driver_id` = " . $_GET['driver_id'];
			$r = mysqli_query($ms, $q);
			$row = mysqli_fetch_assoc($r);

			$otro_img_file = $row['otro_img_file'];

			$file_path = $gsValues['PATH_ROOT'] . 'data/user/otro/' . $otro_img_file;
			@unlink($file_path);

			$current_file = 'driver_id_otro_' . $_GET['driver_id'] . '.' . $_GET['type_img'];
			$q = "UPDATE `gs_user_object_drivers` SET `otro_img_file`='" . $current_file . "' WHERE `driver_id`='" . intval($_GET['driver_id']) . "'";
			$r = mysqli_query($ms, $q);
		}

		$file_path = $gsValues['PATH_ROOT'] . 'data/user/otro/' . $current_file;
		//$file_url = $gsValues['URL_ROOT'] . '/data/user/otro/' . $_SESSION["user_id"] . '_temp.png';

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);

		echo $current_file;
	}
}
if (@$_GET['file'] == 'Drop_Otro_Photo') {
	$driver_id = $_GET['driver_id'];

	if (intval($driver_id) > 0) {
		//recover licence_img_file from table gs_user_object_drivers
		$q = "SELECT `otro_img_file` FROM `gs_user_object_drivers` WHERE `driver_id` = " . $driver_id;
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_assoc($r);

		$otro_img_file = $row['otro_img_file'];

		$file_path = $gsValues['PATH_ROOT'] . 'data/user/otro/' . $row['otro_img_file'];
		unlink($file_path);
		$q = "UPDATE `gs_user_object_drivers` SET `otro_img_file` = '' WHERE `driver_id` = " . $driver_id;
		$r = mysqli_query($ms, $q);
	}
}

if (@$_GET['file'] == 'Download_Otro_Photo') {
	$driver_id = $_GET['driver_id'];
	$q = "SELECT driver_name, `otro_img_file` FROM `gs_user_object_drivers` WHERE `driver_id` = " . $driver_id;
	$r = mysqli_query($ms, $q);
	$row = mysqli_fetch_assoc($r);

	$licence_img_file = $row['otro_img_file'];
	$file_path = $gsValues['PATH_ROOT'] . 'data/user/otro/' . $row['otro_img_file'];

	if (file_exists($file_path)) {
		$fileData = base64_encode(file_get_contents($file_path));
		$filename = $row['driver_name'] . '_otro.png';
		$response = [
			'fileName' => $filename,
			'fileData' => $fileData
		];
		header('Content-Type: application/json');
		echo json_encode($response);
	} else {
		echo json_encode(['error' => 'File not found.']);
	}
	exit;
}
if (@$_GET['file'] == 'carta_porte') {
	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		if (intval($_GET['task_id']) == 0) {
			$current_file = 'carta_porte_' . time() . '.pdf';
		} else {
			$current_file = 'carta_porte_id_' . $_GET['task_id'] . '.pdf';
			$q = "UPDATE `gs_object_tasks` SET `carta_porte`='" . $current_file . "' WHERE `task_id`='" . intval($_GET['task_id']) . "'";
			$r = mysqli_query($ms, $q);
		}
		$file_path = $gsValues['PATH_ROOT'] . 'data/user/viaje_programado/' . $current_file;
		//$file_url = $gsValues['URL_ROOT'] . '/data/user/viaje_programado/carta_porte_' . $current_time . '.pdf';

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);

		echo $current_file;
	}
}

if (@$_GET['file'] == 'doc1') {
	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);
		if (intval($_GET['task_id']) == 0) {
			$current_file = 'doc_1_' . time() . '.pdf';
		} else {
			$current_file = 'doc_1_id_' . $_GET['task_id'] . '.pdf';
			$q = "UPDATE `gs_object_tasks` SET `doc1`='" . $current_file . "' WHERE `task_id`='" . intval($_GET['task_id']) . "'";
			$r = mysqli_query($ms, $q);
		}

		$file_path = $gsValues['PATH_ROOT'] . 'data/user/viaje_programado/' . $current_file;
		//$file_url = $gsValues['URL_ROOT'] . '/data/user/viaje_programado/carta_porte_' . $current_time . '.pdf';

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);

		echo $current_file;
	}
}
if (@$_GET['file'] == 'contrato') {
	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);
		if (intval($_GET['user_id']) == 0) {
			$current_file = 'contrato_' . time() . '.pdf';
		} else {
			$current_file = 'contrato_id_' . $_GET['user_id'] . '.pdf';
			$q = "UPDATE `gs_user_docs` SET `doc1`='" . $current_file . "' WHERE `user_id`='" . intval($_GET['user_id']) . "'";
			$r = mysqli_query($ms, $q);
		}

		$file_path = $gsValues['PATH_ROOT'] . 'data/user/Documents/' . $current_file;
		//$file_url = $gsValues['URL_ROOT'] . '/data/user/viaje_programado/carta_porte_' . $current_time . '.pdf';

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);

		echo $current_file;
	}
}
if (@$_GET['file'] == 'doc2') {
	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);
		if (intval($_GET['task_id']) == 0) {
			$current_file = 'doc_2_' . time() . '.pdf';
		} else {
			$current_file = 'doc_2_id_' . $_GET['task_id'] . '.pdf';
			$q = "UPDATE `gs_object_tasks` SET `doc2`='" . $current_file . "' WHERE `task_id`='" . intval($_GET['task_id']) . "'";
			$r = mysqli_query($ms, $q);
		}

		$file_path = $gsValues['PATH_ROOT'] . 'data/user/viaje_programado/' . $current_file;
		//$file_url = $gsValues['URL_ROOT'] . '/data/user/viaje_programado/carta_porte_' . $current_time . '.pdf';

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);

		echo $current_file;
	}
}

if (@$_GET['file'] == 'doc3') {
	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		if (intval($_GET['task_id']) == 0) {
			$current_file = 'doc_3_' . time() . '.pdf';
		} else {
			$current_file = 'doc_3_id_' . $_GET['task_id'] . '.pdf';
			$q = "UPDATE `gs_object_tasks` SET `doc3`='" . $current_file . "' WHERE `task_id`='" . intval($_GET['task_id']) . "'";
			$r = mysqli_query($ms, $q);
		}
		$file_path = $gsValues['PATH_ROOT'] . 'data/user/viaje_programado/' . $current_file;
		//$file_url = $gsValues['URL_ROOT'] . '/data/user/viaje_programado/carta_porte_' . $current_time . '.pdf';

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);

		echo $current_file;
	}
}

if (@$_GET['file'] == 'MiContrato') {
	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		$user_id = intval($_GET['user_id']);
		$current_file = ($user_id == 0) ? 'MiContrato_' . time() . '.pdf' : 'MiContrato_user_id_' . $user_id . '.pdf';

		// Verificar si ya existe un registro para el user_id
		$q = "SELECT COUNT(*) as count FROM `gs_user_docs` WHERE `user_id` = $user_id";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_assoc($r);

		if ($row['count'] > 0) {
			// Actualizar el registro existente en la base de datos
			$q = "UPDATE `gs_user_docs` SET `doc_1`='$current_file' WHERE `user_id`=$user_id";
			$r = mysqli_query($ms, $q);
		} else {
			// Insertar un nuevo registro en la base de datos
			$q = "INSERT INTO `gs_user_docs` (`user_id`, `doc_1`) VALUES ($user_id, '$current_file')";
			$r = mysqli_query($ms, $q);
		}

		$file_path = $gsValues['PATH_ROOT'] . 'data/user/settingsUsersDocument/' . $current_file;

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);

		echo $current_file;
	}
}



if (@$_GET['file'] == 'ActaConstitutiva') {
	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		$user_id = intval($_GET['user_id']);
		$current_file = ($user_id == 0) ? 'ActaConstitutiva_' . time() . '.pdf' : 'ActaConstitutiva_user_id_' . $user_id . '.pdf';

		// Verificar si ya existe un registro para el user_id
		$q = "SELECT COUNT(*) as count FROM `gs_user_docs` WHERE `user_id` = $user_id";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_assoc($r);

		if ($row['count'] > 0) {
			// Actualizar el registro existente en la base de datos
			$q = "UPDATE `gs_user_docs` SET `doc_2`='$current_file' WHERE `user_id`=$user_id";
			$r = mysqli_query($ms, $q);
		} else {
			// Insertar un nuevo registro en la base de datos
			$q = "INSERT INTO `gs_user_docs` (`user_id`, `doc_2`) VALUES ($user_id, '$current_file')";
			$r = mysqli_query($ms, $q);
		}

		$file_path = $gsValues['PATH_ROOT'] . 'data/user/settingsUsersDocument/' . $current_file;

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);

		echo $current_file;
	}
}

if (@$_GET['file'] == 'RepLegal') {
	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		$user_id = intval($_GET['user_id']);
		$current_file = ($user_id == 0) ? 'RepLegal' . time() . '.pdf' : 'RepLegal_user_id_' . $user_id . '.pdf';

		// Verificar si ya existe un registro para el user_id
		$q = "SELECT COUNT(*) as count FROM `gs_user_docs` WHERE `user_id` = $user_id";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_assoc($r);

		if ($row['count'] > 0) {
			// Actualizar el registro existente en la base de datos
			$q = "UPDATE `gs_user_docs` SET `doc_3`='$current_file' WHERE `user_id`=$user_id";
			$r = mysqli_query($ms, $q);
		} else {
			// Insertar un nuevo registro en la base de datos
			$q = "INSERT INTO `gs_user_docs` (`user_id`, `doc_3`) VALUES ($user_id, '$current_file')";
			$r = mysqli_query($ms, $q);
		}

		$file_path = $gsValues['PATH_ROOT'] . 'data/user/settingsUsersDocument/' . $current_file;

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);

		echo $current_file;
	}
}

if (@$_GET['file'] == 'IdentificacionOficial') {
	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		$user_id = intval($_GET['user_id']);
		$current_file = ($user_id == 0) ? 'IdentificacionOficial_' . time() . '.pdf' : 'IdentificacionOficial_user_id_' . $user_id . '.pdf';

		// Verificar si ya existe un registro para el user_id
		$q = "SELECT COUNT(*) as count FROM `gs_user_docs` WHERE `user_id` = $user_id";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_assoc($r);

		if ($row['count'] > 0) {
			// Actualizar el registro existente en la base de datos
			$q = "UPDATE `gs_user_docs` SET `doc_4`='$current_file' WHERE `user_id`=$user_id";
			$r = mysqli_query($ms, $q);
		} else {
			// Insertar un nuevo registro en la base de datos
			$q = "INSERT INTO `gs_user_docs` (`user_id`, `doc_4`) VALUES ($user_id, '$current_file')";
			$r = mysqli_query($ms, $q);
		}

		$file_path = $gsValues['PATH_ROOT'] . 'data/user/settingsUsersDocument/' . $current_file;

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);

		echo $current_file;
	}
}

if (@$_GET['file'] == 'OpinionPositiva') {
	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		$user_id = intval($_GET['user_id']);
		$current_file = ($user_id == 0) ? 'OpinionPositiva_' . time() . '.pdf' : 'OpinionPositiva_user_id_' . $user_id . '.pdf';

		// Verificar si ya existe un registro para el user_id
		$q = "SELECT COUNT(*) as count FROM `gs_user_docs` WHERE `user_id` = $user_id";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_assoc($r);

		if ($row['count'] > 0) {
			// Actualizar el registro existente en la base de datos
			$q = "UPDATE `gs_user_docs` SET `doc_5`='$current_file' WHERE `user_id`=$user_id";
			$r = mysqli_query($ms, $q);
		} else {
			// Insertar un nuevo registro en la base de datos
			$q = "INSERT INTO `gs_user_docs` (`user_id`, `doc_5`) VALUES ($user_id, '$current_file')";
			$r = mysqli_query($ms, $q);
		}

		$file_path = $gsValues['PATH_ROOT'] . 'data/user/settingsUsersDocument/' . $current_file;

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);

		echo $current_file;
	}
}

if (@$_GET['file'] == 'ConstanciaFiscal') {
	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		$user_id = intval($_GET['user_id']);
		$current_file = ($user_id == 0) ? 'ConstanciaFiscal_' . time() . '.pdf' : 'ConstanciaFiscal_user_id_' . $user_id . '.pdf';

		// Verificar si ya existe un registro para el user_id
		$q = "SELECT COUNT(*) as count FROM `gs_user_docs` WHERE `user_id` = $user_id";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_assoc($r);

		if ($row['count'] > 0) {
			// Actualizar el registro existente en la base de datos
			$q = "UPDATE `gs_user_docs` SET `doc_6`='$current_file' WHERE `user_id`=$user_id";
			$r = mysqli_query($ms, $q);
		} else {
			// Insertar un nuevo registro en la base de datos
			$q = "INSERT INTO `gs_user_docs` (`user_id`, `doc_6`) VALUES ($user_id, '$current_file')";
			$r = mysqli_query($ms, $q);
		}

		$file_path = $gsValues['PATH_ROOT'] . 'data/user/settingsUsersDocument/' . $current_file;

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);

		echo $current_file;
	}
}

if (@$_GET['file'] == 'DomicilioFiscal') {
	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		$user_id = intval($_GET['user_id']);
		$current_file = ($user_id == 0) ? 'DomicilioFiscal_' . time() . '.pdf' : 'DomicilioFiscal_user_id_' . $user_id . '.pdf';

		// Verificar si ya existe un registro para el user_id
		$q = "SELECT COUNT(*) as count FROM `gs_user_docs` WHERE `user_id` = $user_id";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_assoc($r);

		if ($row['count'] > 0) {
			// Actualizar el registro existente en la base de datos
			$q = "UPDATE `gs_user_docs` SET `doc_7`='$current_file' WHERE `user_id`=$user_id";
			$r = mysqli_query($ms, $q);
		} else {
			// Insertar un nuevo registro en la base de datos
			$q = "INSERT INTO `gs_user_docs` (`user_id`, `doc_7`) VALUES ($user_id, '$current_file')";
			$r = mysqli_query($ms, $q);
		}

		$file_path = $gsValues['PATH_ROOT'] . 'data/user/settingsUsersDocument/' . $current_file;

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);

		echo $current_file;
	}
}

if (@$_GET['file'] == 'Otros') {
	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		$user_id = intval($_GET['user_id']);
		$current_file = ($user_id == 0) ? 'Otros_' . time() . '.pdf' : 'Otros_user_id_' . $user_id . '.pdf';

		// Verificar si ya existe un registro para el user_id
		$q = "SELECT COUNT(*) as count FROM `gs_user_docs` WHERE `user_id` = $user_id";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_assoc($r);

		if ($row['count'] > 0) {
			// Actualizar el registro existente en la base de datos
			$q = "UPDATE `gs_user_docs` SET `doc_8`='$current_file' WHERE `user_id`=$user_id";
			$r = mysqli_query($ms, $q);
		} else {
			// Insertar un nuevo registro en la base de datos
			$q = "INSERT INTO `gs_user_docs` (`user_id`, `doc_8`) VALUES ($user_id, '$current_file')";
			$r = mysqli_query($ms, $q);
		}

		$file_path = $gsValues['PATH_ROOT'] . 'data/user/settingsUsersDocument/' . $current_file;

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);

		echo $current_file;
	}
}


if (@$_GET['file'] == 'object_icon_png') {
	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		$file_path = $gsValues['PATH_ROOT'] . 'data/user/objects/' . $_SESSION["user_id"] . '_' . md5(gmdate("Y-m-d H:i:s")) . '.png';

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);
	}
}

if (@$_GET['file'] == 'object_icon_svg') {
	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		$file_path = $gsValues['PATH_ROOT'] . 'data/user/objects/' . $_SESSION["user_id"] . '_' . md5(gmdate("Y-m-d H:i:s")) . '.svg';

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);
	}
}

if (@$_GET['file'] == 'places_icon_png') {
	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		$file_path = $gsValues['PATH_ROOT'] . 'data/user/places/' . $_SESSION["user_id"] . '_' . md5(gmdate("Y-m-d H:i:s")) . '.png';

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);
	}
}

if (@$_GET['file'] == 'places_icon_svg') {
	$postdata = file_get_contents("php://input");

	if (isset($postdata)) {
		$imageData = $postdata;
		$filteredData = substr($imageData, strpos($imageData, ",") + 1);

		$unencodedData = base64_decode($filteredData);

		$file_path = $gsValues['PATH_ROOT'] . 'data/user/places/' . $_SESSION["user_id"] . '_' . md5(gmdate("Y-m-d H:i:s")) . '.svg';

		if (!isFilePathValid($file_path)) {
			die;
		}

		$fp = fopen($file_path, 'wb');
		fwrite($fp, $unencodedData);
		fclose($fp);
	}
}
