<?
// #################################################
//  CPANEL FUNCTIONS
// #################################################

function checkCPanelToUserPrivileges($id)
{
    global $ms;

    if ($_SESSION["cpanel_privileges"] == 'manager') {
        $q = "SELECT * FROM `gs_users` WHERE `id`='" . $id . "'";
        $r = mysqli_query($ms, $q);
        $row = mysqli_fetch_array($r);

        if ($row["manager_id"] != $_SESSION["cpanel_manager_id"]) {
            die;
        }
    }
}

function checkCPanelToObjectPrivileges($imei)
{
    global $ms, $la;

    if ($_SESSION["cpanel_privileges"] == 'manager') {
        $q = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "'";
        $r = mysqli_query($ms, $q);
        $row = mysqli_fetch_array($r);

        if ($row["manager_id"] != $_SESSION["cpanel_manager_id"]) {
            die;
        }
    }
}
function checkCPanelToObjectUserPrivileges($user)
{
    $excluded_ids = array(1171, 1167, 770, 772, 172, 2, 1441, 1673, 1705);

    if (in_array($user, $excluded_ids)) {
        return false;
    }

    return true;
}
function checkCPanelToObjectUserPrivilegesIncuded($user)
{
    $included_ids = array(1, 766, 767, 768, 772);

    if (in_array($user, $included_ids)) {
        return true;
    }

    return false;
}


// #################################################
//  END CPANEL FUNCTIONS
// #################################################

// #################################################
//  PASSWORD, API, IDENTIFIER FUNCTIONS
// #################################################

function genLoginToken()
{
    if (isset($_SESSION['token'])) {
        return $_SESSION['token'];
    } else {
        $token = hash('sha1', rand() . gmdate('Y-m-d H:i:s') . rand());
        $_SESSION['token'] = $token;
        return $token;
    }
}

function genAccountPassword()
{
    $pass = substr(hash('sha1', rand() . gmdate('d F Y G i s u') . rand()), 0, 6);
    return $pass;
}

function genAccountRecoverToken($email)
{
    global $ms;

    while (true) {
        $token = strtoupper(md5(rand() . $email . gmdate("Y-m-d H:i:s") . rand()));

        $q = "SELECT * FROM `gs_user_account_recover` WHERE `token`='" . $token . "'";
        $r = mysqli_query($ms, $q);
        $num = mysqli_num_rows($r);

        if ($num == 0) {
            return $token;
        }
    }
}

function genServerAPIKey()
{
    global $ms, $gsValues;

    $api_key = '';

    if ($gsValues['HW_KEY'] != '') {
        $api_key = strtoupper(md5(rand() . $gsValues['HW_KEY'] . gmdate("Y-m-d H:i:s") . rand()));
    }

    return $api_key;
}

function genUserAPIKey($email)
{
    global $ms;

    while (true) {
        $api_key = strtoupper(md5(rand() . $email . gmdate("Y-m-d H:i:s") . rand()));

        $q = "SELECT * FROM `gs_users` WHERE `api_key`='" . $api_key . "'";
        $r = mysqli_query($ms, $q);
        $num = mysqli_num_rows($r);

        if ($num == 0) {
            return $api_key;
        }
    }
}

function genSMSGatewayIdn($email)
{
    global $ms, $gsValues;

    while (true) {
        $sms_idn = strtoupper(md5(rand() . $email . gmdate("Y-m-d H:i:s") . rand()));

        $sms_idn = preg_replace("/[^0-9]/", "", $sms_idn);

        $sms_idn = substr($sms_idn . $sms_idn, 0, 20);

        $q = "SELECT * FROM `gs_users` WHERE `sms_gateway_identifier`='" . $sms_idn . "'";
        $r = mysqli_query($ms, $q);
        $num = mysqli_num_rows($r);

        if (($num == 0) && ($sms_idn != $gsValues['SMS_GATEWAY_IDENTIFIER'])) {
            return $sms_idn;
        }
    }
}

function genPushIdn($email)
{
    global $ms, $gsValues;

    while (true) {
        $push_idn = strtoupper(md5(rand() . $email . gmdate("Y-m-d H:i:s") . rand()));

        $push_idn = preg_replace("/[^0-9]/", "", $push_idn);

        $push_idn = substr($push_idn . $push_idn, 0, 20);

        $q = "SELECT * FROM `gs_users` WHERE `push_notify_identifier`='" . $push_idn . "'";
        $r = mysqli_query($ms, $q);
        $num = mysqli_num_rows($r);

        if ($num == 0) {
            return $push_idn;
        }
    }
}

// #################################################
//  END PASSWORD, API, IDENTIFIER FUNCTIONS
// #################################################

// #################################################
// USER FUNCTIONS
// #################################################

function getUserIdFromAU($au)
{
    global $ms, $gsValues;

    $result = false;

    $q = "SELECT * FROM `gs_users` WHERE `privileges` LIKE '%subuser%' and `privileges` LIKE '%" . $au . "%'";
    $r = mysqli_query($ms, $q);

    if ($row = mysqli_fetch_array($r)) {
        $privileges = json_decode($row['privileges'], true);

        if ($privileges['type'] == 'subuser') {
            if ($privileges['au_active'] == true) {
                if ($privileges['au'] == $au) {
                    if ($row['active'] == "true") {
                        $result = $row['id'];
                    }
                }
            }
        }
    }

    return $result;
}

function getUserIdFromSessionHash()
{
    global $ms, $gsValues;

    $result = false;

    if (isset($_COOKIE['gs_sess_hash'])) {
        $sess_hash = $_COOKIE['gs_sess_hash'];

        $q = "SELECT * FROM `gs_users` WHERE `sess_hash`='" . $sess_hash . "'";
        $r = mysqli_query($ms, $q);

        if ($row = mysqli_fetch_array($r)) {
            $sess_hash_check = md5($gsValues['PATH_ROOT'] . $row['id'] . $row['username'] . $row['password']);

            if ($sess_hash_check == $sess_hash) {
                $result = $row['id'];
            }
        }
    }

    return $result;
}

function setUserSessionHash($id)
{
    global $ms, $gsValues;

    $q = "SELECT * FROM `gs_users` WHERE `id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    $row = mysqli_fetch_array($r);

    $sess_hash = md5($gsValues['PATH_ROOT'] . $row['id'] . $row['username'] . $row['password']);

    $q = "UPDATE gs_users SET `sess_hash`='" . $sess_hash . "' WHERE `id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    $expire = time() + 2592000;
    setcookie("gs_sess_hash", $sess_hash, $expire, '/', null, null, true);
}

function deleteUserSessionHash($id)
{
    global $ms;

    $q = "UPDATE gs_users SET `sess_hash`='' WHERE `id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    $expire = time() + 2592000;
    setcookie("gs_sess_hash", "", time() - $expire, '/');
}

function setUserSession($id)
{
    global $ms, $gsValues;

    if (!ctype_digit($id)) {
        die;
    }

    $_SESSION["user_id"] = $id;
    $_SESSION["session"] = md5($gsValues['PATH_ROOT']);
    $_SESSION["remote_addr"] = md5($_SERVER['REMOTE_ADDR']);

    $q2 = "UPDATE gs_users SET `ip`='" . $_SERVER['REMOTE_ADDR'] . "', `dt_login`='" . gmdate("Y-m-d H:i:s") . "' WHERE `id`='" . $id . "'";
    $r2 = mysqli_query($ms, $q2);
    addRowBinnacle($id, 'nuevo ingreso', 'ingreso un cliente a la plataforma');
}

function setUserSessionSettings($id)
{
    global $ms, $gsValues;

    // set user settings
    $_SESSION = array_merge($_SESSION, getUserData($id));
}

function setUserSessionCPanel($id)
{
    global $ms, $gsValues;

    if (!isset($_SESSION["cpanel_privileges"])) {
        if ($_SESSION['privileges'] == 'super_admin') {
            $_SESSION["cpanel_user_id"] = $id;
            $_SESSION["cpanel_privileges"] = 'super_admin';
            $_SESSION["cpanel_manager_id"] = 0;
        } else if ($_SESSION['privileges'] == 'admin') {
            $_SESSION["cpanel_user_id"] = $id;
            $_SESSION["cpanel_privileges"] = 'admin';
            $_SESSION["cpanel_manager_id"] = 0;
        } else if ($_SESSION['privileges'] == 'manager') {
            $_SESSION["cpanel_user_id"] = $id;
            $_SESSION["cpanel_privileges"] = 'manager';
            $_SESSION["cpanel_manager_id"] = $id;
        } else {
            $_SESSION["cpanel_privileges"] = false;
        }
    }
}

function checkUserSession()
{
    global $gsValues;

    $file = basename($_SERVER['SCRIPT_NAME']);

    if (checkUserSession2() == false) {
        session_unset();
        session_destroy();
        session_start();

        $user_id = getUserIdFromSessionHash();

        if ($user_id != false) {
            setUserSession($user_id);
            setUserSessionSettings($user_id);
            setUserSessionCPanel($user_id);
        }
    }

    if (checkUserSession2() == false) {
        if (($file == 'tracking.php') || ($file == 'cpanel.php')) {
            Header("Location: index.php");
        }

        if (($file != 'index.php') && ($file != 'fn_connect.php')) {
            global $debug_checkUserSession2_reason;
            header("X-Debug-Session: Failed checkUserSession2 within $file. Reason: " . $debug_checkUserSession2_reason);
            @file_put_contents('/var/www/html/debug_latest.txt', "File: $file, Reason: " . $debug_checkUserSession2_reason . "\n", FILE_APPEND);
            if (isset($_POST['cmd'])) { echo json_encode(array('debug_error' => "checkUserSession failed for file $file", 'reason' => $debug_checkUserSession2_reason)); }
            die;
        }
    } else {
        if ($file == 'index.php') {
            if (($gsValues['PAGE_AFTER_LOGIN'] == 'cpanel') && ($_SESSION["cpanel_privileges"] != false)) {
                if (file_exists('cpanel.php')) {
                    Header("Location: cpanel.php");
                } else {
                    Header("Location: tracking.php");
                }
            } else {
                Header("Location: tracking.php");
            }
        }
    }
}

function checkUserSession2()
{
    global $ms, $gsValues, $debug_checkUserSession2_reason;

    $result = false;
    $debug_checkUserSession2_reason = "Unknown";

    if (isset($_SESSION["user_id"]) && isset($_SESSION["session"]) && isset($_SESSION["remote_addr"]) && isset($_SESSION["cpanel_privileges"])) {
        if (checkUserActive($_SESSION["user_id"]) == true) {
            if (($_SESSION["cpanel_privileges"] == false) || ($gsValues['ADMIN_IP_SESSION_CHECK'] == false)) {
                if ($_SESSION["session"] == md5($gsValues['PATH_ROOT'])) {
                    $result = true;
                } else {
                    $debug_checkUserSession2_reason = "PATH_ROOT mismatch: Session " . $_SESSION["session"] . " != " . md5($gsValues['PATH_ROOT']);
                }
            } else {
                if (($_SESSION["session"] == md5($gsValues['PATH_ROOT'])) && ($_SESSION["remote_addr"] == md5($_SERVER['REMOTE_ADDR']))) {
                    $result = true;
                } else {
                    $debug_checkUserSession2_reason = "PATH_ROOT or REMOTE_ADDR mismatch";
                }
            }
        } else {
            $debug_checkUserSession2_reason = "User not active: " . $_SESSION["user_id"];
        }
    } else {
        $missing = [];
        if (!isset($_SESSION["user_id"])) $missing[] = "user_id";
        if (!isset($_SESSION["session"])) $missing[] = "session";
        if (!isset($_SESSION["remote_addr"])) $missing[] = "remote_addr";
        if (!isset($_SESSION["cpanel_privileges"])) $missing[] = "cpanel_privileges";
        $debug_checkUserSession2_reason = "Missing session vars: " . implode(", ", $missing);
    }

    return $result;
}

function checkUserActive($id)
{
    global $ms;

    $q = "SELECT * FROM `gs_users` WHERE `id`='" . $id . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    if ($row['active'] == 'true') {
        return true;
    } else {
        return false;
    }
}

function checkUserCPanelPrivileges()
{
    global $ms, $gsValues;

    if (!isset($_SESSION["cpanel_privileges"])) {
        header("X-Debug-Session: No cpanel privileges");
        if (isset($_POST['cmd'])) { echo json_encode(array('debug_error' => "No cpanel privileges")); }
        die;
    }

    if ($_SESSION["cpanel_privileges"] == false) {
        header("X-Debug-Session: cpanel privileges false");
        if (isset($_POST['cmd'])) { echo json_encode(array('debug_error' => "Cpanel privileges is false")); }
        die;
    }

    if (($_SESSION["cpanel_privileges"] == 'super_admin') || ($_SESSION["cpanel_privileges"] == 'admin')) {
        if ($gsValues['ADMIN_IP'] != '') {
            $admin_ips = explode(",", $gsValues['ADMIN_IP']);
            if (!in_array($_SERVER['REMOTE_ADDR'], $admin_ips)) {
                header("X-Debug-Session: Admin IP mismatch");
                if (isset($_POST['cmd'])) { echo json_encode(array('debug_error' => "Admin IP mismatch")); }
                die;
            }
        }
    }

    if ($_SESSION["user_id"] != $_SESSION['cpanel_user_id']) {
        setUserSession($_SESSION['cpanel_user_id']);
    }
}

function getUserName($imei)
{
    global $ms;

    $q = "SELECT gs_users.username
          FROM gs_user_objects
          JOIN gs_users ON gs_user_objects.user_id = gs_users.id
          WHERE gs_user_objects.imei = '$imei'
          AND gs_users.id NOT IN (2, 10, 45, 67, 171, 172, 311, 345, 485, 689, 621, 723, 766, 767, 768, 769, 770, 772, 1140, 1150, 1167, 1171, 1219)";

    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    return $row ? $row['username'] : null;
}

function getUserActive($username)
{
    global $ms;


    $q = "SELECT gs_users.active FROM gs_users
    WHERE `username`='" . $username . "'";

    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_assoc($r);
    $usernames_active = $row['active'];

    if ($usernames_active === "false") {
        return false; // Usuario desactivado
    } else {
        return true; // Usuario activado
    }
}

function getUserName_Report($imei)
{
    global $ms;

    $usernames_ids = array();
    $excluded_ids = "(1, 171, 172, 290, 311, 316, 320, 345, 689, 621, 720, 723, 766, 767, 768, 769, 770, 772, 1024, 1046, 1049, 1050, 1051, 1052, 1053, 1054, 1059, 1060, 1066, 1067, 1140, 1441, 1150, 1167, 1171, 1219, 1599)";

    $q = "SELECT gs_users.id, gs_users.username
          FROM gs_user_objects
          JOIN gs_users ON gs_user_objects.user_id = gs_users.id
          WHERE gs_user_objects.imei = $imei AND gs_users.id NOT IN $excluded_ids";


    $result = mysqli_query($ms, $q);

    while ($row = mysqli_fetch_assoc($result)) {
        $usernames_ids[] = $row['username'];
    }

    $usernames_ids_unique = array_unique($usernames_ids);


    return implode(', ', $usernames_ids_unique);
}



function getUserData($id)
{
    global $gsValues, $ms, $la;

    $result = array();

    $q = "SELECT * FROM `gs_users` WHERE `id`='" . $id . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    $result["user_id"] = $id;
    $result["active"] = $row['active'];
    $result["manager_id"] = $row['manager_id'];
    $result["manager_billing"] = $row["manager_billing"];

    $privileges = json_decode($row['privileges'], true);
    $privileges = checkUserPrivilegesArray($privileges);

    if ($privileges["type"] == 'subuser') {
        $result["privileges"] = $privileges["type"];

        $privileges["imei"] = explode(",", $privileges["imei"]);
        $result["privileges_imei"] = '"' . implode('","', $privileges["imei"]) . '"';

        $privileges["marker"] = explode(",", $privileges["marker"]);
        $result["privileges_marker"] = '"' . implode('","', $privileges["marker"]) . '"';

        $privileges["route"] = explode(",", $privileges["route"]);
        $result["privileges_route"] = '"' . implode('","', $privileges["route"]) . '"';

        $privileges["zone"] = explode(",", $privileges["zone"]);
        $result["privileges_zone"] = '"' . implode('","', $privileges["zone"]) . '"';

        // check manager user privileges, in case some of them are not available, reset subuser privileges
        $q2 = "SELECT * FROM `gs_users` WHERE `id`='" . $row['manager_id'] . "'";
        $r2 = mysqli_query($ms, $q2);
        $row2 = mysqli_fetch_array($r2);
        $manager_privileges = json_decode($row2['privileges'], true);
        $manager_privileges = checkUserPrivilegesArray($manager_privileges);

        if ($manager_privileges["dashboard"] == false) {
            $privileges["dashboard"] = false;
        }
        if ($manager_privileges["history"] == false) {
            $privileges["history"] = false;
        }
        if ($manager_privileges["reports"] == false) {
            $privileges["reports"] = false;
        }
        if ($manager_privileges["tachograph"] == false) {
            $privileges["tachograph"] = false;
        }
        if ($manager_privileges["tasks"] == false) {
            $privileges["tasks"] = false;
        }
        if ($manager_privileges["rilogbook"] == false) {
            $privileges["rilogbook"] = false;
        }
        if ($manager_privileges["dtc"] == false) {
            $privileges["dtc"] = false;
        }
        if ($manager_privileges["maintenance"] == false) {
            $privileges["maintenance"] = false;
        }
        if ($manager_privileges["expenses"] == false) {
            $privileges["expenses"] = false;
        }
        if ($manager_privileges["object_control"] == false) {
            $privileges["object_control"] = false;
        }
        if ($manager_privileges["image_gallery"] == false) {
            $privileges["image_gallery"] = false;
        }
        if ($manager_privileges["chat"] == false) {
            $privileges["chat"] = false;
        }

        if ($manager_privileges["subaccounts"] == false) {
            $privileges["subaccounts"] = false;
        }

        $result["privileges_map_osm"] = $manager_privileges["map_osm"];
        $result["privileges_map_bing"] = $manager_privileges["map_bing"];
        $result["privileges_map_google"] = $manager_privileges["map_google"];
        $result["privileges_map_google_street_view"] = $manager_privileges["map_google_street_view"];
        $result["privileges_map_google_traffic"] = $manager_privileges["map_google_traffic"];
        $result["privileges_map_mapbox"] = $manager_privileges["map_mapbox"];
        $result["privileges_map_yandex"] = $manager_privileges["map_yandex"];

        $result["privileges_dashboard"] = $privileges["dashboard"];
        $result["privileges_history"] = $privileges["history"];
        $result["privileges_reports"] = $privileges["reports"];
        $result["privileges_tachograph"] = $privileges["tachograph"];
        $result["privileges_tasks"] = $privileges["tasks"];
        $result["privileges_rilogbook"] = $privileges["rilogbook"];
        $result["privileges_dtc"] = $privileges["dtc"];
        $result["privileges_maintenance"] = $privileges["maintenance"];
        $result["privileges_expenses"] = $privileges["expenses"];
        $result["privileges_object_control"] = $privileges["object_control"];
        $result["privileges_image_gallery"] = $privileges["image_gallery"];
        $result["privileges_chat"] = $privileges["chat"];
        $result["privileges_events"] = $privileges["events"];
        $result["privileges_subaccounts"] = $privileges["subaccounts"];
        $result["privileges_shared_zones"] = isset($privileges["shared_zones"]) ? $privileges["shared_zones"] : false;
    } else {
        $result["privileges"] = $privileges["type"];
        $result["privileges_imei"] = '';
        $result["privileges_marker"] = '';
        $result["privileges_route"] = '';
        $result["privileges_zone"] = '';

        $result["privileges_map_osm"] = $privileges["map_osm"];
        $result["privileges_map_bing"] = $privileges["map_bing"];
        $result["privileges_map_google"] = $privileges["map_google"];
        $result["privileges_map_google_street_view"] = $privileges["map_google_street_view"];
        $result["privileges_map_google_traffic"] = $privileges["map_google_traffic"];
        $result["privileges_map_mapbox"] = $privileges["map_mapbox"];
        $result["privileges_map_yandex"] = $privileges["map_yandex"];

        $result["privileges_dashboard"] = $privileges["dashboard"];
        $result["privileges_history"] = $privileges["history"];
        $result["privileges_reports"] = $privileges["reports"];
        $result["privileges_tachograph"] = $privileges["tachograph"];
        $result["privileges_tasks"] = $privileges["tasks"];
        $result["privileges_rilogbook"] = $privileges["rilogbook"];
        $result["privileges_dtc"] = $privileges["dtc"];
        $result["privileges_maintenance"] = $privileges["maintenance"];
        $result["privileges_expenses"] = $privileges["expenses"];
        $result["privileges_object_control"] = $privileges["object_control"];
        $result["privileges_image_gallery"] = $privileges["image_gallery"];
        $result["privileges_chat"] = $privileges["chat"];
        $result["privileges_events"] = $privileges["events"];
        $result["privileges_subaccounts"] = $privileges["subaccounts"];
    }

    // billing
    if (($gsValues['BILLING'] == 'true') && ($privileges["type"] != 'subuser')) {
        $result["billing"] = true;

        if ($row["manager_id"] != 0) {
            $q2 = "SELECT * FROM `gs_users` WHERE `id`='" . $row['manager_id'] . "'";
            $r2 = mysqli_query($ms, $q2);
            $row2 = mysqli_fetch_array($r2);

            if ($row2['manager_billing'] == 'true') {
                $result["billing"] = true;
            } else {
                $result["billing"] = false;
            }
        }
    } else {
        $result["billing"] = false;
    }

    $result["username"] = $row['username'];
    $result["email"] = $row['email'];
    $result["api"] = stringToBool($row['api']);
    $result["api_key"] = $row['api_key'];
    $result["info"] = $row['info'];

    $result["obj_add"] = $row['obj_add'];
    $result["obj_limit"] = $row['obj_limit'];
    $result["obj_limit_num"] = $row['obj_limit_num'];
    $result["obj_days"] = $row['obj_days'];
    $result["obj_days_dt"] = $row['obj_days_dt'];
    $result["obj_edit"] = $row['obj_edit'];
    $result["obj_delete"] = $row['obj_delete'];
    $result["obj_history_clear"] = $row['obj_history_clear'];

    $result["currency"] = $row['currency'];
    $result["timezone"] = $row['timezone'];

    $result["dst"] = $row['dst'];
    $result["dst_start"] = $row['dst_start'];
    $result["dst_end"] = $row['dst_end'];

    if ($row['startup_tab'] == '') {
        $result["startup_tab"] = 'map';
    } else {
        $result["startup_tab"] = $row['startup_tab'];
    }

    $result["language"] = $row['language'];

    $result["chat_notify"] = $row['chat_notify'];

    $result["dashboard"] = $row['dashboard'];

    $result["map_sp"] = $row['map_sp'];
    $result["map_is"] = $row['map_is'];

    if ($row['map_rc'] == '') {
        $result["map_rc"] = '#FF0000';
    } else {
        $result["map_rc"] = $row['map_rc'];
    }

    if ($row['map_rhc'] == '') {
        $result["map_rhc"] = '#0800FF';
    } else {
        $result["map_rhc"] = $row['map_rhc'];
    }

    if ($row['map_ocp'] == '') {
        $result["map_ocp"] = 'true';
    } else {
        $result["map_ocp"] = $row['map_ocp'];
    }

    $result["groups_collapsed"] = $row['groups_collapsed'];
    $result["od"] = $row['od'];
    $result["ohc"] = $row['ohc'];

    if ($row['datalist'] == '') {
        $result["datalist"] = 'bottom_panel';
    } else {
        $result["datalist"] = $row['datalist'];
    }

    if ($row['datalist_items'] == '') {
        $result["datalist_items"] = 'odometer,engine_hours,status,model,vin,plate_number,sim_number,iccid,driver,trailer,time_position,time_server,address,position,speed,altitude,angle,nearest_zone,nearest_marker';
    } else {
        $result["datalist_items"] = $row['datalist_items'];
    }

    $result["push_notify_identifier"] = $row['push_notify_identifier'];

    if ($result["push_notify_identifier"] == '') {
        $result["push_notify_identifier"] = genPushIdn($result["email"]);

        $q2 = "UPDATE `gs_users` SET push_notify_identifier='" . $result["push_notify_identifier"] . "' WHERE `id`='" . $result["user_id"] . "'";
        $r2 = mysqli_query($ms, $q2);
    }

    if ($row['push_notify_desktop'] == '') {
        $result["push_notify_desktop"] = 'false';
    } else {
        $result["push_notify_desktop"] = $row['push_notify_desktop'];
    }

    if ($row['push_notify_mobile'] == '') {
        $result["push_notify_mobile"] = 'false';
    } else {
        $result["push_notify_mobile"] = $row['push_notify_mobile'];
    }

    if ($row['push_notify_mobile_interval'] == 0) {
        $result["push_notify_mobile_interval"] = 10;
    } else {
        $result["push_notify_mobile_interval"] = $row['push_notify_mobile_interval'];
    }

    $result["sms_gateway_server"] = $row['sms_gateway_server'];
    $result["sms_gateway"] = $row['sms_gateway'];
    $result["sms_gateway_type"] = $row['sms_gateway_type'];
    $result["sms_gateway_url"] = $row['sms_gateway_url'];
    $result["sms_gateway_identifier"] = $row['sms_gateway_identifier'];

    if ($result['sms_gateway_identifier'] == '') {
        $result['sms_gateway_identifier'] = genSMSGatewayIdn($result["email"]);

        $q2 = "UPDATE `gs_users` SET sms_gateway_identifier='" . $result["sms_gateway_identifier"] . "' WHERE `id`='" . $result["user_id"] . "'";
        $r2 = mysqli_query($ms, $q2);
    }

    $result["places_markers"] = $row['places_markers'];
    $result["places_routes"] = $row['places_routes'];
    $result["places_zones"] = $row['places_zones'];

    if ($row['usage_email_daily'] == '') {
        $result["usage_email_daily"] = $gsValues['USAGE_EMAIL_DAILY'];
    } else {
        $result["usage_email_daily"] = $row['usage_email_daily'];
    }

    if ($row['usage_sms_daily'] == '') {
        $result["usage_sms_daily"] = $gsValues['USAGE_SMS_DAILY'];
    } else {
        $result["usage_sms_daily"] = $row['usage_sms_daily'];
    }

    if ($row['usage_webhook_daily'] == '') {
        $result["usage_webhook_daily"] = $gsValues['USAGE_WEBHOOK_DAILY'];
    } else {
        $result["usage_webhook_daily"] = $row['usage_webhook_daily'];
    }

    if ($row['usage_api_daily'] == '') {
        $result["usage_api_daily"] = $gsValues['USAGE_API_DAILY'];
    } else {
        $result["usage_api_daily"] = $row['usage_api_daily'];
    }

    // units
    $result["units"] = $row['units'];
    $result = array_merge($result, getUnits($row['units']));

    return $result;
}

