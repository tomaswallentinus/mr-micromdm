<?php $this->view('partials/head'); ?>

<div class="container">
    <div class="row"><span id="micromdm_pull_all"></span></div>
    <div class="col-lg-12" id="micromdm_sync_dep"></div>
    <div class="col-lg-5">
        <div id="GetAllMicroMDM-Progress" class="progress hide">
            <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em; width: 0%;">
                <span id="Progress-Bar-Percent"></span>
            </div>
        </div>
        <br id="Progress-Space" class="hide">
        <div id="MicroMDM-Status"></div>
    </div>
</div>  <!-- /container -->

<script>
$(document).on('appReady', function(e, lang) {

    // Get JSON of admin data
    $.getJSON(appUrl + '/module/micromdm/get_admin_data', function (processdata) {

        // Build table
        var sosrows = '<table class="table table-striped table-condensed" id="micromdm_status"><tbody>'

        if (processdata['last_update'] > 0){
            var date = new Date(processdata['last_update'] * 1000);
            sosrows = sosrows + '<tr><th>'+i18n.t('micromdm.last_cache_update')+'</th><td id="sos_time"><span title="'+moment(date).fromNow()+'">'+moment(date).format('llll')+'</span></td></tr>';
        } else {
            sosrows = sosrows + '<tr><th>'+i18n.t('micromdm.last_cache_update')+'</th><td id="sos_time">'+i18n.t('micromdm.never')+'</td></tr>';
        }

        if (processdata['source'] == 1){
            sosrows = sosrows + '<tr><th>'+i18n.t('micromdm.cache_source')+'</th><td id="sos_source"><?php echo env('MICROMDM_PATH', '');?></td></tr>';
        } else if (processdata['source'] == 2){
            sosrows = sosrows + '<tr><th>'+i18n.t('micromdm.cache_source')+'</th><td id="sos_source">'+i18n.t('micromdm.local')+'</td></tr>';
        }

        sosrows = sosrows + '<tr><th>'+i18n.t('micromdm.version')+'</th><td id="sos_current_os">'+(mr.integerToVersion(processdata['version']))+'</td></tr>';

        $('#MicroMDM-Status').html(sosrows+'</tbody></table>') // Close table framework and assign to HTML ID
    });
    
    // Generate fetch version button and header    
    $('#micromdm_pull_all').html('<h3 class="col-lg-6" >&nbsp;&nbsp;'+i18n.t('micromdm.title_admin')+'&nbsp;&nbsp;<button id="UpdateMicroMDM" class="btn btn-info btn-xs">'+i18n.t("micromdm.fetch_version")+'</button>&nbsp;&nbsp;<button id="SyncDepMicroMDM" class="btn btn-info btn-xs">'+i18n.t("micromdm.sync_dep")+'</button>&nbsp;<i id="GetAllMicroMDMProgess" class="hide fa fa-cog fa-spin" aria-hidden="true"></i></h3>');
    
    // Update cache file function
    $('#UpdateMicroMDM').click(function (e) {
        // Disable buttons
        $('#GetAllMicroMDMProgess').removeClass('hide');
        $('#UpdateMicroMDM').addClass('disabled');
        
        $.getJSON(appUrl + '/module/micromdm/update_cached_data', function (processdata) {
            if(processdata['status'] == 1){
                var date = new Date(processdata['timestamp'] * 1000);
                $('#sos_time').html('<span title="'+moment(date).fromNow()+'">'+moment(date).format('llll')+'</span>')
                $('#sos_source').html('<?php echo env('MICROMDM_PATH', '');?>')
                $('#sos_version').html(mr.integerToVersion(processdata['current_os']))
                $('#UpdateMicroMDM').removeClass('disabled');
                $('#GetAllMicroMDMProgess').addClass('hide');
                
            } else if(processdata['status'] == 2){
                
                var date = new Date(processdata['timestamp'] * 1000);
                $('#sos_time').html('<span title="'+moment(date).fromNow()+'">'+moment(date).format('llll')+'</span>')
                $('#sos_source').html(i18n.t('micromdm.update_from_local'))
                $('#sos_version').html(mr.integerToVersion(processdata['version']))
                $('#UpdateMicroMDM').removeClass('disabled');
                $('#GetAllMicroMDMProgess').addClass('hide');
            }
        });
    });
    // Sync devices from ABM and ASM
    $('#SyncDepMicroMDM').click(function (e) {
        // Disable buttons
        $('#GetAllMicroMDMProgess').removeClass('hide');
        $('#SyncDepMicroMDM').addClass('disabled');
        $('#micromdm_sync_dep').html('');
        
        $.getJSON(appUrl + '/module/micromdm/dep_sync', function (processdata) {
            if(processdata['status'] == "ok"){
                $('#micromdm_sync_dep').html('DEP Synced');
                $('#SyncDepMicroMDM').removeClass('disabled');
                $('#GetAllMicroMDMProgess').addClass('hide');
            } else {
                $('#micromdm_sync_dep').html('DEP Sync error');
                $('#SyncDepMicroMDM').removeClass('disabled');
                $('#GetAllMicroMDMProgess').addClass('hide');
            }
        });
    });
});
</script>

<?php $this->view('partials/foot'); ?>
