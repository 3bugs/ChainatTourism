<?php
session_start();
require_once 'global.php';

define('PLACE_TYPE_TOUR', 'TOUR');
define('PLACE_TYPE_TEMPLE', 'TEMPLE');
define('PLACE_TYPE_RESTAURANT', 'RESTAURANT');
define('PLACE_TYPE_HOTEL', 'HOTEL');
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
    case 'login':
        doLogin();
        break;
    case 'logout':
        doLogout();
        break;
    case 'get_recommend':
        doGetRecommend();
        break;
    case 'get_place':
        doGetPlace();
        break;
    case 'get_otop_by_district':
    case 'search_otop':
        doGetOtop();
        break;
    case 'add_rating':
        doAddRating();
        break;
    case 'add_place':
        doAddPlace();
        break;
    case 'update_place':
        doUpdatePlace();
        break;
    case 'update_place_recommend':
        doUpdatePlaceRecommend();
        break;
    case 'delete_place':
        doDeletePlace();
        break;
    case 'delete_place_asset':
        doDeletePlaceAsset();
        break;
    case 'get_sub_district':
        doGetSubDistrict();
        break;
    case 'get_news':
        doGetNews();
        break;
    case 'add_news':
        doAddNews();
        break;
    case 'update_news':
        doUpdateNews();
        break;
    case 'update_news_active':
        doUpdateNewsActive();
        break;
    case 'delete_news':
        doDeleteNews();
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

function doLogin()
{
    global $db, $response;

    $username = trim($db->real_escape_string($_POST['username']));
    $password = $db->real_escape_string($_POST['password']);

    $sql = "SELECT * FROM ct_user WHERE username = '$username' AND password = MD5('$password')";
    if ($result = $db->query($sql)) {
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $_SESSION[KEY_SESSION_USER_ID] = (int)$row['id'];
            $_SESSION[KEY_SESSION_USER_USERNAME] = $row['username'];
            $_SESSION[KEY_SESSION_USER_DISPLAY_NAME] = $row['display_name'];

            $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
            $response[KEY_ERROR_MESSAGE] = 'เข้าสู่ระบบสำเร็จ';
            $response[KEY_ERROR_MESSAGE_MORE] = '';
        } else {
            $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
            $response[KEY_ERROR_MESSAGE] = 'Username หรือ Password ไม่ถูกต้อง';
            $response[KEY_ERROR_MESSAGE_MORE] = '';
        }
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE] = "เกิดข้อผิดพลาดในการตรวจสอบข้อมูลผู้ใช้งาน: $errMessage";
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
    }
}

function doLogout()
{
    global $response;

    session_destroy();
    $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
    $response[KEY_ERROR_MESSAGE] = 'ออกจากระบบสำเร็จ';
    $response[KEY_ERROR_MESSAGE_MORE] = '';
}

