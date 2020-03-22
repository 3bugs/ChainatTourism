<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <!-- sidebar menu: : style can be found in sidebar.less -->
        <ul class="sidebar-menu" data-widget="tree" style="margin-top: 10px">
            <li>
                <a href="place.php?place_type=tour">
                    <i class="fa fa-image"></i> <span>สถานที่ท่องเที่ยว</span>
                </a>
            </li>
            <li>
                <a href="place.php?place_type=temple">
                    <i class="fa fa-bell"></i> <span>วัด</span>
                </a>
            </li>
            <li>
                <a href="place.php?place_type=restaurant">
                    <i class="fa fa-cutlery"></i> <span>ร้านอาหาร</span>
                </a>
            </li>
            <li>
                <a href="place.php?place_type=hotel">
                    <i class="fa fa-hotel"></i> <span>ที่พัก</span>
                </a>
            </li>
            <li>
                <a href="place.php?place_type=otop">
                    <i class="fa fa-star"></i> <span>สินค้า OTOP</span>
                </a>
            </li>
        </ul>
    </section>
    <!-- /.sidebar -->
</aside>
<script>
    $(document).ready(function () {
        $('#logoutMenu').click(function (e) {
            BootstrapDialog.show({
                title: 'Confirm Logout',
                message: 'ยืนยันออกจากระบบ?',
                buttons: [
                    {
                        label: 'ยกเลิก',
                        action: function (self) {
                            self.close();
                        }
                    },
                    {
                        label: 'ออกจากระบบ',
                        action: function (self) {
                            self.close();
                            doLogout();
                        }
                    }
                ]
            });
        });

        // Sets active link in Bootstrap menu
        // Add this code in a central place used\shared by all pages
        // like your _Layout.cshtml in ASP.NET MVC for example
        const url = (location.pathname + location.search).substr(1);
        $('a[href="' + url + '"]').parents('li,ul').addClass('active');
        //alert(url);
    });
</script>