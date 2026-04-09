<?
        function sendWebhookQueue($webhook_url)
        {
                global $ms;
		
		$q = "INSERT INTO `gs_webhook_queue` 	(`dt_webhook`,
							`webhook_url`)
							VALUES
							('".gmdate("Y-m-d H:i:s")."',
							'".$webhook_url."')";
		$r = mysqli_query($ms, $q);
                
                if ($r)
                {
                        return true;
                }
                else
                {
                        return false;
                }
        }
        
        function sendWebhook($webhook_url)
        {                
                if ($webhook_url != '')
                {
                        $context = stream_context_create(array(
                                        'http' => array(
                                            'timeout' => 3
                                        )
                                ));
                        
                        $result = @file_get_contents($webhook_url, false, $context);
                                
                        return true;
                }
                else
                {
                        return false;
                }
        }
        
        function sendWebhookCURL($webhook_urls)
        {                
                $curl_arr = array();
                $master = curl_multi_init(); 
                
                for ($i = 0; $i < count($webhook_urls); $i++)
                {
                        $curl_arr[$i] = curl_init($webhook_urls[$i]);
			curl_setopt($curl_arr[$i], CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl_arr[$i], CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl_arr[$i], CURLOPT_SSL_VERIFYPEER, false);
			curl_multi_add_handle($master, $curl_arr[$i]); 
                }
                
                do
		{
			curl_multi_exec($master, $running);
		}
		while ($running > 0);
		
		for ($i = 0; $i < count($webhook_urls); $i++)
		{
			$result = curl_multi_getcontent($curl_arr[$i]);
                        curl_multi_remove_handle($master, $curl_arr[$i]);
		}

                curl_multi_close($master);		
		unset($curl_arr);
                
                return true;
        }
?>