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
            } else {
                $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
                $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการอ่านข้อมูล';
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
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการอ่านข้อมูล';
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
    }
}

function doSaveFarmInfo()
{
    global $db, $response;

    $farmName = $db->real_escape_string($_POST['farmName']);
    $address = $db->real_escape_string($_POST['address']);
    $subDistrict = $db->real_escape_string($_POST['subDistrict']);
    $district = $db->real_escape_string($_POST['district']);
    $province = $db->real_escape_string($_POST['province']);
    $postalCode = $db->real_escape_string($_POST['postalCode']);
    $farmRegId = $db->real_escape_string($_POST['farmRegId']);

    $selectExistingFarmSQL = "SELECT * FROM `farm`";
    $selectExistingFarmResult = $db->query($selectExistingFarmSQL);
    $count = $selectExistingFarmResult->num_rows;
    $selectExistingFarmResult->close();
    if ($count > 0) {
        $sql = "UPDATE `farm` SET name='$farmName', address='$address', sub_district='$subDistrict', district='$district', province='$province', postal_code='$postalCode', farm_reg_id='$farmRegId'";
    } else {
        $sql = "INSERT INTO `farm` (name, address, sub_district, district, province, postal_code, farm_reg_id) VALUES ('$farmName', '$address', '$subDistrict', '$district', '$province', '$postalCode', '$farmRegId')";
    }
    if ($db->query($sql)) {
        $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
        $response[KEY_ERROR_MESSAGE] = 'บันทึกข้อมูลฟาร์มสำเร็จ';
        $response[KEY_ERROR_MESSAGE_MORE] = '';
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูลฟาร์ม';
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
    }
}

function doGetPond()
{
    global $db, $response;

    $sql = "SELECT * FROM `pond` ORDER BY `number`";
    if ($result = $db->query($sql)) {
        $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
        $response[KEY_ERROR_MESSAGE] = 'อ่านข้อมูลบ่อเลี้ยงสำเร็จ';
        $response[KEY_ERROR_MESSAGE_MORE] = '';

        $pondList = array();
        while ($row = $result->fetch_assoc()) {
            $pond = array();
            $pond['id'] = (int)$row['id'];
            $pond['number'] = (int)$row['number'];
            $pond['area'] = (int)$row['area'];
            $pond['initial_shrimp_count'] = (int)$row['initial_shrimp_count'];
            array_push($pondList, $pond);
        }
        $result->close();
        $response[KEY_DATA_LIST] = $pondList;
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการอ่านข้อมูลบ่อเลี้ยง';
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
    }
}

function doAddPond()
{
    global $db, $response;

    $pondNumber = $db->real_escape_string($_POST['pondNumber']);
    $pondArea = $db->real_escape_string($_POST['pondArea']);
    $initialShrimpCount = $db->real_escape_string($_POST['initialShrimpCount']);

    if ($pondNumber <= 0) {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'หมายเลขบ่อต้องเป็นเลขบวก (1, 2, 3, ...)';
        $response[KEY_ERROR_MESSAGE_MORE] = '';
        return;
    }

    $selectExistingPondNumberSQL = "SELECT * FROM `pond` WHERE `number`=$pondNumber";
    $selectExistingPondNumberResult = $db->query($selectExistingPondNumberSQL);
    $count = $selectExistingPondNumberResult->num_rows;
    $selectExistingPondNumberResult->close();
    if ($count > 0) {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'ไม่สามารถเพิ่มข้อมูลบ่อได้ เนื่องจากมีหมายเลขบ่อที่ระบุแล้ว (หมายเลขบ่อห้ามซ้ำ)';
        $response[KEY_ERROR_MESSAGE_MORE] = '';
    } else {
        $sql = "INSERT INTO `pond` (number, area, initial_shrimp_count) VALUES ($pondNumber, $pondArea, $initialShrimpCount)";
        if ($db->query($sql)) {
            $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
            $response[KEY_ERROR_MESSAGE] = 'เพิ่มข้อมูลบ่อเลี้ยงสำเร็จ';
            $response[KEY_ERROR_MESSAGE_MORE] = '';
        } else {
            $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
            $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูลบ่อเลี้ยง';
            $errMessage = $db->error;
            $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
        }
    }
}

