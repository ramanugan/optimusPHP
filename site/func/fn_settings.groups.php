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

if (@$_POST['cmd'] == 'delete_object_group') {
	$group_id = $_POST["group_id"];

	$q = "SELECT * FROM `gs_user_objects` WHERE `user_id`='$user_id' AND `group_id`='" . $group_id . "'";
	$r = mysqli_query($ms, $q);

	$duplicate_imei = array();

	$all_imeis = array();

	while ($row = mysqli_fetch_array($r)) {
		$group = $row["group_id"];
		$imei = $row["imei"];
		$all_imeis[] = $imei;

		$q_ = "DELETE FROM `gs_user_objects` WHERE `user_id`='$user_id' AND `imei`='$imei' AND `group_id`='0'";
		mysqli_query($ms, $q_);

		$q3 = "SELECT * FROM `gs_object_custom_fields` WHERE `imei`='" . $imei . "' AND `group_id`='$group'";
		$r3 = mysqli_query($ms, $q3);
		if (mysqli_num_rows($r3) > 0) {
			$q4 = "DELETE FROM `gs_object_custom_fields` WHERE `imei`='" . $imei . "' AND `group_id`='$group'";
			$r4 = mysqli_query($ms, $q4);
		}
	}
	if (empty($all_imeis)) {
		$q1 = "DELETE FROM `gs_user_object_groups` WHERE `group_id`='$item' AND `user_id`='$user_id'";
		$r1 = mysqli_query($ms, $q1);
	}

	foreach ($all_imeis as $imei) {
		$q_ = "SELECT * FROM `gs_user_object_groups` WHERE `group_id`='$group_id' AND `user_id`='$user_id'";
		$r_ = mysqli_query($ms, $q_);

		if (mysqli_num_rows($r_) > 0) {
			$q1 = "DELETE FROM `gs_user_object_groups` WHERE `group_id`='$group_id' AND `user_id`='$user_id'";
			$r1 = mysqli_query($ms, $q1);
		}

		if ($group != '0') {
			// reset group_id in objects
			$q = "UPDATE `gs_user_objects` SET `group_id`='0' WHERE `group_id`='$group_id' AND `imei`='$imei'";
			$r = mysqli_query($ms, $q);
		}
	}

	echo 'OK';
	die;
}


if (@$_POST['cmd'] == 'delete_selected_object_groups') {
	$items = $_POST["items"];

	for ($i = 0; $i < count($items); ++$i) {
		$item = $items[$i];

		$q1 = "SELECT * FROM `gs_user_objects` WHERE `group_id`='" . $item . "'";
		$r1 = mysqli_query($ms, $q1);
		$all_imeis = array();

		while ($row = mysqli_fetch_array($r1)) {
			$group = $row["group_id"];
			$imei = $row["imei"];
			$all_imeis[] = $imei;

			$q_ = "DELETE FROM `gs_user_objects` WHERE `user_id`='$user_id' AND `imei`='$imei' AND `group_id`='0'";
			mysqli_query($ms, $q_);

			$q3 = "SELECT * FROM `gs_object_custom_fields` WHERE `imei`='" . $imei . "' AND `group_id`='$group'";
			$r3 = mysqli_query($ms, $q3);
			if (mysqli_num_rows($r3) > 0) {
				$q4 = "DELETE FROM `gs_object_custom_fields` WHERE `imei`='" . $imei . "' AND `group_id`='$group'";
				$r4 = mysqli_query($ms, $q4);
			}
		}
		if (empty($all_imeis)) {
			$q1 = "DELETE FROM `gs_user_object_groups` WHERE `group_id`='$item' AND `user_id`='$user_id'";
			$r1 = mysqli_query($ms, $q1);
		}

		foreach ($all_imeis as $imei) {
			$q_ = "SELECT * FROM `gs_user_object_groups` WHERE `group_id`='$item' AND `user_id`='$user_id'";
			$r_ = mysqli_query($ms, $q_);

			if (mysqli_num_rows($r_) > 0) {
				$q1 = "DELETE FROM `gs_user_object_groups` WHERE `group_id`='$item' AND `user_id`='$user_id'";
				$r1 = mysqli_query($ms, $q1);
			}

			if ($group != '0') {
				// reset group_id in objects
				$q = "UPDATE `gs_user_objects` SET `group_id`='0' WHERE `group_id`='$item' AND `imei`='$imei'";
				$r = mysqli_query($ms, $q);
			}
		}
	}

	echo 'OK';
	die;
}

