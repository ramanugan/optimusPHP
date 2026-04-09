<?php
session_start();
include('../init.php');
include('../custom/consultas.php');

function outputCsv($assocDataArray)
{

    if (!empty($assocDataArray)) :

        $fp = fopen('php://output', 'w');
        //fputcsv( $fp, array_keys( reset($assocDataArray) ) );

        foreach ($assocDataArray as $values) :
            fputcsv($fp, $values);
        endforeach;

        fclose($fp);
    endif;

    exit();
}

//////////////////////////////////////////////
//        EXPORT PARA USUARIOS             //
////////////////////////////////////////////

if (@$_POST['cmd'] == 'user_export_csv') {
    // La consulta SQL para obtener los datos de la base de datos
    $q = "SELECT * FROM `gs_users` WHERE `manager_id` = 0 AND `id` NOT IN (1, 2, 10, 45, 67, 171, 172, 311, 345, 485, 689, 621, 723, 766, 767, 768, 769, 770, 772, 1140, 1150, 1167, 1171, 1219) ORDER BY id";
    $recorset1 = mysqli_query($ms, $q);

    $registros = array();
    array_push($registros, array('#Cliente', 'Cuenta', 'Docs', 'E-mail', 'Regimen', 'Empresa', 'S. EN C.S.', 'Direccion', 'C.P', 'Ciudad', 'Pais', 'Telefono', 'E-mail', 'Sitio-web', '', 'Contacto', 'Telefono', 'E-mail', 'Comentario', 'Total de unidades', 'Docs Faltantes'));

    while ($row = mysqli_fetch_assoc($recorset1)) {
        $info = json_decode($row['info'], true);

        $username = $row['username'] ?? '';
        $email = $info['email'] ?? '';
        $company = $info['company'] ?? '';
        $business = $info['business'] ?? '';
        $address = $info['address'] ?? '';
        $code = $info['code'] ?? '';
        $city = $info['city'] ?? '';
        $country = $info['country'] ?? '';
        $phone1 = $info['phone1'] ?? '';
        $email1 = $info['email1'] ?? '';
        $web = $info['web'] ?? '';
        $name = $info['name'] ?? '';
        $phone2 = $info['phone2'] ?? '';
        $email2 = $info['email2'] ?? '';
        $comment = $info['comment'] ?? '';

        $person_value = isset($info['person']) ? filter_var($info['person'], FILTER_VALIDATE_BOOLEAN) : null;

        if ($person_value === true) {
            $person_type = 'Moral';
        } elseif ($person_value === false) {
            $person_type = 'Fisica';
        } else {
            $person_type = '';
        }

        $id = $row['id'];

        if (!empty($id)) {
            $sql_client_id = "SELECT client_id FROM gs_user_objects WHERE user_id = '$id'";
            $res_client_id = mysqli_query($ms, $sql_client_id);

            if ($res_client_id && mysqli_num_rows($res_client_id) > 0) {
                $client_id_row = mysqli_fetch_assoc($res_client_id);
                $user_id = $client_id_row['client_id'];

                mysqli_free_result($res_client_id);

                $sql = "SELECT COUNT(DISTINCT imei) AS unique_imei_count FROM gs_user_objects WHERE user_id = '$id'";
                $res = mysqli_query($ms, $sql);

                $unique_imei_count = 0;
                if ($res && mysqli_num_rows($res) > 0) {
                    $count_row = mysqli_fetch_assoc($res);
                    $unique_imei_count = $count_row['unique_imei_count'];

                    mysqli_free_result($res);
                }
            }
        }


        $docs = '';
        $docs_presentes = array();

        $q_docs = "SELECT * FROM gs_user_docs WHERE user_id = '$id'";
        $r_docs = mysqli_query($ms, $q_docs);

        if ($r_docs && mysqli_num_rows($r_docs) > 0) {
            while ($doc_row = mysqli_fetch_assoc($r_docs)) {
                $first_value = true;
                foreach ($doc_row as $key => $value) {
                    if ($key === 'user_id') {
                        continue;
                    }
                    $underscore_pos = strpos($value, '_');

                    if ($underscore_pos !== false) {
                        $value = substr($value, 0, $underscore_pos);
                    }
                    if (!$first_value && $value !== '') {
                        $docs .= ', ';
                    }
                    if ($value !== '') {
                        $docs .= $value;
                        $first_value = false;
                        $docs_presentes[$value] = true;
                    }
                }
            }
        }
        $docs = rtrim($docs, ', ');


        $documentos_esperados = array(
            'MiContrato',
            'ActaConstitutiva',
            'RepLegal',
            'IdentificacionOficial',
            'OpinionPositiva',
            'ConstanciaFiscal',
            'DomicilioFiscal',
            'Otros'
        );
        $docs_faltantes = array_diff($documentos_esperados, array_keys($docs_presentes));
        $docs_faltantes_str = implode(', ', $docs_faltantes);


        $linea = array($user_id, $username, $docs, $email, $person_type, $company, $business, $address, $code, $city, $country, $phone1, $email1, $web, '', $name, $phone2, $email2, $comment, $unique_imei_count, $docs_faltantes_str);
        array_push($registros, $linea);
    }

    mysqli_free_result($recorset1);
    return outputCsv($registros);
}