function doGetRecommend()
{
    global $db, $response;

    $placeTypeTour = 'ท่องเที่ยว';
    $placeTypeTemple = 'วัด';

    $sql = "SELECT * FROM ct_place WHERE recommend = 1";
    if ($result = $db->query($sql)) {
        $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
        $response[KEY_ERROR_MESSAGE] = 'อ่านข้อมูลสำเร็จ';
        $response[KEY_ERROR_MESSAGE_MORE] = '';
        $response['sql'] = $sql;
        $response['num_rows'] = $result->num_rows;

        $recommendPlaceList = array();
        $recommendTempleList = array();
        $recommendRestaurantList = array();
        $recommendHotelList = array();
        $recommendOtopList = array();

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
            $place['place_type'] = $row['place_type'];

            $place['gallery_images'] = array();

            $sql = "SELECT image_file_name FROM ct_asset WHERE place_id = " . $place['id'];
            if ($galleryResult = $db->query($sql)) {
                while ($galleryRow = $galleryResult->fetch_assoc()) {
                    array_push($place['gallery_images'], $galleryRow['image_file_name']);
                }
                $galleryResult->close();

                $sql = "SELECT FORMAT(AVG(rate), 1) AS average_rate, COUNT(id) AS count_rate 
                        FROM ct_rating 
                        WHERE item_id = {$place['id']}";
                if ($ratingResult = $db->query($sql)) {
                    $ratingRow = $ratingResult->fetch_assoc();
                    $averageRate = $ratingRow['average_rate'];
                    if ($averageRate == null) {
                        $place['average_rate'] = 0;
                    } else {
                        $place['average_rate'] = floatval($averageRate);
                    }
                    $place['count_rate'] = (int)$ratingRow['count_rate'];
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

            if ($place['place_type'] === 'ท่องเที่ยว') {
                array_push($recommendPlaceList, $place);
            } else if ($place['place_type'] === 'วัด') {
                array_push($recommendTempleList, $place);
            } else if ($place['place_type'] === 'ร้านอาหาร') {
                array_push($recommendRestaurantList, $place);
            } else if ($place['place_type'] === 'ที่พัก') {
                array_push($recommendHotelList, $place);
            } else if ($place['place_type'] === 'otop') {
                array_push($recommendOtopList, $place);
            }
        }

        sortPlaceByRating($recommendPlaceList);
        sortPlaceByRating($recommendTempleList);
        sortPlaceByRating($recommendRestaurantList);
        sortPlaceByRating($recommendHotelList);
        sortPlaceByRating($recommendOtopList);

        $response['place_list'] = $recommendPlaceList;
        $response['temple_list'] = $recommendTempleList;
        $response['restaurant_list'] = $recommendRestaurantList;
        $response['hotel_list'] = $recommendHotelList;
        $response['otop_list'] = $recommendOtopList;
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการอ่านข้อมูล (1)';
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
    }
}

function sortPlaceByRating(&$placeList)
{
    for ($i = 0; $i < count($placeList) - 1; $i++) {
        for ($j = $i + 1; $j < count($placeList); $j++) {
            if ($placeList[$i]['average_rate'] < $placeList[$j]['average_rate']) {
                $temp = $placeList[$i];
                $placeList[$i] = $placeList[$j];
                $placeList[$j] = $temp;
            }
        }
    }
}

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
        case PLACE_TYPE_HOTEL:
            $placeType = 'ที่พัก';
            break;
        case PLACE_TYPE_OTOP:
            $placeType = 'otop';
            break;
    }

    $sql = "SELECT * FROM ct_place WHERE place_type = '$placeType'";
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
            $place['facility_internet'] = (!$row['facility_internet'] || trim($row['facility_internet']) === '') ? null : trim($row['facility_internet']);
            $place['facility_recreation'] = (!$row['facility_recreation'] || trim($row['facility_recreation']) === '') ? null : trim($row['facility_recreation']);
            $place['facility_food'] = (!$row['facility_food'] || trim($row['facility_food']) === '') ? null : trim($row['facility_food']);
            $place['facility_service'] = (!$row['facility_service'] || trim($row['facility_service']) === '') ? null : trim($row['facility_service']);
            $place['place_type'] = $placeType;

            $place['gallery_images'] = array();

            $sql = "SELECT image_file_name FROM ct_asset WHERE place_id = " . $place['id'];
            if ($galleryResult = $db->query($sql)) {
                while ($galleryRow = $galleryResult->fetch_assoc()) {
                    array_push($place['gallery_images'], $galleryRow['image_file_name']);
                }
                $galleryResult->close();

                $sql = "SELECT FORMAT(AVG(rate), 1) AS average_rate, COUNT(id) AS count_rate 
                        FROM ct_rating
                        WHERE item_id = {$place['id']}";
                if ($ratingResult = $db->query($sql)) {
                    $ratingRow = $ratingResult->fetch_assoc();
                    $averageRate = $ratingRow['average_rate'];
                    if ($averageRate == null) {
                        $place['average_rate'] = 0;
                    } else {
                        $place['average_rate'] = floatval($averageRate);
                    }
                    $place['count_rate'] = (int)$ratingRow['count_rate'];
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

        sortPlaceByRating($placeList);
        $response[KEY_DATA_LIST] = $placeList;
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการอ่านข้อมูล (1)';
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
    }
}