if (@$_POST['cmd'] == 'save_object_group') {
    $group_id = $_POST["group_id"];
    $group_name = $_POST["group_name"];
    $group_desc = $_POST["group_desc"];
    $group_imei = strtoupper($_POST['group_imei']);
    $group_name_api = $_POST["group_name_api"];
    $group_api_pass = $_POST["group_api_pass"];

    // Quita duplicados del user_id
    removeDuplicateImeis($ms, $user_id);

    $imeis = array_filter(array_map('trim', explode(",", $group_imei)));

    if ($group_id == 'false') {
        // 1. Crear el grupo
        $q = "INSERT INTO `gs_user_object_groups` 
                (`user_id`, `group_name`, `group_desc`, `ws_name`, `ws_pass`)
              VALUES ('$user_id', '$group_name', '$group_desc', '$group_name_api', '$group_api_pass')";
        mysqli_query($ms, $q);

        // Obtener el nuevo group_id
        $q = "SELECT group_id FROM `gs_user_object_groups` WHERE `user_id`='$user_id' AND `group_name`='$group_name' LIMIT 1";
        $r = mysqli_query($ms, $q);
        $row = mysqli_fetch_array($r);
        $group_id = $row['group_id'];
    } else {
        // 2. Actualizar datos del grupo si existe
        $q = "UPDATE `gs_user_object_groups` SET `ws_name`='$group_name_api', `ws_pass`='$group_api_pass', `group_name`='$group_name', `group_desc`='$group_desc'
              WHERE `user_id`='$user_id' AND `group_id`='$group_id'";
        mysqli_query($ms, $q);
    }

    // 3. Obtener imeis actuales en el grupo
    $current_imeis = [];
    $imeis_data = [];
    $q = "SELECT imei, driver_id, trailer_id, config_fota FROM `gs_user_objects` 
            WHERE `group_id`='$group_id' AND `user_id`='$user_id'";
    $r = mysqli_query($ms, $q);
    while ($row = mysqli_fetch_assoc($r)) {
        $current_imeis[] = $row['imei'];
        $imeis_data[$row['imei']] = $row; // guarda datos asociados
    }

    // 4. Identifica los que hay que quitar y los que hay que agregar
    $imeis_to_remove = array_diff($current_imeis, $imeis); // en el grupo pero NO en la selección actual
    $imeis_to_add = array_diff($imeis, $current_imeis); // en la selección pero NO en el grupo actual

    // 5. Eliminar solo los IMEIs que salen del grupo (no borres el registro, solo actualiza su group_id)
    foreach ($imeis_to_remove as $imei) {
        mysqli_query($ms, "UPDATE `gs_user_objects` SET `group_id`='0' 
                           WHERE `user_id`='$user_id' AND `imei`='$imei' AND `group_id`='$group_id'");
        // Opcional: limpiar custom_fields si aplica
        mysqli_query($ms, "DELETE FROM `gs_object_custom_fields` WHERE `group_id`='$group_id' AND `imei`='$imei'");
    }

    // 6. Agregar o actualizar solo los nuevos
    // Si el IMEI existe, solo actualiza el group_id, si no, lo inserta con los valores mínimos
    foreach ($imeis_to_add as $imei) {
        // ¿Ya existe ese imei para este user?
        $q = "SELECT * FROM `gs_user_objects` WHERE `imei`='$imei' AND `user_id`='$user_id' LIMIT 1";
        $r = mysqli_query($ms, $q);
        if ($row = mysqli_fetch_assoc($r)) {
            // Ya existe, solo cambia group_id
            mysqli_query($ms, "UPDATE `gs_user_objects` SET `group_id`='$group_id' WHERE `user_id`='$user_id' AND `imei`='$imei'");
        } else {
            // No existe, inserta nuevo (puedes personalizar campos)
            mysqli_query($ms, "INSERT INTO `gs_user_objects` 
                (`user_id`, `imei`, `group_id`, `driver_id`, `trailer_id`, `client_id`) 
                VALUES ('$user_id', '$imei', '$group_id', '0', '0', '0')");
        }
    }

    // 7. Actualizar custom_fields (solo para los que están activos en el grupo)
    foreach ($imeis as $imei) {
        // Borra viejo custom field
        mysqli_query($ms, "DELETE FROM `gs_object_custom_fields` WHERE `group_id`='$group_id' AND `imei`='$imei'");
        // Inserta si corresponde
        if ($group_name_api != '' && $group_api_pass != '') {
            mysqli_query($ms, "INSERT INTO `gs_object_custom_fields` 
                (`group_id`, `imei`, `name`, `value`, `data_list`, `popup`) 
                VALUES ('$group_id', '$imei', '$group_name_api', '$group_api_pass', 'true', 'true')");
        }
    }

    echo 'OK';
}


function removeDuplicateImeis($ms, $user_id)
{
	$q = "SELECT imei FROM gs_user_objects 
          WHERE user_id = '$user_id' 
          GROUP BY imei 
          HAVING COUNT(*) > 1";
	$r = mysqli_query($ms, $q);

	while ($row = mysqli_fetch_assoc($r)) {
		$imei = $row['imei'];

		$q_sub = "SELECT * FROM gs_user_objects 
                  WHERE user_id = '$user_id' AND imei = '$imei'";
		$r_sub = mysqli_query($ms, $q_sub);

		$to_keep = null;
		$to_delete = [];

		while ($obj = mysqli_fetch_assoc($r_sub)) {
			if ($obj['group_id'] != 0) {

				$to_keep = $obj;
			} else {
				$to_delete[] = $obj;
			}
		}

		if (!$to_keep && count($to_delete) > 0) {
			$to_keep = array_shift($to_delete);
		}

		foreach ($to_delete as $del_obj) {
			if (!empty($del_obj['driver_id']) && empty($to_keep['driver_id'])) {
				$new_driver_id = $del_obj['driver_id'];
				$new_trailer_id = $del_obj["trailer_id"];
				$new_config_fota = $del_obj["config_fota"];
				mysqli_query($ms, "UPDATE gs_user_objects 
                                   SET driver_id = '$new_driver_id' , trailer_id = '$new_trailer_id' , config_fota = '$new_config_fota'
                                   WHERE user_id = '{$to_keep['user_id']}' AND imei = '{$to_keep['imei']}'");
			}

			if ($del_obj['group_id'] == 0) {
				mysqli_query($ms, "DELETE FROM gs_user_objects WHERE user_id = '{$del_obj['user_id']}' AND group_id = '{$del_obj['group_id']}' AND imei = '{$del_obj['imei']}'");
			}
		}
	}
}

if (@$_GET['cmd'] == 'load_object_group_list') {
	$page = $_GET['page']; // get the requested page
	$limit = $_GET['rows']; // get how many rows we want to have into the grid
	$sidx = $_GET['sidx']; // get index row - i.e. user click to sort
	$sord = $_GET['sord']; // get the direction
	$search = caseToUpper(@$_GET['s']); // get search

	if (!$sidx) $sidx = 1;

	$q = "SELECT * FROM `gs_user_object_groups` WHERE `user_id`='" . $user_id . "'";

	if ($search != '') {
		$q .= " AND (UPPER(`group_name`) LIKE '%$search%')";
	}

	$r = mysqli_query($ms, $q);
	$count = mysqli_num_rows($r);

	if ($count > 0) {
		$total_pages = ceil($count / $limit);
	} else {
		$total_pages = 1;
	}

	if ($page > $total_pages) $page = $total_pages;
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
			$group_id = $row['group_id'];
			$group_name = str_replace(array('"', "'"), '', $row['group_name']);
			$group_desc = $row['group_desc'];

			$q2 = "SELECT DISTINCT imei FROM `gs_user_objects` WHERE `group_id`='" . $group_id . "' AND `user_id`='" . $user_id . "'";
			$r2 = mysqli_query($ms, $q2);
			$object_number = mysqli_num_rows($r2);

			// set modify buttons
			$modify = '<a href="#" onclick="settingsObjectGroupProperties(\'' . $group_id . '\');" title="' . $la['EDIT'] . '"><img src="theme/images/edit.svg" />';
			$modify .= '</a><a href="#" onclick="settingsObjectGroupDelete(\'' . $group_id . '\');" title="' . $la['DELETE'] . '"><img src="theme/images/remove3.svg" /></a>';

			// set row
			$response->rows[$i]['id'] = $group_id;
			$response->rows[$i]['cell'] = array($group_name, $object_number, $group_desc, $modify);
			$i++;
		}
	}

	header('Content-type: application/json');
	echo json_encode($response);
	die;
}

if (@$_POST['cmd'] == 'load_object_group_data') {
	$q = "SELECT * FROM `gs_user_object_groups` WHERE `user_id`='" . $user_id . "' ORDER BY `group_name` ASC";
	$r = mysqli_query($ms, $q);

	$result = array();

	// add ungrouped group
	$result[] = array(
		'name' => $la['UNGROUPED'],
		'desc' => '',
		'visible' => true,
		'follow' => false
	);

	while ($row = mysqli_fetch_array($r)) {
		$group_id = $row['group_id'];

		$group_name = str_replace(array('"', "'"), '', $row['group_name']);

		$result[$group_id] = array(
			'name' => $group_name,
			'desc' => $row['group_desc'],
			'ws_name' => $row['ws_name'],
			'ws_pass' => $row['ws_pass'],
			'visible' => true,
			'follow' => false
		);
	}
	echo json_encode($result);
	die;
}