function doUpdatePond()
{
    global $db, $response;

    $pondId = (int)$db->real_escape_string($_POST['pondId']);
    $pondNumber = $db->real_escape_string($_POST['pondNumber']);
    $pondArea = $db->real_escape_string($_POST['pondArea']);
    $initialShrimpCount = $db->real_escape_string($_POST['initialShrimpCount']);

    $selectExistingPondNumberSQL = "SELECT * FROM `pond` WHERE `number`=$pondNumber";
    $selectExistingPondNumberResult = $db->query($selectExistingPondNumberSQL);
    if ($selectExistingPondNumberResult->num_rows > 0) {
        $row = $selectExistingPondNumberResult->fetch_assoc();
        if ($pondId !== (int)$row['id']) {
            $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
            $response[KEY_ERROR_MESSAGE] = 'ไม่สามารถบันทึกข้อมูลบ่อได้ เนื่องจากมีหมายเลขบ่อที่ระบุแล้ว (หมายเลขบ่อห้ามซ้ำ)';
            $response[KEY_ERROR_MESSAGE_MORE] = '';
            $selectExistingPondNumberResult->close();
            return;
        }
    }
    $selectExistingPondNumberResult->close();

    $sql = "UPDATE `pond` SET number=$pondNumber, area=$pondArea, initial_shrimp_count=$initialShrimpCount WHERE id=$pondId";
    if ($db->query($sql)) {
        $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
        $response[KEY_ERROR_MESSAGE] = 'แก้ไขข้อมูลบ่อเลี้ยงสำเร็จ';
        $response[KEY_ERROR_MESSAGE_MORE] = '';
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูลบ่อเลี้ยง';
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
    }
}

function doDeletePond()
{
    global $db, $response;

    $pondId = $db->real_escape_string($_POST['pondId']);

    $sql = "DELETE FROM `pond` WHERE id=$pondId";
    if ($db->query($sql)) {
        $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
        $response[KEY_ERROR_MESSAGE] = 'ลบข้อมูลบ่อเลี้ยงสำเร็จ';
        $response[KEY_ERROR_MESSAGE_MORE] = '';
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการลบข้อมูลบ่อเลี้ยง';
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
    }
}

function doGetFeedingByPond()
{
    global $db, $response;

    $pondId = $db->real_escape_string($_POST['pondId']);

    $sql = "SELECT * FROM `feeding` WHERE `pond_id`=$pondId ORDER BY `feed_date` DESC";
    if ($result = $db->query($sql)) {
        $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
        $response[KEY_ERROR_MESSAGE] = 'อ่านข้อมูลการให้อาหารสำเร็จ';
        $response[KEY_ERROR_MESSAGE_MORE] = '';

        $feedingList = array();
        while ($row = $result->fetch_assoc()) {
            $feeding = array();
            $feeding['id'] = (int)$row['id'];
            $feeding['pond_id'] = (int)$row['pond_id'];
            $feeding['feed_date'] = $row['feed_date'];
            $feeding['first_feed'] = (int)$row['first_feed'];
            $feeding['second_feed'] = (int)$row['second_feed'];
            $feeding['third_feed'] = (int)$row['third_feed'];
            array_push($feedingList, $feeding);
        }
        $response[KEY_DATA_LIST] = $feedingList;
        $result->close();
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการอ่านข้อมูลการให้อาหาร';
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
    }
}

