<?php
require_once '../include/head_php.inc';

$placeType = $_GET['place_type'];
$placeTypeList = array(
    'tour', 'temple', 'restaurant', 'hotel', 'otop'
);
if (!isset($placeType) || !in_array($placeType, $placeTypeList)) {
    echo "Invalid place type '$placeType' - ระบุประเภทสถานที่ไม่ถูกต้อง";
    $db->close();
    exit();
}

$pageTitles['tour'] = 'สถานที่ท่องเที่ยว';
$pageTitles['temple'] = 'วัด';
$pageTitles['restaurant'] = 'ร้านอาหาร';
$pageTitles['hotel'] = 'ที่พัก';
$pageTitles['otop'] = 'สินค้า OTOP';

$placeTypeKeys['tour'] = 'ท่องเที่ยว';
$placeTypeKeys['temple'] = 'วัด';
$placeTypeKeys['restaurant'] = 'ร้านอาหาร';
$placeTypeKeys['hotel'] = 'ที่พัก';
$placeTypeKeys['otop'] = 'otop';

$pageTitle = $pageTitles[$placeType];
$placeTypeKey = $placeTypeKeys[$placeType];

$sql = "SELECT *
            FROM ct_place
            WHERE place_type = '$placeTypeKey'
            ORDER BY id";
if ($result = $db->query($sql)) {
    $placeList = array();
    while ($row = $result->fetch_assoc()) {
        array_push($placeList, $row);
    }
    $result->close();
} else {
    echo 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล';
    $db->close();
    exit();
}