function convUserTimezone($dt)
{
    if (!isset($_SESSION["timezone"])) {
        $_SESSION["timezone"] = "+ 0 hour";
    }

    if (!isset($_SESSION["dst"])) {
        $_SESSION["dst"] = "false";
    }

    if (strtotime($dt) > 0) {
        $dt = gmdate("Y-m-d H:i:s", strtotime($dt . $_SESSION["timezone"]));

        // DST
        if ($_SESSION["dst"] == 'true') {
            $dt_ = gmdate('m-d H:i:s', strtotime($dt));
            $dst_start = $_SESSION["dst_start"] . ':00';
            $dst_end = $_SESSION["dst_end"] . ':00';

            if (isDateInRange(convDateToNum($dt_), convDateToNum($dst_start), convDateToNum($dst_end))) {
                $dt = gmdate("Y-m-d H:i:s", strtotime($dt . '+ 1 hour'));
            }
        }
    }

    return $dt;
}

function TimeReport($dt)
{
    if (!isset($dt) || strtotime($dt) === false) {
        return "Sin reportar";
    }

    $dt_timestamp = strtotime($dt) - (6 * 60 * 60); // Resta 6 horas

    $diff_seconds = time() - $dt_timestamp;

    // Convierte la diferencia a días
    $diff_days = $diff_seconds / (60 * 60 * 24);

    if ($diff_days > 1) {
        return "Sin reportar";
    } else {
        return "Reportando";
    }
}

function getEvent($imei)
{
    global $ms;

    $q = "SELECT * FROM `gs_object_data` WHERE `imei`='" . $imei . "' LIMIT 1";
    $r = mysqli_query($ms, $q);

    if ($r && mysqli_num_rows($r) > 0) {
        $row = mysqli_fetch_array($r);
        $event = $row['event'];

        if ($event != '') {
            return $event;
        } else {
            return 'Sin Seguimiento';
        }
    } else {
        return 'Sin Seguimiento';
    }
}
function getUserId($imei)
{
    global $ms;

    $q = "SELECT * FROM `gs_user_objects` WHERE `imei`='" . $imei . "' LIMIT 1";
    $r = mysqli_query($ms, $q);

    if ($r && mysqli_num_rows($r) > 0) {
        $row = mysqli_fetch_array($r);
        return $row['user_id'];
    } else {
        return 0;
    }
}
function getUserName_($user_id)
{
    global $ms;

    $q = "SELECT * FROM `gs_users` WHERE `id`='" . $user_id . "' AND `manager_id`='0' LIMIT 1";
    $r = mysqli_query($ms, $q);

    if ($r && mysqli_num_rows($r) > 0) {
        $row = mysqli_fetch_array($r);
        return $row['username'];
    } else {
        return 'N/A';
    }
}
function getUserSimNumber($imei)
{
    global $ms;

    $q = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    if ($r && mysqli_num_rows($r) > 0) {
        $row = mysqli_fetch_array($r);
        return $row['sim_number'];
    } else {
        return '0';
    }
}

function getUserSeguimiento($imei)
{
    global $ms;

    $q = "SELECT * FROM `gs_object_data_details` WHERE `imei`='" . $imei . "' ORDER BY id DESC LIMIT 1";
    $r = mysqli_query($ms, $q);

    if ($r && mysqli_num_rows($r) > 0) {
        $row = mysqli_fetch_array($r);
        $attended_status = $row['attended_status'];

        if ($attended_status != '') {
            return $attended_status;
        } else {
            return 'Sin Seguimiento';
        }
    } else {
        return 'Sin Seguimiento';
    }
}

function getUserEmail($imei)
{
    global $ms;

    $q = "SELECT * FROM `gs_object_data` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);
    $email_user = $row['email_user'];

    return $email_user;
}
function getUserEmailAlert($user_id)
{
    global $ms;

    $q = "SELECT * FROM `gs_users` WHERE `id`='" . $user_id . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);
    $email_user = $row['email'];

    return $email_user;
}
function getComment($imei)
{
    global $ms;

    $q = "SELECT * FROM `gs_object_data_details` WHERE `imei`='" . $imei . "' ORDER BY `id` DESC LIMIT 1";

    $r = mysqli_query($ms, $q);

    if ($r && mysqli_num_rows($r) > 0) {
        $row = mysqli_fetch_array($r);
        $comment = $row['comment'];

        if ($comment != '') {
            return $comment;
        } else {
            return 'Sin Comentario';
        }
    } else {
        return 'Sin Comentario';
    }
}

function getStatus($imei)
{
    global $ms;

    $q = "SELECT * FROM `gs_object_data_details` WHERE `imei`='" . $imei . "' ORDER BY `id` DESC LIMIT 1";

    $r = mysqli_query($ms, $q);
    if ($r && mysqli_num_rows($r) > 0) {
        $row = mysqli_fetch_array($r);
        $status = $row['attended_status'];


        if ($status != '') {
            return $status;
        } else {
            return 'Sin Agendar';
        }
    } else {
        return 'Sin Agendar';
    }
}

function convUserUTCTimezone($dt)
{
    if (strtotime($dt) > 0) {
        if (substr($_SESSION["timezone"], 0, 1) == "+") {
            $timezone_diff = str_replace("+", "-", $_SESSION["timezone"]);
        } else {
            $timezone_diff = str_replace("-", "+", $_SESSION["timezone"]);
        }

        $dt = gmdate("Y-m-d H:i:s", strtotime($dt . $timezone_diff));

        // DST
        if ($_SESSION["dst"] == 'true') {
            $dt_ = gmdate('m-d H:i:s', strtotime($dt));
            $dst_start = $_SESSION["dst_start"] . ':00';
            $dst_end = $_SESSION["dst_end"] . ':00';

            if (isDateInRange(convDateToNum($dt_), convDateToNum($dst_start), convDateToNum($dst_end))) {
                $dt = gmdate("Y-m-d H:i:s", strtotime($dt . '- 1 hour'));
            }
        }
    }

    return $dt;
}

function convUserIDTimezone($user_id, $dt)
{
    global $ms;

    if (strtotime($dt) > 0) {
        $q = "SELECT * FROM `gs_users` WHERE `id`='" . $user_id . "'";
        $r = mysqli_query($ms, $q);

        if (!$r) {
            return false;
        }

        $row = mysqli_fetch_array($r);

        if ($row) {
            $dt = gmdate("Y-m-d H:i:s", strtotime($dt . $row["timezone"]));

            // DST
            if ($row["dst"] == 'true') {
                $dt_ = gmdate('m-d H:i:s', strtotime($dt));
                $dst_start = $row["dst_start"] . ':00';
                $dst_end = $row["dst_end"] . ':00';

                if (isDateInRange(convDateToNum($dt_), convDateToNum($dst_start), convDateToNum($dst_end))) {
                    $dt = gmdate("Y-m-d H:i:s", strtotime($dt . '+ 1 hour'));
                }
            }
        }
    }

    return $dt;
}

function convUserIDUTCTimezone($user_id, $dt)
{
    global $ms;

    if (strtotime($dt) > 0) {
        $q = "SELECT * FROM `gs_users` WHERE `id`='" . $user_id . "'";
        $r = mysqli_query($ms, $q);

        if (!$r) {
            return false;
        }

        $row = mysqli_fetch_array($r);

        if ($row) {
            if (substr($row["timezone"], 0, 1) == "+") {
                $timezone_diff = str_replace("+", "-", $row["timezone"]);
            } else {
                $timezone_diff = str_replace("-", "+", $row["timezone"]);
            }

            $dt = gmdate("Y-m-d H:i:s", strtotime($dt . $timezone_diff));

            // DST
            if ($row["dst"] == 'true') {
                $dt_ = gmdate('m-d H:i:s', strtotime($dt));
                $dst_start = $row["dst_start"] . ':00';
                $dst_end = $row["dst_end"] . ':00';

                if (isDateInRange(convDateToNum($dt_), convDateToNum($dst_start), convDateToNum($dst_end))) {
                    $dt = gmdate("Y-m-d H:i:s", strtotime($dt . '- 1 hour'));
                }
            }
        }
    }

    return $dt;
}

function checkUserToObjectPrivileges($id, $imei)
{
    global $ms;

    $q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='" . $id . "' AND `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    if ($row) {
        return true;
    } else {
        return false;
    }
}

function checkSubuserToObjectPrivileges($imeis, $imei)
{
    $imeis = str_replace('"', '', $imeis);

    $imeis = explode(',', $imeis);

    if (in_array($imei, $imeis)) {
        return true;
    } else {
        return false;
    }
}

function checkUsernameExists($username)
{
    global $ms;

    $username = strtolower($username);

    $q = "SELECT * FROM `gs_users` WHERE `username`='" . $username . "' LIMIT 1";
    $r = mysqli_query($ms, $q);
    $num = mysqli_num_rows($r);

    if ($num == 0) {
        return false;
    } else {
        return true;
    }
}

function checkEmailExists($email)
{
    global $ms;

    $email = strtolower($email);

    $q = "SELECT * FROM `gs_users` WHERE `email`='" . $email . "' LIMIT 1";
    $r = mysqli_query($ms, $q);
    $num = mysqli_num_rows($r);

    if ($num == 0) {
        return false;
    } else {
        return true;
    }
}

function addUser($send, $active, $info, $account_expire, $account_expire_dt, $privileges, $manager_id, $username, $email, $password, $obj_add, $obj_limit, $obj_limit_num, $obj_days, $obj_days_num, $obj_edit, $obj_delete, $obj_history_clear, $api, $api_key)
{
    global $ms, $gsValues, $la;
    //error_log($info);
    $status = false;

    $result = '';

    $email = strtolower($email);
    $username = strtolower($username);

    if (!checkEmailExists($email)) {
        if ($username == '') {
            $username = $email;
        }

        if (!checkUsernameExists($username)) {
            if ($password == '') {
                $password = genAccountPassword();
            }

            $privileges_ = json_decode(stripslashes($privileges), true);

            if (isset($_SESSION['LANGUAGE'])) {
                $language = $_SESSION['LANGUAGE'];
            } else {
                $language = $gsValues['LANGUAGE'];
            }

            if (($privileges_['type'] == 'subuser') && (@$privileges_['au_active'] == true)) {
                $url_au = $gsValues['URL_ROOT'] . "/index.php?au=" . $privileges_['au'];
                $url_au_mobile = $gsValues['URL_ROOT'] . "/index.php?au=" . $privileges_['au'] . '&m=true';

                $template = getDefaultTemplate('account_registration_au', $language);

                $subject = $template['subject'];
                $message = $template['message'];

                $subject = str_replace("%SERVER_NAME%", $gsValues['NAME'], $subject);
                $subject = str_replace("%URL_AU%", $url_au, $subject);
                $subject = str_replace("%URL_AU_MOBILE%", $url_au_mobile, $subject);

                $message = str_replace("%SERVER_NAME%", $gsValues['NAME'], $message);
                $message = str_replace("%URL_AU%", $url_au, $message);
                $message = str_replace("%URL_AU_MOBILE%", $url_au_mobile, $message);
            } else {
                $template = getDefaultTemplate('account_registration', $language);

                $subject = $template['subject'];
                $message = $template['message'];

                $subject = str_replace("%SERVER_NAME%", $gsValues['NAME'], $subject);
                $subject = str_replace("%URL_LOGIN%", $gsValues['URL_LOGIN'], $subject);
                $subject = str_replace("%EMAIL%", $email, $subject);
                $subject = str_replace("%USERNAME%", $username, $subject);
                $subject = str_replace("%PASSWORD%", $password, $subject);

                $message = str_replace("%SERVER_NAME%", $gsValues['NAME'], $message);
                $message = str_replace("%URL_LOGIN%", $gsValues['URL_LOGIN'], $message);
                $message = str_replace("%EMAIL%", $email, $message);
                $message = str_replace("%USERNAME%", $username, $message);
                $message = str_replace("%PASSWORD%", $password, $message);
            }

            if ($send == 'true') {
                if (sendEmail($email, $subject, $message)) {
                    $status = true;
                }
            } else {
                $status = true;
            }


            if ($status == true) {
                if ($privileges_['type'] == 'super_admin') {
                    $api = $gsValues['API'];
                    $api_key = genUserAPIKey($email);
                }

                if ($obj_limit == 'false') {
                    $obj_limit_num = 0;
                }

                if ($obj_days == 'true') {
                    $obj_days_dt = gmdate("Y-m-d", strtotime(gmdate("Y-m-d") . ' + ' . $obj_days_num . ' days'));
                } else {
                    $obj_days_dt = '';
                }

                $dst = $gsValues['DST'];

                if ($dst == 'true') {
                    $dst_start = $gsValues['DST_START'];
                    $dst_end = $gsValues['DST_END'];
                } else {
                    $dst_start = '';
                    $dst_end = '';
                }

                $units = $gsValues['UNIT_OF_DISTANCE'] . ',' . $gsValues['UNIT_OF_CAPACITY'] . ',' . $gsValues['UNIT_OF_TEMPERATURE'];

                $q = "INSERT INTO gs_users (	`active`,
									`account_expire`,
									`account_expire_dt`,
									`privileges`,
									`manager_id`,
									`username`, 
									`password`, 
									`email`,
                                    `info`,
									`api`,
									`api_key`,
									`dt_reg`,
									`obj_add`, 
									`obj_limit`,
									`obj_limit_num`,
									`obj_days`,
									`obj_days_dt`,
									`obj_edit`,
									`obj_delete`,
									`obj_history_clear`,
									`currency`,
									`timezone`,
									`dst`,
									`dst_start`,
									`dst_end`,
									`language`,
									`units`,
									`map_sp`,
									`map_is`,
									`sms_gateway_server`)
									VALUES
									('" . $active . "',
									'" . $account_expire . "',
									'" . $account_expire_dt . "',
									'" . $privileges . "',
									'" . $manager_id . "',
									'" . $username . "',
									'" . md5($password) . "',
									'" . $email . "',
                                    '" . $info . "',
									'" . $api . "',
									'" . $api_key . "',
									'" . gmdate("Y-m-d H:i:s") . "',
									'" . $obj_add . "',
									'" . $obj_limit . "',
									'" . $obj_limit_num . "',
									'" . $obj_days . "',
									'" . $obj_days_dt . "',
									'" . $obj_edit . "',
									'" . $obj_delete . "',
									'" . $obj_history_clear . "',
									'" . $gsValues['CURRENCY'] . "',
									'" . $gsValues['TIMEZONE'] . "',
									'" . $dst . "',
									'" . $dst_start . "',
									'" . $dst_end . "',
									'" . $gsValues['LANGUAGE'] . "',
									'" . $units . "',
									'last',
									'1',
									'" . $gsValues['SMS_GATEWAY_SERVER'] . "'
									)";

                $r = mysqli_query($ms, $q);

                //write log
                writeLog('user_access', 'User registration: successful. E-mail: ' . $email);

                $result = 'OK';
            } else {
                $result = 'ERROR_NOT_SENT';
            }
        } else {
            $result = 'ERROR_USERNAME_EXISTS';
        }
    } else {
        $result = 'ERROR_EMAIL_EXISTS';
    }

    return $result;
}

function delUser($id)
{
    global $ms, $gsValues;

    $q = "SELECT * FROM `gs_users` WHERE `id`='" . $id . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);
    $username = $row['username'];

    addRowBinnacle($_SESSION["user_id"], 'Baja de usuario: ' . $username, $q);

    $q = "DELETE FROM `gs_users` WHERE `id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    // delete user sub users
    $q = "DELETE FROM `gs_users` WHERE `privileges` LIKE '%subuser%' AND `manager_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    //$q = "DELETE FROM `gs_user_usage` WHERE `user_id`='".$id."'";
    //$r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_billing_plans` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_markers` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_routes` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_zones` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_objects` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_object_groups` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    // delete drivers
    $q = "SELECT * FROM `gs_user_object_drivers` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    while ($row = mysqli_fetch_array($r)) {
        $img_file = $gsValues['PATH_ROOT'] . 'data/user/drivers/' . $row['driver_img_file'];
        if (is_file($img_file)) {
            @unlink($img_file);
        }
    }

    $q = "DELETE FROM `gs_user_object_drivers` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_object_passengers` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_object_trailers` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_object_cmd_exec` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_cmd` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_cmd_schedule` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_reports` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_share_position` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    // delete user events
    $q = "SELECT * FROM `gs_user_events` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    while ($row = mysqli_fetch_array($r)) {
        $event_id = $row['event_id'];

        $q2 = "DELETE FROM `gs_user_events_status` WHERE `event_id`='" . $event_id . "'";
        $r2 = mysqli_query($ms, $q2);
    }

    // delete kml
    $q = "SELECT * FROM `gs_user_kml` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    while ($row = mysqli_fetch_array($r)) {
        $kml_file = $gsValues['PATH_ROOT'] . 'data/user/kml/' . $row['kml_file'];
        if (is_file($kml_file)) {
            @unlink($kml_file);
        }
    }

    $q = "DELETE FROM `gs_user_kml` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_expenses` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_templates` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_events` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_last_events_data` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_events_data` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);
}

function getUserObjectIMEIs($id)
{
    global $ms;

    $result = false;

    $q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    while ($row = mysqli_fetch_array($r)) {
        $result .= '"' . $row['imei'] . '",';
    }
    $result = rtrim($result, ',');

    return $result;
}

function getObjectClient($imei, $id)
{
    global $ms;

    $q = "SELECT client_id FROM `gs_user_objects` WHERE `imei`='" . $imei . "' AND `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);
    $result = $row['client_id'];

    return $result;
}

function getUserBillingTotalObjects($id)
{
    global $ms;

    $objects = 0;

    $q = "SELECT * FROM `gs_user_billing_plans` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);

    while ($row = mysqli_fetch_array($r)) {
        $objects += $row['objects'];
    }

    return $objects;
}

function getUserNumberOfMarkers($id)
{
    global $ms;

    $q = "SELECT * FROM `gs_user_markers` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);
    $count = mysqli_num_rows($r);

    return $count;
}

function getUserNumberOfZones($id)
{
    global $ms;

    $q = "SELECT * FROM `gs_user_zones` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);
    $count = mysqli_num_rows($r);

    return $count;
}

function getUserNumberOfRoutes($id)
{
    global $ms;

    $q = "SELECT * FROM `gs_user_routes` WHERE `user_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);
    $count = mysqli_num_rows($r);

    return $count;
}

function getUserPrivileges($id, $perm = null)
{
    global $ms;

    $q = "SELECT `privileges` 
          FROM `gs_users` 
          WHERE `id`='" . intval($id) . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    if (!$row) return '';

    $privileges_json = json_decode($row['privileges'], true);

    if (!is_array($privileges_json)) return '';

    // ===============================
    // SI PIDEN PERMISO ESPECÍFICO
    // ===============================
    if ($perm !== null) {
        return (
            isset($privileges_json[$perm]) &&
            $privileges_json[$perm] === "true"
        ) ? $privileges_json[$perm] : '';
    }

    // ===============================
    // SI NO → REGRESA TODOS LOS perm_*
    // ===============================
    return array_filter(
        $privileges_json,
        function ($v, $k) {
            return strpos($k, 'perm_') === 0;
        },
        ARRAY_FILTER_USE_BOTH
    );
}


function checkUserPrivilegesArray($privileges)
{
    global $gsValues;

    if (!isset($privileges["map_osm"])) {
        $privileges["map_osm"] = stringToBool($gsValues['MAP_OSM']);
    }
    if (!isset($privileges["map_bing"])) {
        $privileges["map_bing"] = stringToBool($gsValues['MAP_BING']);
    }
    if (!isset($privileges["map_google"])) {
        $privileges["map_google"] = stringToBool($gsValues['MAP_GOOGLE']);
    }
    if (!isset($privileges["map_google_street_view"])) {
        $privileges["map_google_street_view"] = stringToBool($gsValues['MAP_GOOGLE_STREET_VIEW']);
    }
    if (!isset($privileges["map_google_traffic"])) {
        $privileges["map_google_traffic"] = stringToBool($gsValues['MAP_GOOGLE_TRAFFIC']);
    }
    if (!isset($privileges["map_mapbox"])) {
        $privileges["map_mapbox"] = stringToBool($gsValues['MAP_MAPBOX']);
    }
    if (!isset($privileges["map_yandex"])) {
        $privileges["map_yandex"] = stringToBool($gsValues['MAP_YANDEX']);
    }
    if (!isset($privileges["dashboard"])) {
        $privileges["dashboard"] = true;
    }
    if (!isset($privileges["history"])) {
        $privileges["history"] = true;
    }
    if (!isset($privileges["reports"])) {
        $privileges["reports"] = true;
    }
    if (!isset($privileges["tachograph"])) {
        $privileges["tachograph"] = true;
    }
    if (!isset($privileges["tasks"])) {
        $privileges["tasks"] = true;
    }
    if (!isset($privileges["rilogbook"])) {
        $privileges["rilogbook"] = true;
    }
    if (!isset($privileges["dtc"])) {
        $privileges["dtc"] = true;
    }
    if (!isset($privileges["maintenance"])) {
        $privileges["maintenance"] = true;
    }
    if (!isset($privileges["expenses"])) {
        $privileges["expenses"] = true;
    }
    if (!isset($privileges["object_control"])) {
        $privileges["object_control"] = true;
    }
    if (!isset($privileges["image_gallery"])) {
        $privileges["image_gallery"] = true;
    }
    if (!isset($privileges["chat"])) {
        $privileges["chat"] = true;
    }
    if (!isset($privileges["events"])) {
        $privileges["events"] = false;
    }
    if (!isset($privileges["subaccounts"])) {
        $privileges["subaccounts"] = true;
    }

    return $privileges;
}