function doGetOtop()
{
    global $db, $response;

    $district = $_GET['district'];
    $searchTerm = $_GET['search_term'];

    if (isset($district)) {
        $sql = "SELECT * FROM ct_place 
            WHERE place_type = 'otop' AND district = '$district' 
            ORDER BY sub_district, village";
    } else if (isset($searchTerm)) {
        $sql = "SELECT * FROM ct_place 
            WHERE place_type = 'otop' AND name LIKE '%{$searchTerm}%' 
            ORDER BY district, sub_district, village";
    }
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

            $sql = "SELECT image_file_name FROM ct_asset WHERE place_id = " . $otop['id'];
            if ($galleryResult = $db->query($sql)) {
                while ($galleryRow = $galleryResult->fetch_assoc()) {
                    array_push($otop['gallery_images'], $galleryRow['image_file_name']);
                }
                $galleryResult->close();

                $sql = "SELECT FORMAT(AVG(rate), 1) AS average_rate, COUNT(id) AS count_rate 
                        FROM ct_rating 
                        WHERE item_id = {$otop['id']}";
                if ($ratingResult = $db->query($sql)) {
                    $ratingRow = $ratingResult->fetch_assoc();
                    $averageRate = $ratingRow['average_rate'];
                    if ($averageRate == null) {
                        $otop['average_rate'] = 0;
                    } else {
                        $otop['average_rate'] = floatval($averageRate);
                    }
                    $otop['count_rate'] = (int)$ratingRow['count_rate'];
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
    //$type = $_POST['type'];
    $rate = $_POST['rate'];

    $sql = "INSERT INTO ct_rating (item_id, rate) 
            VALUES ($id, $rate)";
    if ($db->query($sql)) {
        $sql = "SELECT FORMAT(AVG(rate), 1) AS average_rate FROM ct_rating 
                WHERE item_id = $id";
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

function doAddPlace()
{
    global $db, $response;

    $placeType = $db->real_escape_string($_POST['placeType']);
    $name = trim($db->real_escape_string($_POST['name']));
    $district = $db->real_escape_string($_POST['district']);
    $phone = trim($db->real_escape_string($_POST['phone']));
    $openingTime = trim($db->real_escape_string($_POST['openingTime']));
    $address = trim($db->real_escape_string($_POST['address']));
    $latitude = $db->real_escape_string($_POST['latitude']);
    $longitude = $db->real_escape_string($_POST['longitude']);
    $details = trim($db->real_escape_string($_POST['details']));

    $subDistrict = $_POST['subDistrict'] ? $db->real_escape_string($_POST['subDistrict']) : null;
    $village = $_POST['village'] ? $db->real_escape_string($_POST['village']) : null;
    $price = $_POST['price'] ? $db->real_escape_string($_POST['price']) : null;
    $contactUrl = $_POST['contactUrl'] ? $db->real_escape_string($_POST['contactUrl']) : null;

    $facilityInternet = trim($db->real_escape_string($_POST['facilityInternet']));
    $facilityRecreation = trim($db->real_escape_string($_POST['facilityRecreation']));
    $facilityFood = trim($db->real_escape_string($_POST['facilityFood']));
    $facilityService = trim($db->real_escape_string($_POST['facilityService']));

    if (!moveUploadedFile('listImageFile', DIR_IMAGES, $listImageFileName)) {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการอัพโหลดไฟล์ (รูปภาพหน้า List)';
        $response[KEY_ERROR_MESSAGE_MORE] = '';
        return;
    }

    if (!moveUploadedFile('coverImageFile', DIR_IMAGES, $coverImageFileName)) {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการอัพโหลดไฟล์ (รูปภาพ Cover)';
        $response[KEY_ERROR_MESSAGE_MORE] = '';
        return;
    }

    $db->query('START TRANSACTION');

    $sql = null;
    if ($subDistrict && $village) {
        $sql = "INSERT INTO ct_place (name, place_type, district, details, phone, 
                           opening_time, address, latitude, longitude, image_list, image_cover, 
                           sub_district, village, price, contact_url) 
                VALUES ('$name', '$placeType', '$district', '$details', '$phone', 
                        '$openingTime', '$address', $latitude, $longitude, '$listImageFileName', '$coverImageFileName',
                        '$subDistrict', '$village', $price, '$contactUrl')";
    } else {
        $sql = "INSERT INTO ct_place (name, place_type, district, details, phone, 
                            opening_time, address, latitude, longitude, image_list, image_cover, 
                            facility_internet, facility_recreation, facility_food, facility_service) 
                VALUES ('$name', '$placeType', '$district', '$details', '$phone', 
                        '$openingTime', '$address', $latitude, $longitude, '$listImageFileName', '$coverImageFileName',
                        '$facilityInternet', '$facilityRecreation', '$facilityFood', '$facilityService')";
    }
    if ($result = $db->query($sql)) {
        $insertId = $db->insert_id;

        for ($i = 0; $i < sizeof($_FILES[KEY_IMAGE_FILES]['name']); $i++) {
            if ($_FILES[KEY_IMAGE_FILES]['name'][$i] !== '') {
                $fileName = null;

                if (!moveUploadedFile(KEY_IMAGE_FILES, DIR_IMAGES_GALLERY, $fileName, $i)) {
                    $db->query('ROLLBACK');

                    $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
                    $errorValue = $_FILES[KEY_IMAGE_FILES]['error'][$i];
                    $response[KEY_ERROR_MESSAGE] = "เกิดข้อผิดพลาดในการอัพโหลดรูปภาพ [Error: $errorValue]";
                    $response[KEY_ERROR_MESSAGE_MORE] = '';
                    return;
                }

                $sql = "INSERT INTO ct_asset (place_id, image_file_name) 
                    VALUES ($insertId, '$fileName')";
                if (!($insertCourseAssetResult = $db->query($sql))) {
                    $db->query('ROLLBACK');

                    $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
                    $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูลรูปภาพ Gallery: ' . $db->error;
                    $response[KEY_ERROR_MESSAGE_MORE] = '';
                    return;
                }
            }
        }

        $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
        $response[KEY_ERROR_MESSAGE] = 'เพิ่มข้อมูลสำเร็จ';
        $response[KEY_ERROR_MESSAGE_MORE] = '';

        $db->query('COMMIT');
    } else {
        $db->query('ROLLBACK');

        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการเพิ่มข้อมูล: ' . $db->error;
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
    }
}

function doUpdatePlace()
{
    global $db, $response;

    $id = $db->real_escape_string($_POST['placeId']);
    $name = trim($db->real_escape_string($_POST['name']));
    $district = $db->real_escape_string($_POST['district']);
    $phone = trim($db->real_escape_string($_POST['phone']));
    $openingTime = trim($db->real_escape_string($_POST['openingTime']));
    $address = trim($db->real_escape_string($_POST['address']));
    $latitude = $db->real_escape_string($_POST['latitude']);
    $longitude = $db->real_escape_string($_POST['longitude']);
    $details = trim($db->real_escape_string($_POST['details']));

    $subDistrict = $_POST['subDistrict'] ? $db->real_escape_string($_POST['subDistrict']) : null;
    $village = $_POST['village'] ? $db->real_escape_string($_POST['village']) : null;
    $price = $_POST['price'] ? $db->real_escape_string($_POST['price']) : null;
    $contactUrl = $_POST['contactUrl'] ? $db->real_escape_string($_POST['contactUrl']) : null;

    $facilityInternet = trim($db->real_escape_string($_POST['facilityInternet']));
    $facilityRecreation = trim($db->real_escape_string($_POST['facilityRecreation']));
    $facilityFood = trim($db->real_escape_string($_POST['facilityFood']));
    $facilityService = trim($db->real_escape_string($_POST['facilityService']));

    $listImageFileName = NULL;
    if ($_FILES['listImageFile']['name'] !== '') {
        if (!moveUploadedFile('listImageFile', DIR_IMAGES, $listImageFileName)) {
            $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
            $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการอัพโหลดไฟล์ (รูปภาพหน้า List)';
            $response[KEY_ERROR_MESSAGE_MORE] = '';
            return;
        }
    }
    $setListFileName = $listImageFileName ? "image_list = '$listImageFileName', " : '';

    $coverImageFileName = NULL;
    if ($_FILES['coverImageFile']['name'] !== '') {
        if (!moveUploadedFile('coverImageFile', DIR_IMAGES, $coverImageFileName)) {
            $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
            $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการอัพโหลดไฟล์ (รูปภาพ Cover)';
            $response[KEY_ERROR_MESSAGE_MORE] = '';
            return;
        }
    }
    $setCoverFileName = $coverImageFileName ? "image_cover = '$coverImageFileName', " : '';

    $db->query('START TRANSACTION');

    if ($subDistrict && $village) {
        $sql = "UPDATE ct_place 
                SET $setListFileName $setCoverFileName 
                    name = '$name', district = '$district', details = '$details', phone = '$phone', 
                    opening_time = '$openingTime', address = '$address', latitude = $latitude, longitude = '$longitude',
                    sub_district = '$subDistrict', village = '$village', price = $price, contact_url = '$contactUrl'
                WHERE id = $id";
    } else {
        $sql = "UPDATE ct_place 
                SET $setListFileName $setCoverFileName 
                    name = '$name', district = '$district', details = '$details', phone = '$phone', 
                    opening_time = '$openingTime', address = '$address', latitude = $latitude, longitude = '$longitude',
                    facility_internet = '$facilityInternet', facility_recreation = '$facilityRecreation', 
                    facility_food = '$facilityFood', facility_service = '$facilityService' 
                WHERE id = $id";
    }
    if ($result = $db->query($sql)) {
        for ($i = 0; $i < sizeof($_FILES[KEY_IMAGE_FILES]['name']); $i++) {
            if ($_FILES[KEY_IMAGE_FILES]['name'][$i] !== '') {
                $fileName = null;

                if (!moveUploadedFile(KEY_IMAGE_FILES, DIR_IMAGES_GALLERY, $fileName, $i)) {
                    $db->query('ROLLBACK');

                    $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
                    $errorValue = $_FILES[KEY_IMAGE_FILES]['error'][$i];
                    $response[KEY_ERROR_MESSAGE] = "เกิดข้อผิดพลาดในการอัพโหลดรูปภาพ [Error: $errorValue]";
                    $response[KEY_ERROR_MESSAGE_MORE] = '';
                    return;
                }

                $sql = "INSERT INTO ct_asset (place_id, image_file_name) 
                    VALUES ($id, '$fileName')";
                if (!($insertCourseAssetResult = $db->query($sql))) {
                    $db->query('ROLLBACK');

                    $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
                    $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูลรูปภาพ Gallery: ' . $db->error;
                    $response[KEY_ERROR_MESSAGE_MORE] = '';
                    return;
                }
            }
        }

        $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
        $response[KEY_ERROR_MESSAGE] = 'แก้ไขข้อมูลสำเร็จ';
        $response[KEY_ERROR_MESSAGE_MORE] = '';

        $db->query('COMMIT');
    } else {
        $db->query('ROLLBACK');

        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการแก้ไขข้อมูล: ' . $db->error;
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
    }
}

function doDeletePlace()
{
    global $db, $response;

    $id = $db->real_escape_string($_POST['id']);

    $deleteNewsSql = "DELETE FROM ct_place WHERE id = $id";

    if ($deleteResult = $db->query($deleteNewsSql)) {
        $deletePlaceAssetsSql = "DELETE FROM ct_asset WHERE place_id = $id";

        if ($deletePlaceAssetsResult = $db->query($deletePlaceAssetsSql)) {
            $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
            $response[KEY_ERROR_MESSAGE] = 'ลบข้อมูลสำเร็จ';
            $response[KEY_ERROR_MESSAGE_MORE] = '';
        } else {
            $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
            $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการลบข้อมูล (2): ' . $db->error;
            $errMessage = $db->error;
            $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $deletePlaceAssetsSql";
        }
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการลบข้อมูล (1): ' . $db->error;
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $deleteNewsSql";
    }
}

function doDeletePlaceAsset()
{
    global $db, $response;

    $assetId = $db->real_escape_string($_POST['assetId']);

    $sql = "DELETE FROM ct_asset WHERE id = $assetId";
    if ($result = $db->query($sql)) {
        $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
        $response[KEY_ERROR_MESSAGE] = 'ลบข้อมูลสำเร็จ';
        $response[KEY_ERROR_MESSAGE_MORE] = '';
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_SQL_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการลบข้อมูล';
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
    }
}

function doUpdatePlaceRecommend()
{
    global $db, $response;

    $placeId = $db->real_escape_string($_POST['placeId']);
    $recommend = $db->real_escape_string($_POST['recommend']);

    $sql = "UPDATE ct_place SET recommend = $recommend WHERE id = $placeId";
    if ($result = $db->query($sql)) {
        $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
        $response[KEY_ERROR_MESSAGE] = 'อัพเดทสถานะปักหมุดข่าวสำเร็จ';
        $response[KEY_ERROR_MESSAGE_MORE] = '';
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $db->error;
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
    }
}

function doGetSubDistrict()
{
    global $db, $response;

    $district = $_GET['district'];

    $response[KEY_DATA_LIST] = array();
    $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
    $response[KEY_ERROR_MESSAGE] = 'อ่านข้อมูลสำเร็จ';

    switch ($district) {
        case 'เมืองชัยนาท':
            $subDistrict = array();
            $subDistrict['key'] = 'ชัยนาท';
            $subDistrict['name'] = 'ชัยนาท';
            array_push($response[KEY_DATA_LIST], $subDistrict);

            $subDistrict = array();
            $subDistrict['key'] = 'ธรรมามูล';
            $subDistrict['name'] = 'ธรรมามูล';
            array_push($response[KEY_DATA_LIST], $subDistrict);
            break;
        case 'หันคา':
            $subDistrict = array();
            $subDistrict['key'] = 'บ้านเชี่ยน';
            $subDistrict['name'] = 'บ้านเชี่ยน';
            array_push($response[KEY_DATA_LIST], $subDistrict);
            break;
        case 'สรรพยา':
            $subDistrict = array();
            $subDistrict['key'] = 'หาดอาสา';
            $subDistrict['name'] = 'หาดอาสา';
            array_push($response[KEY_DATA_LIST], $subDistrict);
            break;
        case 'เนินขาม':
            $subDistrict = array();
            $subDistrict['key'] = 'เนินขาม';
            $subDistrict['name'] = 'เนินขาม';
            array_push($response[KEY_DATA_LIST], $subDistrict);
            break;
        case 'มโนรมย์':
            $subDistrict = array();
            $subDistrict['key'] = 'ศิลาดาน';
            $subDistrict['name'] = 'ศิลาดาน';
            array_push($response[KEY_DATA_LIST], $subDistrict);
            break;
        case 'สรรคบุรี':
            $subDistrict = array();
            $subDistrict['key'] = 'แพรกศรีราชา';
            $subDistrict['name'] = 'แพรกศรีราชา';
            array_push($response[KEY_DATA_LIST], $subDistrict);

            $subDistrict = array();
            $subDistrict['key'] = 'บางขุด';
            $subDistrict['name'] = 'บางขุด';
            array_push($response[KEY_DATA_LIST], $subDistrict);
            break;
        case 'วัดสิงห์':
            $subDistrict = array();
            $subDistrict['key'] = 'มะขามเฒ่า';
            $subDistrict['name'] = 'มะขามเฒ่า';
            array_push($response[KEY_DATA_LIST], $subDistrict);
            break;
        case 'หนองมะโมง':
            $subDistrict = array();
            $subDistrict['key'] = 'กุดจอก';
            $subDistrict['name'] = 'กุดจอก';
            array_push($response[KEY_DATA_LIST], $subDistrict);

            $subDistrict = array();
            $subDistrict['key'] = 'วังตะเคียน';
            $subDistrict['name'] = 'วังตะเคียน';
            array_push($response[KEY_DATA_LIST], $subDistrict);
            break;
    }
}

function doGetNews()
{
    global $db, $response;

    $sql = "SELECT * FROM ct_news WHERE active = 1";
    if ($result = $db->query($sql)) {
        $newsList = array();
        while ($row = $result->fetch_assoc()) {
            $news = array();
            $news['id'] = (int)$row['id'];
            $news['title'] = $row['title'];
            $news['image'] = $row['image'];

            array_push($newsList, $news);
        }
        $result->close();

        $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
        $response[KEY_ERROR_MESSAGE] = 'อ่านข้อมูลสำเร็จ';
        $response[KEY_ERROR_MESSAGE_MORE] = '';
        $response[KEY_DATA_LIST] = $newsList;
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการอ่านข้อมูล';
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
    }
}

function doAddNews()
{
    global $db, $response;

    $title = trim($db->real_escape_string($_POST['title']));

    if (!moveUploadedFile('imageFile', DIR_IMAGES, $imageFileName)) {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการอัพโหลดรูปภาพข่าว)';
        $response[KEY_ERROR_MESSAGE_MORE] = '';
        return;
    }

    $db->query('START TRANSACTION');

    $sql = "INSERT INTO ct_news (title, image, active) 
            VALUES ('$title', '$imageFileName', 1)";

    if ($result = $db->query($sql)) {
        $insertId = $db->insert_id;

        $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
        $response[KEY_ERROR_MESSAGE] = 'เพิ่มข้อมูลสำเร็จ';
        $response[KEY_ERROR_MESSAGE_MORE] = '';

        $db->query('COMMIT');
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการเพิ่มข้อมูล: ' . $db->error;
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";

        $db->query('ROLLBACK');
    }
}

function doUpdateNews()
{
    global $db, $response;

    $id = $db->real_escape_string($_POST['newsId']);
    $title = trim($db->real_escape_string($_POST['title']));

    $imageFileName = NULL;
    if ($_FILES['imageFile']['name'] !== '') {
        if (!moveUploadedFile('imageFile', DIR_IMAGES, $imageFileName)) {
            $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
            $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการอัพโหลดรูปภาพข่าว)';
            $response[KEY_ERROR_MESSAGE_MORE] = '';
            return;
        }
    }
    $setImageFileName = $imageFileName ? "image = '$imageFileName', " : '';

    $db->query('START TRANSACTION');

    $sql = "UPDATE ct_news 
            SET $setImageFileName 
                title = '$title'
            WHERE id = $id";

    if ($result = $db->query($sql)) {
        $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
        $response[KEY_ERROR_MESSAGE] = 'แก้ไขข้อมูลสำเร็จ';
        $response[KEY_ERROR_MESSAGE_MORE] = '';

        $db->query('COMMIT');
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการแก้ไขข้อมูล: ' . $db->error;
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";

        $db->query('ROLLBACK');
    }
}

