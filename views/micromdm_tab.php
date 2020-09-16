<?php
/**
 * microMDM communication
**/

function secIn($input){
    return filter_var($input, FILTER_SANITIZE_STRING);
}
if (isset($_GET['requestType'])){
    $requestType=secIn($_GET['requestType']);
//How do we get the UUID?
    $payload['udid']=$udid;
    $payload['request_type']=$requestType;
    switch ($requestType) {
        case 'push':
            micromdmCall("push/" . $UUID,"GET",null);
        break;
        case 'version':
            $micromdmVersion=micromdmCall("version","GET",null);
            if ($micromdmVersion!='' && strpos($micromdmVersion,'"version"') !== false){
                $micromdmVersion=json_decode($micromdmVersion);
            } else {
                $mícromdmVersion->version='Unable to query microMDM';
                $micromdmVersion->build='';
            }
            echo 'MicroMDM version: ' . $micromdmVersion->version . ' (' . $micromdmVersion->build_date . ')';
        break;
        case 'InstallProfile':
            $payload['payload']='base64decoded_profile_data';
            $payload['management_flags']=1;
            micromdmCall(null,null,$payload);
        break;
        case 'InstallApplication':
            $payload['manifest_url']='url_to_plist';
            $payload['management_flags']='1';
            micromdmCall(null,null,$payload);
        break;
        case 'DeviceConfigured';
            micromdmCall(null,null,$payload);
        break;
        case 'AccountConfiguration';
            $payload['skip_primary_setup_account_creation']=false;
            $payload['set_primary_setup_account_as_regular_user']=false;
            $payload['dont_auto_populate_primary_account_info']=false;
            $payload['lock_primary_account_info']=true;
            $payload['primary_account_user_name']='short name';
            $payload['primary_account_full_name']='full name';
            micromdmCall(null,null,$payload);
        break;
        case 'RestartDevice';
            micromdmCall(null,null,$payload);
        break;
        case 'LogOutUser';
            micromdmCall(null,null,$payload);
        break;
        case 'DEPAccount';
            micromdmCall('v1/dep/account','GET',null);
        break;
        case 'DEPSyncNow';
            micromdmCall('v1/dep/syncnow','POST',null);
        break;
        case 'DeviceLock';
            //Need to save the PIN
        break;
        case 'VerifyFirmwarePassword';
            $payload['password']='firmwarepassword';
            micromdmCall(null,null,$payload);
        break;
        case 'RemoveProfile';
            $payload['identifier']='profile identifier';
            $payload['management_flags']='1';
            micromdmCall(null,null,$payload);
        break;
        case 'ProfileList';
            $installedProfiles=micromdmCall(null,null,$payload);
            //Use XML Parsing, or do we need this as it's possible from other modules?
        break;
    }
}
echo '<span id="micromdm-buttonrow">
<button class="btn btn-xs btn-info">Fetch microMDM version</button>
<button class="btn btn-xs btn-warning">Remove profile</button>
</span>

<a href="' . $_SERVER['REQUEST_URI'] . '?requestType=version#tab_micromdm">Get Micromdm version</a>';
//echo '<a href="' . $_SERVER['REQUEST_URI'] . '?requestType=push#tab_micromdm">Push</a>';
//echo '<a href="' . $_SERVER['REQUEST_URI'] . '?requestType=push#tab_micromdm">Push</a>';
//echo '<a href="' . $_SERVER['REQUEST_URI'] . '?requestType=push#tab_micromdm">Push</a>';
//echo '<a href="' . $_SERVER['REQUEST_URI'] . '?requestType=push#tab_micromdm">Push</a>';

//require_once('plistParser.php');
//$xmlParse = new PlistParser;
//$micromdmVersion=$xmlParse->StringToArray($micromdmVersion);
//print_r($micromdmVersion);
?>
<div id="micromdm-tab"></div>
<h2 data-i18n="micromdm.title"></h2>

<table id="micromdm-tab-table"></table>

<script>
$(document).on('appReady', function(){
    $.getJSON(appUrl + '/module/micromdm/get_data/' + serialNumber, function(data){
        var table = $('#micromdm-tab-table');
        $.each(data, function(key,val){
            var th = $('<th>').text(i18n.t('micromdm.column.' + key));
            var td = $('<td>').text(val);
            table.append($('<tr>').append(th, td));
        });
    });
});
</script>