function doAddFeeding()
{
    global $db, $response;

    $pondId = $db->real_escape_string($_POST['pondId']);
    $feedDate = $db->real_escape_string($_POST['feedDate']);
    $firstFeed = $db->real_escape_string($_POST['firstFeed']);
    $secondFeed = $db->real_escape_string($_POST['secondFeed']);
    $thirdFeed = $db->real_escape_string($_POST['thirdFeed']);

    $selectExistingFeedDateSql = "SELECT id FROM `feeding` WHERE feed_date='$feedDate'";
    if ($selectExistingFeedDateResult = $db->query($selectExistingFeedDateSql)) {
        if ($selectExistingFeedDateResult->num_rows > 0) {
            $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
            $response[KEY_ERROR_MESSAGE] = 'มีข้อมูลการให้อาหารในวันดังกล่าวแล้ว กรุณาย้อนไปหน้าแสดงรายการ แล้วกดที่วันที่นั้นหากต้องการกรอกมื้ออาหารที่ยังไม่ได้กรอก';
            $response[KEY_ERROR_MESSAGE_MORE] = '';
            $selectExistingFeedDateResult->close();
            return;
        }
        $selectExistingFeedDateResult->close();

        $sql = "INSERT INTO `feeding` (`pond_id`, `feed_date`, `first_feed`, `second_feed`, `third_feed`) "
            . " VALUES ($pondId, '$feedDate', $firstFeed, $secondFeed, $thirdFeed)";
        if ($result = $db->query($sql)) {
            $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
            $response[KEY_ERROR_MESSAGE] = 'บันทึกข้อมูลการให้อาหารสำเร็จ';
            $response[KEY_ERROR_MESSAGE_MORE] = '';
        } else {
            $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
            $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูลการให้อาหาร';
            $errMessage = $db->error;
            $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
        }
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูลการให้อาหาร';
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $selectExistingFeedDateSql";
    }
}

function doUpdateFeeding()
{
    global $db, $response;

    $feedingId = $db->real_escape_string($_POST['feedingId']);
    $firstFeed = $db->real_escape_string($_POST['firstFeed']);
    $secondFeed = $db->real_escape_string($_POST['secondFeed']);
    $thirdFeed = $db->real_escape_string($_POST['thirdFeed']);

    $sql = "UPDATE `feeding` SET `first_feed`=$firstFeed, `second_feed`=$secondFeed, `third_feed`=$thirdFeed "
        . " WHERE `id`=$feedingId";
    if ($result = $db->query($sql)) {
        $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
        $response[KEY_ERROR_MESSAGE] = 'บันทึกข้อมูลการให้อาหารสำเร็จ';
        $response[KEY_ERROR_MESSAGE_MORE] = '';
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูลการให้อาหาร';
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
    }
}

function doGetSummary()
{
    global $db, $response;

    $pondId = $db->real_escape_string($_POST['pondId']);

    try {
        $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
        $response[KEY_ERROR_MESSAGE] = 'อ่านข้อมูลสำเร็จ';
        $response[KEY_ERROR_MESSAGE_MORE] = '';

        $sql = "SELECT * FROM farm";
        $result = $db->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $summary['farm_name'] = $row['name'];
        } else {
            $summary['farm_name'] = null;
        }
        $result->close();

        $sql = "SELECT * FROM pond WHERE id=$pondId";
        $result = $db->query($sql);
        $row = $result->fetch_assoc();
        $pondId = (int)$row['id'];
        $summary['pond_number'] = (int)$row['number'];
        $summary['pond_area'] = (int)$row['area']; //ขนาดบ่อ
        $summary['shrimp_count'] = (int)$row['initial_shrimp_count']; //จำนวนกุ้งที่ปล่อย
        $summary['final_weight'] = (int)$row['final_weight']; //น้ำหนักกุ้งที่จับได้
        $summary['cost'] = (int)$row['cost']; //ค่าใช้จ่าย
        $summary['sale_price'] = (int)$row['sale_price']; //ราคากุ้งที่ขายได้
        $result->close();

        $sql = "SELECT MIN(feed_date) AS begin_date, MAX(feed_date) AS end_date FROM feeding "
            . " WHERE pond_id=$pondId";
        $result = $db->query($sql);
        $row = $result->fetch_assoc();
        $beginDate = $row['begin_date'];
        $endDate = $row['end_date'];
        $summary['begin_date'] = $beginDate; //วันที่ปล่อยกุ้ง
        $summary['end_date'] = $endDate; //วันที่จับกุ้ง
        if ($beginDate != null && $endDate != null) {
            $diff = date_diff(date_create($beginDate), date_create($endDate));
            $summary['period'] = (int)($diff->format("%a")); //ระยะเวลาการเลี้ยง
        } else {
            $summary['period'] = 0;
        }
        $result->close();

        $sql = "SELECT SUM(first_feed) AS sum1, SUM(second_feed) AS sum2, SUM(third_feed) AS sum3 FROM feeding"
            . " WHERE pond_id=$pondId";
        $result = $db->query($sql);
        $row = $result->fetch_assoc();
        $sum1 = (int)$row['sum1'];
        $sum2 = (int)$row['sum2'];
        $sum3 = (int)$row['sum3'];
        $summary['feed'] = $sum1 + $sum2 + $sum3; //ปริมาณอาหารที่ใช้

        $response['summary'] = $summary;
    } catch (Exception $e) {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล';
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
    }
}

