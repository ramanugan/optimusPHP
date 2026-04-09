<?php

class sendDataWS
{

    public $serviceURL;
    public $method;
    public $auth;

    function __construct($serviceURL, $method, $auth = null)
    {
        $this->serviceURL = $serviceURL;
        $this->method = $method;
        $this->auth = $auth;
    }

    function getObjectAuthLala($dataAuth)
    {
        $headers = array(
            "Content-Type: application/json",
        );
        return $this->CallAPI($this->method, $this->serviceURL, $headers, $dataAuth);
    }

    function setPosicion($PosicionRequest)
    {
        $headers = null;
        if ($this->auth) {
            $headers = array(
                "Content-Type: application/json",
                "Authorization: {$this->Auth}"
            );
        }
        return $this->CallAPI($this->method, $this->serviceURL, $headers, $PosicionRequest);
    }

    function setPosicionLala($PosicionRequest)
    {
        $headers = array(
            "Content-Type: application/json",
        );
        return $this->CallAPI($this->method, $this->serviceURL, $headers, $PosicionRequest);
    }

    function CallAPI($method, $url, $headers, $data = false)
    {
        $curl = curl_init($url);

        switch ($method) {
            case "GET":
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;            
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
        }

        if ($headers) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($curl);
        
       // echo "<pre>";
       // print_r($result);
       // echo "</pre>";
        
        curl_close($curl);

        return $result;
    }

    function castDateSQL_UNIX_PR($date)
    {
        return strtotime($date);
    }
}
