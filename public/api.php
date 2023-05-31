<?php

include "../src/func/func.php";
include "../config/scandata_conf.php";

// Create an instance of the API class
$api = new SimpleAPI($db_ip, $db_user, $db_password, $db_name);


///////////SHOW ALL FUNCTIONS
//show all clients
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        $param = $_GET;
        unset($param['action']);
        if (method_exists($api,$action)) {
            $result = $api->$action($param);
            if (isset($result['code'])) {
                echo $api->print_json($result['code'],$code_array[$result['code']],$result['data'],$result['tip']);
            } else {
                echo $api->print_json("506", $code_array['506']);
            }
        } else {
            echo $api->print_json("402", $_GET['action']. ' ' . $code_array['402']);
        }
    } else {
        echo $api->print_json("403", $code_array['403']);
    }
} else {
    echo $api->print_json("401", $code_array['401']);
}

?>
