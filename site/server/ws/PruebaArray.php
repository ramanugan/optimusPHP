<?php
$enlace = mysqli_connect("localhost", "root", "Opt1234*-", "gpsdb");

/* verificar la conexión */
if (mysqli_connect_errno()) {
    printf("Conexión fallida: %s\n", mysqli_connect_error());
    exit();
}

$consulta = "SELECT gs_object_custom_fields.*, gs_objects.name as economico,gs_objects.plate_number FROM gs_object_custom_fields join gs_objects on gs_object_custom_fields.imei = gs_objects.imei where gs_object_custom_fields.imei ='861074021997401'";

if ($resultado = mysqli_query($enlace, $consulta)) {

    /* obtener array asociativo */
    while ($row = mysqli_fetch_assoc($resultado)) {
		$result = json_encode($row);
		$loc_ws= json_decode($result,true);
		// echo $loc_ws;
		
        // printf ("%s (%s)\n", $row["name"], $row["value"]));



		//********************* COMIENZA AREA PARA WS **********************
		
		
		if($loc_ws){

			$ws_name = $loc_ws["name"];
			
			$ws_id = 0;
			
			$ws_credentials = explode(',',$loc_ws["value"]);
			// $imei = $loc['imei'];	
			
			// fclose($filefor);
			switch ($ws_name) {
				case "WS_ASSISTCARGO":
					// $ws_id = 1;
					break;
				case "WALMART":
					// //Array,User,Passw
					// envioWalmart($loc,$ws_credentials[0],$ws_credentials[1],$ws_credentials[2],$ws_credentials[3]);
					echo "Walmart: ".$loc_ws['name'];
				case "ALTOTRACK":
					//Array,economico,placa
					// envioAltoTrack($loc,$loc_ws['economico'],$loc_ws['plate_number']);
					// $wsid = 2;
					break;
				case "SUKARNE":
					// //Array,user,passw,temp,tipo de vehiculo
					// $sensor = getSensorFromType($imei, 'temp');
					// $sensor_data = getSensorValue($loc['params'], $sensor[0]);
					// $sensor_value = $sensor_data['value'];
					// $temp_value = $sensor_value;					
					// envioSukarne($loc,$ws_credentials[0],$ws_credentials[1],$temp_value,$ws_credentials[2]);					
					// $ws_id = 3;
					break;
				case "RCONFIABLE":
					echo "RCONFIABLE: ".$loc_ws['name'];
					// //Array,user,passw,id,name,placa
					// envioRecursoConfiable($loc,$ws_credentials[0],$ws_credentials[1],$ws_credentials[2],$ws_credentials[3],$loc_ws['plate_number']);
					// $ws_id = 4;
					break;
			}		
			
		}
			
			
			
		//***************************************** TERMINA AREA PARA WS *****************************************
		
	    }

    /* liberar el conjunto de resultados */
    mysqli_free_result($resultado);
}

/* cerrar la conexión */
mysqli_close($enlace);
?>