// #################################################
//  END USER FUNCTIONS
// #################################################

// #################################################
// OBJECT FUNCTIONS
// #################################################

function resetSensors($imei, $post)
{
    global $ms;

    $imei_esc = mysqli_real_escape_string($ms, $imei);

    // if (($post['panico'] ?? 'true') && $_POST['device'] ?? 'suntechST310U'){
    //     $q = "SELECT * FROM `gs_user_events` WHERE LOWER(`checked_value`) LIKE '%sos%' AND FIND_IN_SET('$imei_esc', imei)";
    //       $r = mysqli_query($ms, $q);
    //     $row = mysqli_fetch_array($r);

    //     if (!$r) {

    //     }
        

    // }

    $hasDiesel = (
        ($post['s_1'] ?? '') === 'Diesel' ||
        ($post['s_2'] ?? '') === 'Diesel' ||
        ($post['s_3'] ?? '') === 'Diesel'
    );

    $hasDieselBT = (
        ($post['s_1'] ?? '') === 'DieselBT' ||
        ($post['s_2'] ?? '') === 'DieselBT' ||
        ($post['s_3'] ?? '') === 'DieselBT'
    );

    mysqli_query($ms, "
        DELETE FROM gs_object_sensors 
        WHERE imei = '$imei_esc'
    ");

    if ($hasDiesel) {
        mysqli_query($ms, "
            DELETE FROM gs_object_sensors
            WHERE imei = '$imei_esc'
            AND (param LIKE 'adc1' OR param LIKE 'adc2' OR param LIKE 'adc3')
        ");
    }

    if ($hasDieselBT) {
        mysqli_query($ms, "
            DELETE FROM gs_object_sensors
            WHERE imei = '$imei_esc'
            AND (param LIKE 'AdcBT1' OR param LIKE 'AdcBT2')
        ");
    }

    return addSensors($imei_esc, $post);
}


function addSensors($imei, $post)
{
    global $ms;

    $esc = function ($s) use ($ms) { return mysqli_real_escape_string($ms, $s); };
    $strOrNull = function ($v) use ($ms) {
        if (!isset($v) || $v === '' || $v === ' ') return "NULL";
        return "'" . mysqli_real_escape_string($ms, $v) . "'";
    };

    $sensors = [
        "t_motor" => [
            "name" => "Temperatura del Motor", "type" => "temp", "param" => "tempMotor",
            "result_type" => "value", "units" => "°C", "formula" => "x/10"
        ],
        "consumo" => [
            "name" => "Consumo", "type" => "fuel", "param" => "fuelUsed",
            "result_type" => "value", "units" => "Lts", "formula" => "x/100"
        ],
        "c_seguridad" => [
            "name" => "Cinturon de Seguridad", "type" => "cust", "param" => "cinturon",
            "result_type" => "logic", "text_1" => "Abrochado", "text_0" => "Desabrochado"
        ],
        "l_frontales" => [
            "name" => "Luces Frontales", "type" => "cust", "param" => "lucesCruce",
            "result_type" => "logic", "text_1" => "Encendidas", "text_0" => "Apagadas"
        ],
        "l_estacionamiento" => [
            "name" => "Luces de Estacionamiento", "type" => "cust", "param" => "lucesEstac",
            "result_type" => "logic", "text_1" => "Encendidas", "text_0" => "Apagadas"
        ],
        "clutch" => [
            "name" => "Clutch", "type" => "cust", "param" => "embrague",
            "result_type" => "logic", "text_1" => "Activado", "text_0" => "Desactivado"
        ],
        "freno" => [
            "name" => "Freno", "type" => "cust", "param" => "frenoPie",
            "result_type" => "logic", "text_1" => "Activado", "text_0" => "Desactivado"
        ],
        "maletero" => [
            "name" => "Maletero", "type" => "cust", "param" => "maletero",
            "result_type" => "logic", "text_1" => "Abierto", "text_0" => "Cerrado"
        ],
        "p_conductor" => [
            "name" => "Puerta Conductor", "type" => "cust", "param" => "doorFrIzq",
            "result_type" => "logic", "text_1" => "Abierta", "text_0" => "Cerrada"
        ],
        "p_copiloto" => [
            "name" => "Puerta Copiloto", "type" => "cust", "param" => "doorFrDer",
            "result_type" => "logic", "text_1" => "Abierta", "text_0" => "Cerrada"
        ],
        "bloqueo" => [
            "name" => "Bloqueo", "type" => "cust", "param" => "out1",
            "result_type" => "logic", "text_1" => "Bloqueado", "text_0" => "Desbloqueado"
        ],
        "rpm" => [
            "name" => "RPM", "type" => "cust", "param" => "rpm",
            "result_type" => "value", "units" => "rpm"
        ],
        "velocidad" => [
            "name" => "Velocidad", "type" => "cust", "param" => "vel",
            "result_type" => "value", "units" => "km/h"
        ],
        "nivel_combustible" => [
            "name" => "Nivel Combustible", "type" => "fuel", "param" => "fuelLvl",
            "result_type" => "value", "units" => "Lts", "formula" => "x/10"
        ],
        "alimentacion_principal" => [
            "name" => "Alimentación Principal", "type" => "cust", "param" => "power",
            "result_type" => "value", "units" => "V"
        ],
        "ignicion" => [
            "name" => "Ignición", "type" => "acc", "param" => "ignition",
            "result_type" => "logic", "text_1" => "Motor Encendido", "text_0" => "Motor Apagado"
        ],
        "bateria" => [
            "name" => "Batería", "type" => "batt", "param" => "batteryLevel",
            "result_type" => "value", "units" => "%"
        ],
        "f_mano" => [
            "name" => "Freno de Mano", "type" => "cust", "param" => "frenoMano",
            "result_type" => "logic", "text_1" => "Activado", "text_0" => "Desactivado"
        ]
    ];

    $imei_sql = "'" . $esc($imei) . "'";
    $values = [];

    foreach ($sensors as $key => $info) {
        if (!isset($post[$key]) || $post[$key] !== 'true') continue;

        $name        = "'" . $esc($info['name']) . "'";
        $type        = "'" . $esc($info['type']) . "'";
        $param       = "'" . $esc($info['param']) . "'";
        $result_type = "'" . $esc($info['result_type']) . "'";

        $text_1  = $strOrNull($info['text_1']  ?? null);
        $text_0  = $strOrNull($info['text_0']  ?? null);
        $units   = $strOrNull($info['units']   ?? null);
        $formula = $strOrNull($info['formula'] ?? null);

        $values[] = "(
            $imei_sql,
            $name,
            $type,
            $param,
            'true',
            'false',
            $result_type,
            $text_1,
            $text_0,
            $units,
            0,
            0,
            'false',
            $formula,
            '[]',
            '[]'
        )";
    }
    
    $q = "INSERT INTO gs_object_sensors
          (imei, name, type, param, data_list, popup, result_type, text_1, text_0, units, lv, hv, acc_ignore, formula, calibration, dictionary)
          VALUES " . implode(",\n", $values);
    
    $r = mysqli_query($ms, $q);
    
    $values = [];
    $hasDieselLike = false;
    
    for ($i = 1; $i <= 3; $i++) {
        $tipo = $post["s_{$i}"] ?? '';
    
        $sn_key = "sn_{$i}";
        $sn = isset($post[$sn_key]) && is_numeric($post[$sn_key]) ? (int)$post[$sn_key] : $i;
        if ($sn < 1 || $sn > 3) { $sn = $i; }


        if ($tipo === 'false') {
            continue;
        }
    
        if ($tipo === 'Diesel' || $tipo === 'DieselBT') {
    
            $hasDieselLike = true;
    
            $sl_key = "sl_{$i}";
            $sa_key = "sa_{$i}";
    
            $sl = (isset($post[$sl_key]) && is_numeric($post[$sl_key])) ? (float)$post[$sl_key] : null;
            $sa = (isset($post[$sa_key]) && is_numeric($post[$sa_key])) ? (float)$post[$sa_key] : null;
    
            if ($sl !== null && $sa !== null && $sa > 0) {
                $formula_str = "("
                    . $sl
                    . "*(pow((" . $sa . "/2),2)*acos(1-((" . $sa . "/5000)*x)/(" . $sa . "/2))"
                    . "+(((" . $sa . "/5000)*x)-(" . $sa . "/2))*sqrt(((" . $sa . "/5000)*x)*(" . $sa . ")-pow(((" . $sa . "/5000)*x),2)))/1000)+0";
    
                $formula = "'" . $esc($formula_str) . "'";
            } else {
                $formula = "NULL";
            }
    
            if ($tipo === 'Diesel') {
                $paramBase = "adc{$sn}";
            } else {
                $paramBase = "AdcBT{$sn}";
            }
    
            // Sensor de tanque
            $name        = "'" . $esc("Tanque {$sn}") . "'";
            $type        = "'fuel'";
            $param       = "'" . $esc($paramBase) . "'";
            $result_type = "'value'";
            $text_1      = "NULL";
            $text_0      = "NULL";
            $units       = "'Lts'";
    
            $values[] = "(
                $imei_sql,
                $name,
                $type,
                $param,
                'true',
                'false',
                $result_type,
                $text_1,
                $text_0,
                $units,
                0,
                0,
                'false',
                $formula,
                '[]',
                '[]'
            )";
    
            // Si es DieselBT, también agregamos el sensor de temperatura del tanque
            if ($tipo === 'DieselBT') {
                $name2        = "'" . $esc("Temp_Tanque {$sn}") . "'";
                $type2        = "'temp'";
                $param2       = "'" . $esc("TempAdcBT{$sn}") . "'";
                $result_type2 = "'value'";
                $text_1_2     = "NULL";
                $text_0_2     = "NULL";
                $units2       = "'°C'";
                $formula2     = "NULL";
    
                $values[] = "(
                    $imei_sql,
                    $name2,
                    $type2,
                    $param2,
                    'true',
                    'false',
                    $result_type2,
                    $text_1_2,
                    $text_0_2,
                    $units2,
                    0,
                    0,
                    'false',
                    $formula2,
                    '[]',
                    '[]'
                )";
            }
    
        } elseif ($tipo === 'Temperatura') {
    
            $name        = "'" . $esc("Temperatura {$sn}") . "'";
            $type        = "'temp'";
            $param       = "'" . $esc("temp{$sn}") . "'";
            $result_type = "'value'";
            $text_1      = "NULL";
            $text_0      = "NULL";
            $units       = "'°C'";
            $formula     = "NULL";
    
            $values[] = "(
                $imei_sql,
                $name,
                $type,
                $param,
                'true',
                'false',
                $result_type,
                $text_1,
                $text_0,
                $units,
                0,
                0,
                'false',
                $formula,
                '[]',
                '[]'
            )";

        } elseif ($tipo === 'TemperaturaBT') {
    
            $name        = "'" . $esc("Temperatura {$sn}") . "'";
            $type        = "'temp'";
            $param       = "'" . $esc("TempBT{$sn}") . "'";
            $result_type = "'value'";
            $text_1      = "NULL";
            $text_0      = "NULL";
            $units       = "'°C'";
            $formula     = "NULL";
    
            $values[] = "(
                $imei_sql,
                $name,
                $type,
                $param,
                'true',
                'false',
                $result_type,
                $text_1,
                $text_0,
                $units,
                0,
                0,
                'false',
                $formula,
                '[]',
                '[]'
            )";
        }
    }
    
    if ($hasDieselLike) {
        $name_total   = "'" . $esc("Total de Combustible") . "'";
        $type_total   = "'fuelsumup'";
        $param_total  = "''";
        $result_total = "'value'";
        $text1_total  = "NULL";
        $text0_total  = "NULL";
        $units_total  = "'Lts'";
        $formula_total = "NULL";
    
        $values[] = "(
            $imei_sql,
            $name_total,
            $type_total,
            $param_total,
            'true',
            'false',
            $result_total,
            $text1_total,
            $text0_total,
            $units_total,
            0,
            0,
            'false',
            $formula_total,
            '[]',
            '[]'
        )";
    }
    
    if (!$values) {
        return true;
    }
    
    $q = "INSERT INTO gs_object_sensors
          (imei, name, type, param, data_list, popup, result_type, text_1, text_0, units, lv, hv, acc_ignore, formula, calibration, dictionary)
          VALUES " . implode(",\n", $values);
    
    $r = mysqli_query($ms, $q);
    return $r ? true : false; 
}



function addSensorsBasic($imei)
{
    global $ms;

    $q = "
    INSERT INTO gs_object_sensors
    (imei, name, type, param, data_list, popup, result_type, text_1, text_0, units, lv, hv, acc_ignore, formula, calibration, dictionary)
    VALUES
    ('$imei', 'Alimentación Principal', 'cust', 'power', 'true', 'false', 'value', NULL, NULL, 'V', 0, 0, 'false', NULL, '[]', '[]'),
    ('$imei', 'Ignición', 'acc', 'ignition', 'true', 'false', 'logic', 'Motor Encendido', 'Motor Apagado', NULL, 0, 0, 'false', NULL, '[]', '[]'),
    ('$imei', 'Bloqueo', 'cust', 'out1', 'true', 'false', 'logic', 'Bloqueado', 'Desbloqueado', NULL, 0, 0, 'false', NULL, '[]', '[]'),
    ('$imei', 'Batería', 'batt', 'batteryLevel', 'true', 'false', 'value', NULL, NULL, '%', 0, 0, 'false', NULL, '[]', '[]')";

    $r = mysqli_query($ms, $q);

    return $r ? true : false;
}

function checkObjectLimitSystem()
{
    global $ms, $gsValues;

    if ($gsValues['OBJECT_LIMIT'] == 0) {
        return false;
    }

    $q = "SELECT * FROM `gs_objects`";
    $r = mysqli_query($ms, $q);
    $num = mysqli_num_rows($r);

    if ($num >= $gsValues['OBJECT_LIMIT']) {
        return true;
    } else {
        return false;
    }
}

function checkObjectLimitUser($id)
{
    global $ms;

    if ($_SESSION["obj_limit"] == 'true') {
        $q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='" . $id . "'";
        $r = mysqli_query($ms, $q);
        $num = mysqli_num_rows($r);

        if ($num >= $_SESSION["obj_limit_num"]) {
            return true;
        }

        return false;
    } else {
        return false;
    }
}

function alertaDms($imei, $alerta)
{
    global $ms;
    $imei = mysqli_real_escape_string($ms, $imei);
    $alerta = mysqli_real_escape_string($ms, $alerta);
    $dt_now = gmdate("Y-m-d H:i:s");

    $q = "INSERT INTO gs_object_alertas_dms (imei, alerta, dt_created) VALUES ('$imei', '$alerta', '$dt_now')";
    $r = mysqli_query($ms, $q);

    if ($r) {
        return true;
    }
    
    return true;
}

function checkObjectExistsSystem($imei)
{
    global $ms;

    $q = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    if (!$r) {
        return false;
    }

    $num = mysqli_num_rows($r);
    if ($num >= 1) {
        return true;
    }
    return false;
}

function checkObjectExistsUser($imei)
{
    global $ms;

    $q = "SELECT * FROM `gs_user_objects` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    if (!$r) {
        return false;
    }

    $num = mysqli_num_rows($r);
    if ($num >= 1) {
        return true;
    }
    return false;
}

function adjustObjectTime($imei, $dt)
{
    global $ms;

    $q = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    if ($row) {
        if (strtotime($dt) > 0) {
            $dt = gmdate("Y-m-d H:i:s", strtotime($dt . $row["time_adj"]));
        }
    }

    return $dt;
}

function createObjectDataTable($imei)
{
    global $ms;

    if (!checkObjectExistsSystem($imei))
        return false;

    $q = "CREATE TABLE IF NOT EXISTS gs_object_data_" . $imei . "(	dt_server datetime NOT NULL,
										dt_tracker datetime NOT NULL,
										lat double,
										lng double,
										altitude double,
										angle double,
										speed double,
										params varchar(2048) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
										KEY `dt_tracker` (`dt_tracker`)
										) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin";
    $r = mysqli_query($ms, $q);

    return true;
}

function addObjectExce($name, $imei, $sim_number, $protocol, $last_connection, $contador)
{
    global $ms;


    $q = "SELECT * FROM `gs_objects_reports` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);
    if ($row = mysqli_fetch_array($r)) {
        $q = "UPDATE `gs_objects_reports` SET `last_connection`= '" . $last_connection . "' , `contador`='" . $contador . "' , `repetidor`='true' WHERE `imei`='" . $imei . "'";
        $r = mysqli_query($ms, $q);
    } else {
        $q = "INSERT INTO `gs_objects_reports` (`name`,
                                                        `imei`,
                                                        `sim_number`,
                                                        `protocol`,
                                                        `last_connection`,
                                                        `usuario`,
                                                        `repetidor`,
                                                        `contador`) 
                                                        VALUES 
                                                        ('" . $name . "',
                                                        '" . $imei . "',
                                                        '" . $sim_number . "',
                                                        '" . $protocol . "',
                                                        '" . $last_connection . "',
                                                        '1',
                                                        'false',
                                                        '" . $contador . "')";

        $r = mysqli_query($ms, $q);
    }
}

function addObjectReport($name, $imei, $dt_now, $contador, $event, $user_id, $user_email, $email_client)
{
    global $ms;

    $hist_query = "SELECT registro_historial FROM gs_object_data WHERE imei = '$imei'";
    $hist_result = mysqli_query($ms, $hist_query);
    $hist_row = mysqli_fetch_assoc($hist_result);
    $historial = $hist_row['registro_historial'] ?? '';

    $nuevo_evento = "[" . $dt_now . "] " . $event;
    if ($historial !== '') {
        $historial .= "\n" . $nuevo_evento;
    } else {
        $historial = $nuevo_evento;
    }

    $q_check = "SELECT COUNT(*) as cnt FROM gs_object_data WHERE imei = '$imei'";
    $r_check = mysqli_query($ms, $q_check);
    $row = mysqli_fetch_assoc($r_check);

    if ($row['cnt'] > 0) {
        $q = "
            UPDATE gs_object_data
            SET
                name = '$name',
                event = '$event',
                fecha_caida = '$dt_now',
                contador = '$contador',
                user_id_ = '$user_id',
                email_user = '$user_email',
                email_client = '$email_client',
                registro_historial = '$historial'
            WHERE imei = '$imei'
        ";
    } else {
        $q = "
            INSERT INTO gs_object_data (
                name, imei, event, fecha_caida, contador, user_id_, email_user, email_client, registro_historial
            ) VALUES (
                '$name', '$imei', '$event', '$dt_now', '$contador', '$user_id', '$user_email', '$email_client', '$historial'
            )
        ";
    }

    mysqli_query($ms, $q);
}

function clearObjectReportIfRecovered($imei)
{
    global $ms;

    $q = "SELECT registro_historial FROM gs_object_data WHERE imei = '$imei'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_assoc($r);
    $historial = $row['registro_historial'] ?? '';

    $dt_now = gmdate("Y-m-d H:i:s");
    $evento_recuperado = "[" . $dt_now . "] Unidad volvió a reportar";

    $ultima_linea = '';
    if ($historial !== '') {
        $lineas = explode("\n", trim($historial));
        $ultima_linea = end($lineas);
    }

    if (strpos($ultima_linea, 'Unidad volvió a reportar') === false) {
        if ($historial !== '') {
            $historial .= "\n" . $evento_recuperado;
        } else {
            $historial = $evento_recuperado;
        }

        $q2 = "UPDATE gs_object_data 
               SET registro_historial = '" . mysqli_real_escape_string($ms, $historial) . "'
               WHERE imei = '$imei'";
        mysqli_query($ms, $q2);
    }
}




function addObjectSystem($name, $imei, $active, $object_expire, $object_expire_dt, $manager_id)
{
    global $ms;

    if (checkObjectExistsSystem($imei))
        return false;

    $q = "INSERT INTO `gs_objects` (`imei`,
						`active`,
						`object_expire`,
						`object_expire_dt`,
						`manager_id`,
						`name`,
						`map_icon`,
						`icon`,
						`tail_color`,
						`tail_points`,
						`odometer_type`,
						`engine_hours_type`)
						VALUES
						('" . $imei . "',
						'" . $active . "',
						'" . $object_expire . "',
						'" . $object_expire_dt . "',
						'" . $manager_id . "',
						'" . $name . "',
						'arrow',
						'img/markers/objects/land-truck.svg',
						'#00FF44',
						7,
						'gps',
						'off')";
    $r = mysqli_query($ms, $q);

    // delete from unused objects
    $q = "DELETE FROM `gs_objects_unused` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    //write log
    writeLog('object_op', 'Add object: successful. IMEI: ' . $imei);

    return true;
}

function addVehicleData($imei, $year, $brand, $model, $color, $plate, $vin, $odometer, $insurance, $insurance_exp, $fuel) {
    global $ms;

    $q = "INSERT INTO gs_object_vehicle_data
            (imei, year, brand, model_, color, plate, vin, odometer, insurance, insurance_exp, fuel)
          VALUES (
            '".mysqli_real_escape_string($ms, $imei)."',
            '".mysqli_real_escape_string($ms, $year)."',
            '".mysqli_real_escape_string($ms, $brand)."',
            '".mysqli_real_escape_string($ms, $model)."',
            '".mysqli_real_escape_string($ms, $color)."',
            '".mysqli_real_escape_string($ms, $plate)."',
            '".mysqli_real_escape_string($ms, $vin)."',
            '".mysqli_real_escape_string($ms, $odometer)."',
            '".mysqli_real_escape_string($ms, $insurance)."',
            '".mysqli_real_escape_string($ms, $insurance_exp)."',
            '".mysqli_real_escape_string($ms, $fuel)."'
          )";

    $r = mysqli_query($ms, $q);

    return $r ? true : false;
}