//////////////////////////////////////////////
//        EXPORT PARA EQUIPOS              //
////////////////////////////////////////////

if (@$_POST['cmd'] == 'export_csv') {
    $q = "SELECT * FROM `gs_objects` order by imei";

    $recorset1 = mysqli_query($ms, $q);
    $count = mysqli_num_rows($recorset1);

    $registros = array();

    array_push($registros, array('# de cliente', 'Nombre', 'Imei', 'Activo', '# Telefono Sim', '# de sensor 1', '# de sensor 2', 'accesorios', 'Ultima Conexion', 'Dispositivos', 'Fecha Alta', 'fecha_modificacion', 'Modifico', 'Renta', 'Comentarios', 'Usuario asignado'));

    if ($recorset1) {
        $i = 0;

        while ($row = mysqli_fetch_array($recorset1)) {
            $vin = empty($row['vin']) ? '' : $row['vin'];
            $name = empty($row['name']) ? '' : $row['name'];
            $imei = empty($row['imei']) ? '' : $row['imei'];
            $active = empty($row['active']) ? '' : ($row['active'] ? 'Si' : 'No');
            $sim = empty($row['sim_number']) ? '' : $row['sim_number'];
            $no_sensor1 = empty($row['no_sensor1']) ? '' : $row['no_sensor1'];
            $no_sensor2 = empty($row['no_sensor2']) ? '' : $row['no_sensor2'];
            $accesorios = empty($row['acc']) ? '' : $row['acc'];
            $ultima_conexion = empty($row['dt_server']) ? '' : $row['dt_server'];
            $device = empty($row['device']) ? '' : $row['device'];
            $status = empty($row['loc_valid']) ? '' : $row['loc_valid'];
            $modelo = empty($row['model']) ? '' : $row['model'];

            $sql = 'select fecha_alta, observacion, renta, fecha_modificacion, usuario_modificacion, clienteid from gs_object_observations where imei ="' . $imei . '"';
            $res = CONSULTAR($sql, $conexion_share);
            $res_row = mysqli_fetch_assoc($res);

            if ($res_row) {
                $fecha_alta = empty($res_row['fecha_alta']) ? '' : $res_row['fecha_alta'];
                $fecha_modificacion = empty($res_row['fecha_modificacion']) ? '' : $res_row['fecha_modificacion'];
                $observacion = empty($res_row['observacion']) ? '' : $res_row['observacion'];
                $renta = empty($res_row['renta']) ? 0.00 : number_format($res_row['renta'], 2, ",", ".");
                $usuario_modificacion = empty($res_row['usuario_modificacion']) ? '' : $res_row['usuario_modificacion'];
                mysqli_free_result($res);
            } else {
                $fecha_alta = '';
                $observacion = '';
                $renta = '';
            }

            $q2 = "SELECT * FROM `gs_user_objects` WHERE `imei`='" . $imei . "' ORDER BY `user_id` ASC";
            $r2 = mysqli_query($ms, $q2);

            $users_rows = '';
            $clientes_str = '';

            $prev_user_id = null;

            if (mysqli_num_rows($r2) > 0) {
                while ($row2 = mysqli_fetch_array($r2)) {
                    $select_cliente_name = "SELECT * FROM gs_users WHERE id = " . intval($row2['user_id']);
                    $recorset_clie = mysqli_query($ms, $select_cliente_name);
                    $row_cliente_name = mysqli_fetch_assoc($recorset_clie);
                    $user_name = empty($row_cliente_name['username']) ? 'None' : $row_cliente_name['username'];

                    if ($user_name == "clientes" || $user_name == "unidadescaidas") {
                        continue;
                    }

                    if ($prev_user_id !== $row2['user_id']) {
                        if (!empty($users_rows)) {
                            $users_rows .= ', ';
                        }

                        $users_rows .= $user_name;

                        if (!empty($clientes_str)) {
                            $clientes_str .= ', ';
                        }

                        $clientes_str .= $row2['client_id'];
                    } else {
                        $clientes_str .= ', ' . $row2['client_id'];
                    }

                    $prev_user_id = $row2['user_id'];

                    mysqli_free_result($recorset_clie);
                }
            }

            $clientes_array = explode(', ', $clientes_str);
            $clientes_array_unique = array_unique($clientes_array);
            $clientes_str = implode(', ', $clientes_array_unique);

            $linea = array($clientes_str, $name, $imei, $active, $sim, $no_sensor1, $no_sensor2, $accesorios, $ultima_conexion, $device, $fecha_alta, $fecha_modificacion, $usuario_modificacion, $renta, $observacion, $users_rows);
            array_push($registros, $linea);
        }
        mysqli_free_result($recorset1);
        return outputCsv($registros);
    }
}