function doUpdateSummary()
{
    global $db, $response;

    $pondId = $db->real_escape_string($_POST['pondId']);
    $finalWeight = $db->real_escape_string($_POST['finalWeight']);
    $cost = $db->real_escape_string($_POST['cost']);
    $salePrice = $db->real_escape_string($_POST['salePrice']);

    $sql = "UPDATE `pond` SET `final_weight`=$finalWeight, `cost`=$cost, `sale_price`=$salePrice "
        . " WHERE `id`=$pondId";
    if ($result = $db->query($sql)) {
        $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
        $response[KEY_ERROR_MESSAGE] = 'บันทึกข้อมูลสำเร็จ';
        $response[KEY_ERROR_MESSAGE_MORE] = '';
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
    }
}

function doGetWaterQualityByPondAndDate()
{
    global $db, $response;

    $pondId = $db->real_escape_string($_POST['pondId']);
    $testDate = $db->real_escape_string($_POST['testDate']);

    $sql = "SELECT * FROM `water_quality` WHERE `pond_id`=$pondId AND `test_date`='$testDate'";
    if ($result = $db->query($sql)) {
        $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
        $response[KEY_ERROR_MESSAGE] = 'อ่านข้อมูลคุณภาพน้ำสำเร็จ';
        $response[KEY_ERROR_MESSAGE_MORE] = '';

        $waterQualityList = array();
        while ($row = $result->fetch_assoc()) {
            $waterQuality = array();
            $waterQuality['id'] = (int)$row['id'];
            $waterQuality['pond_id'] = (int)$row['pond_id'];
            $waterQuality['test_date'] = $row['test_date'];
            $waterQuality['ph_morning'] = floatval($row['ph_morning']);
            $waterQuality['ph_evening'] = floatval($row['ph_evening']);
            $waterQuality['salty'] = floatval($row['salty']);
            $waterQuality['ammonia'] = floatval($row['ammonia']);
            $waterQuality['nitrite'] = floatval($row['nitrite']);
            $waterQuality['alkaline'] = floatval($row['alkaline']);
            $waterQuality['calcium'] = floatval($row['calcium']);
            $waterQuality['magnesium'] = floatval($row['magnesium']);
            array_push($waterQualityList, $waterQuality);
        }
        $response[KEY_DATA_LIST] = $waterQualityList;
        $result->close();
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการอ่านข้อมูลคุณภาพน้ำ';
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
    }
}

function doAddWaterQuality()
{
    global $db, $response;

    $pondId = $db->real_escape_string($_POST['pondId']);
    $testDate = $db->real_escape_string($_POST['testDate']);
    $phMorning = $db->real_escape_string($_POST['phMorning']);
    $phEvening = $db->real_escape_string($_POST['phEvening']);
    $salty = $db->real_escape_string($_POST['salty']);
    $ammonia = $db->real_escape_string($_POST['ammonia']);
    $nitrite = $db->real_escape_string($_POST['nitrite']);
    $alkaline = $db->real_escape_string($_POST['alkaline']);
    $calcium = $db->real_escape_string($_POST['calcium']);
    $magnesium = $db->real_escape_string($_POST['magnesium']);

    $modeUpdate = FALSE;

    $sql = "SELECT id FROM `water_quality` WHERE `pond_id`=$pondId AND `test_date`='$testDate'";
    if ($result = $db->query($sql)) {
        if ($result->num_rows > 0) {
            $modeUpdate = TRUE;
        }
        $result->close();
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูลคุณภาพน้ำ';
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
        return;
    }

    if ($modeUpdate) {
        $sql = "UPDATE `water_quality` SET `ph_morning`=$phMorning, `ph_evening`=$phEvening, `salty`=$salty, `ammonia`=$ammonia, `nitrite`=$nitrite, `alkaline`=$alkaline, `calcium`=$calcium, `magnesium`=$magnesium "
            . " WHERE `pond_id`=$pondId AND `test_date`='$testDate'";
    } else {
        $sql = "INSERT INTO `water_quality` (`pond_id`, `test_date`, `ph_morning`, `ph_evening`, `salty`, `ammonia`, `nitrite`, `alkaline`, `calcium`, `magnesium`) "
            . " VALUES ($pondId, '$testDate', $phMorning, $phEvening, $salty, $ammonia, $nitrite, $alkaline, $calcium, $magnesium)";
    }
    if ($result = $db->query($sql)) {
        $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
        $response[KEY_ERROR_MESSAGE] = 'บันทึกข้อมูลคุณภาพน้ำสำเร็จ';
        $response[KEY_ERROR_MESSAGE_MORE] = '';
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูลคุณภาพน้ำ';
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
    }
}