$userHasPermission = true;

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
            #tableDownload td:nth-child(5) {
                text-align: center;
            }
        </style>
    </head>
    <body class="hold-transition skin-blue sidebar-mini fixed">

    <div class="wrapper">
        <?php require_once('../include/header.inc'); ?>
        <?php require_once('../include/sidebar.inc'); ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>
                    <?= trim($pageTitle); ?>
                </h1>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box">
                            <div class="box-header">
                                <h3 class="box-title">&nbsp;</h3>
                                <?php
                                if ($userHasPermission) {
                                    ?>
                                    <button type="button" class="btn btn-success pull-right"
                                            onclick="onClickAdd(this)">
                                        <span class="fa fa-plus"></span>&nbsp;
                                        เพิ่ม<?= $pageTitle; ?>
                                    </button>
                                    <?php
                                }
                                ?>
                            </div>
                            <div class="box-body">
                                <table id="tablePlace_<?= $placeType; ?>" class="table table-bordered table-striped">
                                    <thead>
                                    <tr>
                                        <?php
                                        if ($placeType === 'otop') {
                                            ?>
                                            <th style="text-align: center; width: 21%;">ชื่อสินค้า</th>
                                            <th style="text-align: center; width: 13%;">อำเภอ</th>
                                            <th style="text-align: center; width: 13%;">ตำบล</th>
                                            <th style="text-align: center; width: 13%;">หมู่บ้าน</th>
                                            <th style="text-align: center; width: 20%;">รูปภาพหน้า List</th>
                                            <th style="text-align: center; width: 20%;">รูปภาพ Cover</th>
                                            <?php
                                        } else {
                                            ?>
                                            <th style="text-align: center; width: 45%;">ชื่อสถานที่</th>
                                            <th style="text-align: center; width: 15%;">อำเภอ</th>
                                            <th style="text-align: center; width: 20%;">รูปภาพหน้า List</th>
                                            <th style="text-align: center; width: 20%;">รูปภาพ Cover</th>
                                            <?php
                                        }
                                        ?>
                                        <th style="text-align: center;" nowrap>แนะนำ</th>
                                        <th style="text-align: center;">จัดการ</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (sizeof($placeList) == 0) {
                                        ?>
                                        <!--<tr valign="middle">
                                            <td colspan="20" align="center">ไม่มีข้อมูล</td>
                                        </tr>-->
                                        <?php
                                    } else {
                                        foreach ($placeList as $place) {
                                            ?>
                                            <tr style="">
                                                <!--ชื่อสถานที่-->
                                                <td><?= $place['name']; ?></td>

                                                <!--อำเภอ-->
                                                <td style="text-align: center"><?= $place['district']; ?></td>

                                                <?php
                                                if ($placeType === 'otop') {
                                                    ?>
                                                    <!--ตำบล-->
                                                    <td style="text-align: center"><?= $place['sub_district']; ?></td>

                                                    <!--หมู่บ้าน-->
                                                    <td style="text-align: center"><?= $place['village']; ?></td>
                                                    <?php
                                                }
                                                ?>

                                                <!--ภาพหน้า List-->
                                                <td style="text-align: center; cursor: pointer">
                                                    <a href="<?= (DIR_IMAGES . ($place['image_list'] ? $place['image_list'] : 'ic_no_image_2.png')); ?>"
                                                       data-lightbox="listImage" data-title="รูปภาพหน้า List '<?= $place['name']; ?>'">
                                                        <img src="<?= (DIR_IMAGES . ($place['image_list'] ? $place['image_list'] : 'ic_no_image_2.png')); ?>"
                                                             height="60px">
                                                    </a>
                                                </td>

                                                <!--ภาพ Cover-->
                                                <td style="text-align: center; cursor: pointer">
                                                    <a href="<?= (DIR_IMAGES . ($place['image_cover'] ? $place['image_cover'] : 'ic_no_image_2.png')); ?>"
                                                       data-lightbox="coverImage" data-title="รูปภาพ Cover '<?= $place['name']; ?>'">
                                                        <img src="<?= (DIR_IMAGES . ($place['image_cover'] ? $place['image_cover'] : 'ic_no_image_2.png')); ?>"
                                                             height="60px">
                                                    </a>
                                                </td>

                                                <!--แนะนำ-->
                                                <td style="text-align: center; vertical-align: top">
                                                    <span style="display: none">
                                                        <?= (int)$place['recommend'] === 1 ? 'on' : 'off' ?>>
                                                    </span>
                                                    <?php
                                                    //$entitiesCorrection = htmlentities($place['name'], ENT_COMPAT, 'UTF-8');
                                                    //$placeNameEncoded =  htmlentities($entitiesCorrection, ENT_COMPAT, 'UTF-8');
                                                    //$placeNameEncoded = htmlspecialchars($place['name']);
                                                    $placeNameEncoded = str_replace("'", '&#39;', $place['name']);
                                                    ?>
                                                    <input name="status" type="checkbox"
                                                           class="my-toggle"
                                                           data-toggle="toggle"
                                                           onChange="onChangePlaceRecommend(this, <?= $place['id']; ?>)"
                                                        <?= (int)$place['recommend'] === 1 ? 'checked' : '' ?>>
                                                </td>

                                                <td nowrap>
                                                    <form method="get" action="place_add_edit.php" style="display: inline; margin: 0">
                                                        <input type="hidden" name="place_type" value="<?= $placeType; ?>"/>
                                                        <input type="hidden" name="place_id" value="<?= $place['id']; ?>"/>

                                                        <?php
                                                        if ($userHasPermission) {
                                                            ?>
                                                            <button type="submit" class="btn btn-warning"
                                                                    style="margin-left: 3px">
                                                                <span class="fa fa-pencil"></span>&nbsp;
                                                                แก้ไข
                                                            </button>
                                                            <button type="button" class="btn btn-danger"
                                                                    style="margin-left: 3px; margin-right: 3px"
                                                                    onclick="onClickDelete(this, <?= $place['id']; ?>, '<?= $place['name']; ?>')">
                                                                <span class="fa fa-remove"></span>&nbsp;
                                                                ลบ
                                                            </button>
                                                            <?php
                                                        }
                                                        ?>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.box-body -->
                        </div>
                        <!-- /.box -->
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <?php require_once('../include/footer.inc'); ?>
    </div>
    <!-- ./wrapper -->

    <script>
        let downloadListDataTable = null;

        $(document).ready(function () {
            $('#tablePlace_<?= $placeType; ?>').DataTable({
                stateSave: true,
                stateDuration: -1, // sessionStorage
                order: [[3, 'desc']],
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
                },
                drawCallback: function (row, data) {
                    $('.my-toggle').bootstrapToggle();
                }
            });

            lightbox.option({
                fadeDuration: 500,
                imageFadeDuration: 500,
                resizeDuration: 500,
            });
        });

        function onClickAdd() {
            window.location.href = 'place_add_edit.php?place_type=<?= $placeType; ?>';
        }

        function onChangePlaceRecommend(element, placeId) {
            let result = confirm("ยืนยัน" + (element.checked ? 'ตั้ง' : 'ยกเลิก') + "เป็น<?= $placeType === 'otop' ? 'สินค้า' : 'สถานที่' ?>แนะนำ?");
            ;
            if (result) {
                doChangePlaceRecommend(placeId, (element.checked ? 1 : 0));
            } else {
                /*รีโหลด เพื่อให้สถานะ checkbox กลับมาเหมือนเดิม*/
                location.reload(true);
            }
        }

        function doChangePlaceRecommend(placeId, recommend) {
            let title = 'แก้ไขสถานะ <?= $placeType === 'otop' ? 'สินค้า' : 'สถานที่' ?>แนะนำ';

            $.post(
                '../api/api.php/update_place_recommend',
                {
                    placeId: placeId,
                    recommend: recommend,
                }
            ).done(function (data) {
                if (data.error_code === 0) {
                    location.reload(true);
                } else {
                    BootstrapDialog.show({
                        title: title + ' - ผิดพลาด',
                        message: data.error_message,
                        buttons: [{
                            label: 'ปิด',
                            action: function (self) {
                                self.close();
                                location.reload(true);
                            }
                        }]
                    });
                }
            }).fail(function () {
                BootstrapDialog.show({
                    title: title + ' - ผิดพลาด',
                    message: 'เกิดข้อผิดพลาดในการเชื่อมต่อ Server',
                    buttons: [{
                        label: 'ปิด',
                        action: function (self) {
                            self.close();
                            location.reload(true);
                        }
                    }]
                });
            });
        }

        function onClickDelete(element, id, title) {
            BootstrapDialog.show({
                title: 'ลบ<?= $pageTitle; ?>',
                message: 'ยืนยันลบ \'' + title + '\' ?',
                buttons: [{
                    label: 'ลบ',
                    action: function (self) {
                        doDeleteplace(id);
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

        function doDeleteplace(id) {
            $.post(
                '../api/api.php/delete_place',
                {
                    id: id,
                }
            ).done(function (data) {
                if (data.error_code === 0) {
                    location.reload(true);
                } else {
                    BootstrapDialog.show({
                        title: 'ลบ<?= $pageTitle; ?> - ผิดพลาด',
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
                    title: 'ลบ<?= $pageTitle; ?> - ผิดพลาด',
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

    <?php require_once('../include/foot.inc'); ?>
    <!-- DataTables -->
    <script src="../bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <!--Lightbox-->
    <script src="../dist/lightbox/js/lightbox.js"></script>

    </body>
    </html>

<?php
require_once '../include/foot_php.inc';
?>