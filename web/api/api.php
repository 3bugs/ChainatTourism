<?php
session_start();
require_once 'global.php';

define('PLACE_TYPE_TOUR', 'TOUR');
define('PLACE_TYPE_TEMPLE', 'TEMPLE');
define('PLACE_TYPE_RESTAURANT', 'RESTAURANT');
define('PLACE_TYPE_OTOP', 'OTOP');

error_reporting(E_ERROR | E_PARSE);
header('Content-type: application/json; charset=utf-8');

header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');

$response = array();

$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$action = strtolower(array_shift($request));
$id = array_shift($request);

require_once 'db_config.php';
$db = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

if ($db->connect_errno) {
    $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
    $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล';
    $response[KEY_ERROR_MESSAGE_MORE] = $db->connect_error;
    echo json_encode($response);
    exit();
}
$db->set_charset("utf8");

//sleep(1); //todo:

switch ($action) {
    case 'get_place':
        doGetPlace();
        break;
    case 'get_otop_by_district':
        doGetOtopByDistrict();
        break;
    case 'add_rating':
        doAddRating();
        break;
    default:
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'No action specified or invalid action.';
        $response[KEY_ERROR_MESSAGE_MORE] = '';
        break;
}

$db->close();
echo json_encode($response);
exit();

function doGetPlace()
{
    global $db, $response;

    $getPlaceType = strtoupper(trim($_GET['place_type']));
    switch ($getPlaceType) {
        case PLACE_TYPE_TOUR:
            $placeType = 'ท่องเที่ยว';
            break;
        case PLACE_TYPE_TEMPLE:
            $placeType = 'วัด';
            break;
        case PLACE_TYPE_RESTAURANT:
            $placeType = 'ร้านอาหาร';
            break;
        case PLACE_TYPE_OTOP:
            $placeType = 'otop';
            break;
    }

    $sql = "SELECT * FROM chainat_place WHERE place_type = '$placeType'";
    if ($result = $db->query($sql)) {
        $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
        $response[KEY_ERROR_MESSAGE] = 'อ่านข้อมูลสำเร็จ';
        $response[KEY_ERROR_MESSAGE_MORE] = '';

        $placeList = array();
        while ($row = $result->fetch_assoc()) {
            $place = array();
            $place['id'] = (int)$row['id'];
            $place['name'] = $row['name'];
            $place['district'] = $row['district'];
            $place['address'] = $row['address'];
            $place['details'] = $row['details'];
            $place['phone'] = $row['phone'];
            $place['opening_time'] = $row['opening_time'];
            $place['latitude'] = floatval($row['latitude']);
            $place['longitude'] = floatval($row['longitude']);
            $place['image_list'] = $row['image_list'];
            $place['image_cover'] = $row['image_cover'];
            $place['recommend'] = (boolean)$row['recommend'];
            $place['place_type'] = $placeType;

            $place['gallery_images'] = array();

            $sql = "SELECT image_file_name FROM chainat_gallery WHERE place_id = " . $place['id'];
            if ($galleryResult = $db->query($sql)) {
                while ($galleryRow = $galleryResult->fetch_assoc()) {
                    array_push($place['gallery_images'], $galleryRow['image_file_name']);
                }
                $galleryResult->close();

                $sql = "SELECT FORMAT(AVG(rate), 1) AS average_rate FROM chainat_rating 
                WHERE item_id = {$place['id']} AND item_type = 'place'";
                if ($ratingResult = $db->query($sql)) {
                    $ratingRow = $ratingResult->fetch_assoc();
                    $averageRate = $ratingRow['average_rate'];
                    if ($averageRate == null) {
                        $place['average_rate'] = 0;
                    } else {
                        $place['average_rate'] = floatval($averageRate);
                    }
                } else {
                    $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
                    $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการอ่านข้อมูล (3)';
                    $errMessage = $db->error;
                    $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
                    return;
                }
            } else {
                $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
                $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการอ่านข้อมูล (2)';
                $errMessage = $db->error;
                $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
                return;
            }

            array_push($placeList, $place);
        }
        $result->close();
        $response[KEY_DATA_LIST] = $placeList;
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการอ่านข้อมูล (1)';
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
    }
}

