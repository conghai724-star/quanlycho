<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config.php';
require_once 'application/database.class.php';
require_once 'model/marketModel.php';
$marketModel = new marketModel();
$id = 3;
$data = [
    'manager_name' => 'Test Manager',
    'phone' => '0909090909',
    'email' => 'andong@test.com',
    'market_code' => 'CHO_AD',
    'name' => 'Cho An Dong Test',
    'status_code' => 'active'
];
try {
    $res = $marketModel->update($id, $data);
    echo "Success: ";
    var_dump($res);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
