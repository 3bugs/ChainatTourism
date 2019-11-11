<?php
require_once '../api/global.php';

error_reporting(E_ERROR | E_PARSE);
header('Content-type: text/html; charset=utf-8');
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');

/*session_start();
if (!isset($_SESSION[KEY_SESSION_USER_ID])) {
    header('Location: login.php');
    exit();
}*/

require_once '../include/db_config.inc';
$db = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

if ($db->connect_errno) {
    echo 'การเชื่อมต่อฐานข้อมูลล้มเหลว: ' . $db->error;
    exit();
}
$db->set_charset("utf8");
?>