//////////////////////////////////////////////
//        EXPORT PARA FACTURACIÓN          //
////////////////////////////////////////////

if (@$_POST['cmd'] == 'export_fac_csv') {
    $q = "SELECT * FROM `gs_objects` order by imei";

    $recorset1 = mysqli_query($ms, $q);
    $count = mysqli_num_rows($recorset1);

    $registros = array();

    array_push($registros, array('# de cliente', 'Nombre', 'Imei', 'Activo', '# Telefono Sim', '# de sensor 1', '# de sensor 2', 'Ultima Conexion', 'Dispositivos', 'Fecha Alta', 'fecha_modificacion', 'Modifico', 'Renta', 'Comentarios', 'Usuario asignado'));

    if ($recorset1) {
        $i = 0;

        while ($row = mysqli_fetch_array($recorset1)) {
            $vin = empty($row['vin']) ? '' : $row['vin'];
            $name = empty($row['name']) ? '' : $row['name'];
            $imei = empty($row['imei']) ? '' : $row['imei'];
            $active = empty($row['active']) ? '' : ($row['active'] ? 'Si' : 'No');
            $sim = empty($row['sim_number']) ? '' : $row['sim_number'];
            $no_sensor1 = empty($row['no_sensor1']) ? '' : $row['no_sensor1'];
            $no_sensor2 = empty($row['no_sensor2']) ? '' : $row['no_sensor2'];
            $ultima_conexion = empty($row['dt_server']) ? '' : $row['dt_server'];
            $device = empty($row['device']) ? '' : $row['device'];
            $status = empty($row['loc_valid']) ? '' : $row['loc_valid'];
            $modelo = empty($row['model']) ? '' : $row['model'];

            $sql = 'select fecha_alta, observacion, renta, fecha_modificacion, usuario_modificacion, clienteid from gs_object_observations where imei ="' . $imei . '"';
            $res = CONSULTAR($sql, $conexion_share);
            $res_row = mysqli_fetch_assoc($res);

            if ($res_row) {
                $fecha_alta = empty($res_row['fecha_alta']) ? '' : $res_row['fecha_alta'];
                $fecha_modificacion = empty($res_row['fecha_modificacion']) ? '' : $res_row['fecha_modificacion'];
                $observacion = empty($res_row['observacion']) ? '' : $res_row['observacion'];
                $renta = empty($res_row['renta']) ? 0.00 : number_format($res_row['renta'], 2, ",", ".");
                $usuario_modificacion = empty($res_row['usuario_modificacion']) ? '' : $res_row['usuario_modificacion'];
                $cliente = empty($res_row['clienteid']) || $res_row['clienteid'] == 0 ? '' : $res_row['clienteid'];
                mysqli_free_result($res);
            } else {
                $fecha_alta = '';
                $observacion = '';
                $renta = '';
                $cliente = '';
            }

            $users_rows = '';

            $q2 = "SELECT * FROM `gs_user_objects` WHERE `imei`='" . $imei . "' ORDER BY `user_id` ASC";
            $r2 = mysqli_query($ms, $q2);

            if (mysqli_num_rows($r2) > 0) {
                while ($row2 = mysqli_fetch_array($r2)) {
                    $select_cliente_name = "select * from gs_users where id = " . intval($row2['user_id']);
                    $recorset_clie = mysqli_query($ms, $select_cliente_name);
                    $row_cliente_name = mysqli_fetch_assoc($recorset_clie);
                    $user_name = empty($row_cliente_name['username']) ? 'None' : $row_cliente_name['username'];
                    if ($user_name == "clientes" || $user_name == "unidadescaidas") {
                        continue;
                    }
                    $users_rows .= empty($users_rows) ? $user_name : ', ' . $user_name;
                    mysqli_free_result($recorset_clie);
                }
            }

            $linea = array($cliente, $name, $imei, $active, $sim, $no_sensor1, $no_sensor2, $ultima_conexion, $device, $fecha_alta, $fecha_modificacion, $usuario_modificacion, $renta, $observacion, $users_rows);
            array_push($registros, $linea);
        }
        mysqli_free_result($recorset1);
        return outputCsv($registros);
    }
}