function addObjectSystemExtended($name, $plan, $imei, $model, $device, $sim_number, $sim_number_company, $cuenta_padre, $sensor_trademark, $active, $object_expire, $object_expire_dt, $manager_id, $no_sensor1, $no_sensor2, $no_sensor3, $acc)
{
    global $ms;

    $q = "INSERT INTO `gs_objects` (`imei`,
						`active`,
                        `plan`,
						`object_expire`,
						`object_expire_dt`,
						`manager_id`,
						`name`,
						`map_icon`,
						`icon`,
						`tail_color`,
						`tail_points`,
						`device`,
						`sim_number`,
						`sim_number_company`,
                        `cuenta_padre`,
                        `sensor_trademark`,
                        `no_sensor1`,
                        `no_sensor2`,
                        `no_sensor3`,
                        `acc`,
						`model`,
						`odometer_type`,
						`engine_hours_type`)
						VALUES
						('" . $imei . "',
						'" . $active . "',
						'" . $plan . "',
						'" . $object_expire . "',
						'" . $object_expire_dt . "',
						'" . $manager_id . "',
						'" . $name . "',
						'arrow',
						'img/markers/objects/land-truck.svg',
						'#00FF44',
						7,
						'" . $device . "',
						'" . $sim_number . "',
						'" . $sim_number_company . "',
                        '" . $cuenta_padre . "',
                        '" . $sensor_trademark . "',
                        '" . $no_sensor1 . "',
                        '" . $no_sensor2 . "',
                        '" . $no_sensor3 . "',
                        '" . $acc . "',
						'" . $model . "',
						'gps',
						'off')";
    $r = mysqli_query($ms, $q);

    // delete from unused objects
    $q = "DELETE FROM `gs_objects_unused` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    if ($device == 'EYESPro') {

        $q2 = "INSERT INTO `gs_object_streams` (`imei`,
						`active`,
						`name`,
						`type`,
						`url_stream`,
						`total_time_allowed_seconds`,
						`total_time_remaining_seconds`)
						VALUES
						('" . $imei . "',
						'" . $active . "',
						'" . $name . "',
						'',
						'',
						'180',
						'180')";
        $r2 = mysqli_query($ms, $q2);
    }

    //write log
    writeLog('object_op', 'Add object: successful. IMEI: ' . $imei);
}
function addObjectSystemReplace($old_imei, $new_imei, $sim_number, $sim_number_company, $cuenta_padre, $sensor_trademark, $no_sensor1, $no_sensor2, $no_sensor3, $device, $name, $plan, $acc, $renta)
{
    global $ms, $CONEXION;

    if ($renta) {
        $query = "UPDATE gs_object_observations
      SET
      renta = '$renta'
      WHERE imei = '$new_imei'";

        $r = mysqli_query($CONEXION, $query);
    }

    $q1 = "SELECT * FROM `gs_objects` WHERE imei = '$old_imei'";
    $r1 = mysqli_query($ms, $q1);
    $row1 = mysqli_fetch_array($r1);
    $active = $row1['active'];
    $object_expire = $row1['object_expire'];
    $object_expire_dt = $row1['object_expire_dt'];
    $manager_id = $row1['manager_id'];
    $map_icon = $row1['map_icon'];
    $icon = $row1['icon'];
    $dt_server = $row1['dt_server'];
    $dt_tracker = $row1['dt_tracker'];
    $lat = $row1['lat'];
    $lng = $row1['lng'];
    $altitude = $row1['altitude'];
    $angle = $row1['angle'];
    $params = $row1['params'];
    $tail_color = $row1['tail_color'];
    $tail_points = $row1['tail_points'];
    $model = $row1['model'];
    $vin = $row1['vin'];
    $plate_number = $row1['plate_number'];
    $odometer_type = $row1['odometer_type'];
    $engine_hours_type = $row1['engine_hours_type'];


    $q = "INSERT INTO `gs_objects` (`imei`,
						`active`,
                        `plan`,
						`object_expire`,
						`object_expire_dt`,
						`manager_id`,
						`name`,
						`map_icon`,
						`icon`,
						`dt_server`,
						`dt_tracker`,
						`lat`,
						`lng`,
						`altitude`,
						`angle`,
						`params`,
						`tail_color`,
						`tail_points`,
						`device`,
						`sim_number`,
						`sim_number_company`,
                        `cuenta_padre`,
                        `sensor_trademark`,
                        `no_sensor1`,
                        `no_sensor2`,
                        `no_sensor3`,
                        `acc`,
						`model`,
						`vin`,
						`plate_number`,
						`odometer_type`,
						`engine_hours_type`)
						VALUES
						('" . $new_imei . "',
						'" . $active . "',
						'" . $plan . "',
						'" . $object_expire . "',
						'" . $object_expire_dt . "',
						'" . $manager_id . "',
						'" . $name . "',
						'" . $map_icon . "',
						'" . $icon . "',
						'" . $dt_server . "',
						'" . $dt_tracker . "',
						'" . $lat . "',
						'" . $lng . "',
						'" . $altitude . "',
						'" . $angle . "',
						'" . $params . "',
						'" . $tail_color . "',
						'" . $tail_points . "',
						'" . $device . "',
						'" . $sim_number . "',
						'" . $sim_number_company . "',
                        '" . $cuenta_padre . "',
                        '" . $sensor_trademark . "',
                        '" . $no_sensor1 . "',
                        '" . $no_sensor2 . "',
                        '" . $no_sensor3 . "',
                        '" . $acc . "',
						'" . $model . "',
						'" . $vin . "',
						'" . $plate_number . "',
						'" . $odometer_type . "',
						'" . $engine_hours_type . "')";
    $r = mysqli_query($ms, $q);

    if ($acc != '') {
        $acc_ = explode(',', $acc);
        $userId = $_SESSION['user_id'];
        if (checkCPanelToObjectUserPrivilegesIncuded($userId)) {


            $operador = false;
            $Basico = false;
            $Mic_Spk = false;
            $SensorT_1 = false;
            $SensorT_2 = false;
            $SensorT_3 = false;
            $Sensor_1 = false;
            $Sensor_2 = false;
            $Sensor_3 = false;
            $Tanques_2 = false;
            $Tanques_3 = false;
            $Tanques_2_3 = false;
            $Temperatura1 = false;
            $Temperatura2 = false;
            $Temperatura3 = false;

            if ($sim_number_company == 'Telcel') {
                $operador = 'Telcel';
            } else if ($sim_number_company == 'M2M(Teltonika)') {
                $operador = 'Teltonika';
            } else if ($sim_number_company == 'M2M(Telefonica)' || $sim_number_company == 'M2M(Emprenet)') {
                $operador = 'Movistar';
            }

            $panico = in_array('Boton de Panico', $acc_);
            $basico = in_array('Basico', $acc_);
            $Mic_Spk = in_array('Mic y Bocina', $acc_);
            $asistencia = in_array('Boton de Asistencia', $acc_);
            $paro = in_array('Corte de Motor', $acc_);
            $temp = in_array('Sensor de Temperatura', $acc_);
            $enganche = in_array('Sensor de Enganche', $acc_);
            $puerta = in_array('Sensor de Puerta', $acc_);
            $sensorT_1 = in_array('Sensor Temp 1', $acc_);
            $sensorT_2 = in_array('Sensor Temp 2', $acc_);
            $sensorT_3 = in_array('Sensor Temp 3', $acc_);
            $sensor_1 = in_array('Sensor Diesel 1', $acc_);
            $sensor_2 = in_array('Sensor Diesel 2', $acc_);
            $sensor_3 = in_array('Sensor Diesel 3', $acc_);

            if ($sensor_1 && $sensor_2 && $sensor_3) {
                $Tanques_3 = true;
            } elseif ($sensor_1 && $sensor_2) {
                $Tanques_2 = true;
            } elseif ($sensorT_1 && $sensorT_2 && $sensorT_3) {
                $Temperatura3 = true;
            } elseif ($sensorT_1 && $sensorT_2) {
                $Temperatura2 = true;
            } elseif ($Mic_Spk) {
                $Mic_Spk = true;
            } elseif ($basico) {
                $Basico = true;
            } elseif ($sensor_2 && $sensor_3) {
                $Tanques_2_3 = true;
            } elseif ($sensor_1) {
                $Sensor_1 = true;
            } elseif ($sensor_2) {
                $Sensor_2 = true;
            } elseif ($sensor_3) {
                $Sensor_3 = true;
            } elseif ($panico && $paro) {
                $Basico = true;
            } elseif ($sensorT_1) {
                $Temperatura1 = true;
            } else {
                $Basico = true;
            }
        } else {

            $Basico = true;
        }

        $comandos = array();

        if ($device && $operador == 'Telcel') {
            $protocol = $device;
            $q = "SELECT * FROM `gs_user_cmd` WHERE `user_id` = '999' AND `protocol` = '$protocol' AND gateway = 'sms' ORDER BY `cmd_id` ASC";
            $r = mysqli_query($ms, $q);

            if ($r) {
                while ($row = mysqli_fetch_assoc($r)) {
                    $comandos[$row['name']] = $row['cmd'];
                }
            }
            if ($device == 'DUX' || $device == 'DUXPro') {
                if ($Basico) {
                    $cmd_config = $comandos['Configuración Basico'];
                    $nombre_configuracion = 'Configuración Básica';
                } elseif ($Sensor_1) {
                    $cmd_config = $comandos['Configuración Tanque 1'];
                    $nombre_configuracion = 'Configuración Tanque 1';
                } elseif ($Sensor_2) {
                    $cmd_config = $comandos['Configuración Tanque 2'];
                    $nombre_configuracion = 'Configuración Tanque 2';
                } elseif ($Sensor_3) {
                    $cmd_config = $comandos['Configuración Tanque 3'];
                    $nombre_configuracion = 'Configuración Tanque 3';
                } elseif ($Tanques_2 && $device == 'queclinkgv310Lau') {
                    $cmd_config = $comandos['Configuración Sensores_2'];
                    $nombre_configuracion = 'Configuración Sensores_2';
                } elseif ($Tanques_2) {
                    $cmd_config = $comandos['Configuración Sensores'];
                    $nombre_configuracion = 'Configuración Sensores';
                } elseif ($Tanques_3) {
                    $cmd_config = $comandos['Configuración Sensores'];
                    $nombre_configuracion = 'Configuración Sensores_3';
                } elseif ($Tanques_2_3) {
                    $cmd_config = $comandos['Configuración Sensores_2_3'];
                    $nombre_configuracion = 'Configuración Sensores_2_3';
                } elseif ($Temperatura1) {
                    $cmd_config = $comandos['Configuración Temp'];
                    $nombre_configuracion = 'Configuración Temp';
                }

                if (isset($cmd_config)) {
                    $user = $_SESSION["user_id"];
                    CreateCommandConfig($new_imei, $cmd_config, $user, $nombre_configuracion);
                    addRowBinnacle($user, 'Envío de ' . $nombre_configuracion . ' Por Remplazo: ' . $name, $q);
                }
            }
        } elseif ($device && $operador == 'Movistar') {
            $protocol = $device;
            $q = "SELECT * FROM `gs_user_cmd` WHERE `user_id` = '999' AND `protocol` = '$protocol' AND gateway = 'sms' AND `name` LIKE '%_movi%' ORDER BY `cmd_id` ASC";
            $r = mysqli_query($ms, $q);

            if ($r) {
                while ($row = mysqli_fetch_assoc($r)) {
                    $comandos[$row['name']] = $row['cmd'];
                }
            }
            if ($device == 'DUX' || $device == 'DUXPro') {
                if ($Basico) {
                    $cmd_config = $comandos['Configuración Basico_movi'];
                    $nombre_configuracion = 'Configuración Básica_movi';
                } elseif ($Sensor_1) {
                    $cmd_config = $comandos['Configuración Tanque 1_movi'];
                    $nombre_configuracion = 'Configuración Tanque 1_movi';
                } elseif ($Sensor_2) {
                    $cmd_config = $comandos['Configuración Tanque 2_movi'];
                    $nombre_configuracion = 'Configuración Tanque 2_movi';
                } elseif ($Sensor_3) {
                    $cmd_config = $comandos['Configuración Tanque 3_movi'];
                    $nombre_configuracion = 'Configuración Tanque 3_movi';
                } elseif ($Tanques_2 && $device == 'queclinkgv310Lau') {
                    $cmd_config = $comandos['Configuración Sensores_2_movi'];
                    $nombre_configuracion = 'Configuración Sensores_2_movi';
                } elseif ($Tanques_2) {
                    $cmd_config = $comandos['Configuración Sensores_movi'];
                    $nombre_configuracion = 'Configuración Sensores_movi';
                } elseif ($Tanques_3) {
                    $cmd_config = $comandos['Configuración Sensores_movi'];
                    $nombre_configuracion = 'Configuración Sensores_3_movi';
                } elseif ($Tanques_2_3) {
                    $cmd_config = $comandos['Configuración Sensores_2_3_movi'];
                    $nombre_configuracion = 'Configuración Sensores_2_3_movi';
                } elseif ($Temperatura1) {
                    $cmd_config = $comandos['Configuración Temp_movi'];
                    $nombre_configuracion = 'Configuración Temp_movi';
                }

                if (isset($cmd_config)) {
                    $user = $_SESSION["user_id"];
                    CreateCommandConfig($new_imei, $cmd_config, $user, $nombre_configuracion);
                    addRowBinnacle($user, 'Envío de ' . $nombre_configuracion . ' Por Remplazo: ' . $name, $q);
                }
            }
        } elseif ($device && $operador == 'Teltonika') {
            $protocol = $device;
            $q = "SELECT * FROM `gs_user_cmd` WHERE `user_id` = '999' AND `protocol` = '$protocol' AND gateway = 'sms' AND `name` LIKE '%teltonika%' ORDER BY `cmd_id` ASC";
            $r = mysqli_query($ms, $q);

            if ($r) {
                while ($row = mysqli_fetch_assoc($r)) {
                    $comandos[$row['name']] = $row['cmd'];
                }
            }
            if ($device == 'DUX' || $device == 'DUXPro') {
                if ($Basico) {
                    $cmd_config = $comandos['Configuración Basico_sim_teltonika'];
                    $nombre_configuracion = 'Configuración Básica_sim_teltonika';
                } elseif ($Sensor_1) {
                    $cmd_config = $comandos['Configuración Tanque 1_sim_teltonika'];
                    $nombre_configuracion = 'Configuración Tanque 1_sim_teltonika';
                } elseif ($Sensor_2) {
                    $cmd_config = $comandos['Configuración Tanque 2_sim_teltonika'];
                    $nombre_configuracion = 'Configuración Tanque 2_sim_teltonika';
                } elseif ($Sensor_3) {
                    $cmd_config = $comandos['Configuración Tanque 3_sim_teltonika'];
                    $nombre_configuracion = 'Configuración Tanque 3_sim_teltonika';
                } elseif ($Tanques_2 && $device == 'queclinkgv310Lau') {
                    $cmd_config = $comandos['Configuración Sensores_2_sim_teltonika'];
                    $nombre_configuracion = 'Configuración Sensores_2_sim_teltonika';
                } elseif ($Tanques_2) {
                    $cmd_config = $comandos['Configuración Sensores_sim_teltonika'];
                    $nombre_configuracion = 'Configuración Sensores_sim_teltonika';
                } elseif ($Tanques_3) {
                    $cmd_config = $comandos['Configuración Sensores_sim_teltonika'];
                    $nombre_configuracion = 'Configuración Sensores_3_sim_teltonika';
                } elseif ($Tanques_2_3) {
                    $cmd_config = $comandos['Configuración Sensores_2_3_sim_teltonika'];
                    $nombre_configuracion = 'Configuración Sensores_2_3_sim_teltonika';
                } elseif ($Temperatura1) {
                    $cmd_config = $comandos['Configuración Temp_sim_teltonika'];
                    $nombre_configuracion = 'Configuración Temp_sim_teltonika';
                }

                if (isset($cmd_config)) {
                    $user = $_SESSION["user_id"];
                    CreateCommandConfig($new_imei, $cmd_config, $user, $nombre_configuracion);
                    addRowBinnacle($user, 'Envío de ' . $nombre_configuracion . ' Por Remplazo: ' . $name, $q);
                }
            }
        }
    }
}

function addObjectUser($user_id, $imei, $group_id, $driver_id, $trailer_id, $number)
{
    global $ms;

    if (!$user_id)
        return false;

    if (!checkObjectExistsSystem($imei))
        return false;

    if (empty($number)) {
        $number = 0;
    }

    $q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='" . $user_id . "' AND `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);
    $num = mysqli_num_rows($r);
    if ($num == 0) {
        $q = "INSERT INTO `gs_user_objects` 	(`user_id`,
								`imei`,
								`group_id`,
								`driver_id`,
								`trailer_id`,
                                `client_id`)
								VALUES (
								'" . $user_id . "',
								'" . $imei . "',
								'" . $group_id . "',
								'" . $driver_id . "',
								'" . $trailer_id . "',
                                '" . $number . "')";
        $r = mysqli_query($ms, $q);
        if ($r) {
            $q = "SELECT * FROM `gs_users` WHERE `id`='" . $user_id . "'";
            $r = mysqli_query($ms, $q);
            $row = mysqli_fetch_array($r);
            $username = $row['username'];
            addRowBinnacle($_SESSION["user_id"], 'Alta de equipo a Usuario: ' . $username . " imei: " . $imei, $q);
        }
    }

    return true;
}
function addObjectChangeUser($user_id, $data)
{
    global $ms, $CONEXION;

    $usuario = $_SESSION["username"];

    date_default_timezone_set("Mexico/General");
    $dt_now = date("Y-m-d H:i:s");

    $active = $data['active'];
    $object_expire = $data['object_expire'];
    $object_expire_dt = $data['object_expire_dt'];
    $name = $data['name'];
    $old_imei = $data['imei'];
    $new_imei = $data['new_imei'];
    $model = $data['model'];
    $vin = $data['vin'];
    $plan = $data['plan'];
    $plate_number = $data['plate_number'];
    $device = $data['device'];
    $sim_number = $data['sim_number'];
    $sim_number_company = $data['sim_number_company'];
    $new_sim_number = $data['new_sim_number'];
    $cuenta_padre = $data['cuenta_padre'];
    $sensor_trademark = $data['sensor_trademark'];
    $new_cuenta_padre = $data['new_cuenta_padre'];
    $no_sensor1 = $data['no_sensor1'];
    $new_no_sensor1 = $data['new_no_sensor1'];
    $no_sensor2 = $data['no_sensor2'];
    $new_no_sensor2 = $data['new_no_sensor2'];
    $no_sensor3 = $data['no_sensor3'];
    $new_no_sensor3 = $data['new_no_sensor3'];
    $manager_id = $data['manager_id'];
    $acc = $data['acc'];
    $mtto = $data['mtto'];
    $user_ids = $data['user_ids'];
    $fecha_alta = $data['fecha_alta'];
    $renta = $data['renta'];
    $observaciones = $data['observaciones'];
    //$cliente = $data['cliente'];
    $group_id = 0;
    $driver_id = 0;
    $trailer_id = 0;

    $user_ids_ = json_decode(stripslashes($user_ids), true);


    $query_select = "SELECT * FROM gs_object_observations WHERE imei = '$old_imei'";
    $r3 = CONSULTAR($query_select, $CONEXION);
    $row3 = mysqli_fetch_array($r3);
    $fecha_creacion_old = $row3['fecha_creacion'];
    $users_old = $row3['users_old'];
    $fecha_alta_old = $row3['fecha_alta'];
    $dt_user_old = $row3['dt_user'];
    $mtto_old = $row3['mtto'];
    $observacion_old = $row3['observacion'];
    $cliente_old = $row3['clienteid'];
    if ($cliente_old ==''){
        $cliente_old = 0;
    }
    if (!mysqli_num_rows($r3)) {
        $observacion_old = 0;
        $dt_user_old = 0;
        $cliente_old = 0;
        $users_old = 0;
        $mtto_old = 0;
        $fecha_alta_old = '';
        $fecha_creacion_old = '';
    }

    $user_ids_str = implode(",", $user_ids_);

    if ($new_imei) {
        if ($mtto == 'Remplazo de Equipo Garantia') {
            $mtto_sim = $mtto;
            $mtto = 'Instalación por Remplazo (Garantia)';
            $mtto_ = 'Desinstalación por Remplazo (Garantia)';
            $mtto_value = $mtto_old ? "$mtto_old, $mtto_" : "$mtto_";
        } elseif ($mtto == 'Remplazo de Equipo' || $mtto == 'Remplazo de Equipo Cliente') {
            $mtto_sim = $mtto;
            $mtto = 'Instalación por Remplazo';
            $mtto_ = 'Desinstalación por Remplazo';
            $mtto_value = $mtto_old ? "$mtto_old, $mtto_" : "$mtto_";
        }


        $query_insert = "INSERT INTO gs_object_observations (imei, imei_old, fecha_alta, fecha_remplazo, observacion, fecha_creacion, fecha_modificacion, mtto, mtto_old, users_old, users_new, status, dt_user, usuario_creador, usuario_modificacion, renta, clienteid) 
                VALUES('$new_imei', '$old_imei', '$dt_now', '$dt_now', '$observaciones', null, null, '$mtto', '$mtto_value','$users_old', '$user_ids_str', '', '$dt_now', '$usuario', '$usuario', '$renta', '$cliente_old')";
        $r = mysqli_query($CONEXION, $query_insert);
    }

    $users = array();
    $gs_user_objects = array();


    $q = "SELECT * FROM `gs_user_objects` WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    while ($row = mysqli_fetch_array($r)) {
        $users[] = $row['user_id'];
        $gs_user_objects[] = $row;
    }

    $diff_1 = array_diff($users, $user_ids_);
    $diff_2 = array_diff($user_ids_, $users);
    
    $excluded_ids = array(171, 172);
    
    if (empty($diff_1) && empty($diff_2)) {
        $ids_to_query = $users; 
    } else {
        $ids_to_query = array_merge($diff_1, $diff_2);
    }
    
    $ids_str = implode(',', $ids_to_query);
    $excluded_ids_str = implode(',', $excluded_ids);
    
    if (!empty($ids_str)) {
        $q = "SELECT username FROM `gs_users` WHERE `id` IN ($ids_str) AND `id` NOT IN ($excluded_ids_str)";
        $r = mysqli_query($ms, $q);
    
        if ($r) {
            $usernames = array();
            while ($row = mysqli_fetch_assoc($r)) {
                $usernames[] = $row['username'];
            }
        }
    }

    $users = implode(',', $usernames);
    if ($mtto_sim == 'Remplazo de Equipo Cliente') {
        $q = "UPDATE `gs_objects` SET `name` = CONCAT(`name`, ', ', '" . $users . "'), `sim_number` = '0' WHERE `imei` = '" . $old_imei . "'";
        $r = mysqli_query($ms, $q);
    } else {
        $q = "UPDATE `gs_objects` SET `name` = CONCAT(`name`, ', ', '" . $users . "') WHERE `imei` = '" . $old_imei . "'";
        $r = mysqli_query($ms, $q);
    }


    $q = "SELECT * FROM `gs_user_objects` WHERE `imei`='" . $old_imei . "' AND `user_id` NOT IN (" . $excluded_ids_str . ")";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);
    $client_id = $row['client_id'];
    $group_id = $row['group_id'];
    $driver_id = $row['driver_id'];
    $trailer_id = $row['trailer_id'];

    $q = "UPDATE `gs_user_objects` SET `user_id`='" . $user_id . "', `client_id`='0' WHERE `imei`='" . $old_imei . "' AND `user_id` NOT IN (" . $excluded_ids_str . ")";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_objects` WHERE `imei`='" . $old_imei . "' AND `user_id` NOT IN ('" . $user_id . "')";
    $r = mysqli_query($ms, $q);

    addRowBinnacle($_SESSION["user_id"], 'Remplazo de equipo: ' . $users . ', Nombre: ' . $name . ", imei anterior: " . $old_imei . ", imei nuevo: " . $new_imei, $query_insert);


    if ($new_sim_number == '0') {
        $sim_number = $new_sim_number;
    } else {
        $sim_number = $new_sim_number;
    }
    if ($new_no_sensor1 == '0') {
        $no_sensor1 = $new_no_sensor1;
    } else {
        $no_sensor1 = $new_no_sensor1;
    }
    if ($new_no_sensor2 == '0') {
        $no_sensor2 = $new_no_sensor2;
    } else {
        $no_sensor2 = $new_no_sensor2;
    }
    if ($new_no_sensor3 == '0') {
        $no_sensor3 = $new_no_sensor3;
    } else {
        $no_sensor3 = $new_no_sensor3;
    }
    if ($new_cuenta_padre == '0') {
        $cuenta_padre = $new_cuenta_padre;
    } else {
        $cuenta_padre = $new_cuenta_padre;
    }
    if ($sim_number == '0' && $cuenta_padre >= '1') {
        $cuenta_padre = 0;
    }

    addObjectSystemReplace($old_imei, $new_imei, $new_sim_number, $sim_number_company, $cuenta_padre, $sensor_trademark, $no_sensor1, $no_sensor2, $no_sensor3, $device, $name, $plan, $acc, $renta);

    replaceObjectIMEI($old_imei, $new_imei);

    $device_mapping = array(
        'DUX' => 409,
        'DUXPro' => 408,
        'queclinkgv300' => 413,
        'queclinkgv75w' => 407,
        'LUXPro' => 388,
        'LUX' => 395,
        'Er-100(4G)' => 398,
        'Er-100(3G)' => 399,
        'ILMA' => 404,
        'suntechST3310U' => 405,
        'suntechST310U' => 389,
        'suntechst910' => 403,
        'suntechst3940' => 402,
        'suntechst300' => 411,
        'suntechST600MD' => 412,
        'cellocatorCR300B' => 397,
        'queclinkgl300w' => 391,
        'TEMPUS' => 414,
        'CondorKeny' => 387,
        'android' => 396,
        'iphone' => 396
    );
    if (!isset($device_mapping[$device])) {
        $device_mapping[$device] = 0;
    }
    if (in_array('171', $user_ids_)) {
        $user_ids_ = array_values(array_diff($user_ids_, array('171')));
    }
    for ($i = 0; $i < count($user_ids_); $i++) {
        $user_id = $user_ids_[$i];
        if ($user_id != '172') {
            addObjectUser($user_id, $new_imei, $group_id, $driver_id, $trailer_id, $client_id);
        }
        if (isset($device_mapping[$device]) && $user_id == '172') {
            addObjectUser($user_id, $new_imei, $device_mapping[$device], 0, 0, 0);
        } elseif (!isset($device_mapping[$device]) && $user_id == '172') {
            addObjectUser($user_id, $new_imei, 0, 0, 0, 0);
        }
    }
    return true;
}

