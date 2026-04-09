<?
	session_start();
	include ('../init.php');
	include ('fn_common.php');
	checkUserSession();
	
	include ('../tools/email.php');
	
	loadLanguage($_SESSION["language"], $_SESSION["units"]);
	
	if(@$_POST['cmd'] == 'load_subaccount_data')
	{

		$user_id = $_SESSION["user_id"];
		if ($_SESSION["manager_id"] == '0') {
			$q = "SELECT * FROM `gs_users` WHERE `privileges` LIKE '%subuser%' AND `manager_id`='" . $user_id . "' ORDER BY `email` ASC";
		} else {
			$q = "SELECT * FROM `gs_users` WHERE `id`='" . $user_id . "'";
		}
		$r = mysqli_query($ms, $q);
		
		$result = array();
		
		while($row=mysqli_fetch_array($r))
		{
			$privileges = json_decode($row['privileges'],true);
			
			$privileges = checkUserPrivilegesArray($privileges);
			
			$imei = $privileges['imei'];
			$marker = $privileges['marker'];
			$route = $privileges['route'];
			$zone = $privileges['zone'];
			$dashboard = $privileges['dashboard'];
			$history = $privileges['history'];
			$reports = $privileges['reports'];
			$tachograph = $privileges['tachograph'];
			$tasks = $privileges['tasks'];
			$rilogbook = $privileges['rilogbook'];
			$dtc = $privileges['dtc'];
			$maintenance = $privileges['maintenance'];
			$expenses = $privileges['expenses'];
			$object_control = $privileges['object_control'];
			$image_gallery = $privileges['image_gallery'];
			$chat = $privileges['chat'];
			$events = $privileges['events'];
			$shared_zones = isset($privileges['shared_zones']) ? $privileges['shared_zones']: false;
			
			if (!isset($privileges['au_active'])) { $privileges['au_active'] = false; }
			$au_active = $privileges['au_active'];
			
			if (!isset($privileges['au'])) { $privileges['au'] = ''; }
			$au = $privileges['au'];
			
			$subaccount_id = $row['id'];
			$result[$subaccount_id] = array('active' => $row['active'],
											'username' => $row['username'],
											'api' => $row['api'],
											'api_key' => $row['api_key'],
											'email' => $row['email'],							
											'account_expire' => $row['account_expire'],
											'account_expire_dt' => $row['account_expire_dt'],
											'dashboard' => $dashboard,
											'history' => $history,
											'reports' => $reports,
											'tachograph' => $tachograph,
											'tasks' => $tasks,
											'rilogbook' => $rilogbook,
											'dtc' => $dtc,
											'maintenance' => $maintenance,
											'expenses' => $expenses,
											'object_control' => $object_control,
											'image_gallery' => $image_gallery,
											'chat' => $chat,
											'events' => $events,
											'shared_zones' => $shared_zones,
											'imei' => $imei,
											'marker' => $marker,
											'route' => $route,
											'zone' => $zone,							
											'au_active' => $au_active,
											'au' => $au
											);
		}
		echo json_encode($result);
		die;
	}
	
	if(@$_POST['cmd'] == 'delete_subaccount')
	{
		$subaccount_id= $_POST["subaccount_id"];
		$manager_id = $_SESSION["user_id"];
		
		$q = "DELETE FROM `gs_users` WHERE `id`='".$subaccount_id."' AND `manager_id`='".$manager_id."'";
		$r = mysqli_query($ms, $q);
		addRowBinnacle($_SESSION["user_id"], 'Se elimina subcuenta:'. $subaccount_id, $q);
		
		echo 'OK';
		die;
	}
	
	if(@$_POST['cmd'] == 'delete_selected_subaccounts')
	{
		$items = $_POST["items"];
		$manager_id = $_SESSION["user_id"];
				
		for ($i = 0; $i < count($items); ++$i)
		{
			$item = $items[$i];
			
			$q = "DELETE FROM `gs_users` WHERE `id`='".$item."' AND `manager_id`='".$manager_id."'";
			$r = mysqli_query($ms, $q);
			addRowBinnacle($_SESSION["user_id"], 'Se eliminan subcuenta:'. $item, $q);
		}
		
		echo 'OK';
		die;
	}
	
	if(@$_POST['cmd'] == 'save_subaccount')
	{
		$result = '';
		
		$api = $_POST["api"];
		$api_key = $_POST["api_key"];
		$subaccount_id = $_POST["subaccount_id"];
		$active = $_POST["active"];
		$username = strtolower($_POST["username"]);
		$email = strtolower($_POST["email"]);
		$password = $_POST["password"];
		$send = $_POST["send"];
		$account_expire = $_POST["account_expire"];
		$account_expire_dt = $_POST["account_expire_dt"];
		$manager_id = $_SESSION["user_id"];
			
		$privileges = array();
        $privileges['type'] = 'subuser';
		$privileges['dashboard'] = stringToBool($_POST["dashboard"]);
		$privileges['history'] = stringToBool($_POST["history"]);
		$privileges['reports'] = stringToBool($_POST["reports"]);
		$privileges['tachograph'] = stringToBool($_POST["tachograph"]);
		$privileges['tasks'] = stringToBool($_POST["tasks"]);
		$privileges['rilogbook'] = stringToBool($_POST["rilogbook"]);
		$privileges['dtc'] = stringToBool($_POST["dtc"]);
		$privileges['maintenance'] = stringToBool($_POST['maintenance']);
		$privileges['expenses'] = stringToBool($_POST['expenses']);
		$privileges['object_control'] = stringToBool($_POST["object_control"]);
		$privileges['image_gallery'] = stringToBool($_POST["image_gallery"]);
		$privileges['chat'] = stringToBool($_POST["chat"]);
		$privileges['events'] = stringToBool($_POST["events"]);
		$privileges['imei'] = $_POST["imei"];
		$privileges['marker'] = $_POST["marker"];
		$privileges['route'] = $_POST["route"];
		$privileges['zone'] = $_POST["zone"];
		$privileges['au_active'] = stringToBool($_POST["au_active"]);
		$privileges['au'] = $_POST["au"];
		$privileges['shared_zones'] = stringToBool($_POST["shared_zone"]);

		// $privileges['perm_eventos'] = stringToBool($gsValues['PERM_EVENTOS']);
		// $privileges['perm_alertas'] = stringToBool($gsValues['PERM_ALERTAS']);
		// $privileges['perm_geocercas'] = stringToBool($gsValues['PERM_GEOCERCAS']);
		// $privileges['perm_historial'] = stringToBool($gsValues['PERM_HISTORIAL']);
		
		// $privileges['perm_buscar_gps'] = stringToBool($gsValues['PERM_BUSCAR_GPS']);
		// $privileges['perm_refrescar'] = stringToBool($gsValues['PERM_REFRESCAR']);
		// $privileges['perm_compartir_unidad'] = stringToBool($gsValues['PERM_COMPARTIR_UNIDAD']);
		// $privileges['perm_agregar_unidad'] = stringToBool($gsValues['PERM_AGREGAR_UNIDAD']);
		// $privileges['perm_mostrar_ocultar'] = stringToBool($gsValues['PERM_MOSTRAR_OCULTAR']);
		// $privileges['perm_seguimiento'] = stringToBool($gsValues['PERM_SEGUIMIENTO']);
		
		// $privileges['perm_historial_edit'] = stringToBool($gsValues['PERM_HISTORIAL_EDIT']);
		// $privileges['perm_vista_calle'] = stringToBool($gsValues['PERM_VISTA_CALLE']);
		// $privileges['perm_enviar_comando'] = stringToBool($gsValues['PERM_ENVIAR_COMANDO']);
		// $privileges['perm_editar'] = stringToBool($gsValues['PERM_EDITAR']);
		// $privileges['perm_ver_en_vivo'] = stringToBool($gsValues['PERM_VER_EN_VIVO']);
		// $privileges['perm_ver_eventos_camara'] = stringToBool($gsValues['PERM_VER_EVENTOS_CAMARA']);
		// $privileges['perm_lista_datos'] = stringToBool($gsValues['PERM_LISTA_DATOS']);
		// $privileges['perm_lista_marcadores'] = stringToBool($gsValues['PERM_LISTA_MARCADORES']);
		// $privileges['perm_lista_zonas'] = stringToBool($gsValues['PERM_LISTA_ZONAS']);
		// $privileges['perm_lista_rutas'] = stringToBool($gsValues['PERM_LISTA_RUTAS']);
		
		// $privileges['perm_acerca_de'] = stringToBool($gsValues['PERM_ACERCA_DE']);
		// $privileges['perm_info'] = stringToBool($gsValues['PERM_INFO']);
		// $privileges['perm_configuracion'] = stringToBool($gsValues['PERM_CONFIGURACION']);
		// $privileges['perm_coordenadas'] = stringToBool($gsValues['PERM_COORDENADAS']);
		// $privileges['perm_buscar'] = stringToBool($gsValues['PERM_BUSCAR']);
		// $privileges['perm_reportes'] = stringToBool($gsValues['PERM_REPORTES']);
		// $privileges['perm_tareas'] = stringToBool($gsValues['PERM_TAREAS']);
		// $privileges['perm_mantenimientos'] = stringToBool($gsValues['PERM_MANTENIMIENTOS']);
		// $privileges['perm_comandos'] = stringToBool($gsValues['PERM_COMANDOS']);
		// $privileges['perm_grafica_combustible'] = stringToBool($gsValues['PERM_GRAFICA_COMBUSTIBLE']);
		// $privileges['perm_grafica_temperatura'] = stringToBool($gsValues['PERM_GRAFICA_TEMPERATURA']);
		// $privileges['perm_imagenes'] = stringToBool($gsValues['PERM_IMAGENES']);
		// $privileges['perm_chat'] = stringToBool($gsValues['PERM_CHAT']);
		// $privileges['perm_lenguaje'] = stringToBool($gsValues['PERM_LENGUAJE']);
		// $privileges['perm_panel_de_control'] = stringToBool($gsValues['PERM_PANEL_DE_CONTROL']);
		// $privileges['perm_mi_cuenta'] = stringToBool($gsValues['PERM_MI_CUENTA']);
		// $privileges['perm_version_celular'] = stringToBool($gsValues['PERM_VERSION_CELULAR']);
		
		// $privileges['perm_gps'] = stringToBool($gsValues['PERM_GPS']);
		// $privileges['perm_plantillas'] = stringToBool($gsValues['PERM_PLANTILLAS']);
		// $privileges['perm_kml'] = stringToBool($gsValues['PERM_KML']);
		// $privileges['perm_gprs'] = stringToBool($gsValues['PERM_GPRS']);
		// $privileges['perm_sms'] = stringToBool($gsValues['PERM_SMS']);
		// $privileges['perm_crear_eventos'] = stringToBool($gsValues['PERM_CREAR_EVENTOS']);
		// $privileges['perm_interfaz_usuario'] = stringToBool($gsValues['PERM_INTERFAZ_USUARIO']);
		// $privileges['perm_subcuentas'] = stringToBool($gsValues['PERM_SUBCUENTAS']);
		
		// $privileges['perm_grupos'] = stringToBool($gsValues['PERM_GRUPOS']);
		// $privileges['perm_conductor'] = stringToBool($gsValues['PERM_CONDUCTOR']);
		// $privileges['perm_pasajero'] = stringToBool($gsValues['PERM_PASAJERO']);
		// $privileges['perm_trailer'] = stringToBool($gsValues['PERM_TRAILER']);
		// $privileges['perm_logs'] = stringToBool($gsValues['PERM_LOGS']);
		
		// $privileges['perm_crear_reportes'] = stringToBool($gsValues['PERM_CREAR_REPORTES']);
		// $privileges['perm_ver_reportes'] = stringToBool($gsValues['PERM_VER_REPORTES']);
		// $privileges['perm_eliminar_reportes'] = stringToBool($gsValues['PERM_ELIMINAR_REPORTES']);
		// $privileges['perm_editar_reportes'] = stringToBool($gsValues['PERM_EDITAR_REPORTES']);
		
		// $privileges['perm_crear_mantenimiento'] = stringToBool($gsValues['PERM_CREAR_MANTENIMIENTO']);
		// $privileges['perm_eliminar_mantenimientos'] = stringToBool($gsValues['PERM_ELIMINAR_MANTENIMIENTOS']);
		// $privileges['perm_editar_mantenimientos'] = stringToBool($gsValues['PERM_EDITAR_MANTENIMIENTOS']);
		
		// $privileges['perm_crear_comandos_gprs'] = stringToBool($gsValues['PERM_CREAR_COMANDOS_GPRS']);
		// $privileges['perm_crear_comandos_sms'] = stringToBool($gsValues['PERM_CREAR_COMANDOS_SMS']);
		// $privileges['perm_plantillas_comandos'] = stringToBool($gsValues['PERM_PLANTILLAS_COMANDOS']);
		// $privileges['perm_programar_comandos'] = stringToBool($gsValues['PERM_PROGRAMAR_COMANDOS']);
		
		// $privileges['perm_activar_desactivar_tools'] = stringToBool($gsValues['PERM_ACTIVAR_DESACTIVAR_TOOLS']);
		// $privileges['perm_marcadores_mapa'] = stringToBool($gsValues['PERM_MARCADORES_MAPA']);
		// $privileges['perm_rutas_mapa'] = stringToBool($gsValues['PERM_RUTAS_MAPA']);
		// $privileges['perm_geocercas_mapa'] = stringToBool($gsValues['PERM_GEOCERCAS_MAPA']);
		// $privileges['perm_cambiar_mapa'] = stringToBool($gsValues['PERM_CAMBIAR_MAPA']);

		
		$q = "SELECT * FROM `gs_users` WHERE `id`='".$manager_id."'";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_array($r);
		
		$manager_array = json_decode($row['privileges'], true);

		
		if (!is_array($privileges)) {
			$privileges = [];
		}
		
		if (is_array($manager_array)) {
			foreach ($manager_array as $key => $value) {
				if (strpos($key, 'perm') === 0) {
					$privileges[$key] = $value;
				}
			}
		}
		
		$privileges = json_encode($privileges);

		$json_data = array(
			'username' => $username,
			'email' => $email,
		);
	
		$info = json_encode($json_data);
		
		if ($subaccount_id == 'false')
		{						
			$result = addUser($send, $active, $info, $account_expire, $account_expire_dt, $privileges, $manager_id, $username, $email, $password, 'false', 'false', '', 'false', '', 'false', 'false', 'false', $api, $api_key);
		}
		else
		{
			// check if same username and email is not used by another user
			$q = "SELECT * FROM `gs_users` WHERE `username`='".$username."' AND `id`<>'".$subaccount_id."' LIMIT 1";
			$r = mysqli_query($ms, $q);
			$num = mysqli_num_rows($r);
			
			if ($num != 0)
			{
				echo 'ERROR_USERNAME_EXISTS';
				die;
			}
			
			$q = "SELECT * FROM `gs_users` WHERE `email`='".$email."' AND `id`<>'".$subaccount_id."' LIMIT 1";
			$r = mysqli_query($ms, $q);
			$num = mysqli_num_rows($r);
			
			if ($num != 0)
			{
				echo 'ERROR_EMAIL_EXISTS';
				die;
			}
			
			$q = "UPDATE `gs_users` SET 	`active`='".$active."',
											`api`='".$api."',
											`api_key`='".$api_key."',
											`account_expire`='".$account_expire."',
											`account_expire_dt`='".$account_expire_dt."',
											`username`='".$username."',
											`email`='".$email."',
											`privileges`='".$privileges."'
											WHERE `id`='".$subaccount_id."' AND `manager_id`='".$manager_id."'";
			$r = mysqli_query($ms, $q);
			addRowBinnacle($_SESSION["user_id"], 'Edición de subcuenta en :' . $manager_id . " nombre: " . $subaccount_id, $q);
			
			if ($password != '')
			{
				$q = "UPDATE `gs_users` SET `password`='".md5($password)."' WHERE `id`='".$subaccount_id."' AND `manager_id`='".$manager_id."'";
				$r = mysqli_query($ms, $q);
			}
			
			$result = 'OK';
		}
		
		echo $result;
		die;
	}
	
	if(@$_GET['cmd'] == 'load_subaccount_list')
	{
		$manager_id = $_SESSION["user_id"];
		
		$page = $_GET['page']; // get the requested page
		$limit = $_GET['rows']; // get how many rows we want to have into the grid
		$sidx = $_GET['sidx']; // get index row - i.e. user click to sort
		$sord = $_GET['sord']; // get the direction
		$search = caseToUpper(@$_GET['s']); // get search
		
		if(!$sidx) $sidx = 1;
		
		// get records number
		$q = "SELECT * FROM `gs_users` WHERE `privileges` LIKE '%subuser%' AND `manager_id`='".$manager_id."'";
		
		if ($search != '')
		{
			$q .= " AND (UPPER(`username`) LIKE '%$search%' OR UPPER(`email`) LIKE '%$search%')";	
		}
		
		$r = mysqli_query($ms, $q);
		$count = mysqli_num_rows($r);
		
		if ($count > 0)
		{
			$total_pages = ceil($count/$limit);
		}
		else
		{
			$total_pages = 1;
		}
		
		if ($page > $total_pages) $page=$total_pages;
		$start = $limit*$page - $limit; // do not put $limit*($page - 1)
		
		$q .= " ORDER BY $sidx $sord LIMIT $start, $limit";
		$r = mysqli_query($ms, $q);
		
		$response = new stdClass();
		$response->page = $page;
		$response->total = $total_pages;
		$response->records = $count;
		
		if ($r)
		{
			$i=0;
			while($row = mysqli_fetch_array($r))
			{
				$subaccount_id = $row["id"];
				$username = $row['username'];
				$email = $row['email'];
				
				if ($row['active'] == 'true')
				{
					$active = '<img src="theme/images/tick-green.svg" />';
				}
				else
				{
					$active = '<img src="theme/images/remove-red.svg" style="width:12px;" />';
				}
				
				$privileges = json_decode($row['privileges'],true);
				
				$imeis = explode(",", $privileges['imei']);
				if ($imeis[0] != '')
				{
					$imeis = count($imeis);
				}
				else
				{
					$imeis = 0;
				}
				
				$markers = explode(",", $privileges['marker']);
				if ($markers[0] != '')
				{
					$markers = count($markers);
				}
				else
				{
					$markers = 0;
				}
				
				$routes = explode(",", $privileges['route']);
				if ($routes[0] != '')
				{
					$routes = count($routes);
				}
				else
				{
					$routes = 0;
				}
				
				$zones = explode(",", $privileges['zone']);
				if ($zones[0] != '')
				{
					$zones = count($zones);
				}
				else
				{
					$zones = 0;
				}
				
				$places = $markers.'/'.$routes.'/'.$zones;
				
				// set modify buttons
				$modify = '<a href="#" onclick="settingsSubaccountProperties(\''.$subaccount_id.'\');" title="'.$la['EDIT'].'"><img src="theme/images/edit.svg"/></a>';
				$modify .= '<a href="#" onclick="settingsSubaccountDelete(\''.$subaccount_id.'\');" title="'.$la['DELETE'].'"><img src="theme/images/remove3.svg"/></a>';
				// set row
				$response->rows[$i]['id']=$subaccount_id;
				$response->rows[$i]['cell']=array($username,$email,$active,$imeis,$places,$modify);
				$i++;
			}
		}

		header('Content-type: application/json');
		echo json_encode($response);
		die;
	}

	if (@$_POST['cmd'] == 'reset_subaccount_api_key') {

		$api_key = strtoupper(md5(rand() . $gsValues['HW_KEY'] . gmdate("Y-m-d H:i:s") . rand()));
		$result = array('api_key' => $api_key);

		echo json_encode($result);
		die;
	}
        
?>