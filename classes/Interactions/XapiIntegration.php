<?php
/**
 * xAPI sender class for moodle_lrs_plugin.
 * @package    local_moodle_lrs_plugin
 * @copyright  2025, Mohammed Hassan <betalamoud@gmail.com>
 * @license    MIT
 * @doc        https://docs.moodle.org/dev/Admin_settings
 */

namespace local_moodle_lrs_plugin\Interactions;

defined('MOODLE_INTERNAL') || die();

use local_moodle_lrs_plugin\Interactions\Registered;
use local_moodle_lrs_plugin\Interactions\Initialized;
use local_moodle_lrs_plugin\Interactions\Watched;
use local_moodle_lrs_plugin\Interactions\Completed;
use local_moodle_lrs_plugin\Interactions\CompletedUnit;
use local_moodle_lrs_plugin\Interactions\Progressed;
use local_moodle_lrs_plugin\Interactions\Attempted;
use local_moodle_lrs_plugin\Interactions\CompletedCourse;
use local_moodle_lrs_plugin\Interactions\Earned;
use local_moodle_lrs_plugin\Interactions\Rated;

class XapiIntegration {
    private $endpoint;
    private $username;
    private $password;
    private $platformname;
    private $platformname_ar;
    private $platformname_en;

    public function __construct() {
        $this->endpoint = get_config('local_moodle_lrs_plugin', 'endpoint');
        $this->username = get_config('local_moodle_lrs_plugin', 'username');
        $this->password = get_config('local_moodle_lrs_plugin', 'password');
        $this->platformname = get_config('local_moodle_lrs_plugin', 'platformname');
        $this->platformname_ar = get_config('local_moodle_lrs_plugin', 'platformname_ar');
        $this->platformname_en = get_config('local_moodle_lrs_plugin', 'platformname_en');
    }

    public function sendXAPIRequest($data = []) {
        global $CFG;
    
        if (!$this->checkInternetConnection()) {
            return [
                'http_code' => 0,
                'response' => null,
                'data_sent' => null,
                'error' => 'No internet connection',
            ];
        }

        $url = $this->endpoint;

        require_once($CFG->libdir . '/filelib.php');
        $curl = new \curl();
    
        $headers = array(
            'Content-Type: application/json',
            'X-Experience-API-Version: 1.0.3',
            'Authorization: Basic ' . base64_encode($this->username . ':' . $this->password)
        );
    
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HTTPHEADER' => $headers
        );
    
        $response = $curl->post($url, json_encode($data), $options);
    
        if ($curl->info['http_code'] == 200) {

            return [
                'http_code' => $curl->info['http_code'],
                'response' => $response,
                'data_sent' => json_encode($data)
            ];
            
        } else {
            $error = $curl->error;

            return [
                'http_code' => $curl->info['http_code'],
                'response' => $response,
                'data_sent' => json_encode($data),
                'error' => $error,
            ];

            //return "Error: HTTP Code: {$curl->info['http_code']}, cURL Error: $error, Response: $response";
        }
    }
    

    // فحص الاتصال بالإنترنت
    private function checkInternetConnection($url = "www.google.com", $port = 80, $timeout = 5) {
        $connected = @fsockopen($url, $port, $errno, $errstr, $timeout);
        if ($connected) {
            fclose($connected);
            return true; // الاتصال بالإنترنت متاح
        } else {
            return false; // لا يوجد اتصال بالإنترنت
        }
    }

    public function Registered( $data)
    {
        $instance = new Registered();
        $var = $instance->Send( $data);

        return $this->sendXAPIRequest( $var );
    }

    public function Initialized( $data)
    {
        $instance = new Initialized();
        $var = $instance->Send( $data);

        return $this->sendXAPIRequest( $var );
    }

    public function Watched( $data)
    {
        $instance = new Watched();
        $var = $instance->Send( $data);

        return $this->sendXAPIRequest( $var );
    }

    public function CompletedUnit( $data)
    {
        $instance = new CompletedUnit();
        $var = $instance->Send( $data);

        return $this->sendXAPIRequest( $var );
    }

    public function Completed( $data)
    {
        $instance = new Completed();
        $var = $instance->Send( $data);

        return $this->sendXAPIRequest( $var );
    }

    public function Progressed( $data)
    {
        $instance = new Progressed();
        $var = $instance->Send( $data);

        return $this->sendXAPIRequest( $var );
    }

    public function Attempted( $data = [] )
    {
        
        $instance = new Attempted();

        $var = $instance->Send( $data );

        return $this->sendXAPIRequest( $var );
    }

    public function CompletedCourse( $data = [] )
    {
        $instance = new CompletedCourse();

        $var = $instance->Send( $data );

        // Send CompletedCourse statement
        $completedResponse = $this->sendXAPIRequest( $var );

        // Immediately send Earned statement after Completed
        $earnedResponse = $this->Earned( $data );

        return [
            'completed' => $completedResponse,
            'earned' => $earnedResponse,
        ];
    }

    public function Earned( $data = [] )
    {
        
        $instance = new Earned();

        $var = $instance->Send( $data );

        return $this->sendXAPIRequest( $var );
    }

    public function Rated( $data = [] )
    {
        
        $instance = new Rated();

        $var = $instance->Send( $data );

        return $this->sendXAPIRequest( $var );
    }
}