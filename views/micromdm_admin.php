<?php $this->view('partials/head'); ?>

<div class="container">
    <div class="row"><span id="micromdm_pull_all"></span></div>
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
var micromdm_pull_all_running = 0;
    
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
    
    // Generate pull all button and header    
    $('#micromdm_pull_all').html('<h3 class="col-lg-6" >&nbsp;&nbsp;'+i18n.t('micromdm.title_admin')+'&nbsp;&nbsp;<button id="GetAllMicroMDM" class="btn btn-default btn-xs">'+i18n.t("micromdm.pull_in_all")+'</button>&nbsp;&nbsp;<button id="UpdateMicroMDM" class="btn btn-default btn-xs">'+i18n.t("micromdm.fetch_version")+'</button>&nbsp;<i id="GetAllMicroMDMProgess" class="hide fa fa-cog fa-spin" aria-hidden="true"></i></h3>');
    
    // Update cache file function
    $('#UpdateMicroMDM').click(function (e) {
        // Disable buttons
        $('#GetAllMicroMDM').addClass('disabled');
        $('#GetAllMicroMDMProgess').removeClass('hide');
        $('#UpdateMicroMDM').addClass('disabled');
        
        $.getJSON(appUrl + '/module/micromdm/update_cached_data', function (processdata) {
            if(processdata['status'] == 1){
                var date = new Date(processdata['timestamp'] * 1000);
                $('#sos_time').html('<span title="'+moment(date).fromNow()+'">'+moment(date).format('llll')+'</span>')
                $('#sos_source').html('<?php echo env('MICROMDM_PATH', '');?>')
                $('#sos_version').html(mr.integerToVersion(processdata['current_os']))
                $('#GetAllMicroMDM').removeClass('disabled');
                $('#UpdateMicroMDM').removeClass('disabled');
                $('#GetAllMicroMDMProgess').addClass('hide');
                
            } else if(processdata['status'] == 2){
                
                var date = new Date(processdata['timestamp'] * 1000);
                $('#sos_time').html('<span title="'+moment(date).fromNow()+'">'+moment(date).format('llll')+'</span>')
                $('#sos_source').html(i18n.t('micromdm.update_from_local'))
                $('#sos_version').html(mr.integerToVersion(processdata['version']))
                $('#GetAllMicroMDM').removeClass('disabled');
                $('#UpdateMicroMDM').removeClass('disabled');
                $('#GetAllMicroMDMProgess').addClass('hide');
            }
        });
    });

    micromdm_pull_all_running = 0;

    // Process all serials
    $('#GetAllMicroMDM').click(function (e) {
        // Disable button and unhide progress bar
        $('#GetAllMicroMDM').html(i18n.t('micromdm.processing')+'...');
        $('#Progress-Bar-Percent').text('0%');
        $('#GetAllMicroMDM-Progress').removeClass('hide');
        $('#Progress-Space').removeClass('hide');
        $('#GetAllMicroMDM').addClass('disabled');
        $('#UpdateMicroMDM').addClass('disabled');
        micromdm_pull_all_running = 1;

        // Get JSON of all serial numbers
        $.getJSON(appUrl + '/module/micromdm/pull_all_micromdm_data', function (processdata) {

            // Set count of serial numbers to be processed
            var progressmax = (processdata.length);
            var progessvalue = 0;;
            $('.progress-bar').attr('aria-valuemax', progressmax);

            var serial_index = 0;
            var serial = processdata[0]

            // Process the serial numbers
            process_serial(serial,progessvalue,progressmax,processdata,serial_index)
        });
    });
});

// Process each serial number one at a time
function process_serial(serial,progessvalue,progressmax,processdata,serial_index){

        // Get JSON for each serial number
        request = $.ajax({
        url: appUrl + '/module/micromdm/pull_all_micromdm_data/'+processdata[serial_index],
        type: "get",
        success: function (obj, resultdata) {

            // Calculate progress bar's percent
            var processpercent = Math.round((((progessvalue+1)/progressmax)*100));
            progessvalue++
            $('.progress-bar').css('width', (processpercent+'%')).attr('aria-valuenow', processpercent);
            $('#Progress-Bar-Percent').text(progessvalue+"/"+progressmax);

            // Cleanup and reset when done processing serials
            if ((progessvalue) == progressmax) {
                // Make button clickable again and hide process bar elements
                $('#GetAllMicroMDM').html(i18n.t('micromdm.pull_in_all'));
                $('#GetAllMicroMDM').removeClass('disabled');
                $('#UpdateMicroMDM').removeClass('disabled');
                micromdm_pull_all_running = 0;
                $("#Progress-Space").fadeOut(1200, function() {
                    $('#Progress-Space').addClass('hide')
                    var progresselement = document.getElementById('Progress-Space');
                    progresselement.style.display = null;
                    progresselement.style.opacity = null;
                });
                $("#GetAllMicroMDM-Progress").fadeOut( 1200, function() {
                    $('#GetAllMicroMDM-Progress').addClass('hide')
                    var progresselement = document.getElementById('GetAllMicroMDM-Progress');
                    progresselement.style.display = null;
                    progresselement.style.opacity = null;
                    $('.progress-bar').css('width', 0+'%').attr('aria-valuenow', 0);
                });

                return true;
            }

            // Go to the next serial
            serial_index++

            // Get next serial
            serial = processdata[serial_index];

            // Run function again with new serial
            process_serial(serial,progessvalue,progressmax,processdata,serial_index)
        },
        statusCode: {
            500: function() {
                micromdm_pull_all_running = 0;
                alert("An internal server occurred. Please refresh the page and try again.");
            }
        }
    });
}

// Warning about leaving page if supported os pull all is running
window.onbeforeunload = function() {
    if (micromdm_pull_all_running == 1) {
        return i18n.t('micromdm.leave_page_warning');
    } else {
        return;
    }
};
    
</script>

<?php $this->view('partials/foot'); ?>
