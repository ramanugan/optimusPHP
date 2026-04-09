<?
	ob_start();
	echo "OK";
	header("Content-length: " . (string)ob_get_length());
	ob_end_flush();
	
	chdir('../');
	include ('s_insert.php');
	
	$data = json_decode(file_get_contents("php://input"), true);
				
	for ($i = 0; $i < count($data); ++$i)
	{
		$loc = $data[$i];
		
		if (!isset($loc["imei"]))
		{
			continue;
		}
		
		$loc['dt_server'] = gmdate("Y-m-d H:i:s");
		$loc['params'] = paramsToArray($loc['params']);
			
		if (@$loc["op"] == "loc")
		{
			insert_db_loc($loc);	
		}
		else if (@$loc["op"] == "noloc")
		{
			insert_db_noloc($loc);
		}
		else if (@$loc["op"] == "imgloc")
		{
			insert_db_imgloc($loc);
		}
	}

	mysqli_close($ms);
	die;
?>