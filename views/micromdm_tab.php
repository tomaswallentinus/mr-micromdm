<?php
/**
 * microMDM communication
**/
echo 'CSS-classes to use btn btn-xs btn-info btn-warning';
?>
<div id="micromdm-tab"></div>
<h2 data-i18n="micromdm.title"></h2>
<div class="row"><span id="micromdm_log"></span><span id="micromdm_button_row"></span></div>

<table id="micromdm-tab-table"></table>

<script>

$(document).on('appReady', function(){
    $('#micromdm_button_row').html('<button id="PushDevice" class="btn btn-info btn-xs">'+i18n.t("micromdm.push")+'</button>');
    $.getJSON(appUrl + '/module/micromdm/get_data/' + serialNumber, function(data){
        var table = $('#micromdm-tab-table');
        $.each(data, function(key,val){
            var th = $('<th>').text(i18n.t('micromdm.column.' + key));
            var td = $('<td>').text(val);
            table.append($('<tr>').append(th, td));
        });
    });
    $('#PushDevice').on('click', function(){
        $.getJSON(appUrl + '/module/micromdm/requestType/push/' + serialNumber, function(data){
            $('#micromdm_log').html(data);
        });
    });
});
</script>
