<?php
error_reporting(E_ERROR | E_PARSE);
header('Content-type: text/html; charset=utf-8');
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');

session_start();
if (isset($_SESSION[KEY_SESSION_USER_ID])) {
    header('Location: place.php?place_type=tour');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Chainat Tourism Backend</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--===============================================================================================-->
    <link rel="icon" type="image/png" href="images/icons/favicon.ico"/>
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="fonts/Linearicons-Free-v1.0.0/icon-font.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="vendor/css-hamburgers/hamburgers.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="vendor/select2/select2.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="css/util.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <!--===============================================================================================-->
    <!-- jQuery 3 -->
    <!--<script src="../bower_components/jquery/dist/jquery.min.js"></script>-->
</head>
<body>
<div class="limiter">
    <div class="container-login100">
        <div class="wrap-login100 p-l-50 p-r-50 p-t-60 p-b-30">
            <form id="loginForm" class="login100-form validate-form">
                <div style="width: 100%; border: 0px solid red; text-align: center">
                    <img src="../images/logo.png"
                         style="width: 120px; height: 120px; border-radius: 60px"/>
                    <span class="login100-form-title p-b-50 p-t-10">
						Login
					</span>
                </div>

                <div class="wrap-input100 validate-input m-b-16" data-validate="ต้องกรอก Username">
                    <input class="input100" type="text" id="usernameInput" name="username" placeholder="Username">
                    <span class="focus-input100"></span>
                    <span class="symbol-input100">
							<span class="lnr lnr-user"></span>
						</span>
                </div>

                <div class="wrap-input100 validate-input m-b-16" data-validate="ต้องกรอก Password">
                    <input class="input100" type="password" id="passwordInput" name="password" placeholder="Password">
                    <span class="focus-input100"></span>
                    <span class="symbol-input100">
							<span class="lnr lnr-lock"></span>
						</span>
                </div>

                <!--<div class="contact100-form-checkbox m-l-4">
                    <input class="input-checkbox100" id="ckb1" type="checkbox" name="remember-me">
                    <label class="label-checkbox100" for="ckb1">
                        Remember me
                    </label>
                </div>-->

                <div class="container-login100-form-btn p-t-25">
                    <button class="login100-form-btn"
							type="submit">
                        Login
                    </button>
                </div>

                <!--<div class="text-center w-full p-t-42 p-b-22">
                    <span class="txt1">
                        Or login with
                    </span>
                </div>

                <a href="#" class="btn-face m-b-10">
                    <i class="fa fa-facebook-official"></i>
                    Facebook
                </a>

                <a href="#" class="btn-google m-b-10">
                    <img src="images/icons/icon-google.png" alt="GOOGLE">
                    Google
                </a>

                <div class="text-center w-full p-t-115">
                    <span class="txt1">
                        Not a member?
                    </span>

                    <a class="txt1 bo1 hov1" href="#">
                        Sign up now
                    </a>
                </div>-->
            </form>
        </div>
    </div>
</div>

<!--===============================================================================================-->
<script src="vendor/jquery/jquery-3.2.1.min.js"></script>
<!--===============================================================================================-->
<script src="vendor/bootstrap/js/popper.js"></script>
<script src="vendor/bootstrap/js/bootstrap.min.js"></script>
<!--===============================================================================================-->
<script src="vendor/select2/select2.min.js"></script>
<!--===============================================================================================-->
<script src="js/main.js"></script>

</body>
</html>
