<?php
include('../init.php');
include('../custom/consultas.php');

$handle = fopen("./test_import.csv", "r");

fgets($handle); // ignoramos el encabezado
while (($raw_string = fgets($handle)) !== false) {
    $row = str_getcsv($raw_string, '|');
    $qryDB = "SELECT * FROM gs_objects where imei = '${row[0]}' and sim_number = '${row[1]}'";

    $recordsetDB = mysqli_query($ms, $qryDB);
    $countDB = mysqli_num_rows($recordsetDB);
    if ($countDB > 0) {
        $qryCompany = "SELECT * FROM gs_object_observations where imei = '${row[0]}'";
        $recordsetCompany = mysqli_query($conexion_share, $qryCompany);
        $countCompany = mysqli_num_rows($recordsetCompany);
        $row[2] = str_replace('$', '', $row[2]);
        $fechaAlta = preg_replace("/(\d+)\D+(\d+)\D+(\d+)/","$3-$2-$1",$row[3]);
        $row[3] = $fechaAlta;
        if ($countCompany > 0) { // Existe el IMEI solo se actualiza
            $qryUpdateCompany = 'UPDATE gs_object_observations SET fecha_alta="' . $row[3] . '", renta="' . $row[2] . '"  WHERE imei="' . $row[0] . '"';
            $r = mysqli_query($conexion_share, $qryUpdateCompany);
        } else { // Se crea el registro
            $qryInsertCompany = "INSERT INTO gs_object_observations ( imei, fecha_alta, observacion, fecha_creacion, fecha_modificacion, usuario_creador, usuario_modificacion, renta, clienteid) VALUES ('" . $row[0] . "','" . $row[3] . "','','" . $row[3] . "', now(), 'admin', 'admin', " . $row[2] . ", 1 )";
            $r = mysqli_query($conexion_share, $qryInsertCompany);
        }
    }
}

fclose($handle);