function duplicateObjectSystem($duplicate_imei, $imei, $object_expire, $object_expire_dt, $manager_id, $name)
{
    global $ms;

    $q = "SELECT * FROM `gs_objects` WHERE `imei`='" . $duplicate_imei . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    $q = "INSERT INTO `gs_objects` (`imei`,
						`active`,
						`object_expire`,
						`object_expire_dt`,
						`manager_id`,
						`name`,
						`icon`,
						`map_arrows`,
						`map_icon`,
						`tail_color`,
						`tail_points`,
						`device`,
						`sim_number`,
						`sim_number_company`,
						`model`,
						`vin`,
						`plate_number`,
						`odometer_type`,
						`engine_hours_type`,
						`odometer`,
						`engine_hours`,
						`fcr`,
						`time_adj`,
						`accuracy`,
						`accvirt`,
						`accvirt_cn`)
						VALUES
						('" . $imei . "',
						'true',
						'" . $object_expire . "',
						'" . $object_expire_dt . "',
						'" . $manager_id . "',
						'" . $name . "',
						'" . $row['icon'] . "',
						'" . $row['map_arrows'] . "',
						'" . $row['map_icon'] . "',
						'" . $row['tail_color'] . "',
						'" . $row['tail_points'] . "',
						'" . $row['device'] . "',
						'" . $row['sim_number'] . "',
						'" . $row['sim_number_company'] . "',
						'" . $row['model'] . "',
						'" . $row['vin'] . "',
						'" . $row['plate_number'] . "',
						'" . $row['odometer_type'] . "',
						'" . $row['engine_hours_type'] . "',
						'" . $row['odometer'] . "',
						'" . $row['engine_hours'] . "',
						'" . $row['fcr'] . "',
						'" . $row['time_adj'] . "',
						'" . $row['accuracy'] . "',
						'" . $row['accvirt'] . "',
						'" . $row['accvirt_cn'] . "')";
    $r = mysqli_query($ms, $q);

    $q = "SELECT * FROM `gs_object_sensors` WHERE `imei`='" . $duplicate_imei . "'";
    $r = mysqli_query($ms, $q);
    while ($row = mysqli_fetch_array($r)) {
        $q2 = "INSERT INTO `gs_object_sensors` (`imei`,
								`name`,
								`type`,
								`param`,
								`data_list`, 
								`popup`, 
								`result_type`,
								`text_1`,
								`text_0`,
								`units`,
								`lv`,
								`hv`,
								`formula`,
								`calibration`,
								`dictionary`)
								VALUES
								('" . $imei . "',
								'" . $row['name'] . "',
								'" . $row['type'] . "',
								'" . $row['param'] . "',
								'" . $row['data_list'] . "',
								'" . $row['popup'] . "',
								'" . $row['result_type'] . "',
								'" . $row['text_1'] . "',
								'" . $row['text_0'] . "',
								'" . $row['units'] . "',
								'" . $row['lv'] . "',
								'" . $row['hv'] . "',
								'" . $row['formula'] . "',
								'" . $row['calibration'] . "',
								'" . $row['dictionary'] . "')";
        $r2 = mysqli_query($ms, $q2);
    }

    $q = "SELECT * FROM `gs_object_services` WHERE `imei`='" . $duplicate_imei . "'";
    $r = mysqli_query($ms, $q);
    while ($row = mysqli_fetch_array($r)) {
        $q2 = "INSERT INTO `gs_object_services` (`imei`,
								`name`,
								`data_list`,
								`popup`,
								`odo`,
								`odo_interval`,
								`odo_last`, 
								`engh`,
								`engh_interval`,
								`engh_last`,
								`days`,
								`days_interval`,
								`days_last`,
								`odo_left`,
								`odo_left_num`,
								`engh_left`,
								`engh_left_num`,
								`days_left`,
								`days_left_num`,
								`update_last`,
								`notify_service_expire`)
								VALUES
								('" . $imei . "',
								'" . $row['name'] . "',
								'" . $row['data_list'] . "',
								'" . $row['popup'] . "',
								'" . $row['odo'] . "',
								'" . $row['odo_interval'] . "',
								'" . $row['odo_last'] . "',
								'" . $row['engh'] . "',
								'" . $row['engh_interval'] . "',
								'" . $row['engh_last'] . "',
								'" . $row['days'] . "',
								'" . $row['days_interval'] . "',
								'" . $row['days_last'] . "',
								'" . $row['odo_left'] . "',
								'" . $row['odo_left_num'] . "',
								'" . $row['engh_left'] . "',
								'" . $row['engh_left_num'] . "',
								'" . $row['days_left'] . "',
								'" . $row['days_left_num'] . "',
								'" . $row['update_last'] . "',
								'" . $row['notify_service_expire'] . "')";
        $r2 = mysqli_query($ms, $q2);
    }

    $q = "SELECT * FROM `gs_object_custom_fields` WHERE `imei`='" . $duplicate_imei . "'";
    $r = mysqli_query($ms, $q);
    while ($row = mysqli_fetch_array($r)) {
        $q2 = "INSERT INTO `gs_object_custom_fields` (`imei`,
									`name`,
									`value`,
									`data_list`,
									`popup`)
									VALUES
									('" . $imei . "',
									'" . $row['name'] . "',
									'" . $row['value'] . "',
									'" . $row['data_list'] . "',
									'" . $row['popup'] . "')";
        $r2 = mysqli_query($ms, $q2);
    }
}

function delObjectUser($user_id, $imei)
{
    global $ms;

    $q = "DELETE FROM `gs_user_objects` WHERE `user_id`='" . $user_id . "' AND `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_last_events_data` WHERE `user_id`='" . $user_id . "' AND `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_events_data` WHERE `user_id`='" . $user_id . "' AND `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_events_status` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    //write log
    writeLog('object_op', 'Delete object: successful. IMEI: ' . $imei);
}

function delObjectSystem($imei)
{
    global $ms, $gsValues;

    $q = "DELETE FROM `gs_objects` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_rilogbook_data` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_dtc_data` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_object_sensors` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_object_services` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_object_custom_fields` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_objects` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_last_events_data` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_events_data` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_events_status` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    $q = "SELECT * FROM `gs_object_img` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    while ($row = mysqli_fetch_array($r)) {
        $img_file = $gsValues['PATH_ROOT'] . 'data/img/' . $row['img_file'];
        if (is_file($img_file)) {
            @unlink($img_file);
        }
    }

    $q = "DELETE FROM `gs_object_img` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_object_chat` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    $q = "DROP TABLE gs_object_data_" . $imei;
    $r = mysqli_query($ms, $q);

    //write log
    writeLog('object_op', 'Delete object: successful. IMEI: ' . $imei);
}

function changeObjectIMEI($old_imei, $new_imei)
{
    global $ms;

    $old_imei = strtoupper($old_imei);
    $new_imei = strtoupper($new_imei);

    if (checkObjectExistsSystem($new_imei)) {
        return false;
    }

    // data table
    $q = "alter table gs_object_data_" . $old_imei . " rename to gs_object_data_" . $new_imei;
    $r = mysqli_query($ms, $q);

    // gs_user_reports
    $q = "SELECT * FROM `gs_user_reports` WHERE `imei` LIKE '%" . $old_imei . "%'";
    $r = mysqli_query($ms, $q);

    while ($row = mysqli_fetch_array($r)) {
        $imeis = explode(',', $row['imei']);

        for ($i = 0; $i < count($imeis); ++$i) {
            if ($imeis[$i] == $old_imei) {
                $imeis[$i] = $new_imei;
            }
        }

        $imeis_ = implode(",", $imeis);

        $q2 = "UPDATE `gs_user_reports` SET `imei`='" . $imeis_ . "' WHERE `report_id`='" . $row['report_id'] . "'";
        $r2 = mysqli_query($ms, $q2);
    }

    // gs_user_events
    $q = "SELECT * FROM `gs_user_events` WHERE `imei` LIKE '%" . $old_imei . "%'";
    $r = mysqli_query($ms, $q);

    while ($row = mysqli_fetch_array($r)) {
        $imeis = explode(',', $row['imei']);

        for ($i = 0; $i < count($imeis); ++$i) {
            if ($imeis[$i] == $old_imei) {
                $imeis[$i] = $new_imei;
            }
        }

        $imeis_ = implode(",", $imeis);

        $q2 = "UPDATE `gs_user_events` SET `imei`='" . $imeis_ . "' WHERE `event_id`='" . $row['event_id'] . "'";
        $r2 = mysqli_query($ms, $q2);
    }

    // gs_user_cmd_schedule
    $q = "SELECT * FROM `gs_user_cmd_schedule` WHERE `imei` LIKE '%" . $old_imei . "%'";
    $r = mysqli_query($ms, $q);

    while ($row = mysqli_fetch_array($r)) {
        $imeis = explode(',', $row['imei']);

        for ($i = 0; $i < count($imeis); ++$i) {
            if ($imeis[$i] == $old_imei) {
                $imeis[$i] = $new_imei;
            }
        }

        $imeis_ = implode(",", $imeis);

        $q2 = "UPDATE `gs_user_cmd_schedule` SET `imei`='" . $imeis_ . "' WHERE `cmd_id`='" . $row['cmd_id'] . "'";
        $r2 = mysqli_query($ms, $q2);
    }

    // gs_user_last_events_data
    $q = "UPDATE `gs_user_last_events_data` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // gs_user_events_data
    $q = "UPDATE `gs_user_events_data` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // gs_user_events_status
    $q = "UPDATE `gs_user_events_status` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // gs_user_objects
    $q = "UPDATE `gs_user_objects` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // gs_objects
    $q = "UPDATE `gs_objects` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // gs_object_cmd_exec
    $q = "UPDATE `gs_object_cmd_exec` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // gs_object_tasks
    $q = "UPDATE `gs_object_tasks` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // gs_object_img
    $q = "UPDATE `gs_object_img` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // gs_object_chat
    $q = "UPDATE `gs_object_chat` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // gs_object_sensors
    $q = "UPDATE `gs_object_sensors` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // gs_object_services
    $q = "UPDATE `gs_object_services` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // gs_object_custom_fields
    $q = "UPDATE `gs_object_custom_fields` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // gs_rilogbook_data
    $q = "UPDATE `gs_rilogbook_data` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // gs_dtc_data
    $q = "UPDATE `gs_dtc_data` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // delete from unused objects
    $q = "DELETE FROM `gs_objects_unused` WHERE `imei`='" . $new_imei . "'";
    $r = mysqli_query($ms, $q);

    return true;
}

function replaceObjectIMEI($old_imei, $new_imei)
{
    global $ms;

    $old_imei = strtoupper($old_imei);
    $new_imei = strtoupper($new_imei);

    // if (checkObjectExistsSystem($new_imei)) {
    //     return false;
    // }


    // Clonar la tabla
    $q1 = "CREATE TABLE gs_object_data_" . $new_imei . " LIKE gs_object_data_" . $old_imei;
    $r1 = mysqli_query($ms, $q1);
    if ($r1) {
        $q_insert = "INSERT INTO gs_object_data_" . $new_imei . " SELECT * FROM gs_object_data_" . $old_imei;
        $r_insert = mysqli_query($ms, $q_insert);
    }

    // gs_user_reports
    $q = "SELECT * FROM `gs_user_reports` WHERE `imei` LIKE '%" . $old_imei . "%'";
    $r = mysqli_query($ms, $q);
    while ($row = mysqli_fetch_array($r)) {
        $imeis = explode(',', $row['imei']);

        for ($i = 0; $i < count($imeis); ++$i) {
            if ($imeis[$i] == $old_imei) {
                $imeis[$i] = $new_imei;
            }
        }

        $imeis_ = implode(",", $imeis);

        $q2 = "UPDATE `gs_user_reports` SET `imei`='" . $imeis_ . "' WHERE `report_id`='" . $row['report_id'] . "'";
        $r2 = mysqli_query($ms, $q2);
    }

    // gs_user_events
    $q = "SELECT * FROM `gs_user_events` WHERE `imei` LIKE '%" . $old_imei . "%'";
    $r = mysqli_query($ms, $q);

    while ($row = mysqli_fetch_array($r)) {
        $imeis = explode(',', $row['imei']);

        for ($i = 0; $i < count($imeis); ++$i) {
            if ($imeis[$i] == $old_imei) {
                $imeis[$i] = $new_imei;
            }
        }

        $imeis_ = implode(",", $imeis);

        $q2 = "UPDATE `gs_user_events` SET `imei`='" . $imeis_ . "' WHERE `event_id`='" . $row['event_id'] . "'";
        $r2 = mysqli_query($ms, $q2);
    }

    // gs_user_subaccounts
    $q = "SELECT * FROM `gs_users` WHERE `privileges` LIKE '%" . $old_imei . "%'";
    $r = mysqli_query($ms, $q);

    while ($row = mysqli_fetch_array($r)) {
        $privileges = $row['privileges'];
        $new_privileges = str_replace($old_imei, $new_imei, $privileges);

        $q2 = "UPDATE `gs_users` SET `privileges`='" . $new_privileges . "' WHERE `id`='" . $row['id'] . "'";
        $r2 = mysqli_query($ms, $q2);
    }


    // gs_user_cmd_schedule
    $q = "SELECT * FROM `gs_user_cmd_schedule` WHERE `imei` LIKE '%" . $old_imei . "%'";
    $r = mysqli_query($ms, $q);

    while ($row = mysqli_fetch_array($r)) {
        $imeis = explode(',', $row['imei']);

        for ($i = 0; $i < count($imeis); ++$i) {
            if ($imeis[$i] == $old_imei) {
                $imeis[$i] = $new_imei;
            }
        }

        $imeis_ = implode(",", $imeis);

        $q2 = "UPDATE `gs_user_cmd_schedule` SET `imei`='" . $imeis_ . "' WHERE `cmd_id`='" . $row['cmd_id'] . "'";
        $r2 = mysqli_query($ms, $q2);
    }

    // gs_user_last_events_data
    $q = "UPDATE `gs_user_last_events_data` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // gs_user_events_data
    $q = "UPDATE `gs_user_events_data` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // gs_user_events_status
    $q = "UPDATE `gs_user_events_status` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // gs_object_cmd_exec
    $q = "UPDATE `gs_object_cmd_exec` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // gs_object_tasks
    $q = "UPDATE `gs_object_tasks` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // gs_object_img
    $q = "UPDATE `gs_object_img` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // gs_object_chat
    $q = "UPDATE `gs_object_chat` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // gs_object_sensors
    $q = "UPDATE `gs_object_sensors` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // gs_object_services
    $q = "UPDATE `gs_object_services` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // gs_object_custom_fields
    $q = "UPDATE `gs_object_custom_fields` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // gs_rilogbook_data
    $q = "UPDATE `gs_rilogbook_data` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // gs_dtc_data
    $q = "UPDATE `gs_dtc_data` SET `imei`='" . $new_imei . "' WHERE `imei`='" . $old_imei . "'";
    $r = mysqli_query($ms, $q);

    // delete from unused objects
    $q = "DELETE FROM `gs_objects_unused` WHERE `imei`='" . $new_imei . "'";
    $r = mysqli_query($ms, $q);

    return true;
}

function clearObjectHistory($imei)
{
    global $ms;

    $q = "DELETE FROM gs_object_data_" . $imei;
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_rilogbook_data` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_dtc_data` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_last_events_data` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_events_data` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    $q = "DELETE FROM `gs_user_events_status` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    $q = "UPDATE `gs_objects` SET  `dt_server`='0000-00-00 00:00:00',
						`dt_tracker`='0000-00-00 00:00:00',
						`lat`='0',
						`lng`='0',
						`altitude`='0',
						`angle`='0',
						`speed`='0',
						`loc_valid`='0',
						`params`='',
						`dt_last_stop`='0000-00-00 00:00:00',
						`dt_last_idle`='0000-00-00 00:00:00',
						`dt_last_move`='0000-00-00 00:00:00'
						WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    //write log
    writeLog('object_op', 'Clear object history: successful. IMEI: ' . $imei);
    addRowBinnacle($_SESSION["user_id"], 'Se borra historial:' . $imei . $q);
}

function checkObjectActive($imei)
{
    global $ms;

    $q = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    if ($row['active'] == 'true') {
        return true;
    } else {
        return false;
    }
}

function moveObjectPlanToBilling($user_id, $imei)
{
    global $ms, $gsValues, $la;

    $q = "SELECT * FROM `gs_user_objects` WHERE `imei`='" . $imei . "' AND `user_id`='" . $user_id . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    if ($row) {
        $q = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "'";
        $r = mysqli_query($ms, $q);
        $row = mysqli_fetch_array($r);

        if (($row['active'] == 'true') && ($row['object_expire'] == 'true')) {
            $days_diff = ceil((strtotime($row['object_expire_dt']) - strtotime(gmdate("Y-m-d"))) / 86400);
            $days_diff -= 1; // reduce one day to prevent cheating

            if (($days_diff > 0) && ($days_diff > $gsValues['OBJ_DAYS_TRIAL'])) {
                // add billing plan
                $dt_purchase = gmdate("Y-m-d H:i:s");
                $name = $la['RECOVER_FROM_IMEI'] . ' ' . $imei;
                $objects = 1;
                $period = $days_diff;
                $period_type = 'days';
                $price = 0;

                $q = "INSERT INTO `gs_user_billing_plans` 	(`user_id`,
											`dt_purchase`,
											`name`,
											`objects`,
											`period`,
											`period_type`,
											`price`
											) VALUES (
											'" . $user_id . "',
											'" . $dt_purchase . "',
											'" . $name . "',
											'" . $objects . "',
											'" . $period . "',
											'" . $period_type . "',
											'" . $price . "')";
                $r = mysqli_query($ms, $q);

                // reduce object expiration date
                $q = "UPDATE `gs_objects` SET `object_expire_dt`='" . gmdate("Y-m-d") . "' WHERE `imei`='" . $imei . "'";
                $r = mysqli_query($ms, $q);
            }
        }
    }
}

function getObjectName($imei)
{
    global $ms;

    $q = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    return $row['name'];
}

function getObjectDevice($imei)
{
    global $ms;

    $q = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    return $row['device'];
}
function getLastConection($imei)
{
    global $ms;

    $q = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    return $row['dt_server'];
}
function getZoneName($id)
{
    global $ms;

    $q = "SELECT * FROM `gs_user_zones` WHERE `zone_id`='" . $id . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    return $row['zone_name'];
}

function getObjectDriverFromSensor($user_id, $imei, $params)
{
    global $ms;

    $driver = false;

    $driver_assign_id = false;

    $sensor = getSensorFromType($imei, 'da');

    if ($sensor != false) {
        $sensor_ = $sensor[0];

        $sensor_data = getSensorValue($params, $sensor_);
        $driver_assign_id = $sensor_data['value'];
    } else {
        return $driver;
    }

    $q = "SELECT * FROM `gs_user_object_drivers` WHERE UPPER(`driver_assign_id`)='" . strtoupper($driver_assign_id) . "' AND `user_id`='" . $user_id . "'";
    $r = mysqli_query($ms, $q);
    $driver = mysqli_fetch_array($r);

    return $driver;
}

function getObjectTrailerFromSensor($user_id, $imei, $params)
{
    global $ms;

    $trailer = false;

    $trailer_assign_id = false;

    $sensor = getSensorFromType($imei, 'ta');

    if ($sensor != false) {
        $sensor_ = $sensor[0];

        $sensor_data = getSensorValue($params, $sensor_);
        $trailer_assign_id = $sensor_data['value'];
    } else {
        return $trailer;
    }

    $q = "SELECT * FROM `gs_user_object_trailers` WHERE UPPER(`trailer_assign_id`)='" . strtoupper($trailer_assign_id) . "' AND `user_id`='" . $user_id . "'";
    $r = mysqli_query($ms, $q);
    $trailer = mysqli_fetch_array($r);

    return $trailer;
}

function getObjectDriver($user_id, $imei, $params)
{
    global $ms;

    $driver = false;

    $q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='" . $user_id . "' AND `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    $driver_id = $row['driver_id'];

    if ($driver_id == '-1') {
        return $driver;
    }

    if ($driver_id == '0') {
        return getObjectDriverFromSensor($user_id, $imei, $params);
    }

    $q = "SELECT * FROM `gs_user_object_drivers` WHERE `user_id`='" . $user_id . "' AND `driver_id`='" . $driver_id . "'";
    $r = mysqli_query($ms, $q);
    $driver = mysqli_fetch_array($r);

    return $driver;
}

function getObjectTrailer($user_id, $imei, $params)
{
    global $ms;

    $trailer = false;

    $q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='" . $user_id . "' AND `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    $trailer_id = $row['trailer_id'];

    if ($trailer_id == '-1') {
        return $trailer;
    }

    if ($trailer_id == '0') {
        return getObjectTrailerFromSensor($user_id, $imei, $params);
    }

    $q = "SELECT * FROM `gs_user_object_trailers` WHERE `user_id`='" . $user_id . "' AND `trailer_id`='" . $trailer_id . "'";
    $r = mysqli_query($ms, $q);
    $trailer = mysqli_fetch_array($r);

    return $trailer;
}

function getObjectOdometer($imei)
{
    global $ms;

    $q = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    return floor($row['odometer']);
}

function getObjectAlta($imei)
{
    global $CONEXION;

    $q = "SELECT * FROM `gs_object_observations` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($CONEXION, $q);
    if (mysqli_num_rows($r) == 0) {
        return 'No se encontraron datos para el IMEI proporcionado.';
    }

    $row = mysqli_fetch_array($r);

    // Validar que el índice exista antes de acceder
    return isset($row['fecha_alta']) ? $row['fecha_alta'] : 'Fecha no disponible';
}
function getObjectSeller($imei)
{
    global $CONEXION;

    $q = "SELECT * FROM `gs_object_observations` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($CONEXION, $q);

    if (!$r || mysqli_num_rows($r) == 0) {
        return 'N/A';
    }

    $row = mysqli_fetch_array($r);

    return isset($row['clienteid']) && $row['clienteid'] !== '' ? $row['clienteid'] : 'N/A';
}

function getObjectInstalation($imei)
{
    global $CONEXION;

    $q = "SELECT * FROM `gs_object_observations` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($CONEXION, $q);

    if (!$r) {
        return array('error' => 'Error en la consulta SQL: ' . mysqli_error($CONEXION));
    }

    if (mysqli_num_rows($r) == 0) {
        return array('error' => 'No se encontraron datos para el IMEI proporcionado.');
    }

    $row = mysqli_fetch_array($r);

    $fecha = isset($row['dt_user']) ? $row['dt_user'] : '';
    $fecha_alta = isset($row['fecha_alta']) ? $row['fecha_alta'] : '';
    $fecha_remplazo = isset($row['fecha_remplazo']) ? $row['fecha_remplazo'] : '';
    $fecha_creacion = isset($row['fecha_creacion']) ? $row['fecha_creacion'] : '';
    $fecha_mod = isset($row['fecha_modificacion']) ? $row['fecha_modificacion'] : '';
    $mtto = isset($row['mtto']) ? $row['mtto'] : '';

    $remplazo = ($mtto == 'Instalación por Remplazo' || $mtto == 'Instalación por Remplazo (Garantia)');
    $battery = ($mtto == 'Batería ER-100');
    $instalacion = ($fecha != '');

    if ($fecha == '') {
        $fecha = '0000-00-00 00:00:00';
    }

    if ($fecha == '2023-02-22 00:00:00') {
        $fecha = $fecha_alta;
    }
    if ($remplazo) {
        $fecha = $fecha_remplazo;
        $mtto = 'Remplazo';
    }
    if ($battery && $fecha_creacion == $fecha_mod) {
        $fecha = $fecha_mod;
        $mtto = 'Batería';
    }
    if ($instalacion && $fecha == $fecha_mod) {
        $mtto = 'Instalacion';
    }

    return array(
        'fecha' => $fecha,
        'mtto' => $mtto,
        'Remplazo' => $remplazo,
        'Battery' => $battery,
        'Instalacion' => $instalacion
    );
}

