<?
include('../init.php');
include('../func/fn_common.php');
include('../func/fn_route.php');
include('../tools/gc_func.php');
include('../tools/email.php');
include('../tools/sms.php');

function getUserIdFromAPIKey($key)
{
	global $ms;

	$q = "SELECT * FROM `gs_users` WHERE `api_key`='" . $key . "'";
	$r = mysqli_query($ms, $q);
	$row = mysqli_fetch_array($r);

	if (!$row) {
		return false;
	} else {

		if ($row['api'] == "true") {
			return $row["id"];
		}
	}
}

function getUserIdFromUsername($username)
{
	global $ms;

	$q = "SELECT * FROM `gs_users` WHERE `username`='" . $username . "'";
	$r = mysqli_query($ms, $q);
	$row = mysqli_fetch_array($r);

	return $row["id"];
}

function getUserIdFromEmail($email)
{
	global $ms;

	$q = "SELECT * FROM `gs_users` WHERE `email`='" . $email . "'";
	$r = mysqli_query($ms, $q);

	if (!$r) {
		return false;
	}

	$row = mysqli_fetch_array($r);

	return $row["id"];
}

function getUserAPIKeyFromEmail($email)
{
	global $ms;

	$q = "SELECT * FROM `gs_users` WHERE `email`='" . $email . "'";
	$r = mysqli_query($ms, $q);

	if (!$r) {
		return false;
	}

	$row = mysqli_fetch_array($r);

	return $row["api_key"];
}

function checkServerAPIPrivileges()
{
	global $gsValues;

	if (isset($gsValues['SERVER_API_IP'])) {
		if ($gsValues['SERVER_API_IP'] != '') {
			$api_ips = explode(",", $gsValues['SERVER_API_IP']);
			if (!in_array($_SERVER['REMOTE_ADDR'], $api_ips)) {
				die;
			}
		}
	}
}

// validate access to api
$api_access = false;

$api = @$_GET['api'];
$ver = @$_GET['ver'];
$key = @$_GET['key'];
$cmd = @$_GET['cmd'];


if ($api == '') {
	die;
}
if ($cmd == '') {
	die;
}

if ($api == 'server') {
	if (isset($gsValues['SERVER_ENABLED'])) {
		if ($gsValues['SERVER_ENABLED'] == 'false') {
			die;
		}
	}

	checkServerAPIPrivileges();

	if ($key != 'ADRIANA_ISIDA') {
		die;
	}
	$api_access = true;
	include('api_server.php');
}

if ($api == 'hosting') {
	if ($key != $gsValues['HW_KEY']) {
		die;
	}
	$api_access = true;
	include('api_hosting.php');
}

if ($api == 'user') {
	if (isset($gsValues['SERVER_ENABLED'])) {
		if ($gsValues['SERVER_ENABLED'] == 'false') {
			die;
		}
	}

	$user_id = getUserIdFromAPIKey($key);
	if ($user_id == false) {
		die;
	}
	//check user usage
	if (!checkUserUsage($user_id, 'api')) {
		die;
	}


	if ($key == 'C6C70CF77D2B17EEECB0BA5CA4E02212') {

		$username = $_GET["username"];
		$password = $_GET["password"];

		$q = "SELECT * FROM `gs_users` WHERE `username`='" . $username . "' AND `password`='" . md5($password) . "' LIMIT 1";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_array($r);

		if ($row['id'] == "$user_id") {
			if ($row['active'] == 'true') {
				//write log
				addRowBinnacle($row["id"], 'Consulta de api: "ok"');

				//update user usage
				updateUserUsage($row['api_key'], 1, false, false, false, false);
			} else {
				echo 'ERROR_ACCOUNT_LOCKED';

				//write log
				writeLog('user_access', 'User login: account locked. Username: "' . $username . '"');
			}
		} else {
			echo 'ERROR_USERNAME_PASSWORD_INCORRECT';

			//write log
			$q = "SELECT * FROM `gs_users` WHERE `api_key`='" . $key . "' LIMIT 1";
			$r = mysqli_query($ms, $q);
			$row = mysqli_fetch_array($r);

			addRowBinnacle($row["id"], 'Consulta de api fallida: "user o password incorrecto"');
			die;
		}
	}

	//update user usage
	updateUserUsage($user_id, false, false, false, false, 1);

	$api_access = true;
	include('api_user.php');
}

die;