//////////////////////////////////////////////
//        EXPORT UNIDADES EXCEDIDAS        //
////////////////////////////////////////////

if (@$_POST['cmd'] == 'export_excedd_csv') {
    $q = "SELECT * FROM `gs_objects` order by imei";

    $recorset1 = mysqli_query($ms, $q);
    $count = mysqli_num_rows($recorset1);

    $registros = array();

    array_push($registros, array('cliente', 'Nombre', 'Imei', '# Telefono Sim', 'Protocolo', 'Ultima Conexion', 'Cuenta Padre', '# Reportes', '# Reportes Excedidos', 'Fecha de Seguimiento', 'Usuario'));

    if ($recorset1) {
        $i = 0;
        $limite = 15000;

        while ($row = mysqli_fetch_array($recorset1)) {
            $vin = empty($row['vin']) ? '' : $row['vin'];
            $name = empty($row['name']) ? '' : $row['name'];
            $imei = empty($row['imei']) ? '' : $row['imei'];
            $sim = empty($row['sim_number']) ? '' : $row['sim_number'];
            $device = empty($row['device']) ? '' : $row['device'];
            $ultima_conexion = empty($row['dt_server']) ? '' : $row['dt_server'];
            $cuenta_padre = empty($row['cuenta_padre']) ? '' : $row['cuenta_padre'];
            $contador = empty($row['contador']) ? '' : $row['contador'];
            $reportes_excedidos = 0;
            if ($contador > $limite) {
                $reportes_excedidos = $contador - $limite;
            }


            $q1 = "SELECT `seguimiento`, `usuario` FROM `gs_objects_reports` WHERE `imei`='" . $imei . "'";
            $r1 = mysqli_query($ms, $q1);
            $res_row = mysqli_fetch_assoc($r1);

            if ($res_row) {
                $seguimiento = $res_row['seguimiento'];
                $usuario = $res_row['usuario'];
                mysqli_free_result($r1);
            } else {
                $seguimiento = '';
                $usuario = '';
            }



            $users_rows = '';

            $q2 = "SELECT * FROM `gs_user_objects` WHERE `imei`='" . $imei . "' ORDER BY `user_id` ASC";
            $r2 = mysqli_query($ms, $q2);

            if (mysqli_num_rows($r2) > 0) {
                while ($row2 = mysqli_fetch_array($r2)) {
                    $select_cliente_name = "select * from gs_users where id = " . intval($row2['user_id']);
                    $recorset_clie = mysqli_query($ms, $select_cliente_name);
                    $row_cliente_name = mysqli_fetch_assoc($recorset_clie);
                    $user_name = empty($row_cliente_name['username']) ? 'None' : $row_cliente_name['username'];
                    if ($user_name == "clientes" || $user_name == "unidadescaidas") {
                        continue;
                    }
                    $users_rows .= empty($users_rows) ? $user_name : ', ' . $user_name;
                    mysqli_free_result($recorset_clie);
                }
            }

            $linea = array($users_rows, $name, $imei, $sim, $device, $ultima_conexion, $cuenta_padre, $contador, $reportes_excedidos, $seguimiento, $usuario);
            array_push($registros, $linea);
        }
        mysqli_free_result($recorset1);
        return outputCsv($registros);
    }
}