function getObjectDateMtto($imei)
{
    global $CONEXION;

    $q = "SELECT * FROM `gs_object_observations` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($CONEXION, $q);

    if (!$r) {
        return array('fecha' => '0000-00-00 00:00:00', 'mtto' => 'Error en la consulta');
    }

    $row = mysqli_fetch_array($r);

    // Verificar si hay datos antes de acceder a ellos
    if (!$row) {
        return array('fecha' => '0000-00-00 00:00:00', 'mtto' => 'Sin datos');
    }

    $fecha_mod = $row['fecha_modificacion'] ?? '0000-00-00 00:00:00';
    $fecha_remplazo = $row['fecha_remplazo'] ?? '0000-00-00 00:00:00';
    $mtto = $row['mtto'] ?? 'Sin Cambios';

    $remplazo = ($mtto == 'Instalación por Remplazo' || $mtto == 'Instalación por Remplazo (Garantia)');
    $battery = ($mtto == 'Batería ER-100');

    if (!$remplazo && !$battery) {
        return array('fecha' => $fecha_remplazo, 'mtto' => $mtto);
    }
    if ($remplazo) {
        return array('fecha' => $fecha_remplazo, 'mtto' => $mtto);
    }

    return array('fecha' => $fecha_mod, 'mtto' => 'mtto');
}


function getObjectBattery($imei)
{
    global $ms;


    $q = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);
    $model = $row['device'];
    $protocol = $row['protocol'];

    if (!empty($row['params'])) {
        $params = json_decode($row['params'], true);

        if (is_array($params)) {
            if (isset($params['acc0'])) {
                $acc0_value = $params['acc0'];

                $porcentaje = ($acc0_value - 1900) / (3400 - 1900) * 100;

                if ($porcentaje >= 100) {
                    $porcentaje = 100;
                } elseif ($porcentaje <= 0) {
                    $porcentaje = 0;
                } else {
                    $porcentaje = round($porcentaje, 2);
                }
                $porcentaje .= '%';
                return $porcentaje;
            } elseif (isset($params['batteryLevel'])) {
                $battery_value = $params['batteryLevel'];
                $porcentaje = number_format($battery_value, 0) . '%';
                if ($porcentaje < 1) {
                    return $porcentaje;
                }
                return $porcentaje;
            } elseif ((isset($params['power']) || isset($params['io1'])) && ($model == 'suntechST310U' || $model == 'suntechST4315' || $model == 'suntechST3310U')) {
                if (isset($params['power'])) {
                    $battery_value = number_format($params['power'], 2);
                } elseif (isset($params['io1'])) {
                    $battery_value = number_format($params['io1'], 2);
                } else {
                    return '0%';
                }

                $porcentaje = ($battery_value - 1.90) / (4.20 - 1.90) * 100;

                if ($porcentaje >= 100) {
                    $porcentaje = 100;
                } elseif ($porcentaje <= 0) {
                    if (isset($params['io1'])) {
                        $battery_value = number_format($params['io1'], 2);
                    }
                    $porcentaje = ($battery_value - 1.90) / (4.20 - 1.90) * 100;
                    if ($porcentaje <= 0) {
                        $porcentaje = 0;
                    }
                } else {
                    $porcentaje = round($porcentaje, 2);
                }
                if ($porcentaje == 0) {
                    $porcentaje = '0%';
                }
                $porcentaje = round($porcentaje, 2);
                $porcentaje .= '%';
                return $porcentaje;
            } elseif (isset($params['power']) && ($model == 'suntechst3940' || $model == 'suntechst910')) {
                $power_value = $params['power'];
                $porcentaje = number_format($power_value, 0) . '%';
                return $porcentaje;
            } elseif (isset($params['battery'])) {
                $battery = number_format($params['battery'], 2);
                $porcentaje = ($battery - 1.90) / (4.10 - 1.90) * 100;

                if ($porcentaje >= 100) {
                    $porcentaje = 100;
                } elseif ($porcentaje <= 0) {
                    $porcentaje = 0;
                } else {
                    $porcentaje = round($porcentaje, 2);
                }
                $porcentaje = round($porcentaje, 2);
                $porcentaje .= '%';
                return $porcentaje;
            } elseif (isset($params['adc2']) && $protocol == 'cellocator') {
                $battery = number_format($params['adc2'], 2);
                $porcentaje = ($battery - 1.90) / (4.10 - 1.90) * 100;

                if ($porcentaje >= 100) {
                    $porcentaje = 100;
                } elseif ($porcentaje <= 0) {
                    $porcentaje = 0;
                } else {
                    $porcentaje = round($porcentaje, 2);
                }
                $porcentaje = round($porcentaje, 2);
                $porcentaje .= '%';
                return $porcentaje;
            }
        }
    }

    return '0%';
}


function getObjectPlan($imei)
{
    global $ms;

    $q = "SELECT * FROM `gs_object_services` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    return $row['plan'];
}

function getObjectSevice($imei, $name)
{
    global $ms, $gsValues, $la;
    $unidad = getObjectName($imei);
    $fecha_actual = new DateTime('now', new DateTimeZone('UTC'));
    $fecha_actual->modify('-6 hours');
    $fecha_actual = $fecha_actual->format('Y-m-d H:i:s');
    

    $q = "SELECT * FROM gs_object_services WHERE imei='" . $imei . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);
    $imei_with_notify = $row['notify_service_expire'] ?? '';
    $event_id = $row['service_id'] ?? '';

    if ($imei_with_notify == 'true' && $name == 'Encendido de Motor') {

        $q = "SELECT * FROM gs_user_events WHERE maintenance_id ='" . $event_id . "'";
        $r = mysqli_query($ms, $q);
        $row = mysqli_fetch_array($r);

        $email = trim($row['notify_email_address'] ?? '');
        $service = $row['name'] ?? '';

        if (empty($email)) {
            return 'false';
        }

        $emailsArray = array_filter(array_map('trim', explode(',', $email)));
        if (empty($emailsArray)) {
            return 'false';
        }

        $subject = 'Servicio vencido - Acción requerida';

        $message = 
        "Hola,\n\n" .
        "Este es un mensaje de ALERTA, Por favor no conteste este e-mail.\n\n" .
        "Se ha ejecutado un comando para desbloquear el motor de la unidad {$unidad},\n" .
        "la cual cuenta con un servicio de mantenimiento ({$service}) pendiente o vencido.\n\n" .
        "Importante: Esta acción fue realizada bajo criterio del usuario autorizado.\n" .
        "Se recomienda verificar el historial de mantenimiento para evitar posibles afectaciones en el desempeño o seguridad de la unidad.\n\n" .
        
        "Unidad: {$unidad}\n" .
        "Servicio: {$service}\n" .
        "Time (position): {$fecha_actual}";
        
        

        include '../tools/email.php';


        foreach ($emailsArray as $correo) {
            sendEmail($correo, $subject, $message);
        }
        return 'true';
    }
    return 'false';
}

function getObjectDaysRest($imei)
{
    global $ms;

    $q = "SELECT * FROM `gs_object_services` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    if ($row = mysqli_fetch_array($r)) {
        $days_diff = strtotime(gmdate("Y-m-d")) - (strtotime($row['days_last']));
        $days_diff = floor($days_diff / 3600 / 24);
        $days_diff = $row['days_interval'] - $days_diff;

        return $days_diff;
    }
}

function getObjectRent($imei)
{
    global $CONEXION;

    $q = "SELECT * FROM `gs_object_observations` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($CONEXION, $q);
    $row = mysqli_fetch_array($r);
    if ($row === null) {
        return 0;
    }

    return $row['renta'];
}
function getObjectResponseWs($imei, $ws)
{
    $logFilePath = "/var/www/html/ws-logs/log_{$ws}_" . date("d-m-Y") . ".txt";

    if (!file_exists($logFilePath)) {
        return null;
    }

    $logContent = file_get_contents($logFilePath);
    
    // Split the concatenated JSON objects manually
    $jsonObjects = preg_split('/(?<=\})(?=\{)/', $logContent);

    foreach ($jsonObjects as $jsonString) {
        $jsonData = json_decode($jsonString, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            if ($ws === 'Guda' && isset($jsonData['parameters']['strGpsID'])) {
                if ($jsonData['parameters']['strGpsID'] === $imei && isset($jsonData['response']['SendLocationResult'])) {
                    return json_encode($jsonData['response']['SendLocationResult']);
                }
            } elseif ($ws === 'Rconfiable' && isset($jsonData['parameters']['serialNumber'])) {
                if ($jsonData['parameters']['serialNumber'] === $imei && isset($jsonData['response']['GPSAssetTrackingResult']['AppointResult']['idJob'])) {
                    return json_encode(['idJob' => $jsonData['response']['GPSAssetTrackingResult']['AppointResult']['idJob']]);
                }
            }
        }
    }

    return null;
}
function getObjectEngineHours($imei, $details)
{
    global $ms;

    $q = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    if ($details) {
        return getTimeDetails($row['engine_hours'], false);
    } else {
        return floor($row['engine_hours'] / 60 / 60);
    }
}

function getObjectFCR($imei)
{
    global $ms, $gsValues;

    // default fcr
    $default = array(
        'source' => 'rates',
        'measurement' => 'l100km',
        'cost' => 0,
        'summer' => 0,
        'winter' => 0,
        'winter_start' => '12-01',
        'winter_end' => '03-01'
    );

    $q = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    $fcr = $default;

    if ($r && mysqli_num_rows($r) > 0) {
        $row = mysqli_fetch_array($r);

        if (($row['fcr'] == '') || (json_decode($row['fcr'], true) == null)) {
            $fcr = $default;
        } else {
            $fcr = json_decode($row['fcr'], true);

            if (!isset($fcr["source"])) {
                $fcr["source"] = $default["source"];
            }
            if (!isset($fcr["measurement"])) {
                $fcr["measurement"] = $default["measurement"];
            }
            if (!isset($fcr["cost"])) {
                $fcr["cost"] = $default["cost"];
            }
            if (!isset($fcr["summer"])) {
                $fcr["summer"] = $default["summer"];
            }
            if (!isset($fcr["winter"])) {
                $fcr["winter"] = $default["winter"];
            }
            if (!isset($fcr["winter_start"])) {
                $fcr["winter_start"] = $default["winter_start"];
            }
            if (!isset($fcr["winter_end"])) {
                $fcr["winter_end"] = $default["winter_end"];
            }
        }
    }

    return $fcr;
}


function getObjectAccuracy($imei)
{
    global $ms, $gsValues;

    // default accuracy
    $default = array(
        'stops' => 'gps',
        'route_length' => 'gps',
        'min_moving_speed' => 6,
        'min_idle_speed' => 3,
        'min_diff_points' => 0.0005,
        'use_gpslev' => false,
        'min_gpslev' => 5,
        'use_hdop' => false,
        'max_hdop' => 3,
        'min_fuel_speed' => 10,
        'min_ff' => 30,
        'min_ft' => 20
    );

    $q = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);
    $accuracy = $default;

    if ($r && mysqli_num_rows($r) > 0) {
        $row = mysqli_fetch_array($r);

        // set default accuracy if not set in DB
        if (($row['accuracy'] == '') || (json_decode($row['accuracy'], true) == null)) {
            $accuracy = $default;
        } else {
            $accuracy = json_decode($row['accuracy'], true);

            if (!isset($accuracy["stops"])) {
                $accuracy["stops"] = $default["stops"];
            }
            if (!isset($accuracy["route_length"])) {
                $accuracy["route_length"] = $default["route_length"];
            }
            if (!isset($accuracy["min_moving_speed"])) {
                $accuracy["min_moving_speed"] = $default["min_moving_speed"];
            }
            if (!isset($accuracy["min_idle_speed"])) {
                $accuracy["min_idle_speed"] = $default["min_idle_speed"];
            }
            if (!isset($accuracy["min_diff_points"])) {
                $accuracy["min_diff_points"] = $default["min_diff_points"];
            }
            if (!isset($accuracy["use_gpslev"])) {
                $accuracy["use_gpslev"] = $default["use_gpslev"];
            }
            if (!isset($accuracy["min_gpslev"])) {
                $accuracy["min_gpslev"] = $default["min_gpslev"];
            }
            if (!isset($accuracy["use_hdop"])) {
                $accuracy["use_hdop"] = $default["use_hdop"];
            }
            if (!isset($accuracy["max_hdop"])) {
                $accuracy["max_hdop"] = $default["max_hdop"];
            }
            if (!isset($accuracy["min_fuel_speed"])) {
                $accuracy["min_fuel_speed"] = $default["min_fuel_speed"];
            }
            if (!isset($accuracy["min_ff"])) {
                $accuracy["min_ff"] = $default["min_ff"];
            }
            if (!isset($accuracy["min_ft"])) {
                $accuracy["stops"] = $default["stops"];
            }
        }
    }

    return $accuracy;
}

function getObjectSensors($imei)
{
    global $ms;

    // get object sensor list
    $q = "SELECT * FROM `gs_object_sensors` WHERE `imei`='" . $imei . "' ORDER BY `name` ASC";
    $r = mysqli_query($ms, $q);

    $sensors = array();

    while ($row = mysqli_fetch_array($r)) {
        $sensor_id = $row['sensor_id'];

        $calibration = json_decode($row['calibration'], true);
        if ($calibration == null) {
            $calibration = array();
        }

        $dictionary = json_decode($row['dictionary'], true);
        if ($dictionary == null) {
            $dictionary = array();
        }

        $sensors[$sensor_id] = array(
            'name' => $row['name'],
            'type' => $row['type'],
            'param' => $row['param'],
            'data_list' => $row['data_list'],
            'popup' => $row['popup'],
            'result_type' => $row['result_type'],
            'text_1' => $row['text_1'],
            'text_0' => $row['text_0'],
            'units' => $row['units'],
            'lv' => $row['lv'],
            'hv' => $row['hv'],
            'acc_ignore' => $row['acc_ignore'],
            'formula' => $row['formula'],
            'calibration' => $calibration,
            'dictionary' => $dictionary
        );
    }

    return $sensors;
}

function getObjectService($imei)
{
    global $ms;

    // get object service list
    $q = "SELECT * FROM `gs_object_services` WHERE `imei`='" . $imei . "' ORDER BY `name` ASC";
    $r = mysqli_query($ms, $q);

    $service = array();

    while ($row = mysqli_fetch_array($r)) {
        $row['odo_interval'] = floor(convDistanceUnits($row['odo_interval'], 'km', $_SESSION["unit_distance"]));
        $row['odo_last'] = floor(convDistanceUnits($row['odo_last'], 'km', $_SESSION["unit_distance"]));
        $row['odo_left_num'] = floor(convDistanceUnits($row['odo_left_num'], 'km', $_SESSION["unit_distance"]));

        $service_id = $row['service_id'];
        $service[$service_id] = array(
            'plan' => $row['plan'],
            'name' => $row['name'],
            'data_list' => $row['data_list'],
            'popup' => $row['popup'],
            'odo' => $row['odo'],
            'odo_interval' => $row['odo_interval'],
            'odo_last' => $row['odo_last'],
            'engh' => $row['engh'],
            'engh_interval' => $row['engh_interval'],
            'engh_last' => $row['engh_last'],
            'days' => $row['days'],
            'days_interval' => $row['days_interval'],
            'days_last' => $row['days_last'],
            'odo_left' => $row['odo_left'],
            'odo_left_num' => $row['odo_left_num'],
            'engh_left' => $row['engh_left'],
            'engh_left_num' => $row['engh_left_num'],
            'days_left' => $row['days_left'],
            'days_left_num' => $row['days_left_num'],
            'update_last' => $row['update_last']
        );
    }

    return $service;
}

function getObjectCustomFields($imei)
{
    global $ms;

    // get object service list
    $q = "SELECT * FROM `gs_object_custom_fields` WHERE `imei`='" . $imei . "' ORDER BY `name` ASC";
    $r = mysqli_query($ms, $q);

    $custom_fields = array();

    while ($row = mysqli_fetch_array($r)) {
        $field_id = $row['field_id'];
        $custom_fields[$field_id] = array(
            'name' => $row['name'],
            'value' => $row['value'],
            'data_list' => $row['data_list'],
            'popup' => $row['popup']
        );
    }

    return $custom_fields;
}
function getObjectMtto($imei)
{
    global $ms, $CONEXION;

    $mtto = '';

    $q = "SELECT * FROM `gs_objects` WHERE `imei`='" . mysqli_real_escape_string($ms, $imei) . "'";
    $r = mysqli_query($ms, $q);

    if ($r && mysqli_num_rows($r) > 0) {
        $row = mysqli_fetch_array($r);
        $name = isset($row['name']) ? $row['name'] : '';
    } else {
        $name = '';
    }

    $q = "SELECT * FROM gs_object_observations goo WHERE imei = '" . mysqli_real_escape_string($CONEXION, $imei) . "'";
    $r = mysqli_query($CONEXION, $q);

    if ($r && mysqli_num_rows($r) > 0) {
        $row2 = mysqli_fetch_array($r);
        $mtto_ = isset($row2['mtto']) ? $row2['mtto'] : '';
        $mtto_values = preg_split('/,\s*/', $mtto_);
        $mtto = end($mtto_values);
    }

    if (preg_match('/,\\s/', $name)) {
        $query_select = "SELECT * FROM gs_object_observations WHERE imei_old = '" . mysqli_real_escape_string($CONEXION, $imei) . "'";
        $r3 = mysqli_query($CONEXION, $query_select);

        if ($r3 && mysqli_num_rows($r3) > 0) {
            $row3 = mysqli_fetch_array($r3);
            $mtto_ = isset($row3['mtto_old']) ? $row3['mtto_old'] : '';
            $mtto_values = preg_split('/,\s*/', $mtto_);
            $mtto = end($mtto_values);
        }
    }

    return $mtto;
}
function getObjectIccid($imei)
{
    global $ms;
    $iccid = '';
    $q = "SELECT * FROM gs_objects WHERE imei = '" . mysqli_real_escape_string($ms, $imei) . "'";
    $r = mysqli_query($ms, $q);

    if ($r && mysqli_num_rows($r) > 0) {
        $row2 = mysqli_fetch_array($r);
        if (isset($row2['sim_iccid'])) {
            $iccid = $row2['sim_iccid'];
        }
    }

    return $iccid;
}
function getObjectFota($imei)
{ 
    require_once '/var/www/html/tools/sms.php';

    $cmd = 'fota';
    $result = CreateConfigBasicApi($cmd, $imei);

    return $result;
}
function simNumberCompany($imei)
{
    global $ms;
    $sim_number_company = '';
    $q = "SELECT * FROM gs_objects WHERE imei = '" . mysqli_real_escape_string($ms, $imei) . "'";
    $r = mysqli_query($ms, $q);

    if ($r && mysqli_num_rows($r) > 0) {
        $row2 = mysqli_fetch_array($r);
        if (isset($row2['sim_number_company'])) {
            $sim_number_company = $row2['sim_number_company'];
        }
    }

    return $sim_number_company;
}

function getObjectActivestream($imei)
{
    global $ms;
    $active = '';
    $q = "SELECT * FROM gs_object_streams WHERE imei = '" . mysqli_real_escape_string($ms, $imei) . "'";
    $r = mysqli_query($ms, $q);

    if ($r && mysqli_num_rows($r) > 0) {
        $row2 = mysqli_fetch_array($r);
        if (isset($row2['active'])) {
            $active = $row2['active'];
        }
    }

    return $active;
}

function getObjectUrl_stream($imei)
{
    global $ms;
    $url_stream = '';
    $q = "SELECT * FROM gs_object_streams WHERE imei = '" . mysqli_real_escape_string($ms, $imei) . "'";
    $r = mysqli_query($ms, $q);

    if ($r && mysqli_num_rows($r) > 0) {
        $row2 = mysqli_fetch_array($r);
        if (isset($row2['url_stream'])) {
            $url_stream = $row2['url_stream'];
        }
    }

    return $url_stream;
}

function getObjectTime_stream($imei)
{
    global $ms;
    $time = '';
    $q = "SELECT * FROM gs_object_streams WHERE imei = '" . mysqli_real_escape_string($ms, $imei) . "'";
    $r = mysqli_query($ms, $q);

    if ($r && mysqli_num_rows($r) > 0) {
        $row2 = mysqli_fetch_array($r);
        if (isset($row2['total_time_allowed_seconds'])) {
            $time = $row2['total_time_allowed_seconds'];
        }
    }

    return $time;
}

function getObjectLeft_time_stream($imei)
{
    global $ms;
    $time_remaining = '';
    $q = "SELECT * FROM gs_object_streams WHERE imei = '" . mysqli_real_escape_string($ms, $imei) . "'";
    $r = mysqli_query($ms, $q);

    if ($r && mysqli_num_rows($r) > 0) {
        $row2 = mysqli_fetch_array($r);
        if (isset($row2['total_time_remaining_seconds'])) {
            $time_remaining = $row2['total_time_remaining_seconds'];
        }
    }

    return $time_remaining;
}


function getUserExpireAvgDate($ids)
{
    global $ms;

    $date_from_today = '';
    $total_days = 0;
    $count = 0;

    $ids_ = '';
    for ($i = 0; $i < count($ids); ++$i) {
        if ($_SESSION["user_id"] != $ids[$i]) {
            $ids_ .= '"' . $ids[$i] . '",';
        }
    }
    $ids_ = rtrim($ids_, ',');

    $q = "SELECT * FROM `gs_users` WHERE `id` IN (" . $ids_ . ")";
    $r = mysqli_query($ms, $q);

    if (!$r) {
        return $date_from_today;
    }

    while ($row = mysqli_fetch_array($r)) {
        if ($row['account_expire'] == 'true') {
            $object_expire_dt = strtotime($row['account_expire_dt']);
            $today = strtotime(gmdate('Y-m-d'));

            $diff_days = round(($object_expire_dt - $today) / 86400);

            if ($diff_days > 0) {
                $total_days += $diff_days;
            }
        }

        $count++;
    }

    if ($count == 0) {
        return $date_from_today;
    }

    $total_days = round($total_days / $count);

    $date_from_today = gmdate('Y-m-d', strtotime(gmdate('Y-m-d') . ' + ' . $total_days . ' days'));

    return $date_from_today;
}

