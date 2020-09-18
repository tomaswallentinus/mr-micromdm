<div id="micromdm-tab"></div>
<h2 data-i18n="micromdm.title"></h2>
<div class="row"><span id="micromdm_log"></span><span id="micromdm_button_row" class="col-lg-12"></span></div>

<table id="micromdm-tab-table"></table>

<script>
/**
 * Button array with button color, titel/requesttype to micromdm
**/
var buttons=[
    ['info','Push'],
    ['warning','DeviceConfigured'],
    ['info','AccountConfiguration'],
    ['info','RestartDevice'],
    ['info','LogOutUser'],
    ['info','ProfileList']
];
$(document).on('appReady', function(){
    // Create buttons
    var buttonRow=[];
    $(buttons).each(function(index,data){
        buttonRow.push('<button data-requesttype="' + data[1] + '" class="btn btn-xs btn-' + data[0] + '">' + i18n.t("micromdm." + data[1]) + '</button>&nbsp;&nbsp;');
    });
    $('#micromdm_button_row').html(buttonRow.join(''));
    //  '<button id="PushDevice" class="btn btn-info btn-xs">'+i18n.t("micromdm.push")+'</button>');
    $.getJSON(appUrl + '/module/micromdm/get_data/' + serialNumber, function(data){
        var table = $('#micromdm-tab-table');
        $.each(data, function(key,val){
            var th = $('<th>').text(i18n.t('micromdm.column.' + key));
            var td = $('<td>').text(val);
            table.append($('<tr>').append(th, td));
        });
    });
    //Make buttons clickable and issue micromdm call
    $('#micromdm_button_row').on('click','.btn', function(){
        $.getJSON(appUrl + '/module/micromdm/requestType/' + $(this).data('requesttype') + '/' + serialNumber, function(data){
            $('#micromdm_log').html(data);
        });
    });
});
</script>
