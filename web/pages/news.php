<?php
require_once '../include/head_php.inc';

$pageTitle = 'ข่าวประชาสัมพันธ์';

$sql = "SELECT *
        FROM ct_news
        ORDER BY id";
if ($result = $db->query($sql)) {
    $newsList = array();
    while ($row = $result->fetch_assoc()) {
        array_push($newsList, $row);
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
                                <table id="tableNews" class="table table-bordered table-striped">
                                    <thead>
                                    <tr>
                                        <th style="text-align: center; width: 50%;">หัวข้อข่าว</th>
                                        <th style="text-align: center; width: 50%;">รูปภาพข่าว</th>
                                        <th style="text-align: center;" nowrap>แสดง</th>
                                        <th style="text-align: center;">จัดการ</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (sizeof($newsList) == 0) {
                                        ?>
                                        <!--<tr valign="middle">
                                            <td colspan="20" align="center">ไม่มีข้อมูล</td>
                                        </tr>-->
                                        <?php
                                    } else {
                                        foreach ($newsList as $news) {
                                            ?>
                                            <tr style="">
                                                <!--หัวข้อข่าว-->
                                                <td><?= $news['title']; ?></td>

                                                <!--ภาพข่าว-->
                                                <td style="text-align: center; cursor: pointer">
                                                    <a href="<?= (DIR_IMAGES . ($news['image'] ? $news['image'] : 'ic_no_image_2.png')); ?>"
                                                       data-lightbox="listImage" data-title="รูปภาพข่าว '<?= $news['title']; ?>'">
                                                        <img src="<?= (DIR_IMAGES . ($news['image'] ? $news['image'] : 'ic_no_image_2.png')); ?>"
                                                             height="60px">
                                                    </a>
                                                </td>

                                                <!--แสดง-->
                                                <td style="text-align: center; vertical-align: top">
                                                    <span style="display: none">
                                                        <?= (int)$news['active'] === 1 ? 'on' : 'off' ?>>
                                                    </span>
                                                    <?php
                                                    //$placeNameEncoded = str_replace("'", '&#39;', $news['name']);
                                                    ?>
                                                    <input name="status" type="checkbox"
                                                           class="my-toggle"
                                                           data-toggle="toggle"
                                                           onChange="onChangeActive(this, <?= $news['id']; ?>)"
                                                        <?= (int)$news['active'] === 1 ? 'checked' : '' ?>>
                                                </td>

                                                <td nowrap>
                                                    <form method="get" action="news_add_edit.php" style="display: inline; margin: 0">
                                                        <input type="hidden" name="news_id" value="<?= $news['id']; ?>"/>

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
                                                                    onclick="onClickDelete(this, <?= $news['id']; ?>)">
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
            $('#tableNews').DataTable({
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
            window.location.href = 'news_add_edit.php';
        }

        function onChangeActive(element, newsId) {
            let result = confirm('ยืนยัน' + (element.checked ? 'เปิด' : 'ปิด') + 'การแสดงข่าวนี้?');
            ;
            if (result) {
                doChangeActive(newsId, (element.checked ? 1 : 0));
            } else {
                /*รีโหลด เพื่อให้สถานะ checkbox กลับมาเหมือนเดิม*/
                location.reload(true);
            }
        }

        function doChangeActive(newsId, active) {
            let title = 'แก้ไขสถานะการแสดงข่าว';

            $.post(
                '../api/api.php/update_news_active',
                {
                    newsId: newsId,
                    active: active,
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

        function onClickDelete(element, id) {
            BootstrapDialog.show({
                title: 'ลบ<?= $pageTitle; ?>',
                message: 'ยืนยันลบข่าวนี้?',
                buttons: [{
                    label: 'ลบ',
                    action: function (self) {
                        doDeleteNews(id);
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

        function doDeleteNews(id) {
            $.post(
                '../api/api.php/delete_news',
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