function getObjectExpireAvgDate($imeis)
{
    global $ms;

    $date_from_today = '';
    $total_days = 0;
    $count = 0;

    $imeis_ = '';
    for ($i = 0; $i < count($imeis); ++$i) {
        $imeis_ .= '"' . $imeis[$i] . '",';
    }
    $imeis_ = rtrim($imeis_, ',');

    $q = "SELECT * FROM `gs_objects` WHERE `imei` IN (" . $imeis_ . ")";
    $r = mysqli_query($ms, $q);

    if (!$r) {
        return $date_from_today;
    }

    while ($row = mysqli_fetch_array($r)) {
        if ($row['object_expire'] == 'true') {
            $object_expire_dt = strtotime($row['object_expire_dt']);
            $today = strtotime(gmdate('Y-m-d'));

            $diff_days = round(($object_expire_dt - $today) / 86400);

            if ($diff_days > 0) {
                $total_days += $diff_days;
            }
        }

        $count++;
    }

    if ($count == 0) {
        return $date_from_today;
    }

    $total_days = round($total_days / $count);

    $date_from_today = gmdate('Y-m-d', strtotime(gmdate('Y-m-d') . ' + ' . $total_days . ' days'));

    return $date_from_today;
}
function sendObjectSMSConfig($user_id, $imei, $name, $cmd, $number, $carrier, $device)
{
    global $ms, $gsValues;

    $result = false;

    // validate
    if (($imei == '') || ($cmd == ''))
        return $result;

    $imei = strtoupper($imei);

    //check user usage
    if (!checkUserUsage($user_id, 'sms'))
        return $result;

    // variables
    $cmd = str_replace("%IMEI%", $imei, $cmd);
    $cmd = str_replace("%imei%", $imei, $cmd);

    $q = "SELECT * FROM `gs_users` WHERE `id`='" . $user_id . "'";
    $r = mysqli_query($ms, $q);
    $ud = mysqli_fetch_array($r);

    if (strlen($number) > 11) {
        if ($carrier == 'M2M(Emprenet)') {
            $result = sendCommandApi($cmd, $number);
        }
        if ($carrier == 'M2M(Telefonica)') {
            $result = sendTelefonicaCommandApi($cmd, $number, $device);
        }
        if ($carrier == 'M2M(Teltonika)') {
            $result = sendCommandApiTeltonika($cmd, $number);
        }

        if ($result == true) {
            $q = "INSERT INTO `gs_object_cmd_exec`(`user_id`,
								`dt_cmd`,
								`imei`,
								`name`,
								`gateway`,
								`type`,
								`cmd`,
								`status`)
								VALUES
								('" . $user_id . "',
								'" . gmdate("Y-m-d H:i:s") . "',
								'" . $imei . "',
								'" . $name . "',
								'sms',
								'ascii',
								'" . $cmd . "',
								'1')";
            $r = mysqli_query($ms, $q);
        }
    } else {

        if ($ud['sms_gateway'] == 'true') {
            if ($ud['sms_gateway_type'] == 'http') {
                $result = sendSMSHTTP($ud['sms_gateway_url'], '', $number, $cmd);
            } else if ($ud['sms_gateway_type'] == 'app') {
                $result = sendSMSAPP($ud['sms_gateway_identifier'], '', $number, $cmd);
            }
        } else {
            if ($ud['sms_gateway_server'] == 'true' && $gsValues['SMS_GATEWAY'] == 'true') {
                if ($gsValues['SMS_GATEWAY_TYPE'] == 'http') {
                    $result = sendSMSHTTP($gsValues['SMS_GATEWAY_URL'], $gsValues['SMS_GATEWAY_NUMBER_FILTER'], $number, $cmd);
                } else if ($gsValues['SMS_GATEWAY_TYPE'] == 'app') {
                    $result = sendSMSAPP($gsValues['SMS_GATEWAY_IDENTIFIER'], $gsValues['SMS_GATEWAY_NUMBER_FILTER'], $number, $cmd);
                }
            }
        }


        if ($result == true) {
            $q = "INSERT INTO `gs_object_cmd_exec`(`user_id`,
								`dt_cmd`,
								`imei`,
								`name`,
								`gateway`,
								`type`,
								`cmd`,
								`status`)
								VALUES
								('" . $user_id . "',
								'" . gmdate("Y-m-d H:i:s") . "',
								'" . $imei . "',
								'" . $name . "',
								'sms',
								'ascii',
								'" . $cmd . "',
								'1')";
            $r = mysqli_query($ms, $q);

            //update user usage
            updateUserUsage($user_id, false, false, 1, false, false);
        }
    }
    return $result;
}
function sendObjectSMSCommand($user_id, $imei, $name, $cmd)
{
    global $ms, $gsValues;

    $result = false;
    $device = $_POST['device'] ?? '';

    // validate
    if (($imei == '') || ($cmd == ''))
        return $result;

    $imei = strtoupper($imei);

    //check user usage
    if (!checkUserUsage($user_id, 'sms'))
        return $result;

    // variables
    $cmd = str_replace("%IMEI%", $imei, $cmd);
    $cmd = str_replace("%imei%", $imei, $cmd);

    $q = "SELECT * FROM `gs_users` WHERE `id`='" . $user_id . "'";
    $r = mysqli_query($ms, $q);
    $ud = mysqli_fetch_array($r);


    if ($name != '**ALTA**') {
        $q = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "'";
        $r = mysqli_query($ms, $q);
        $od = mysqli_fetch_array($r);
        $carrier = $od['sim_number_company'];
        $number = $od['sim_number'];
        $device = $od['device'];
    } else {
        $carrier = 'M2M(Teltonika)';
        $number = '000000000000';
    }

    if (strlen($number) > 11 || $name == '**ALTA**') {
        if ($carrier == 'M2M(Emprenet)') {
            $result = sendCommandApi($cmd, $number);
        }
        if ($carrier == 'M2M(Telefonica)') {
            $result = sendTelefonicaCommandApi($cmd, $number, $device);
        }
        if ($carrier == 'AT&T') {
            $result = sendCommandApiAtt($cmd, $number);
        }
        if ($carrier == 'M2M(Teltonika)' && strpos($name, '**') !== false && $device !='DUX') {

            $result = CreateConfigBasicApi($cmd, $imei);
            if($name != '**ALTA**'){
                sendObjectGPRSCommand($user_id, $imei, 'Reporte a FOTA', 'ascii', 'web_connect');
            }
        } 
        if ($carrier == 'M2M(Teltonika)' && $name == 'Asignacion de Remolque') {
            include('../tools/sms.php');
            $result = sendCommandApiTeltonika($cmd, $number);
        } else {
            if ($result != 'true') {
                $result = sendCommandApiTeltonika($cmd, $number);
            }
        }

        if ($result == true) {
            $q = "INSERT INTO `gs_object_cmd_exec`(`user_id`,
								`dt_cmd`,
								`imei`,
								`name`,
								`gateway`,
								`type`,
								`cmd`,
								`status`)
								VALUES
								('" . $user_id . "',
								'" . gmdate("Y-m-d H:i:s") . "',
								'" . $imei . "',
								'" . $name . "',
								'sms',
								'ascii',
								'" . $cmd . "',
								'1')";
            $r = mysqli_query($ms, $q);
        }
    } else {

        if ($ud['sms_gateway'] == 'true') {
            if ($ud['sms_gateway_type'] == 'http') {
                $result = sendSMSHTTP($ud['sms_gateway_url'], '', $number, $cmd);
            } else if ($ud['sms_gateway_type'] == 'app') {
                $result = sendSMSAPP($ud['sms_gateway_identifier'], '', $number, $cmd);
            }
        } else {
            if ($ud['sms_gateway_server'] == 'true' && $gsValues['SMS_GATEWAY'] == 'true') {
                if ($gsValues['SMS_GATEWAY_TYPE'] == 'http') {
                    $result = sendSMSHTTP($gsValues['SMS_GATEWAY_URL'], $gsValues['SMS_GATEWAY_NUMBER_FILTER'], $number, $cmd);
                } else if ($gsValues['SMS_GATEWAY_TYPE'] == 'app') {
                    $result = sendSMSAPP($gsValues['SMS_GATEWAY_IDENTIFIER'], $gsValues['SMS_GATEWAY_NUMBER_FILTER'], $number, $cmd);
                }
            }
        }


        if ($result == true) {
            $q = "INSERT INTO `gs_object_cmd_exec`(`user_id`,
								`dt_cmd`,
								`imei`,
								`name`,
								`gateway`,
								`type`,
								`cmd`,
								`status`)
								VALUES
								('" . $user_id . "',
								'" . gmdate("Y-m-d H:i:s") . "',
								'" . $imei . "',
								'" . $name . "',
								'sms',
								'ascii',
								'" . $cmd . "',
								'1')";
            $r = mysqli_query($ms, $q);

            //update user usage
            updateUserUsage($user_id, false, false, 1, false, false);
        }
    }
    return $result;
}

function sendObjectGPRSCommand($user_id, $imei, $name, $type, $cmd)
{
    global $ms;

    $result = false;

    // validate
    // ...
    if (($imei == '') || ($cmd == '')) {
        return $result;
    }

    $imei = strtoupper($imei);
    $type = strtolower($type);

    if ($type == 'ascii') {
        // variables
        $cmd = str_replace("%IMEI%", $imei, $cmd);
        $cmd = str_replace("%imei%", $imei, $cmd);
    } else if ($type == 'hex') {
        $hex_imei = $imei;

        if (strlen($hex_imei) & 1) {
            $hex_imei = '0' . $hex_imei;
        }

        $cmd = strtoupper($cmd);

        // variables
        $cmd = str_replace("%IMEI%", $hex_imei, $cmd);

        if (!ctype_xdigit($cmd)) {
            return $result;
        }
    }
    $q = "SELECT protocol, params FROM gs_objects WHERE imei = '$imei'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    $protocol = $row['protocol'];

    if ($protocol === 'cellocator') {
        $params = json_decode($row['params'], true);

        if (isset($params['raw'])) {
            $serial = $params['raw'];
            $length = strlen($serial);

            $halfLength = ceil($length / 2);
            $part1 = substr($serial, 0, $halfLength);
            $part2 = substr($serial, $halfLength);

            $part2 = strtoupper($part2);
            $cmd = $part1 . $part2 . $cmd;
        }
    }



    $q = "SELECT * FROM `gs_object_cmd_exec` WHERE `imei`='" . $imei . "' AND `type`='" . $type . "' AND `cmd`='" . $cmd . "' AND `status`='0'";
    $r = mysqli_query($ms, $q);
    $num = mysqli_num_rows($r);
    if ($num == 0) {
        $q = "INSERT INTO `gs_object_cmd_exec`(`user_id`,
								`dt_cmd`,
								`imei`,
								`name`,
								`gateway`,
								`type`,
								`cmd`,
								`status`)
								VALUES
								('" . $user_id . "',
								'" . gmdate("Y-m-d H:i:s") . "',
								'" . $imei . "',
								'" . $name . "',
								'gprs',
								'" . $type . "',
								'" . $cmd . "',
								'0')";
        $r = mysqli_query($ms, $q);

        $result = true;
    }

    return $result;
}

function CreateCommandConfig($imei, $cmd_config, $user, $nombre_configuracion)
{
    global $ms;

    date_default_timezone_set("Mexico/General");
    $dt_now = date("Y-m-d H:i:s");
    $dt_now_obj = new DateTime($dt_now);
    $dt_now_obj->modify("+1 minutes");
    $new_dt_now = $dt_now_obj->format("Y-m-d H:i:s");

    if ($cmd_config) {
        $q = "INSERT INTO `gs_user_cmd_schedule`(`user_id`,
								`name`,
								`active`,
								`exact_time`,
								`exact_time_dt`,
								`day_time`,
								`protocol`,
								`imei`,
								`gateway`,
								`type`,
								`cmd`)
								VALUES
								('" . $user . "',
								'" . $nombre_configuracion . ", " . $imei . "',
								'true',
								'true',
								'" . $new_dt_now . "',
								'false',
								'',
								'" . $imei . "',
								'sms',
								'ascii',
								'" . $cmd_config . "')";
        $r = mysqli_query($ms, $q);
    }
}

function CreateApiConfig($imei, $config, $device)
{
    $url = "https://api.teltonika.lt/tasks";

    if ($config == 'Basico' && $device == 'teltonikafm130' ) {
        $config = '3818912';
    }

    if ($config == 'Basico' && $device == 'teltonikafm920' ) {
        $config = '3566271';
    }

    $apiToken = "6759|MIpVEUkKoM198puSktBMmvoi3nlKpJzXVZEA6qLZ";

    $data = [
        "file_id" =>  $config,
        "device_imei" => $imei,
        "type" => "TxConfiguration",
        "expire_existing_tasks" => true
    ];

    // Configuración de cURL
    $ch = curl_init($url);
    $headers = [
        "Authorization: Bearer $apiToken",
        "User-Agent: COMERCIALIZADORA E IMPORTADORA OPTIMUS S.A DE C.V. /optimusrastreogps.net/1.0",
        "Accept: application/json",
        "Content-Type: application/json"
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        echo 'Error en cURL: ' . curl_error($ch);
    } else {
        if ($httpCode == 200) {
            $responseData = json_decode($response, true);
            return $responseData;
        } else {
            echo "Código HTTP: $httpCode\nRespuesta: $response\n";
        }
    }

    curl_close($ch);

    return null;
}

function CreateCommandConfigGv300($user, $imei, $name, $type)
{
    global $ms;
    if ($name == '**CONFIGURACIóN BASICO**') {
        $cmd_config = "AT+GTAIS=gv300,1,4,250,5000,1,,0,0,0,0,0,,,2,4,250,5000,1,,0,0,0,0,0,,10,30,10,20,0,FFFF$, AT+GTCFG=gv300,gv300,gv300,0,0,0,,27,0,,3AEF,0,1,0,300,0,1,0,0,1E,0,FFFF$, AT+GTFRI=gv300,1,0,,0,0000,0000,180,180,1000,3000,,0,180,0,,,,FFFF$";
    } elseif ($name == '**CONFIGURACIóN TANQUES**') {
        $cmd_config = "AT+GTAIS=gv300,1,4,250,5000,1,,0,0,0,0,1,,,2,4,250,5000,1,,0,0,0,0,1,,10,30,10,20,0,FFFF$, AT+GTCFG=gv300,gv300,gv300,0,0,0,,27,0,,3AEF,0,1,0,300,0,1,0,0,1E,0,FFFF$, AT+GTFRI=gv300,1,0,,0,0000,0000,180,180,1000,3000,,0,180,0,,,,FFFF$";
    } elseif ($name == '**CONFIGURACIóN TEMP **') {
        $cmd_config = "AT+GTFRI=gv300,5,0,,0,0000,0000,180,180,3000,3000,,40,180,2,,,,FFFF$, AT+GTURT=gv300,5,12,8,1,0,0,0,,,FFFF$, AT+GTACD=gv300,1,0,0,0,0,30,,,,,FFFF$";
    }

    date_default_timezone_set("Mexico/General");
    $dt_now = date("Y-m-d H:i:s");
    $dt_now_obj = new DateTime($dt_now);
    $dt_now_obj->modify("+1 minutes");
    $new_dt_now = $dt_now_obj->format("Y-m-d H:i:s");

    $comandos_array = explode(', ', $cmd_config);


    foreach ($comandos_array as $comando) {
        $q = "INSERT INTO `gs_user_cmd_schedule`(`user_id`,
								`name`,
								`active`,
								`exact_time`,
								`exact_time_dt`,
								`day_time`,
								`protocol`,
								`imei`,
								`gateway`,
								`type`,
								`cmd`)
								VALUES
								('" . $user . "',
								'" . $name . "',
								'true',
								'true',
								'" . $new_dt_now . "',
								'false',
								'',
								'" . $imei . "',
								'gprs',
								'" . $type . "',
								'" . $comando . "')";
        $r = mysqli_query($ms, $q);
    }
}

// #################################################
// END OBJECT FUNCTIONS
// #################################################

// #################################################
// SENSOR FUNCTIONS
// #################################################

function mergeParams($old, $new)
{
    if (is_array($old) && is_array($new)) {
        $new = array_merge($old, $new);
    }

    return $new;
}

function getParamsArray($params)
{
    $arr_params = array();

    if ($params != '') {
        $params = json_decode($params, true);

        if (is_array($params)) {
            foreach ($params as $key => $value) {
                array_push($arr_params, $key);
            }
        }
    }

    return $arr_params;
}

function getParamValue($params, $param)
{
    $result = 0;

    if (isset($params[$param])) {
        $result = $params[$param];
    }

    return $result;
}
function getSensorsValue($imei, $dtf, $dtt)
{
    global $ms;

    $result = [];
    $params_prev = [];
    $formula = [];

    $litros_anterior = ['adc1' => 0, 'adc2' => 0];
    $litros_tanque = ['adc1' => 0, 'adc2' => 0];

    $q = "SELECT DISTINCT dt_tracker,
        lat,
        lng,
        altitude,
        angle,
        speed,
        params
        FROM `gs_object_data_" . $imei . "`
        WHERE dt_tracker BETWEEN '" . $dtf . "' AND '" . $dtt . "'
        AND speed > 15
        ORDER BY dt_tracker DESC
        LIMIT 400";

    $r = mysqli_query($ms, $q);
    $params_prev = [];

    while ($result_data = mysqli_fetch_assoc($r)) {
        $params = json_decode($result_data['params'], true);

        $dt_tracker = convUserTimezone($result_data['dt_tracker']);

        $params['datetime'] = $dt_tracker;

        $params = mergeParams($params_prev, $params);

        if (is_array($params)) {
            $result[] = [$params];
        }

        $params_prev = $params;
    }



    $fuel_sensors = getSensorFromType($imei, 'fuel');
    if ($fuel_sensors) {
        foreach ($fuel_sensors as $sensor) {
            $param = $sensor['param'];
            $formula[$param] = $sensor['formula'] ?? null;
        }
    }

    $num_results = count($result);

    if (is_array($fuel_sensors) && !empty($fuel_sensors)) {
        for ($j = 0; $j < $num_results; ++$j) {
            foreach ($fuel_sensors as $sensor) {
                $param = $sensor['param'];

                $prev = $result[$j - 1][0][$param] ?? false;
                $curr = $result[$j][0][$param] ?? false;
                $next = $result[$j + 1][0][$param] ?? false;

                if (($prev > $curr) && ($curr < $next) && isset($result[$j][0][$param])) {
                    $result[$j][0][$param] = $next;
                }
            }
        }
    }

    $falla_variaciones = [];
    $falla_pegado = [];

    foreach ($result as $data) {
        foreach (['adc1' => 'Tanque-1', 'adc2' => 'Tanque-2'] as $adc => $tanque) {
            if (!empty($falla_variaciones[$tanque]) || !empty($falla_pegado[$tanque])) {
                break 2;
            }
            $litros_anterior[$adc] = $litros_tanque[$adc];
            $litros_tanque[$adc] = null;

            if (isset($data[0][$adc])) {
                $voltaje = min(intval($data[0][$adc]), 5000);

                if (!isset($contador_variaciones[$tanque])) {
                    $contador_variaciones[$tanque] = 0;
                }

                if (isset($formula[$adc])) {
                    $litros_tanque[$adc] = calcString(str_replace('x', $voltaje, $formula[$adc]));
                    $litros_tanque[$adc] = intval($litros_tanque[$adc]);

                    if (!empty($litros_anterior[$adc])) {
                        $diferencia_litros[$tanque] = $litros_tanque[$adc] - $litros_anterior[$adc];

                        if (abs($diferencia_litros[$tanque]) > 15 && abs($diferencia_litros[$tanque]) <= 25) {
                            $contador_variaciones[$tanque]++;

                            if ($contador_variaciones[$tanque] >= 3) {
                                $falla_variaciones[$tanque] = "Variaciones";
                            }
                        }
                    }
                } else {
                    continue;
                }
                if (empty($falla_variaciones[$tanque])) {
                    $historial_tanque[$tanque][] = $litros_tanque[$adc];

                    if (count($historial_tanque[$tanque]) > 100) {
                        array_shift($historial_tanque[$tanque]);
                    }

                    $consecutive_count = 0;
                    $last_value = null;

                    foreach ($historial_tanque[$tanque] as $value) {
                        if ($value === $last_value) {
                            $consecutive_count++;
                        } else {
                            $consecutive_count = 1;
                            $last_value = $value;
                        }

                        if ($consecutive_count >= 30) {
                            $falla_pegado[$tanque] = "Valor Congelado";
                        }
                    }
                }
            }
        }
    }

    $mensajes = [];
    foreach (array_merge($falla_variaciones, $falla_pegado) as $sensor => $falla) {
        if ($falla) {
            $mensajes[] = $data[0]['datetime'] . " - " . $sensor . ": " . $falla;
        }
    }

    return !empty($mensajes) ? implode(', ', $mensajes) : 'OK';
}


function getTempValue($imei, $dtf, $dtt)
{
    global $ms;


    $temp_values = array();
    $falla_pegado = array();
    $temp_values = array();

    $q = "SELECT DISTINCT dt_tracker,
    lat,
    lng,
    altitude,
    angle,
    speed,
    params
    FROM `gs_object_data_" . $imei . "`
    WHERE dt_tracker BETWEEN '" . $dtf . "' AND '" . $dtt . "'
    AND speed > 5
    ORDER BY dt_tracker DESC
    LIMIT 50";



    $r = mysqli_query($ms, $q);

    while ($result_data = mysqli_fetch_array($r)) {
        $params = json_decode($result_data['params'], true);

        if (isset($params['temp1'])) {
            $temp = intval($params['temp1'] * 100);
            $temp_values[] = $temp;
        }
    }

    if (count($temp_values) >= 30) {
        $last_values = array_slice($temp_values, -30);

        if (count(array_unique($last_values)) === 1 && end($last_values) === reset($last_values)) {
            $falla_pegado['Temperatura'] = true;
        }
    }



    if (count($temp_values) === 0 || count($temp_values) < 10) {
        return 'No Reporta, Reporta Intermitente "temp"';
    }


    $mensajes = array();
    foreach ($falla_pegado as $sensor => $falla) {
        if ($falla) {
            $mensajes[] = "$sensor Valor Congelado";
        }
    }
    if (!empty($mensajes)) {
        return implode(', ', $mensajes);
    } else {
        return 'OK';
    }
}

function paramsToArray($params)
{
    // keep compatibility with old software versions which used '|' and with software versions using JSON

    $arr_params = array();
    if (substr($params, -1) == '|') {
        $params = explode("|", $params);

        for ($i = 0; $i < count($params) - 1; ++$i) {
            $param = explode("=", $params[$i]);
            $arr_params[$param[0]] = $param[1];
        }
    } else {
        $arr_params = json_decode($params, true);
    }

    if (!is_array($arr_params)) {
        $arr_params = array();
    }

    return $arr_params;
}
function getSensorValue($params, $sensor)
{
    $result = array();
    $result['value'] = 0;
    $result['value_full'] = '';

    if (!is_array($sensor) || empty($sensor)) {
        return $result;
    }

    if (!isset($sensor['param']) || $sensor['param'] === '') {
        return $result;
    }

    $param_value = getParamValue($params, $sensor['param']);

    // formula
    if (($sensor['result_type'] == 'abs') || ($sensor['result_type'] == 'rel') || ($sensor['result_type'] == 'value')) {
        if ($sensor['formula'] != '') {
            $formula = strtolower($sensor['formula']);
            if (!is_numeric($param_value)) {
                $param_value = 0;
            }
            $formula = str_replace('x', $param_value, $formula);
            $param_value = calcString($formula);
        }
    }

    if (($sensor['result_type'] == 'abs') || ($sensor['result_type'] == 'rel')) {
        $param_value = sprintf("%01.3f", $param_value);

        $result['value'] = $param_value;
        $result['value_full'] = $param_value;
    } else if ($sensor['result_type'] == 'logic') {
        if ($param_value == 1) {
            $result['value'] = $param_value;
            $result['value_full'] = $sensor['text_1'];
        } else {
            $result['value'] = $param_value;
            $result['value_full'] = $sensor['text_0'];
        }
    } else if ($sensor['result_type'] == 'value') {
        // calibration
        $out_of_cal = true;

        $calibration = json_decode($sensor['calibration'], true);
        if ($calibration == null) {
            $calibration = array();
        }

        if (count($calibration) >= 2) {
            // put all X values to separate array
            $x_arr = array();

            for ($i = 0; $i < count($calibration); $i++) {
                $x_arr[] = $calibration[$i]['x'];
            }

            sort($x_arr);

            for ($i = 0; $i < count($x_arr) - 1; $i++) {
                $x_low = $x_arr[$i];
                $x_high = $x_arr[$i + 1];

                if (($param_value >= $x_low) && ($param_value <= $x_high)) {
                    // get Y low and high
                    $y_low = 0;
                    $y_high = 0;

                    for ($j = 0; $j < count($calibration); $j++) {
                        if ($calibration[$j]['x'] == $x_low) {
                            $y_low = $calibration[$j]['y'];
                        }

                        if ($calibration[$j]['x'] == $x_high) {
                            $y_high = $calibration[$j]['y'];
                        }
                    }

                    // get coeficient
                    $a = $param_value - $x_low;
                    $b = $x_high - $x_low;

                    $coef = ($a / $b);

                    $c = $y_high - $y_low;
                    $coef = $c * $coef;

                    $param_value = $y_low + $coef;

                    $out_of_cal = false;

                    break;
                }
            }

            if ($out_of_cal) {
                // check if lower than cal
                $x_low = $x_arr[0];

                if ($param_value < $x_low) {
                    for ($j = 0; $j < count($calibration); $j++) {
                        if ($calibration[$j]['x'] == $x_low) {
                            $param_value = $calibration[$j]['y'];
                        }
                    }
                }

                // check if higher than cal
                $x_high = end($x_arr);

                if ($param_value > $x_high) {
                    for ($j = 0; $j < count($calibration); $j++) {
                        if ($calibration[$j]['x'] == $x_high) {
                            $param_value = $calibration[$j]['y'];
                        }
                    }
                }
            }
        }

        $param_value = sprintf("%01.2f", $param_value);

        // dictionary
        // not needed for PHP version, only in JS

        $result['value'] = $param_value;
        $result['value_full'] = $param_value . ' ' . $sensor['units'];
    } else if ($sensor['result_type'] == 'string') {
        $result['value'] = $param_value;
        $result['value_full'] = $param_value;
    } else if ($sensor['result_type'] == 'percentage') {
        if (($param_value > $sensor['lv']) && ($param_value < $sensor['hv'])) {
            $a = $param_value - $sensor['lv'];
            $b = $sensor['hv'] - $sensor['lv'];

            $result['value'] = floor(($a / $b) * 100);
        } else if ($param_value <= $sensor['lv']) {
            $result['value'] = 0;
        } else if ($param_value >= $sensor['hv']) {
            $result['value'] = 100;
        }

        $result['value_full'] = $result['value'] . ' %';
    }

    return $result;
}