function doGetOtopByDistrict()
{
    global $db, $response;

    $district = $_GET['district'];

    $sql = "SELECT * FROM chainat_otop WHERE district = '$district' 
            ORDER BY sub_district, village";
    if ($result = $db->query($sql)) {
        $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
        $response[KEY_ERROR_MESSAGE] = 'อ่านข้อมูลสำเร็จ';
        $response[KEY_ERROR_MESSAGE_MORE] = '';

        $otopList = array();
        while ($row = $result->fetch_assoc()) {
            $otop = array();
            $otop['id'] = (int)$row['id'];
            $otop['name'] = $row['name'];
            $otop['district'] = $row['district'];
            $otop['sub_district'] = $row['sub_district'];
            $otop['village'] = $row['village'];
            $otop['address'] = $row['address'];
            $otop['details'] = $row['details'];
            $otop['price'] = (int)$row['price'];
            $otop['contact_url'] = $row['contact_url'];
            $otop['phone'] = $row['phone'];
            $otop['opening_time'] = $row['opening_time'];
            $otop['latitude'] = floatval($row['latitude']);
            $otop['longitude'] = floatval($row['longitude']);
            $otop['image_list'] = $row['image_list'];
            $otop['image_cover'] = $row['image_cover'];
            $otop['recommend'] = (boolean)$row['recommend'];

            $otop['gallery_images'] = array();

            $sql = "SELECT image_file_name FROM chainat_otop_gallery WHERE otop_id = " . $otop['id'];
            if ($galleryResult = $db->query($sql)) {
                while ($galleryRow = $galleryResult->fetch_assoc()) {
                    array_push($otop['gallery_images'], $galleryRow['image_file_name']);
                }
                $galleryResult->close();

                $sql = "SELECT FORMAT(AVG(rate), 1) AS average_rate FROM chainat_rating 
                WHERE item_id = {$otop['id']} AND item_type = 'otop'";
                if ($ratingResult = $db->query($sql)) {
                    $ratingRow = $ratingResult->fetch_assoc();
                    $averageRate = $ratingRow['average_rate'];
                    if ($averageRate == null) {
                        $otop['average_rate'] = 0;
                    } else {
                        $otop['average_rate'] = floatval($averageRate);
                    }
                } else {
                    $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
                    $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการอ่านข้อมูล (3)';
                    $errMessage = $db->error;
                    $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
                    return;
                }
            } else {
                $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
                $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการอ่านข้อมูล';
                $errMessage = $db->error;
                $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
                return;
            }

            array_push($otopList, $otop);
        }
        $result->close();
        $response[KEY_DATA_LIST] = $otopList;
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการอ่านข้อมูล';
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
    }
}

function doAddRating()
{
    global $db, $response;

    $id = $_POST['id'];
    $type = $_POST['type'];
    $rate = $_POST['rate'];

    $sql = "INSERT INTO chainat_rating (item_id, item_type, rate) 
            VALUES ($id, '$type', $rate)";
    if ($db->query($sql)) {
        $sql = "SELECT FORMAT(AVG(rate), 1) AS average_rate FROM chainat_rating 
                WHERE item_id = $id AND item_type = '$type'";
        if ($result = $db->query($sql)) {
            $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
            $response[KEY_ERROR_MESSAGE] = 'บันทึกข้อมูลสำเร็จ';
            $response[KEY_ERROR_MESSAGE_MORE] = '';

            $row = $result->fetch_assoc();
            $response['average_rate'] = floatval($row['average_rate']);

            $result->close();
        } else {
            $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
            $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล (2)';
            $errMessage = $db->error;
            $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
        }
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล (1)';
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
    }
}

function createRandomString($length)
{
    $key = '';
    $keys = array_merge(range(0, 9), range('a', 'z'));

    for ($i = 0; $i < $length; $i++) {
        $key .= $keys[array_rand($keys)];
    }

    return $key;
}

function moveUploadedFile($key, $dest)
{
    global $response;

    $response['name'] = $_FILES[$key]['name'];
    $response['type'] = $_FILES[$key]['type'];
    $response['size'] = $_FILES[$key]['size'];
    $response['tmp_name'] = $_FILES[$key]['tmp_name'];

    $src = $_FILES[$key]['tmp_name'];
    $response['upload_src'] = $src;

    $response['upload_dest'] = $dest;

    return move_uploaded_file($src, $dest);
}

function getUploadErrorMessage($errCode)
{
    $message = '';
    switch ($errCode) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $message .= 'File too large (limit of ' . get_max_upload() . ' bytes).';
            break;
        case UPLOAD_ERR_PARTIAL:
            $message .= 'File upload was not completed.';
            break;
        case UPLOAD_ERR_NO_FILE:
            $message .= 'Zero-length file uploaded.';
            break;
        default:
            $message .= 'Internal error #' . $errCode;
            break;
    }
    return $message;
}

?>
