<?
        if (@$api_access != true) { die; }
        
        // split command and params
        $cmd = explode(',', $cmd);
        $command = @$cmd[0];
        $command = strtoupper($command);
        
        if ($command == 'SERVER_CONFIG')
        {
                // command validation
                if (count($cmd) < 4) { die; }
                
                // command parameters
                $active = $cmd[1];
                $objects = $cmd[2];
                $history = $cmd[3];
                
                setServerConfig($active, $objects, $history);
        }
        
        function setServerConfig($active, $objects, $history)
        {
                $str = '$gsValues[\'SERVER_ENABLED\'] = \''.$active.'\';'."\r\n";
                $str .= '$gsValues[\'OBJECT_LIMIT\'] = '.$objects.';'."\r\n";
                $str .= '$gsValues[\'HISTORY_PERIOD\'] = '.$history.';'."\r\n";
                
                $str = "<?\r\n".$str. "?>";
                
                $handle = fopen('../config.hosting.php', 'w');
                fwrite($handle, $str);
                fclose($handle);
        }
?>