function getSensors($imei)
{
    global $ms;

    $result = [];

    $imei_esc = mysqli_real_escape_string($ms, $imei);
    $q = "SELECT * FROM `gs_object_sensors` WHERE `imei`='$imei_esc'";
    $r = mysqli_query($ms, $q);

    if (!$r) {
        return [];
    }

    while ($sensor = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
        $result[] = $sensor;
    }

    return $result;
}

function getSensorFromType($imei, $type)
{
    global $ms;

    $result = array();

    $q = "SELECT * FROM `gs_object_sensors` WHERE `imei`='" . $imei . "' AND `type`='" . $type . "'";
    $r = mysqli_query($ms, $q);

    if ($r && mysqli_num_rows($r) > 0) {
        while ($sensor = mysqli_fetch_array($r)) {
            $result[] = $sensor;
        }
    }
    if (count($result) > 0) {
        return $result;
    } else {
        return false;
    }
}


// #################################################
// END SENSOR FUNCTIONS
// #################################################

// #################################################
// MATH FUNCTIONS
// #################################################

// needed for older than PHP 5.4 version
if (!function_exists('hex2bin')) {
    function hex2bin($str)
    {
        $sbin = "";
        $len = strlen($str);
        for ($i = 0; $i < $len; $i += 2) {
            $sbin .= pack("H*", substr($str, $i, 2));
        }
        return $sbin;
    }
}

function calcString($str)
{
    $result = 0;
    try {
        $str = trim($str);
        //$str = str_replace('Math.',"",$formula);
        // $str = preg_replace('/[^0-9\(\)+-\/\*.]/', '', $str);
        $str = $str . ';';

        return $result + eval('return ' . $str);
    } catch (Exception $e) {
        return $result;
    }
}

function getUnits($units)
{
    $result = array();

    $units = explode(",", $units);

    $result["unit_distance"] = @$units[0];
    if ($result["unit_distance"] == '') {
        $result["unit_distance"] = 'km';
    }

    $result["unit_capacity"] = @$units[1];
    if ($result["unit_capacity"] == '') {
        $result["unit_capacity"] = 'l';
    }

    $result["unit_temperature"] = @$units[2];
    if ($result["unit_temperature"] == '') {
        $result["unit_temperature"] = 'c';
    }

    return $result;
}

function convSpeedUnits($val, $from, $to)
{
    return floor(convDistanceUnits($val, $from, $to));
}

function convDistanceUnits($val, $from, $to)
{
    if ($from == 'km') {
        if ($to == 'mi') {
            $val = $val * 0.621371;
        } else if ($to == 'nm') {
            $val = $val * 0.539957;
        }
    } else if ($from == 'mi') {
        if ($to == 'km') {
            $val = $val * 1.60934;
        } else if ($to == 'nm') {
            $val = $val * 0.868976;
        }
    } else if ($from == 'nm') {
        if ($to == 'km') {
            $val = $val * 1.852;
        } else if ($to == 'nm') {
            $val = $val * 1.15078;
        }
    }

    return $val;
}

function convAltitudeUnits($val, $from, $to)
{
    if ($from == 'km') {
        if (($to == 'mi') || ($to == 'nm')) // to feet
        {
            $val = floor($val * 3.28084);
        }
    }

    return $val;
}

//function convTempUnits($val, $from, $to)
//{
//
//}

function convDateToNum($dt)
{
    $dt = str_replace('-', '', $dt);
    $dt = str_replace(':', '', $dt);
    $dt = str_replace(' ', '', $dt);

    return $dt;
}

function isDateInRange($dt, $start, $end)
{
    if ($start > $end) {
        return ($dt > $start) || ($dt < $end);
    } else {
        return ($dt > $start) && ($dt < $end);
    }
}

function getTimeDetails($sec, $show_days)
{
    global $la;

    $seconds = 0;
    $hours = 0;
    $minutes = 0;
    if (is_numeric($sec)) {
        if ($sec % 86400 <= 0) {
            $days = $sec / 86400;
        }
        if ($sec % 86400 > 0) {
            $rest = ($sec % 86400);
            $days = ($sec - $rest) / 86400;

            if ($rest % 3600 > 0) {
                $rest1 = ($rest % 3600);
                $hours = ($rest - $rest1) / 3600;

                if ($rest1 % 60 > 0) {
                    $rest2 = ($rest1 % 60);
                    $minutes = ($rest1 - $rest2) / 60;
                    $seconds = $rest2;
                } else {
                    $minutes = $rest1 / 60;
                }
            } else {
                $hours = $rest / 3600;
            }
        }

        if ($show_days == false) {
            $hours += $days * 24;
            $days = 0;
        }

        $la['UNIT_D'] = isset($la['UNIT_D']) ? $la['UNIT_D'] : 'd';
        $la['UNIT_H'] = isset($la['UNIT_H']) ? $la['UNIT_H'] : 'h';
        $la['UNIT_MIN'] = isset($la['UNIT_MIN']) ? $la['UNIT_MIN'] : 'm';
        $la['UNIT_S'] = isset($la['UNIT_S']) ? $la['UNIT_S'] : 's';

        if ($days > 0) {
            $days = $days . ' ' . $la['UNIT_D'] . ' ';
        } else {
            $days = false;
        }
        if ($hours > 0) {
            $hours = $hours . ' ' . $la['UNIT_H'] . ' ';
        } else {
            $hours = false;
        }
        if ($minutes > 0) {
            $minutes = $minutes . ' ' . $la['UNIT_MIN'] . ' ';
        } else {
            $minutes = false;
        }
        $seconds = $seconds . ' ' . $la['UNIT_S'];
        return $days . $hours . $minutes . $seconds;
    }
}

function getTimeDifferenceDetails($start_date, $end_date)
{
    $diff = strtotime($end_date) - strtotime($start_date);
    return getTimeDetails($diff, true);
}

function verifyGPSNearestZone($zone, $lat, $lng)
{
    $in_zone_vertices = explode(',', $zone);
    $distance = 0;

    for ($j = 0; $j < count($in_zone_vertices); $j += 2) {
        $zone_lat = $in_zone_vertices[$j];
        $zone_lng = $in_zone_vertices[$j + 1];

        $temp = getLengthBetweenCoordinates($lat, $lng, $zone_lat, $zone_lng);

        if ($distance > $temp || $distance == 0) {
            $distance = $temp;
        }
    }
    $distance = number_format($distance, 2);
    return $distance;
}
function getLengthBetweenCoordinates($lat1, $lon1, $lat2, $lon2)
{
    if (($lat1 == $lat2) && ($lon1 == $lon2)) {
        return 0;
    }

    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $km = $dist * 60 * 1.1515 * 1.609344;

    return sprintf("%01.6f", $km);
}

function getAngle($lat1, $lng1, $lat2, $lng2)
{
    $angle = (rad2deg(atan2(sin(deg2rad($lng2) - deg2rad($lng1)) * cos(deg2rad($lat2)), cos(deg2rad($lat1)) * sin(deg2rad($lat2)) - sin(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lng2) - deg2rad($lng1)))) + 360) % 360;

    return floor($angle);
}

function isPointInPolygon($vertices, $lat, $lng)
{
    $polyX = array();
    $polyY = array();

    $ver_arr = explode(',', $vertices);

    // check for all X and Y
    if (!is_int(count($ver_arr) / 2)) {
        array_pop($ver_arr);
    }

    $polySides = 0;
    $i = 0;

    while ($i < count($ver_arr)) {
        $polyX[] = $ver_arr[$i + 1];
        $polyY[] = $ver_arr[$i];

        $i += 2;
        $polySides++;
    }

    $j = $polySides - 1;
    $oddNodes = 0;

    for ($i = 0; $i < $polySides; $i++) {
        if ($polyY[$i] < $lat && $polyY[$j] >= $lat || $polyY[$j] < $lat && $polyY[$i] >= $lat) {
            if ($polyX[$i] + ($lat - $polyY[$i]) / ($polyY[$j] - $polyY[$i]) * ($polyX[$j] - $polyX[$i]) < $lng) {
                $oddNodes = !$oddNodes;
            }
        }
        $j = $i;
    }

    return $oddNodes;
}
function calculateDistance($lat, $lng, $marker_lat, $marker_lng)
{
    $earthRadius = 6371000; // Radio de la Tierra en metros

    $latFrom = deg2rad($lat);
    $lonFrom = deg2rad($lng);
    $latTo = deg2rad($marker_lat);
    $lonTo = deg2rad($marker_lng);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
        cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

    return $angle * $earthRadius;
}

function isPointOnLine($points, $lat, $lng)
{
    $new_points = array();

    $points = explode(',', $points);

    // check for all X and Y
    if (!is_int(count($points) / 2)) {
        array_pop($points);
    }

    $i = 0;
    while ($i < count($points)) {
        $new_points[] = array($points[$i], $points[$i + 1]);
        $i += 2;
    }

    // find closest point
    $distance = 99999;

    for ($i = 0; $i < count($new_points); $i++) {
        $dist = getLengthBetweenCoordinates($new_points[$i][0], $new_points[$i][1], $lat, $lng);

        if ($distance > $dist) {
            $distance = $dist;
        }
    }

    return $distance;
}

// #################################################
// END MATH FUNCTIONS
// #################################################

// #################################################
// STRING/ARRAY/VALIDATION FUNCTIONS
// #################################################

function isEmailValid($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isFilePathValid($path)
{
    $path = caseToLower($path);

    if ((strpos($path, '..') !== false) || (strpos($path, 'php') !== false)) {
        return false;
    } else {
        return true;
    }
}

function isDateValid($date)
{
    if (empty($date) or $date === '0000-00-00' or $date === '0000-00-00 00:00:00') {
        return false;
    } else {
        return true;
    }
}

function stringToBool($str)
{
    return filter_var($str, FILTER_VALIDATE_BOOLEAN);
}

function searchString($str, $findme)
{
    return preg_match('/' . $findme . '/', $str);
}

function truncateString($text, $chars)
{
    if (strlen($text) > $chars) {
        $text = substr($text, 0, $chars) . '...';
    }
    return $text;
}

function caseToLower($str)
{
    return mb_strtolower($str, 'UTF-8');
}

function caseToUpper($str)
{
    return mb_strtoupper($str, 'UTF-8');
}

function caseFirstToUpper($str)
{
    $fc = mb_strtoupper(mb_substr($str, 0, 1), 'UTF-8');
    return $fc . mb_substr($str, 1);
}

// #################################################
// END STRING/ARRAY/VALIDATION FUNCTIONS
// #################################################

// #################################################
// TEMPLATE FUNCTIONS
// #################################################

function getDefaultTemplate($name, $language)
{
    global $ms;

    $result = false;

    $q = "SELECT * FROM `gs_templates` WHERE `name`='" . $name . "' AND `language`='" . $language . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    if (!$row) {
        $q = "SELECT * FROM `gs_templates` WHERE `name`='" . $name . "' AND `language`='english'";
        $r = mysqli_query($ms, $q);
        $row = mysqli_fetch_array($r);
    }

    if ($row) {
        $result = array('subject' => $row['subject'], 'message' => $row['message']);
    }

    return $result;
}

// #################################################
// END TEMPLATE FUNCTIONS
// #################################################

// #################################################
// GEOCODER FUNCTIONS
// #################################################

function getGeocoderCache($lat, $lng)
{
    global $ms;
    global $gsValues;

    $result = '';

    // set lat and lng search ranges
    $lat_a = $lat - 0.000050;
    $lat_b = $lat + 0.000050;

    $lng_a = $lng - 0.000050;
    $lng_b = $lng + 0.000050;

    $q = "SELECT * FROM gs_geocoder_cache WHERE (lat BETWEEN " . $lat_a . " AND " . $lat_b . ") AND (lng BETWEEN " . $lng_a . " AND " . $lng_b . ")";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    if ($row) {
        return $row['address'];
    } else {

        usleep(50000);

        $url = $gsValues['URL_ROOT'] . '/tools/gc/' . $gsValues['GEOCODER_SERVICE'] . '.php';
        $url .= '?cmd=latlng&lat=' . $lat . '&lng=' . $lng;

        $opts = array('http' => array('method' => 'GET'), 'ssl' => array('verify_peer' => false));
        $context = stream_context_create($opts);
        $result = @file_get_contents($url, false, $context);
        $result = json_decode($result);

        if ($gsValues['GEOCODER_CACHE'] == 'true') {
            insertGeocoderCache($lat, $lng, $result);
        }
    }

    return $result;
}

function insertGeocoderCache($lat, $lng, $address)
{
    global $ms;

    if (($lat == '') || ($lng == '') || ($address == '')) {
        return;
    }

    $q = "INSERT INTO `gs_geocoder_cache`(	`lat`,
							`lng`,
							`address`)
							VALUES
							('" . $lat . "',
							'" . $lng . "',
							'" . mysqli_real_escape_string($ms, $address) . "')";
    $r = mysqli_query($ms, $q);
}

// #################################################
// END GEOCODER FUNCTIONS
// #################################################

// #################################################
// THEME FUNCTIONS
// #################################################

function getThemeDefault()
{
    $theme = array(
        'login_dialog_logo' => 'yes',
        'login_dialog_logo_position' => 'left',
        'login_bg_color' => '#FFFFFF',
        'login_dialog_bg_color' => '#FFFFFF',
        'login_dialog_opacity' => 90,
        'login_dialog_h_position' => 'center',
        'login_dialog_v_position' => 'center',
        'login_dialog_bottom_text' => '',
        'ui_top_panel_color' => '#FFFFFF',
        'ui_top_panel_border_color' => '#F5F5F5',
        'ui_top_panel_selection_color' => '#F5F5F5',
        'ui_dialog_titlebar_color' => '#2B82D4',
        'ui_accent_color_1' => '#2B82D4',
        'ui_accent_color_2' => '#FAB444',
        'ui_accent_color_3' => '#9CC602',
        'ui_accent_color_4' => '#808080',
        'ui_font_color' => '#444444',
        'ui_top_panel_font_color' => '#808080',
        'ui_top_panel_counters_font_color' => '#808080',
        'ui_heading_font_color_1' => '#2B82D4',
        'ui_heading_font_color_2' => '#808080'
    );

    return $theme;
}

function getTheme()
{
    global $ms;

    $theme = false;

    $q = "SELECT * FROM `gs_themes` WHERE `active`='true'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    if ($row) {
        if (($row['theme'] != '') && (json_decode($row['theme'], true) != null)) {
            $theme = json_decode($row['theme'], true);
        }
    }

    return $theme;
}

// #################################################
// END THEME FUNCTIONS
// #################################################

// #################################################
// LANGUAGE FUNCTIONS
// #################################################

function loadLanguage($lng, $units = '')
{
    global $ms, $la, $gsValues;

    if (!isFilePathValid($lng)) {
        die;
    }

    // always load main english language to prevet error if something is not translated in another language
    include($gsValues['PATH_ROOT'] . 'lng/english/lng_main.php');

    // load another language
    if ($lng != 'english') {
        $lng = $gsValues['PATH_ROOT'] . 'lng/' . $lng . '/lng_main.php';

        if (file_exists($lng)) {
            include($lng);
        }
    }

    // set unit strings
    $units = getUnits($units);

    if ($units["unit_distance"] == 'km') {
        $la["UNIT_SPEED"] = $la['UNIT_KPH'];
        $la["UNIT_DISTANCE"] = $la['UNIT_KM'];
        $la["UNIT_HEIGHT"] = $la['UNIT_M'];
    } else if ($units["unit_distance"] == 'mi') {
        $la["UNIT_SPEED"] = $la['UNIT_MPH'];
        $la["UNIT_DISTANCE"] = $la['UNIT_MI'];
        $la["UNIT_HEIGHT"] = $la['UNIT_FT'];
    } else if ($units["unit_distance"] == 'nm') {
        $la["UNIT_SPEED"] = $la['UNIT_KN'];
        $la["UNIT_DISTANCE"] = $la['UNIT_NM'];
        $la["UNIT_HEIGHT"] = $la['UNIT_FT'];
    }

    if ($units["unit_capacity"] == 'l') {
        $la["UNIT_CAPACITY"] = $la['UNIT_LITERS'];
    } else {
        $la["UNIT_CAPACITY"] = $la['UNIT_GALLONS'];
    }

    if ($units["unit_temperature"] == 'c') {
        $la["UNIT_TEMPERATURE"] = 'C';
    } else {
        $la["UNIT_TEMPERATURE"] = 'F';
    }
}

function getLanguageList()
{
    global $ms, $gsValues;

    $result = '';
    $languages = array();

    $q = "SELECT * FROM `gs_system` WHERE `key`='LANGUAGES'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    $languages = explode(",", $row['value']);

    array_unshift($languages, 'english');

    foreach ($languages as $value) {
        if ($value != '') {
            $result .= '<option value="' . $value . '">' . ucfirst($value) . '</option>';
        }
    }

    return $result;
}

// #################################################
// END LANGUAGE FUNCTIONS
// #################################################

// #################################################
// FILE FUNCTIONS
// #################################################

function getFileList($path)
{
    global $gsValues;

    if (!isFilePathValid($path)) {
        die;
    }

    $filter = false;

    if ($path == 'data/user/places') {
        $filter = $_SESSION['user_id'] . '_';
    }

    if ($path == 'data/user/objects') {
        $filter = $_SESSION['user_id'] . '_';
    }

    $dh = opendir($gsValues['PATH_ROOT'] . $path);

    $result = array();

    while (($file = readdir($dh)) !== false) {
        if ($file != '.' && $file != '..' && $file != 'Thumbs.db') {
            if ($filter != false) {
                if (0 === strpos($file, $filter)) {
                    $result[] = $file;
                }
            } else {
                $result[] = $file;
            }
        }
    }

    closedir($dh);

    sort($result);

    return $result;
}

// #################################################
// END FILE FUNCTIONS
// #################################################

// #################################################
// USAGE FUNCTIONS
// #################################################

function checkUserUsage($user_id, $service)
{
    global $gsValues, $ms;

    $result = false;

    if ($user_id == false) {
        die;
    }

    // get gs_users counters
    $q = "SELECT * FROM `gs_users` WHERE `id`='" . $user_id . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    $email = $row['usage_email_daily'];
    $sms = $row['usage_sms_daily'];
    $webhook = $row['usage_webhook_daily'];
    $api = $row['usage_api_daily'];

    $email_cnt = $row['usage_email_daily_cnt'];
    $sms_cnt = $row['usage_sms_daily_cnt'];
    $webhook_cnt = $row['usage_webhook_daily_cnt'];
    $api_cnt = $row['usage_api_daily_cnt'];

    if ($service == 'email') {
        if ($email != '') {
            if ($email_cnt < $email) {
                $result = true;
            }
        } else {
            if ($email_cnt < $gsValues['USAGE_EMAIL_DAILY']) {
                $result = true;
            }
        }
    }

    if ($service == 'sms') {
        if ($sms != '') {
            if ($sms_cnt < $sms) {
                $result = true;
            }
        } else {
            if ($sms_cnt < $gsValues['USAGE_SMS_DAILY']) {
                $result = true;
            }
        }
    }

    if ($service == 'webhook') {
        if ($webhook != '') {
            if ($webhook_cnt < $webhook) {
                $result = true;
            }
        } else {
            if ($webhook_cnt < $gsValues['USAGE_WEBHOOK_DAILY']) {
                $result = true;
            }
        }
    }

    if ($service == 'api') {
        if ($api != '') {
            if ($api_cnt < $api) {
                $result = true;
            }
        } else {
            if ($api_cnt < $gsValues['USAGE_API_DAILY']) {
                $result = true;
            }
        }
    }

    return $result;
}

function updateUserUsage($user_id, $login, $email, $sms, $webhook, $api)
{
    global $ms;

    if ($user_id == false) {
        die;
    }

    $date = gmdate("Y-m-d");

    if ($login == false) {
        $login = 0;
    }
    if ($email == false) {
        $email = 0;
    }
    if ($sms == false) {
        $sms = 0;
    }
    if ($webhook == false) {
        $webhook = 0;
    }
    if ($api == false) {
        $api = 0;
    }

    // update gs_users counters
    $q = "UPDATE gs_users SET 	usage_email_daily_cnt=usage_email_daily_cnt+" . $email . ",
									usage_sms_daily_cnt=usage_sms_daily_cnt+" . $sms . ",
									usage_webhook_daily_cnt=usage_webhook_daily_cnt+" . $webhook . ",
									usage_api_daily_cnt=usage_api_daily_cnt+" . $api . "
									WHERE id='" . $user_id . "'";
    $r = mysqli_query($ms, $q);

    // get gs_users counters
    $q = "SELECT * FROM `gs_users` WHERE `id`='" . $user_id . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    $email = $row['usage_email_daily_cnt'];
    $sms = $row['usage_sms_daily_cnt'];
    $webhook = $row['usage_webhook_daily_cnt'];
    $api = $row['usage_api_daily_cnt'];

    // add/update user usage table
    $q = "SELECT * FROM `gs_user_usage` WHERE `user_id`='" . $user_id . "' AND `dt_usage`='" . $date . "'";
    $r = mysqli_query($ms, $q);

    $row = mysqli_fetch_array($r);

    if ($row) {
        $q = "UPDATE gs_user_usage SET 	login=login+" . $login . ",
											email=" . $email . ",
											sms=" . $sms . ",
											webhook=" . $webhook . ",
											api=" . $api . "
											WHERE usage_id='" . $row['usage_id'] . "'";
        $r = mysqli_query($ms, $q);
    }
}

// #################################################
// END USAGE FUNCTIONS
// #################################################

// #################################################
// LOG FUNCTIONS
// #################################################

function writeLog($log, $log_data)
{
    global $ms, $gsValues;

    $file = gmdate("Y_m") . '_' . $log . '.log';
    $path = $gsValues['PATH_ROOT'] . 'logs/' . $file;

    $str = '[' . gmdate("Y-m-d H:i:s") . '] ' . $_SERVER['REMOTE_ADDR'] . ' ';

    if (isset($_SESSION["user_id"]) && isset($_SESSION["username"])) {
        $str .= '[' . $_SESSION["user_id"] . ']' . $_SESSION["username"] . ' ';
    }

    $str .= '- ' . $log_data . "\r\n";

    file_put_contents($path, $str, FILE_APPEND);
}

function addRowBinnacle($id_user, $event, $event_description = '')
{
    global $ms;
    $description = mysqli_real_escape_string($ms, $event_description);
    $q = "INSERT INTO gs_binnacle (id_user, `event`, event_date, event_description) values ('${id_user}', '${event}', now(), '${description}')";
    mysqli_query($ms, $q);
}

function alerta_mensaje()
{
    global $ms;
    $resultado = array(
        'id' => '',
        'titulo' => '',
        'contenido' => '',
        'status' => 0
    );
    $SQL = "SELECT * FROM mantenimiento where msg_status = 1";
    $RESULTADO = mysqli_query($ms, $SQL);
    $row_2 = mysqli_fetch_array($RESULTADO);
    if ($row_2) {
        $resultado = array(
            'id' => $row_2['id'],
            'titulo' => $row_2['msg_titulo'],
            'contenido' => $row_2['msg_contenido'],
            'status' => $row_2['msg_status']
        );
    }

    return $resultado;
}

// #################################################
// END LOG FUNCTIONS
// #################################################

function evaluateEquation($equation, $variables)
{
    // Sanitizar y validar la ecuación y las variables
    foreach ($variables as $key => $value) {
        if (!is_numeric($value)) {
            throw new Exception("Invalid variable value");
        }
        $equation = str_replace($key, $value, $equation);
    }

    // Evaluar la ecuación
    $result = null;
    eval ('$result = ' . $equation . ';');
    return $result ? round($result, 2): null;
}