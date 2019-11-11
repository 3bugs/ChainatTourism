<?php
require_once '../include/head_php.inc';

$placeId = $_GET['place_id'];

$placeType = $_GET['place_type'];
$placeTypeList = array(
    'tour', 'temple', 'restaurant', 'otop'
);
if (!isset($placeType) || !in_array($placeType, $placeTypeList)) {
    echo "Invalid place type '$placeType' - ระบุประเภทสถานที่ไม่ถูกต้อง";
    $db->close();
    exit();
}

$pageTitles['tour'] = 'สถานที่ท่องเที่ยว';
$pageTitles['temple'] = 'วัด';
$pageTitles['restaurant'] = 'ร้านอาหาร';
$pageTitles['otop'] = 'สินค้า OTOP';

$placeTypeKeys['tour'] = 'ท่องเที่ยว';
$placeTypeKeys['temple'] = 'วัด';
$placeTypeKeys['restaurant'] = 'ร้านอาหาร';
$placeTypeKeys['otop'] = 'otop';

$pageTitle = $pageTitles[$placeType];
$placeTypeKey = $placeTypeKeys[$placeType];

$place = array();
if (isset($placeId)) {
    $placeId = $db->real_escape_string($placeId);

    $sql = "SELECT * FROM ct_place WHERE id = $placeId";

    if ($result = $db->query($sql)) {
        if ($result->num_rows > 0) {
            $place = $result->fetch_assoc();
        } else {
            echo 'ไม่พบข้อมูล';
            $result->close();
            $db->close();
            exit();
        }
        $result->close();
    } else {
        echo 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: ' . $db->error;
        $db->close();
        exit();
    }
}

$imageList = array();
if (isset($placeId)) {
    $sql = "SELECT * 
            FROM ct_asset 
            WHERE place_id = $placeId";
    if ($result = $db->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $asset = array();
            $asset['id'] = (int)$row['id'];
            $asset['image_file_name'] = $row['image_file_name'];
            $asset['created_at'] = $row['created_at'];

            /*$prefixPosition = strpos($row['file_name'], '-');
            $extensionPosition = strpos($row['file_name'], '.');
            $asset['title'] = substr(
                $row['file_name'],
                $prefixPosition + 1,
                $extensionPosition - ($prefixPosition + 1)
            );*/

            array_push($imageList, $asset);
        }
        $result->close();
    } else {
        echo 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: ' . $db->error;
        $db->close();
        exit();
    }
}

