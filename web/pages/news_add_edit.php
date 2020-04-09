<?php
require_once '../include/head_php.inc';

$newsId = $_GET['news_id'];

$pageTitle = 'ข่าวประชาสัมพันธ์';

$news = array();
if (isset($newsId)) {
    $newsId = $db->real_escape_string($newsId);

    $sql = "SELECT * FROM ct_news WHERE id = $newsId";

    if ($result = $db->query($sql)) {
        if ($result->num_rows > 0) {
            $news = $result->fetch_assoc();
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
    <body class="hold-transition skin-blue sidebar-mini fixed">

    <div class="wrapper">
        <?php require_once('../include/header.inc'); ?>
        <?php require_once('../include/sidebar.inc'); ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>
                    <?= (isset($newsId) ? 'แก้ไข' : 'เพิ่ม') . $pageTitle; ?>
                </h1>
            </section>

            <!-- Main content -->
            <section class="content">
                <form id="formAddNews"
                      autocomplete="off"
                      action="../api/api.php/<?= (isset($newsId) ? 'update_news' : 'add_news'); ?>"
                      method="post">

                    <input type="hidden" name="newsId" value="<?php echo $newsId; ?>"/>

                    <div class="row">
                        <div class="col-xs-12">

                            <div class="box box-warning">
                                <div class="box-header with-border">
                                    <h3 class="box-title">หัวข้อข่าว</h3>

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

                                    <!--หัวข้อข่าว-->
                                    <div class="row">
                                        <div class="col-md-12">
                                            <ul style="color: orangered; margin-top: 10px; margin-bottom: 15px">
                                                <li>ให้กรอกไว้ก่อน แต่ตอนนี้ยังไม่ได้เอาไปแสดงในแอพ</li>
                                            </ul>
                                            <div class="form-group">
                                                <!--<label for="inputTitle">หัวข้อ<?/*= $pageTitle; */?>:</label>-->
                                                <div class="input-group">
                                                    <span class="input-group-addon">
                                                        <i class="fa fa-font"></i>
                                                    </span>
                                                    <input type="text" class="form-control"
                                                           id="inputTitle"
                                                           name="title"
                                                           value="<?php echo(!empty($news) ? $news['title'] : ''); ?>"
                                                           placeholder="กรอกหัวข้อ<?= $pageTitle; ?>" required
                                                           oninvalid="this.setCustomValidity('กรอกหัวข้อ<?= $pageTitle; ?>')"
                                                           oninput="this.setCustomValidity('')">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.box-body -->
                            </div>
                            <!-- /.box -->

                            <!--รูปภาพข่าว-->
                            <div class="box box-warning">
                                <div class="box-header with-border">
                                    <h3 class="box-title">รูปภาพข่าว
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
                                    if (!empty($news)) {
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
                                                    <a href="<?= (DIR_IMAGES . ($news['image'] ? $news['image'] : 'ic_no_image_2.png')); ?>"
                                                       data-lightbox="listImage" data-title="<?= $news['title']; ?>">
                                                        <img src="<?= (DIR_IMAGES . ($news['image'] ? $news['image'] : 'ic_no_image_2.png')); ?>"
                                                             width="<?= $news['image'] ? '300px' : '100px'; ?>">
                                                    </a>
                                                </div>
                                                <!-- /.tab-pane -->
                                                <div class="tab-pane" id="list_image_tab_2" style="padding: 0px">
                                                    <ul style="color: orangered; margin-top: 10px; margin-bottom: 15px">
                                                        <li>คลิกในกรอบสี่เหลี่ยมเพื่อเลือกไฟล์ หรือลากไฟล์มาปล่อยในกรอบสี่เหลี่ยม</li>
                                                        <li>รูปภาพที่อัพโหลดใหม่ จะแทนที่รูปภาพปัจจุบัน</li>
                                                        <li>ไฟล์จะถูกบันทึกเข้าสู่ระบบ หลังจากกดปุ่ม "บันทึก"</li>
                                                    </ul>
                                                    <input id="image-file-upload" name="imageFile"
                                                           type="file" accept="image/*"
                                                           style="width: 500px; margin-top: 10px; border: 2px dotted #ccc; padding: 10px 10px 50px 10px"/>
                                                    <div id="image-upload-preview"
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
                                        <input id="image-file-upload" name="imageFile" required
                                               type="file" accept="image/*"
                                               style="width: 500px; margin-top: 10px; margin-bottom: 10px; border: 2px dotted #ccc; padding: 10px 10px 50px 10px"
                                               oninvalid="this.setCustomValidity('เลือกรูปภาพข่าว')"
                                               oninput="this.setCustomValidity('')"/>
                                        <div id="image-upload-preview"
                                             style="background: #efffd1; padding: 10px;"></div>
                                        <?php
                                    }
                                    ?>
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
        });

        $(document).ready(function () {
            lightbox.option({
                fadeDuration: 500,
                imageFadeDuration: 500,
                resizeDuration: 500,
            });

            $('#formAddNews #divLoading').hide();

            $('#formAddNews').submit(event => {
                event.preventDefault();
                doAddEditNews();
            });
        });

        $(function () {
            $('#image-upload-preview').hide();

            const imagePreview = function (input, placeToInsertImagePreview) {
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

            $('#image-file-upload').on('change', function () {
                imagePreview(this, 'div#image-upload-preview');
            });
        });

        function doAddEditNews() {
            $('#formAddNews #buttonSave').prop('disabled', true);
            $('#formAddNews #divLoading').show();

            const form = $('#formAddNews')[0];
            const formData = new FormData(form);

            $.ajax({
                url: '../api/api.php/<?= (isset($newsId) ? 'update_news' : 'add_news'); ?>',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST',
                success: function (data) {
                    $('#formAddNews #buttonSave').prop('disabled', false);
                    $('#formAddNews #divLoading').hide();

                    if (data.error_code === 0) {
                        BootstrapDialog.show({
                            title: '<?php echo(isset($newsId) ? 'แก้ไข' : 'เพิ่ม'); ?>',
                            message: data.error_message,
                            buttons: [{
                                label: 'ปิด',
                                action: function (self) {
                                    self.close();
                                    <?php
                                    if (!isset($newsId)) {
                                    ?>
                                    window.location.href = 'news.php';
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
                            title: '<?php echo(isset($newsId) ? 'แก้ไข' : 'เพิ่ม'); ?> - ผิดพลาด',
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
                    $('#formAddNews #buttonSave').prop('disabled', false);
                    $('#formAddNews #divLoading').hide();

                    BootstrapDialog.show({
                        title: '<?php echo(isset($newsId) ? 'แก้ไข' : 'เพิ่ม'); ?> - ผิดพลาด',
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
    </script>

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
