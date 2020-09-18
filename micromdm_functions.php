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
        if (isset($data) && $data!='' && $data!='[]'){curl_setopt($ch, CURLOPT_POSTFIELDS, $data);}
        curl_setopt($ch, CURLOPT_FAILONERROR,true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Munkireport - Micromdm module");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json;charset=utf-8',
            'Content-Type: application/json;charset=utf-8',
            'Authorization: Basic ' . env('MICROMDM_BASIC_LOGIN', '')
        ));
        $response = curl_exec($ch);
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_error($ch)) {
            $response=array('status'=>'Curl error','url'=>env('MICROMDM_PATH', '') . '/' .  $uri,'data'=>$data,'method'=>$method,'authorization'=>env('MICROMDM_BASIC_LOGIN', ''),'error',curl_error($ch));
        }
        if ($response==''){
            $response=$response_code;
        }
        curl_close($ch);
        return $response;
    }
    public function requestType($requestType,$platform_UUID){
        $method='POST';
        $uri='v1/commands';
        $payload['request_type']=secIn($requestType);
        $payload['udid']=$platform_UUID;
        switch ($requestType) {
            case 'Push':
                $payload=array();
                $uri="push/" . $platform_UUID;
                $method="GET";
            break;
            case 'InstallProfile':
                $payload['payload']='base64decoded_profile_data';
                $payload['management_flags']=1;
            break;
            case 'InstallApplication':
                $payload['manifest_url']='url_to_plist';
                $payload['management_flags']='1';
            break;
            case 'DeviceConfigured';
            break;
            case 'AccountConfiguration';
                $payload['skip_primary_setup_account_creation']=false;
                $payload['set_primary_setup_account_as_regular_user']=false;
                $payload['dont_auto_populate_primary_account_info']=false;
                $payload['lock_primary_account_info']=true;
                $payload['primary_account_user_name']='short name';
                $payload['primary_account_full_name']='full name';
            break;
            case 'RestartDevice';
            break;
            case 'LogOutUser';
            break;
            case 'DeviceLock';
                //Need to save the PIN
            break;
            case 'VerifyFirmwarePassword';
                $payload['password']='firmwarepassword';
            break;
            case 'RemoveProfile';
                $payload['identifier']='profile identifier';
                $payload['management_flags']='1';
            break;
            case 'ProfileList';
                //Use XML Parsing, or do we need this as it's possible from other modules?
            break;
            default:
                return 'error: select request type';
        }
        return $this->Call($uri,$method,json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
function secIn($input){
    return filter_var($input, FILTER_SANITIZE_STRING);
}
?>