?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <?php require_once('../include/head.inc'); ?>
        <!-- DataTables -->
        <link rel="stylesheet" href="../bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
        <!--Lightbox-->
        <link href="../dist/lightbox/css/lightbox.css" rel="stylesheet">

        <style>
            input[type="file"] {
                margin-bottom: 15px;
                /*display: none;*/
            }

            .preview-container {
                position: relative;
                display: inline;
                border: 0px solid red;
            }

            .preview-container .selFile {
                opacity: 1;
                transition: .3s ease;
            }

            .preview-container:hover .selFile {
                opacity: 0.3;
                transition: .3s ease;
            }

            .middle {
                transition: .3s ease;
                opacity: 0;
                position: absolute;
                top: 50%;
                left: 80px;
                transform: translate(-50%, -50%);
                -ms-transform: translate(-50%, -50%);
                text-align: center;
            }

            .middle:hover {
                cursor: pointer;
            }

            .preview-container:hover .middle {
                opacity: 1;
                transition: .3s ease;
            }

            .custom-file-upload {
                border: 1px solid #ccc;
                display: inline-block;
                padding: 6px 12px;
                cursor: pointer;
            }

            .custom-file-upload:hover {
                background: #f4f4f4;
            }

            .nav-tabs {
                background-color: #f8f8f8;
            }

            .tab-content {
                /*background-color:#ccc;
                color:#00ff00;
                padding:5px*/
            }

            .nav-tabs > li > a {
                /*border: medium none;*/
            }

            .nav-tabs > li > a:hover {
                /*background-color: #ccc !important;
                border: medium none;
                border-radius: 0;
                color:#fff;*/
            }
        </style>
    </head>
    <body class="hold-transition skin-blue sidebar-mini">

    <div class="wrapper">
        <?php require_once('../include/header.inc'); ?>
        <?php require_once('../include/sidebar.inc'); ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>
                    <?= (isset($placeId) ? 'แก้ไข' : 'เพิ่ม') . $pageTitle; ?>
                </h1>
            </section>

            <!-- Main content -->
            <section class="content">
                <form id="formAddPlace"
                      autocomplete="off"
                      action="../api/api.php/<?= (isset($placeId) ? 'update_place' : 'add_place'); ?>"
                      method="post">

                    <input type="hidden" name="placeId" value="<?php echo $placeId; ?>"/>
                    <input type="hidden" name="placeType" value="<?php echo $placeTypeKey; ?>"/>

                    <div class="row">
                        <div class="col-xs-12">

                            <!--หัวข้อ-->
                            <div class="box box-warning">
                                <div class="box-header with-border">
                                    <h3 class="box-title"><?= ''; //$pageTitle;      ?></h3>

                                    <div class="box-tools pull-right">
                                        <button type="button" class="btn btn-box-tool" data-widget="collapse"
                                                data-toggle="tooltip" title="ย่อ">
                                            <i class="fa fa-minus"></i>
                                        </button>
                                    </div>
                                    <!-- /.box-tools -->
                                </div>
                                <!-- /.box-header -->
                                <div class="box-body">

                                    <!--ชื่อ-->
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="inputName">ชื่อ<?= $pageTitle; ?>:</label>
                                                <div class="input-group">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-font"></i>
                                                </span>
                                                    <input type="text" class="form-control"
                                                           id="inputName"
                                                           name="name"
                                                           value="<?php echo(!empty($place) ? $place['name'] : ''); ?>"
                                                           placeholder="กรอกชื่อ<?= $pageTitle; ?>" required
                                                           oninvalid="this.setCustomValidity('กรอกชื่อ<?= $pageTitle; ?>')"
                                                           oninput="this.setCustomValidity('')">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!--อำเภอ-->
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="selectDistrict">อำเภอ:</label>
                                                <div class="input-group">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-map-marker"></i>
                                                </span>
                                                    <select id="selectDistrict" class="form-control" required
                                                            name="district"
                                                            oninvalid="this.setCustomValidity('เลือกอำเภอ')"
                                                            oninput="this.setCustomValidity('')"
                                                            @change="handleSelectDistrict">
                                                        <option value="" disabled <?= empty($place) ? 'selected' : ''; ?>>-- เลือกอำเภอ --</option>
                                                        <?php
                                                        $districtList = array(
                                                            'เมืองชัยนาท', 'สรรพยา', 'สรรคบุรี', 'วัดสิงห์',
                                                            'หันคา', 'มโนรมย์', 'เนินขาม', 'หนองมะโมง'
                                                        );
                                                        foreach ($districtList as $district) {
                                                            ?>
                                                            <option value="<?= $district; ?>" <?= (!empty($place) && ($place['district'] === $district)) ? 'selected' : ''; ?>>
                                                                <?= $district; ?>
                                                            </option>
                                                            <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <?php
                                        if ($placeType === 'otop') {
                                            ?>
                                            <!--ตำบล-->
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="selectSubDistrict">ตำบล:</label>
                                                    <div class="input-group">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-map-marker"></i>
                                                </span>
                                                        <select id="selectSubDistrict" class="form-control" required
                                                                name="subDistrict"
                                                                oninvalid="this.setCustomValidity('เลือกตำบล')"
                                                                oninput="this.setCustomValidity('')"
                                                                v-model="selectedSubDistrict"
                                                                @change="handleSelectSubDistrict">
                                                            <option value="" disabled <?= empty($place) ? 'selected' : ''; ?>>-- เลือกตำบล --</option>
                                                            <option v-for="subDistrict in subDistrictList"
                                                                    v-bind:value="subDistrict.key">
                                                                {{subDistrict.name}}
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <!--หมู่บ้าน-->
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="inputVillage">หมู่บ้าน:</label>
                                                    <div class="input-group">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-home"></i>
                                                </span>
                                                        <input type="text" class="form-control"
                                                               id="inputVillage"
                                                               name="village"
                                                               value="<?php echo(!empty($place) ? $place['village'] : ''); ?>"
                                                               placeholder="กรอกชื่อหมู่บ้าน" required
                                                               oninvalid="this.setCustomValidity('กรอกหมู่บ้าน')"
                                                               oninput="this.setCustomValidity('')">

                                                        <!--<select id="selectVillage" class="form-control" required
                                                                name="village"
                                                                oninvalid="this.setCustomValidity('เลือกหมู่บ้าน')"
                                                                oninput="this.setCustomValidity('')"
                                                                v-model="selectedVillage"
                                                                @change="handleSelectVillage">
                                                            <option value="" disabled <?/*= empty($place) ? 'selected' : ''; */ ?>>-- เลือกหมู่บ้าน --</option>
                                                            <option v-for="village in villageList"
                                                                    v-bind:value="village.key">
                                                                {{village.name}}
                                                            </option>
                                                        </select>-->
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                        ?>

                                        <!--เบอร์โทร-->
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="inputPhone">เบอร์โทร:</label>
                                                <div class="input-group">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-phone"></i>
                                                </span>
                                                    <input type="text" class="form-control"
                                                           id="inputPhone"
                                                           name="phone"
                                                           value="<?php echo(!empty($place) ? $place['phone'] : ''); ?>"
                                                           placeholder="กรอกเบอร์โทร" required
                                                           oninvalid="this.setCustomValidity('กรอกเบอร์โทร')"
                                                           oninput="this.setCustomValidity('')">
                                                </div>
                                            </div>
                                        </div>

                                        <!--เวลาเปิด-->
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="inputOpeningTime">เวลาเปิด:</label>
                                                <div class="input-group">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-clock-o"></i>
                                                </span>
                                                    <input type="text" class="form-control"
                                                           id="inputOpeningTime"
                                                           name="openingTime"
                                                           value="<?php echo(!empty($place) ? $place['opening_time'] : ''); ?>"
                                                           placeholder="กรอกเวลาเปิด" required
                                                           oninvalid="this.setCustomValidity('กรอกเวลาเปิด')"
                                                           oninput="this.setCustomValidity('')">
                                                </div>
                                            </div>
                                        </div>

                                        <?php
                                        if ($placeType === 'otop') {
                                            ?>
                                            <!--ราคาสินค้า-->
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="inputPrice">ราคาสินค้า:</label>
                                                    <div class="input-group">
                                                <span class="input-group-addon">
                                                    <strong>฿</strong>
                                                </span>
                                                        <input type="text" class="form-control"
                                                               id="inputPrice"
                                                               name="price"
                                                               value="<?php echo(!empty($place) ? $place['price'] : ''); ?>"
                                                               placeholder="กรอกราคาสินค้า" required
                                                               oninvalid="this.setCustomValidity('กรอกราคาสินค้า')"
                                                               oninput="this.setCustomValidity('')">
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                    </div>

                                    <?php
                                    if ($placeType === 'otop') {
                                        ?>
                                        <!--Contact URL-->
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="inputContactUrl">Link สำหรับติดต่อ:</label>
                                                    <div class="input-group">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-link"></i>
                                                </span>
                                                        <input type="text" class="form-control"
                                                               id="inputContactUrl"
                                                               name="contactUrl"
                                                               value="<?php echo(!empty($place) ? $place['contact_url'] : ''); ?>"
                                                               placeholder="กรอก Link สำหรับติดต่อ" required
                                                               oninvalid="this.setCustomValidity('กรอก Link สำหรับติดต่อ')"
                                                               oninput="this.setCustomValidity('')">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>

                                    <div class="row">
                                        <!--ที่อยู่-->
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label for="inputAddress">ที่อยู่:</label>
                                                <div class="input-group">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-envelope-o"></i>
                                                </span>
                                                    <input type="text" class="form-control"
                                                           id="inputAddress"
                                                           name="address"
                                                           value="<?php echo(!empty($place) ? $place['address'] : ''); ?>"
                                                           placeholder="กรอกที่อยู่" required
                                                           oninvalid="this.setCustomValidity('กรอกที่อยู่')"
                                                           oninput="this.setCustomValidity('')">
                                                </div>
                                            </div>
                                        </div>

                                        <!--ละติจูด-->
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="inputLatitude">ละติจูด:</label>
                                                <div class="input-group">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-map-marker"></i>
                                                </span>
                                                    <input type="text" class="form-control"
                                                           id="inputLatitude"
                                                           name="latitude"
                                                           value="<?php echo(!empty($place) ? $place['latitude'] : ''); ?>"
                                                           placeholder="กรอกละติจูด" required
                                                           oninvalid="this.setCustomValidity('กรอกละติจูด')"
                                                           oninput="this.setCustomValidity('')">
                                                </div>
                                            </div>
                                        </div>

                                        <!--ลองจิจูด-->
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="inputLongitude">ลองจิจูด:</label>
                                                <div class="input-group">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-map-marker"></i>
                                                </span>
                                                    <input type="text" class="form-control"
                                                           id="inputLongitude"
                                                           name="longitude"
                                                           value="<?php echo(!empty($place) ? $place['longitude'] : ''); ?>"
                                                           placeholder="กรอกลองจิจูด" required
                                                           oninvalid="this.setCustomValidity('กรอกลองจิจูด')"
                                                           oninput="this.setCustomValidity('')">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.box-body -->
                            </div>
                            <!-- /.box -->

                            <!--content editor-->
                            <div class="box box-warning">
                                <div class="box-header with-border">
                                    <h3 class="box-title">รายละเอียด
                                        <small>&nbsp;</small>
                                    </h3>
                                    <!-- tools box -->
                                    <div class="pull-right box-tools">
                                        <button type="button" class="btn btn-box-tool" data-widget="collapse"
                                                data-toggle="tooltip" title="ย่อ">
                                            <i class="fa fa-minus"></i>
                                        </button>
                                    </div>
                                    <!-- /. tools -->
                                </div>
                                <!-- /.box-header -->
                                <div class="box-body pad">
                                <textarea id="editor" rows="10" cols="120"
                                          name="details" required
                                          placeholder="กรอกรายละเอียด"
                                          oninvalid="this.setCustomValidity('กรอกรายละเอียด')"
                                          oninput="this.setCustomValidity('')"
                                          style="padding: 6px 10px"><?= (!empty($place) ? $place['details'] : ''); ?></textarea>
                                </div>
                            </div>
                            <!-- /.box -->

                            <!--รูปภาพหน้า List-->
                            <div class="box box-warning">
                                <div class="box-header with-border">
                                    <h3 class="box-title">รูปภาพหน้า List
                                        <!--<small></small>-->
                                    </h3>

                                    <!-- tools box -->
                                    <div class="pull-right box-tools">
                                        <button type="button" class="btn btn-box-tool" data-widget="collapse"
                                                data-toggle="tooltip" title="ย่อ">
                                            <i class="fa fa-minus"></i>
                                        </button>
                                    </div>
                                    <!-- /. tools -->
                                </div>
                                <!-- /.box-header -->
                                <div class="box-body pad" style="background_: #f8f8f8">
                                    <?php
                                    if (!empty($place)) {
                                        ?>
                                        <!-- Custom Tabs -->
                                        <div class="nav-tabs-custom">
                                            <ul class="nav nav-tabs">
                                                <li class="active"><a href="#list_image_tab_1" data-toggle="tab">รูปภาพปัจจุบัน</a></li>
                                                <li><a href="#list_image_tab_2" data-toggle="tab">อัพโหลดรูปภาพใหม่</a></li>
                                            </ul>
                                            <div class="tab-content">
                                                <div class="tab-pane active" id="list_image_tab_1">
                                                    <div style="padding: 5px">
                                                        <!--<a target="_blank" href="<?php /*echo(DIR_IMAGES . $place['image_file_name']); */ ?>">แสดงรูปภาพในหน้าจอใหม่</a>-->
                                                    </div>
                                                    <a href="<?= (DIR_IMAGES . ($place['image_list'] ? $place['image_list'] : 'ic_no_image_2.png')); ?>"
                                                       data-lightbox="listImage" data-title="<?= $place['title']; ?>">
                                                        <img src="<?= (DIR_IMAGES . ($place['image_list'] ? $place['image_list'] : 'ic_no_image_2.png')); ?>"
                                                             width="<?= $place['image_list'] ? '300px' : '100px'; ?>">
                                                    </a>
                                                </div>
                                                <!-- /.tab-pane -->
                                                <div class="tab-pane" id="list_image_tab_2" style="padding: 0px">
                                                    <ul style="color: orangered; margin-top: 10px; margin-bottom: 15px">
                                                        <li>คลิกในกรอบสี่เหลี่ยมเพื่อเลือกไฟล์ หรือลากไฟล์มาปล่อยในกรอบสี่เหลี่ยม</li>
                                                        <li>รูปภาพที่อัพโหลดใหม่ จะแทนที่รูปภาพปัจจุบัน</li>
                                                        <li>ไฟล์จะถูกบันทึกเข้าสู่ระบบ หลังจากกดปุ่ม "บันทึก"</li>
                                                    </ul>
                                                    <input id="list-image-file-upload" name="listImageFile"
                                                           type="file" accept="image/*"
                                                           style="width: 500px; margin-top: 10px; border: 2px dotted #ccc; padding: 10px 10px 50px 10px"/>
                                                    <div id="list-image-upload-preview"
                                                         style="background: #efffd1; padding: 10px;"></div>
                                                </div>
                                                <!-- /.tab-pane -->
                                            </div>
                                            <!-- /.tab-content -->
                                        </div>
                                        <!-- nav-tabs-custom -->
                                        <?php
                                    } else {
                                        ?>
                                        <ul style="color: orangered; margin-top: 10px; margin-bottom: 15px">
                                            <li>คลิกในกรอบสี่เหลี่ยมเพื่อเลือกไฟล์ หรือลากไฟล์มาปล่อยในกรอบสี่เหลี่ยม</li>
                                            <li>ไฟล์จะถูกบันทึกเข้าสู่ระบบ หลังจากกดปุ่ม "บันทึก"</li>
                                        </ul>
                                        <input id="list-image-file-upload" name="listImageFile" required
                                               type="file" accept="image/*"
                                               style="width: 500px; margin-top: 10px; margin-bottom: 10px; border: 2px dotted #ccc; padding: 10px 10px 50px 10px"
                                               oninvalid="this.setCustomValidity('เลือกรูปภาพหน้า List')"
                                               oninput="this.setCustomValidity('')"/>
                                        <div id="list-image-upload-preview"
                                             style="background: #efffd1; padding: 10px;"></div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <!-- /.box -->

                            <!--รูปภาพ Cover-->
                            <div class="box box-warning">
                                <div class="box-header with-border">
                                    <h3 class="box-title">รูปภาพ Cover หน้ารายละเอียด
                                        <!--<small></small>-->
                                    </h3>

                                    <!-- tools box -->
                                    <div class="pull-right box-tools">
                                        <button type="button" class="btn btn-box-tool" data-widget="collapse"
                                                data-toggle="tooltip" title="ย่อ">
                                            <i class="fa fa-minus"></i>
                                        </button>
                                    </div>
                                    <!-- /. tools -->
                                </div>
                                <!-- /.box-header -->
                                <div class="box-body pad" style="background_: #f8f8f8">
                                    <?php
                                    if (!empty($place)) {
                                        ?>
                                        <!-- Custom Tabs -->
                                        <div class="nav-tabs-custom">
                                            <ul class="nav nav-tabs">
                                                <li class="active"><a href="#cover_image_tab_1" data-toggle="tab">รูปภาพปัจจุบัน</a></li>
                                                <li><a href="#cover_image_tab_2" data-toggle="tab">อัพโหลดรูปภาพใหม่</a></li>
                                            </ul>
                                            <div class="tab-content">
                                                <div class="tab-pane active" id="cover_image_tab_1">
                                                    <div style="padding: 5px">
                                                        <!--<a target="_blank" href="<?php /*echo(DIR_IMAGES . $place['image_file_name']); */ ?>">แสดงรูปภาพในหน้าจอใหม่</a>-->
                                                    </div>
                                                    <a href="<?= (DIR_IMAGES . ($place['image_cover'] ? $place['image_cover'] : 'ic_no_image_2.png')); ?>"
                                                       data-lightbox="coverImage" data-title="<?= $place['title']; ?>">
                                                        <img src="<?= (DIR_IMAGES . ($place['image_cover'] ? $place['image_cover'] : 'ic_no_image_2.png')); ?>"
                                                             width="<?= $place['image_cover'] ? '300px' : '100px'; ?>">
                                                    </a>
                                                </div>
                                                <!-- /.tab-pane -->
                                                <div class="tab-pane" id="cover_image_tab_2" style="padding: 0px">
                                                    <ul style="color: orangered; margin-top: 10px; margin-bottom: 15px">
                                                        <li>คลิกในกรอบสี่เหลี่ยมเพื่อเลือกไฟล์ หรือลากไฟล์มาปล่อยในกรอบสี่เหลี่ยม</li>
                                                        <li>รูปภาพที่อัพโหลดใหม่ จะแทนที่รูปภาพปัจจุบัน</li>
                                                        <li>ไฟล์จะถูกบันทึกเข้าสู่ระบบ หลังจากกดปุ่ม "บันทึก"</li>
                                                    </ul>
                                                    <input id="cover-image-file-upload" name="coverImageFile"
                                                           type="file" accept="image/*"
                                                           style="width: 500px; margin-top: 10px; border: 2px dotted #ccc; padding: 10px 10px 50px 10px"/>
                                                    <div id="cover-image-upload-preview"
                                                         style="background: #efffd1; padding: 10px;"></div>
                                                </div>
                                                <!-- /.tab-pane -->
                                            </div>
                                            <!-- /.tab-content -->
                                        </div>
                                        <!-- nav-tabs-custom -->
                                        <?php
                                    } else {
                                        ?>
                                        <ul style="color: orangered; margin-top: 10px; margin-bottom: 15px">
                                            <li>คลิกในกรอบสี่เหลี่ยมเพื่อเลือกไฟล์ หรือลากไฟล์มาปล่อยในกรอบสี่เหลี่ยม</li>
                                            <li>ไฟล์จะถูกบันทึกเข้าสู่ระบบ หลังจากกดปุ่ม "บันทึก"</li>
                                        </ul>
                                        <input id="cover-image-file-upload" name="coverImageFile" required
                                               type="file" accept="image/*"
                                               style="width: 500px; margin-top: 10px; margin-bottom: 10px; border: 2px dotted #ccc; padding: 10px 10px 50px 10px"
                                               oninvalid="this.setCustomValidity('เลือกรูปภาพหน้า List')"
                                               oninput="this.setCustomValidity('')"/>
                                        <div id="cover-image-upload-preview"
                                             style="background: #efffd1; padding: 10px;"></div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <!-- /.box -->

                            <!--รูปภาพ Gallery-->
                            <div class="box box-warning">
                                <div class="box-header with-border">
                                    <h3 class="box-title">รูปภาพ Gallery
                                        <!--<small>อัพโหลดรูปภาพ</small>-->
                                    </h3>
                                    <!-- tools box -->
                                    <div class="pull-right box-tools">
                                        <button type="button" class="btn btn-box-tool" data-widget="collapse"
                                                data-toggle="tooltip" title="ย่อ">
                                            <i class="fa fa-minus"></i>
                                        </button>
                                    </div>
                                    <!-- /. tools -->
                                </div>
                                <!-- /.box-header -->
                                <div class="box-body pad" style="background: #f8f8f8">
                                    <!-- Custom Tabs -->
                                    <div class="nav-tabs-custom">
                                        <ul class="nav nav-tabs">
                                            <?php
                                            if (isset($placeId)) {
                                                ?>
                                                <li class="active"><a href="#image_tab_1" data-toggle="tab">รูปภาพปัจจุบัน <strong>(<?php echo sizeof($imageList); ?>)</strong></a></li>
                                                <?php
                                            }
                                            ?>
                                            <li <?php echo(!isset($placeId) ? 'class="active"' : ''); ?>><a href="#image_tab_2" data-toggle="tab">เพิ่มรูปภาพ</a></li>
                                        </ul>
                                        <div class="tab-content">
                                            <?php
                                            if (isset($placeId)) {
                                                ?>
                                                <!--ตารางรูปภาพ-->
                                                <div class="tab-pane active" id="image_tab_1">
                                                    <?php
                                                    if (sizeof($imageList) === 0) {
                                                        ?>
                                                        <div class="callout callout-danger" style="margin-top: 10px">
                                                            <p>ยังไม่มีรูปภาพ!</p>
                                                        </div>
                                                        <?php
                                                    }
                                                    ?>
                                                    <table id="tableImage" class="table table-bordered table-striped">
                                                        <thead>
                                                        <tr>
                                                            <!--<th style="text-align: center; width: 40%;">ชื่อ</th>-->
                                                            <th style="text-align: center; width: 80%;">รูปภาพ</th>
                                                            <th style="text-align: center; width: 20%;">อัพโหลดเมื่อ</th>
                                                            <th style="text-align: center;">จัดการ</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php
                                                        foreach ($imageList as $image) {
                                                            $createdAt = $image['created_at'];
                                                            $dateTimePart = explode(' ', $createdAt);
                                                            $displayDate = getThaiShortDateWithDayName(date_create($dateTimePart[0]));
                                                            $timePart = explode(':', $dateTimePart[1]);
                                                            $displayTime = $timePart[0] . '.' . $timePart[1] . ' น.';
                                                            $displayDateTime = "$displayDate<br>$displayTime";
                                                            $dateHidden = '<span style="display: none">' . $createdAt . '</span></span>';
                                                            ?>
                                                            <tr>
                                                                <!--<td><?php /*echo $image['title']; */ ?></td>-->
                                                                <td style="text-align: center">
                                                                    <a href="<?php echo(DIR_IMAGES_GALLERY . $image['image_file_name']); ?>" data-lightbox="placeImage">
                                                                        <img src="<?php echo(DIR_IMAGES_GALLERY . $image['image_file_name']); ?>"
                                                                             height="120px">
                                                                    </a>
                                                                </td>
                                                                <td style="text-align: center"><?php echo($dateHidden . $displayDateTime); ?></td>
                                                                <td>
                                                                    <button type="button" class="btn btn-danger"
                                                                            style="margin-left: 6px; margin-right: 6px"
                                                                            onClick="onClickDeleteAsset(this, <?php echo $image['id']; ?>, 'รูปภาพ')">
                                                                        <span class="fa fa-remove"></span>&nbsp;
                                                                        ลบ
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                            <?php
                                                        }
                                                        ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                            <!--เพิ่มรูปภาพใหม่-->
                                            <div class="tab-pane <?= (!isset($placeId) ? 'active' : ''); ?>" id="image_tab_2">
                                                <ul style="color: orangered; margin-top: 10px; margin-bottom: 15px">
                                                    <li>คลิกในกรอบสี่เหลี่ยมเพื่อเลือกไฟล์ หรือลากไฟล์มาปล่อยในกรอบสี่เหลี่ยม</li>
                                                    <li>สามารถเลือกได้หลายไฟล์พร้อมกัน</li>
                                                    <li>ไฟล์จะถูกบันทึกเข้าสู่ระบบ หลังจากกดปุ่ม "บันทึก"</li>
                                                </ul>

                                                <!--<div>
                                                    <button onclick="test()" type="button">Test</button>
                                                </div>-->

                                                <input id="image-upload" type="file" accept="image/*" multiple
                                                       style="color: transparent; width: 500px; margin-top: 10px; border: 2px dotted #ccc; padding: 10px 10px 50px 10px"/>
                                                <!--<label for="image-upload"
                                                       style="background-color: #ffffff; width: 500px; margin-top: 10px; border: 2px dotted #ccc; padding: 10px 10px 60px 10px"/>-->
                                                <div id="image-upload-preview"
                                                     style="background: #efffd1; padding: 10px;"></div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <!-- /.box -->

                            <!--ปุ่ม "บันทึก"-->
                            <div class="row">
                                <div class="col-12 text-center">
                                    <div id="divLoading" style="text-align: center; margin-bottom: 10px;">
                                        <img src="../images/ic_loading4.gif" height="32px"/>&nbsp;รอสักครู่
                                    </div>
                                    <button id="buttonSave" type="submit"
                                            class="btn btn-info">
                                        <span class="fa fa-save"></span>&nbsp;
                                        บันทึก
                                    </button>
                                </div>
                            </div>

                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.row -->
                </form>

            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <?php require_once('../include/footer.inc'); ?>
    </div>
    <!-- ./wrapper -->

    <script>
        //https://www.raymondcamden.com/2014/04/14/MultiFile-Uploads-and-Multiple-Selects-Part-2/

        /*function test() {
            $('#image-upload').prop('files').splice(0, 1);
            alert($('#image-upload').prop('files').length());
        }*/

        $(() => {
            <?php
            if (!empty($place)) {
                $placeDatePart = explode('-', $place['place_date']);
                $year = $placeDatePart[0];
                $month = $placeDatePart[1];
                $day = $placeDatePart[2];
                $placeDate = "$day/$month/$year";
            }
            ?>

            //Date picker
            let inputPlaceDate = $('#inputPlaceDate');
            inputPlaceDate.datepicker({
                language: 'th',
                thaiyear: true,
                format: 'dd/mm/yyyy',
                orientation: 'bottom',
                autoclose: true
            }).on('changeDate', e => {
                e.target.setCustomValidity('');
            }).datepicker('update', '<?php echo(!empty($place) ? $placeDate : ''); ?>');

            //CKEDITOR.replace('editor');
        });

        $(document).ready(function () {
            lightbox.option({
                fadeDuration: 500,
                imageFadeDuration: 500,
                resizeDuration: 500,
            });

            $('#tableImage').DataTable({
                stateSave: false,
                //stateDuration: -1, // sessionStorage
                order: [[1, 'desc']],
                language: {
                    lengthMenu: "แสดงหน้าละ _MENU_ แถวข้อมูล",
                    zeroRecords: "ไม่มีข้อมูล",
                    emptyTable: "ไม่มีข้อมูล",
                    info: "หน้าที่ _PAGE_ จากทั้งหมด _PAGES_ หน้า",
                    infoEmpty: "แสดง 0 แถวข้อมูล",
                    infoFiltered: "(กรองจากทั้งหมด _MAX_ แถวข้อมูล)",
                    search: "ค้นหา:",
                    thousands: ",",
                    loadingRecords: "รอสักครู่...",
                    processing: "กำลังประมวลผล...",
                    paginate: {
                        first: "หน้าแรก",
                        last: "หน้าสุดท้าย",
                        next: "ถัดไป",
                        previous: "ก่อนหน้า"
                    },
                }
            });

            $('#formAddPlace #divLoading').hide();

            $('#formAddPlace').submit(event => {
                event.preventDefault();
                doAddEditPlace();
            });
        });

        $(function () {
            $('#list-image-upload-preview').hide();

            const listImagePreview = function (input, placeToInsertImagePreview) {
                $(placeToInsertImagePreview).empty();
                $(placeToInsertImagePreview).hide();

                if (input.files) {
                    let fileCount = input.files.length;

                    for (let i = 0; i < fileCount; i++) {
                        $(placeToInsertImagePreview).show();
                        let reader = new FileReader();

                        reader.onload = function (event) {
                            $($.parseHTML('<img style="width: auto; height: 120px; margin: 3px">')).attr('src', event.target.result).appendTo(placeToInsertImagePreview);
                        };
                        reader.readAsDataURL(input.files[i]);
                    }
                }
            };

            $('#list-image-file-upload').on('change', function () {
                listImagePreview(this, 'div#list-image-upload-preview');
            });
        });

        $(function () {
            $('#cover-image-upload-preview').hide();

            const coverImagePreview = function (input, placeToInsertImagePreview) {
                $(placeToInsertImagePreview).empty();
                $(placeToInsertImagePreview).hide();

                if (input.files) {
                    let fileCount = input.files.length;

                    for (let i = 0; i < fileCount; i++) {
                        $(placeToInsertImagePreview).show();
                        let reader = new FileReader();

                        reader.onload = function (event) {
                            $($.parseHTML('<img style="width: auto; height: 120px; margin: 3px">')).attr('src', event.target.result).appendTo(placeToInsertImagePreview);
                        };
                        reader.readAsDataURL(input.files[i]);
                    }
                }
            };

            $('#cover-image-file-upload').on('change', function () {
                coverImagePreview(this, 'div#cover-image-upload-preview');
            });
        });

        /*function readURL(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#image-upload-preview').attr('src', e.target.result);
                };
                reader.readAsDataURL(input.files[0]);
            }
        }*/

        const storedImageFiles = [];

        $(function () {
            $("body").on("click", ".selFile", removeFile);
            $("body").on("click", ".middle", removeFile);

            $('#image-upload-preview').hide();

            const imagesPreview = function (input, placeToInsertImagePreview) {
                $(placeToInsertImagePreview).empty();
                $(placeToInsertImagePreview).hide();

                if (input.files) {
                    let fileCount = input.files.length;

                    for (let i = 0; i < fileCount; i++) {
                        $(placeToInsertImagePreview).show();
                        let reader = new FileReader();

                        reader.onload = function (event) {
                            $($.parseHTML('<img style="width: auto; height: 120px; margin: 3px">')).attr('src', event.target.result).appendTo(placeToInsertImagePreview);
                        };
                        reader.readAsDataURL(input.files[i]);
                    }
                }
            };

            $('#image-upload').on('change', function (e) {
                const selDiv = $("div#image-upload-preview");
                selDiv.show();

                let files = e.target.files;
                let filesArr = Array.prototype.slice.call(files);

                filesArr.forEach(function (f) {
                    if (!f.type.match("image.*")) {
                        return;
                    }
                    storedImageFiles.push(f);

                    let reader = new FileReader();
                    reader.onload = function (e) {
                        //let html = "<div class=\"preview-container\"><img style=\"width: 160px; height: auto; margin: 3px; cursor: pointer;\" src=\"" + e.target.result + "\" data-file='" + f.name + "' class='selFile' title='คลิกเพื่อลบ'>&nbsp;&nbsp;" + f.name + "<br clear=\"left\"/><div class=\"middle\" data-file='" + f.name + "'><i class=\"fa fa-times-circle\" title=\"คลิกเพื่อลบ\" style=\"color: #000000; font-size: 30px\"/></div></div>";
                        let html = `<div class="preview-container">
                                        <img style="width: 160px; height: auto; margin: 3px; cursor: pointer;" src="${e.target.result}" data-file="${f.name}" class="selFile" title="คลิกเพื่อลบ">
                                            &nbsp;&nbsp;${f.name}<br clear="left"/>
                                        <div class="middle" data-file="${f.name}">
                                            <i class="fa fa-times-circle" title="คลิกเพื่อลบ" style="color: #000000; font-size: 30px"/>
                                        </div>
                                    </div>`;
                        selDiv.append(html);
                    };
                    reader.readAsDataURL(f);
                });

                //imagesPreview(this, 'div#image-upload-preview');
            });
        });

        function removeFile(e) {
            let file = $(this).data("file");
            for (let i = 0; i < storedImageFiles.length; i++) {
                if (storedImageFiles[i].name === file) {
                    storedImageFiles.splice(i, 1);
                    break;
                }
            }
            if (storedImageFiles.length === 0) {
                const selDiv = $("div#image-upload-preview");
                selDiv.hide();
            }

            //alert(storedFiles.length);

            $(this).parent().remove();
        }

        $(function () {
            $('#pdf-upload-preview').hide();

            const pdfPreview = function (input, placeToInsertPdfPreview) {
                $(placeToInsertPdfPreview).empty();
                $(placeToInsertPdfPreview).hide();

                if (input.files) {
                    let fileCount = input.files.length;

                    for (let i = 0; i < fileCount; i++) {
                        $(placeToInsertPdfPreview).show();
                        $($.parseHTML('<li style="">' + input.files[i].name + '</li>')).appendTo(placeToInsertPdfPreview);
                    }
                }
            };

            $('#pdf-upload').on('change', function () {
                pdfPreview(this, 'ul#pdf-upload-preview');
            });
        });

        function doAddEditPlace() {
            // อัพเดท content ของ ckeditor ไปยัง textarea
            //CKEDITOR.instances.editor.updateElement();

            $('#formAddPlace #buttonSave').prop('disabled', true);
            $('#formAddPlace #divLoading').show();

            const form = $('#formAddPlace')[0];
            const formData = new FormData(form);

            for (let i = 0, len = storedImageFiles.length; i < len; i++) {
                formData.append('imageFiles[]', storedImageFiles[i]);
            }

            $.ajax({
                url: '../api/api.php/<?= (isset($placeId) ? 'update_place' : 'add_place'); ?>',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST',
                success: function (data) {
                    $('#formAddPlace #buttonSave').prop('disabled', false);
                    $('#formAddPlace #divLoading').hide();

                    if (data.error_code === 0) {
                        BootstrapDialog.show({
                            title: '<?php echo(isset($placeId) ? 'แก้ไข' : 'เพิ่ม'); ?>',
                            message: data.error_message,
                            buttons: [{
                                label: 'ปิด',
                                action: function (self) {
                                    self.close();
                                    <?php
                                    if (!isset($placeId)) {
                                    ?>
                                    window.location.href = 'place.php?place_type=<?php echo $placeType; ?>';
                                    <?php
                                    } else {
                                    ?>
                                    window.location.reload(true);
                                    <?php
                                    }
                                    ?>
                                }
                            }]
                        });
                    } else {
                        BootstrapDialog.show({
                            title: '<?php echo(isset($placeId) ? 'แก้ไข' : 'เพิ่ม'); ?> - ผิดพลาด',
                            message: data.error_message,
                            buttons: [{
                                label: 'ปิด',
                                action: function (self) {
                                    self.close();
                                }
                            }]
                        });
                    }
                },
                error: function () {
                    $('#formAddPlace #buttonSave').prop('disabled', false);
                    $('#formAddPlace #divLoading').hide();

                    BootstrapDialog.show({
                        title: '<?php echo(isset($placeId) ? 'แก้ไข' : 'เพิ่ม'); ?> - ผิดพลาด',
                        message: 'เกิดข้อผิดพลาดในการเชื่อมต่อ Server',
                        buttons: [{
                            label: 'ปิด',
                            action: function (self) {
                                self.close();
                            }
                        }]
                    });
                }
            });

            $('#formAddPlace_NotUsed').ajaxSubmit({
                dataType: 'json',
                success: (data, statusText) => {
                    //alert(data.error_message);
                    $('#formAddPlace #buttonSave').prop('disabled', false);
                    $('#formAddPlace #divLoading').hide();

                    if (data.error_code === 0) {
                        BootstrapDialog.show({
                            title: '<?php echo(isset($placeId) ? 'แก้ไข' : 'เพิ่ม'); ?>',
                            message: data.error_message,
                            buttons: [{
                                label: 'ปิด',
                                action: function (self) {
                                    self.close();
                                    <?php
                                    if (!isset($placeId)) {
                                    ?>
                                    window.location.href = 'place.php?place_type=<?php echo $placeType; ?>';
                                    <?php
                                    } else {
                                    ?>
                                    window.location.reload(true);
                                    <?php
                                    }
                                    ?>
                                }
                            }]
                        });
                    } else {
                        BootstrapDialog.show({
                            title: '<?php echo(isset($placeId) ? 'แก้ไข' : 'เพิ่ม'); ?> - ผิดพลาด',
                            message: data.error_message,
                            buttons: [{
                                label: 'ปิด',
                                action: function (self) {
                                    self.close();
                                }
                            }]
                        });
                    }
                },
                error: () => {
                    $('#formAddPlace #buttonSave').prop('disabled', false);
                    $('#formAddPlace #divLoading').hide();

                    BootstrapDialog.show({
                        title: '<?php echo(isset($placeId) ? 'แก้ไข' : 'เพิ่ม'); ?> - ผิดพลาด',
                        message: 'เกิดข้อผิดพลาดในการเชื่อมต่อ Server',
                        buttons: [{
                            label: 'ปิด',
                            action: function (self) {
                                self.close();
                            }
                        }]
                    });
                }
            });
        }

        function onClickDeleteAsset(element, assetId, assetType) {
            BootstrapDialog.show({
                title: 'ลบ' + assetType,
                message: `การลบ${assetType}จะมีผลกับฐานข้อมูลทันที!\n\nยืนยันลบ${assetType}นี้?`,
                buttons: [{
                    label: 'ลบ',
                    action: function (self) {
                        doDeleteAsset(assetId, assetType);
                        self.close();
                    },
                    cssClass: 'btn-primary'
                }, {
                    label: 'ยกเลิก',
                    action: function (self) {
                        self.close();
                    }
                }]
            });
        }

        function doDeleteAsset(assetId, assetType) {
            $.post(
                '../api/api.php/delete_place_asset',
                {
                    assetId: assetId,
                }
            ).done(function (data) {
                if (data.error_code === 0) {
                    location.reload(true);
                } else {
                    BootstrapDialog.show({
                        title: 'ลบ' + assetType + ' - ผิดพลาด',
                        message: data.error_message,
                        buttons: [{
                            label: 'ปิด',
                            action: function (self) {
                                self.close();
                            }
                        }]
                    });
                }
            }).fail(function () {
                BootstrapDialog.show({
                    title: 'ลบ' + assetType + ' - ผิดพลาด',
                    message: 'เกิดข้อผิดพลาดในการเชื่อมต่อ Server',
                    buttons: [{
                        label: 'ปิด',
                        action: function (self) {
                            self.close();
                        }
                    }]
                });
            });
        }
    </script>

    <?php
    if ($placeType === 'otop') {
        ?>
        <script>
            const vmDistrict = new Vue({
                el: '#selectDistrict',
                data: {
                    //selectedDistrict: null,
                },
                methods: {
                    async handleSelectDistrict(e) {
                        const result = await getSubDistrict(e.target.value);
                        vmSubDistrict.subDistrictList = result.data_list;
                        vmSubDistrict.selectedSubDistrict = '';
                    }
                },
                created: function () {
                }
            });

            const vmSubDistrict = new Vue({
                el: '#selectSubDistrict',
                data: {
                    subDistrictList: [],
                    selectedSubDistrict: '',
                },
                methods: {
                    handleSelectSubDistrict(e) {
                    }
                },
                created: async function () {
                    <?php
                    if (!empty($place)) {
                    ?>
                    const result = await getSubDistrict('<?= $place['district']; ?>');
                    this.subDistrictList = result.data_list;
                    this.selectedSubDistrict = '<?= $place['sub_district']; ?>';
                    <?php
                    }
                    ?>
                }
            });

            /*const vmVillage = new Vue({
                el: '#selectVillage',
                data: {
                    villageList: [
                    ],
                    selectedVillage: '',
                },
                methods: {
                    handleSelectVillage(e) {
                        //alert(e.target.value);
                    }
                }
            });*/

            async function getSubDistrict(district) {
                const response = await fetch('http://5911011802058.msci.dusit.ac.th/chainat_tourism/api/api.php/get_sub_district?district=' + district, {
                    method: 'GET', // *GET, POST, PUT, DELETE, etc.
                    mode: 'cors', // no-cors, *cors, same-origin
                    cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
                    credentials: 'same-origin', // include, *same-origin, omit
                    headers: {
                        //'Content-Type': 'application/json'
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    redirect: 'follow', // manual, *follow, error
                    referrer: 'no-referrer', // no-referrer, *client
                    //body: JSON.stringify(data) // body data type must match "Content-Type" header
                });
                return await response.json(); // parses JSON response into native JavaScript objects
            }
        </script>
        <?php
    }
    ?>

    <?php require_once('../include/foot.inc'); ?>
    <!-- CK Editor -->
    <!--<script src="../bower_components/ckeditor/ckeditor.js"></script>-->
    <!-- DataTables -->
    <script src="../bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <!--jQuery Form Plugin-->
    <script src="../dist/js/jquery.form.js"></script>
    <!--Lightbox-->
    <script src="../dist/lightbox/js/lightbox.js"></script>

    </body>
    </html>

<?php
require_once '../include/foot_php.inc';
?>