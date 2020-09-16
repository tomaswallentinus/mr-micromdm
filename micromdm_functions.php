<?php
namespace micromdm;

class micromdm {
    public function Call($uri="v1/commands",$method="POST",$data){
        global $micromdmPath, $base64BasicLogin;
        $ch = curl_init(env('MICROMDM_PATH', '') . '/' .  $uri);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if (isset($data) && $data!=''){curl_setopt($ch, CURLOPT_POSTFIELDS, $data);}
        curl_setopt($ch, CURLOPT_FAILONERROR,true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json;charset=utf-8',
            'Content-Type: application/json;charset=utf-8',
            'Authorization: ' . env('MICROMDM_BASIC_LOGIN', '')
        ));
        $response = curl_exec($ch);
        if (curl_error($ch)) {
            echo 'CURL url: ' . $micromdmPath . '/' .  $uri . '<br>';
            echo 'CURL data: ' . $data . '<br>';
            echo 'CURL request method: ' . $method . '<br>';
            echo 'CURL Authorization: ' . $base64BasicLogin . '<br>';
            echo 'CURL error: ' . curl_error($ch) . '<br>';
        }
        curl_close($ch);
        return $response;
    }
    public function prepareData($data){
        return json_encode($data, JSON_UNESCAPE_SLASHES);
    }
}
?>