<?php
require_once 'api/global.php';

error_reporting(E_ERROR | E_PARSE);
header('Content-type: text/html; charset=utf-8');
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');

session_start();
if (isset($_SESSION[KEY_SESSION_USER_ID])) {
    header('Location: index.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <?php require_once('include/head.inc'); ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    <?php
    if (isset($_SESSION[KEY_SESSION_USER_ID])) {
        require_once('include/header.inc');
        require_once('include/sidebar.inc');
    }
    ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                เข้าสู่ระบบ
            </h1>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-md-3"></div>
                <!-- login form -->
                <div class="col-md-6">
                    <!-- Horizontal Form -->
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title">Login</h3>
                        </div>
                        <!-- /.box-header -->
                        <!-- form start -->
                        <form id="loginForm" role="form" style="margin-top: 20px">
                            <div class="box-body">
                                <!--ชื่อผู้ใช้-->
                                <div class="form-group">
                                    <label for="usernameInput">ชื่อผู้ใช้:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-user"></i>
                                        </span>
                                        <input type="text" class="form-control" id="usernameInput"
                                               placeholder="กรอกชื่อผู้ใช้งาน" required
                                               oninvalid="this.setCustomValidity('กรอกชื่อผู้ใช้')"
                                               oninput="this.setCustomValidity('')">
                                    </div>
                                </div>
                                <!--รหัสผ่าน-->
                                <div class="form-group">
                                    <label for="passwordInput">รหัสผ่าน:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-lock"></i>
                                        </span>
                                        <input type="password" class="form-control" id="passwordInput"
                                               placeholder="กรอกรหัสผ่าน" required
                                               oninvalid="this.setCustomValidity('กรอกรหัสผ่าน')"
                                               oninput="this.setCustomValidity('')">
                                    </div>
                                </div>
                            </div>
                            <!-- /.box-body -->
                            <div class="box-footer">
                                <button id="loginButton" type="submit" class="btn btn-info pull-right">
                                    <span class="fa fa-sign-in"></span>&nbsp;
                                    เข้าสู่ระบบ
                                </button>
                            </div>
                            <!-- /.box-footer -->
                        </form>
                    </div>
                    <!-- /.box -->
                </div>
                <!--/.login form -->
                <div class="col-md-3"></div>
            </div>
            <!-- /.row -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <?php require_once('include/footer.inc'); ?>
</div>
<!-- ./wrapper -->

<script>
    $(document).ready(function () {
        $('#loginForm').submit(function (e) {
            e.preventDefault();
            $.post(
                'api/api.php/login',
                {
                    username: $('#usernameInput').val(),
                    password: $('#passwordInput').val()
                }
            ).done(function (data) {
                if (data.error_code === 0) {
                    if (data.login_success) {
                        window.location.href = "index.php";
                    } else {
                        BootstrapDialog.show({
                            title: 'Login',
                            message: data.error_message,
                            buttons: [{
                                label: 'ปิด',
                                action: function(self){
                                    self.close();
                                }
                            }]
                        });
                    }
                } else {
                    BootstrapDialog.show({
                        title: 'Login',
                        message: data.error_message,
                        buttons: [{
                            label: 'ปิด',
                            action: function(self){
                                self.close();
                            }
                        }]
                    });
                }
            }).fail(function () {
                BootstrapDialog.show({
                    title: 'Login',
                    message: 'เกิดข้อผิดพลาดในการเชื่อมต่อ Server',
                    buttons: [{
                        label: 'ปิด',
                        action: function(self){
                            self.close();
                        }
                    }]
                });
            });
        });
    });
</script>

<?php require_once('include/foot.inc'); ?>
</body>
</html>