function doUpdateNewsActive()
{
    global $db, $response;

    $id = $db->real_escape_string($_POST['newsId']);
    $active = $db->real_escape_string($_POST['active']);

    $sql = "UPDATE ct_news SET active = $active WHERE id = $id";
    if ($result = $db->query($sql)) {
        $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
        $response[KEY_ERROR_MESSAGE] = 'อัพเดทสถานะการแสดงข่าวสำเร็จ';
        $response[KEY_ERROR_MESSAGE_MORE] = '';
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $db->error;
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
    }
}

function doDeleteNews()
{
    global $db, $response;

    $id = $db->real_escape_string($_POST['id']);

    $deleteNewsSql = "DELETE FROM ct_news WHERE id = $id";

    if ($deleteResult = $db->query($deleteNewsSql)) {
        $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
        $response[KEY_ERROR_MESSAGE] = 'ลบข้อมูลสำเร็จ';
        $response[KEY_ERROR_MESSAGE_MORE] = '';
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการลบข้อมูล' . $db->error;
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $deleteNewsSql";
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

function moveUploadedFile($key, $dest, &$randomFileName, $index = -1)
{
    global $response;

    $clientName = $index === -1 ? $_FILES[$key]['name'] : $_FILES[$key]['name'][$index];
    $response['name'] = $clientName;
    $response['type'] = $index === -1 ? $_FILES[$key]['type'] : $_FILES[$key]['type'][$index];
    $response['size'] = $index === -1 ? $_FILES[$key]['size'] : $_FILES[$key]['size'][$index];
    $response['tmp_name'] = $index === -1 ? $_FILES[$key]['tmp_name'] : $_FILES[$key]['tmp_name'][$index];

    $src = $index === -1 ? $_FILES[$key]['tmp_name'] : $_FILES[$key]['tmp_name'][$index];
    $response['upload_src'] = $src;
    $response['upload_dest'] = $dest;

    //$date = date('Y-m-d H:i:s');
    //$timestamp = time();
    $timestamp = round(microtime(true) * 1000);
    $randomFileName = "{$timestamp}-{$clientName}";
    return move_uploaded_file($src, "{$dest}{$randomFileName}");
}

function moveUploadedFile_Old($key, $dest)
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
