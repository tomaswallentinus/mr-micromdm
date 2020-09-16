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
            'Authorization: basic ' . env('MICROMDM_BASIC_LOGIN', '')
        ));
        $response = curl_exec($ch);
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_error($ch)) {
            $response=array('status'=>'Curl error','url'=>$micromdmPath . '/' .  $uri,'data'=>$data,'method'=>$method,'authorization'=>$base64BasicLogin,'error',curl_error($ch));
        }
        if ($response==''){
            $response=$response_code;
        }
        curl_close($ch);
        return $response;
    }
    public function prepareData($data){
        return json_encode($data, JSON_UNESCAPE_SLASHES);
    }
}
?>