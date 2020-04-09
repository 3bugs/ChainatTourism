(function ($) {
    "use strict";

    /*==================================================================
    [ Validate ]*/
    const input = $('.validate-input .input100');

    $('.validate-form').on('submit', function (event) {
        let check = true;

        for (let i = 0; i < input.length; i++) {
            if (validate(input[i]) == false) {
                showValidate(input[i]);
                check = false;
            }
        }

        event.preventDefault();
        if (check) {
            doLogin();
        }
        //return check;
    });

    $('.validate-form .input100').each(function () {
        $(this).focus(function () {
            hideValidate(this);
        });
    });

    function doLogin() {
        const username = $('#usernameInput').val();
        const password = $('#passwordInput').val();

        $.post(
            '../api/api.php/login',
            {
                username,
                password,
            }
        ).done(function (data) {
            if (data.error_code === 0) {
                location.replace('place.php?place_type=tour');
            } else {
                alert(data.error_message);
            }
        }).fail(function () {
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ Server');
        });
    }

    function validate(input) {
        if ($(input).attr('type') == 'email' || $(input).attr('name') == 'email') {
            if ($(input).val().trim().match(/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{1,5}|[0-9]{1,3})(\]?)$/) == null) {
                return false;
            }
        } else {
            if ($(input).val().trim() == '') {
                return false;
            }
        }
    }

    function showValidate(input) {
        const thisAlert = $(input).parent();

        $(thisAlert).addClass('alert-validate');
    }

    function hideValidate(input) {
        const thisAlert = $(input).parent();

        $(thisAlert).removeClass('alert-validate');
    }
})(jQuery);
