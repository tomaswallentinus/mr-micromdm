<?php
require "micromdm_functions.php";
/**
 * micromdm class
 *
 * @package munkireport
 * @author 
 **/

class Micromdm_controller extends Module_controller
{
    function __construct()
    {
        // Store module path
        $this->module_path = dirname(__FILE__);
    }
    /**
     * General microMDM information
    **/
    public function admin()
    {
        $obj = new View();
        $obj->view('micromdm_admin', [], $this->module_path.'/views/');
    }
    /**
     * Fetch micromdm version data
    **/
    public function update_cached_data()
    {
        // Create YAML from micromdm json
        $micromdm_functions = new \micromdm\micromdm;
        $version_result = $micromdm_functions->Call("version","GET",null);
        // Check if we got results from micromdm api
        if (strpos($version_result,'version') === false ){
            if (file_exists(__DIR__ . '/micromdm_version.yml')){
                // No data from MDM check cached file
                $version_result = file_get_contents(__DIR__ . '/micromdm_version.yml');
                $return_status = 2;
                $cache_source = 2;
                $yaml_data = (object) Symfony\Component\Yaml\Yaml::parse($version_result);
            } else {
                // Create dummy file
                $yaml_data = (object) array('version'=>'unknown','build_date'=>'unknown');
                $version_result = $yaml_data;
                $yaml_encode=Symfony\Component\Yaml\Yaml::dump($yaml_data);
                file_put_contents(__DIR__ . '/micromdm_version.yml', $yaml_encode);
                $return_status = 2;
                $cache_source = 2;
            }
        } else {
            //Save result to yaml file
            $yaml_data=json_decode($version_result);
            $yaml_encode=Symfony\Component\Yaml\Yaml::dump($yaml_data);
            file_put_contents(__DIR__ . '/micromdm_version.yml', $yaml_encode);
            $return_status = 1;
            $cache_source = 1;
        }
        $version = $yaml_data->version . ' (' . $yaml_data->build_date . ')';


        // Get the current time
        $current_time = time();
        
        // Save new cache data to the cache table
        munkireport\models\Cache::updateOrCreate(
            [
                'module' => 'micromdm', 
                'property' => 'yaml',
            ],[
                'value' => $version_result,
                'timestamp' => $current_time,
            ]
        );
        munkireport\models\Cache::updateOrCreate(
            [
                'module' => 'micromdm', 
                'property' => 'source',
            ],[
                'value' => $cache_source,
                'timestamp' => $current_time,
            ]
        );
        munkireport\models\Cache::updateOrCreate(
            [
                'module' => 'micromdm', 
                'property' => 'version',
            ],[
                'value' => $version,
                'timestamp' => $current_time,
            ]
        );
        munkireport\models\Cache::updateOrCreate(
            [
                'module' => 'micromdm', 
                'property' => 'last_update ',
            ],[
                'value' => $current_time,
                'timestamp' => $current_time,
            ]
        );
        
        // Send result
        $out = array("status"=>$return_status,"source"=>$cache_source,"timestamp"=>$current_time,"version"=>$version);
        jsonView($out);
    }
    
    /**
     * Do a DEP-sync
    **/
    public function dep_sync()
    {
        // Issue a dep-sync with microMDM
        $micromdm_functions = new \micromdm\micromdm;
        $version_result = $micromdm_functions->Call("v1/dep/syncnow","POST",null);
        // Check if we got results from micromdm api
        if ($version_result == "200" ){
            $return_status="ok";
        } else {
            $return_status="error: " . $version_result;
        }
        // Send result
        $out = array("status"=>$return_status);
        jsonView($out);
    }
    /**
     * Issue device specific micromdm calls
    **/
    public function requestType($requestType,$serial_number){
        $platform_UUID = Machine_model::selectRaw('machine.platform_UUID')
            ->filter()
            ->whereSerialNumber($serial_number)
            ->limit(1)
            ->first()
            ->toArray();
        $micromdm_functions = new \micromdm\micromdm;
        print_r($micromdm_functions->requestType($requestType,$platform_UUID['platform_UUID']));
    }
    /**
     * Return JSON with information for admin page
     *
     * @return void
     * @author tuxudo
     **/
    public function get_admin_data()
    {
        $version = munkireport\models\Cache::select('value')
                        ->where('module', 'micromdm')
                        ->where('property', 'version')
                        ->value('value');
        $source = munkireport\models\Cache::select('value')
                        ->where('module', 'micromdm')
                        ->where('property', 'source')
                        ->value('value');
        $last_update = munkireport\models\Cache::select('value')
                        ->where('module', 'micromdm')
                        ->where('property', 'last_update')
                        ->value('value');
        $out = array('version' => $version,'source' => $source,'last_update' => $last_update);
        jsonView($out);
    }

    /**
     * Get micromdm information for serial_number
     *
     * @param string $serial serial number
     **/
    public function get_data($serial_number = '')
    {
        jsonView(
            Micromdm_model::select('micromdm.*')
            ->whereSerialNumber($serial_number)
            ->filter()
            ->limit(1)
            ->first()
            ->toArray()
        );
    }

    public function get_list($column = '')
    {
        jsonView(
            Micromdm_model::select("micromdm.$column AS label")
                ->selectRaw('count(*) AS count')
                ->filter()
                ->groupBy($column)
                ->orderBy('count', 'desc')
                ->get()
                ->toArray()
        );
    }
} 