function doGetHatchery()
{
    global $db, $response;

    $sql = "SELECT * FROM `hatchery`";
    if ($result = $db->query($sql)) {
        $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
        $response[KEY_ERROR_MESSAGE] = 'อ่านข้อมูลแหล่งพันธุ์ลูกกุ้งสำเร็จ';
        $response[KEY_ERROR_MESSAGE_MORE] = '';

        $hatcheryList = array();
        while ($row = $result->fetch_assoc()) {
            $hatchery = array();
            $hatchery['id'] = (int)$row['id'];
            $hatchery['name'] = $row['name'];
            $hatchery['address'] = $row['address'];
            $hatchery['sub_district'] = $row['sub_district'];
            $hatchery['district'] = $row['district'];
            $hatchery['province'] = $row['province'];
            $hatchery['postal_code'] = $row['postal_code'];
            $hatchery['owner'] = $row['owner'];
            $hatchery['fmd_no'] = $row['fmd_no'];
            $hatchery['created_at'] = $row['created_at'];

            array_push($hatcheryList, $hatchery);
        }
        $response[KEY_DATA_LIST] = $hatcheryList;
        $result->close();
    } else {
        $response[KEY_ERROR_CODE] = ERROR_CODE_ERROR;
        $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการอ่านข้อมูลแหล่งพันธุ์ลูกกุ้ง';
        $errMessage = $db->error;
        $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $sql";
    }
}

function doUpdateProfileImage($userType)
{
    global $db, $response;

    $table = '';
    if ($userType == USER_TYPE_ELDERLY) {
        $table = 'elderly';
    } else if ($userType == USER_TYPE_EMPLOYER) {
        $table = 'employer';
    }

    $userId = $_POST['user_id'];

    $imageFilename = createRandomString(16) . '.jpg';
    $dest = '../images/user/' . $imageFilename;

    if (!moveUploadedFile('profile_image', $dest)) {
        $response[KEY_ERROR_CODE] = 9; //ERROR_CODE_FILE_UPLOAD_ERROR;
        $response[KEY_ERROR_MESSAGE] = "เกิดข้อผิดพลาดในการบันทึกไฟล์รูปภาพ";
        $response[KEY_ERROR_MESSAGE_MORE] = getUploadErrorMessage($_FILES['profile_image']['error']);
    } else {
        $updateUserSql = "UPDATE `$table` SET image = '$imageFilename' WHERE id = $userId ";
        $updateUserResult = $db->query($updateUserSql);
        if ($updateUserResult) {
            $selectUserSql = "SELECT * FROM `$table` WHERE id = $userId ";
            $selectUserResult = $db->query($selectUserSql);

            if ($selectUserResult) {
                $response[KEY_ERROR_CODE] = ERROR_CODE_SUCCESS;
                $response[KEY_ERROR_MESSAGE] = '';
                $response[KEY_ERROR_MESSAGE_MORE] = '';
                $response['user'] = fetchUser($userType, $selectUserResult);
            } else {
                $response[KEY_ERROR_CODE] = ERROR_CODE_SQL_ERROR;
                $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล';
                $errMessage = $db->error;
                $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $selectUserSql";
            }
        } else {
            $response[KEY_ERROR_CODE] = ERROR_CODE_SQL_ERROR;
            $response[KEY_ERROR_MESSAGE] = 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล';
            $errMessage = $db->error;
            $response[KEY_ERROR_MESSAGE_MORE] = "$errMessage\nSQL: $updateUserSql";
        }
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
