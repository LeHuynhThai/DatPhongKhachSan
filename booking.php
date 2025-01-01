<?php
session_start();
// Kết nối cơ sở dữ liệu
include 'config.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
$is_logged_in = isset($_SESSION['user_name']) || isset($_COOKIE['user_name']);

// Kiểm tra cookie để tự động đăng nhập
if (!isset($_SESSION['user_name']) && isset($_COOKIE['user_name'])) {
    $_SESSION['user_name'] = $_COOKIE['user_name'];
}

// Query để lấy danh sách loại phòng
$sql = "SELECT * FROM room_types";
$result = $conn->query($sql);
$room_types = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $room_types[] = $row;
    }
}
// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_name'])) {
    echo "<script>alert('Vui lòng đăng nhập để đặt phòng.'); window.location.href='login_page.php';</script>";
    exit();
}

// Lấy user_name từ session
$user_name = $_SESSION['user_name'];

// Truy vấn số điện thoại của người dùng
$user_query = "SELECT phone FROM users WHERE name = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("s", $user_name);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows == 0) {
    echo "<script>alert('Không tìm thấy thông tin người dùng.'); window.location.href='booking.php';</script>";
    exit();
}

// Lấy số điện thoại từ kết quả truy vấn
$user_data = $user_result->fetch_assoc();
$phone_number = $user_data['phone'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $room_number = $_POST['room_number'];
    $children_count = $_POST['children_count'];
    $adults_count = $_POST['adults_count'];

    // Kiểm tra nếu room_number không được chọn
    if (empty($room_number)) {
        echo "<script>alert('Vui lòng chọn phòng.'); window.location.href='booking.php';</script>";
        exit();
    }

    // Xử lý ngày tháng
    $check_in_date = date('Y-m-d', strtotime($check_in));
    $check_out_date = date('Y-m-d', strtotime($check_out));

    // Kiểm tra phòng tồn tại
    $room_check_sql = "SELECT * FROM rooms WHERE room_number = ?";
    $stmt = $conn->prepare($room_check_sql);
    $stmt->bind_param("s", $room_number);
    $stmt->execute();
    $room_result = $stmt->get_result();

    if ($room_result->num_rows == 0) {
        echo "<script>alert('Phòng không tồn tại.'); window.location.href='booking.php';</script>";
        exit();
    }

    // Kiểm tra phòng trống
    $check_sql = "SELECT * FROM bookings WHERE room_number = ? AND (
        (check_in <= ? AND check_out >= ?) OR
        (check_in <= ? AND check_out >= ?)
    )";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("sssss", $room_number, $check_in_date, $check_in_date, $check_out_date, $check_out_date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Phòng đã được đặt trong khoảng thời gian này.'); window.location.href='booking.php';</script>";
    } else {
        // Thêm đặt phòng
        $sql = "INSERT INTO bookings (check_in, check_out, room_number, children_count, adults_count, user_name, phone_number) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssiss', $check_in_date, $check_out_date, $room_number, $children_count, $adults_count, $user_name, $phone_number);

        if ($stmt->execute()) {
            echo "<script>alert('Đặt phòng thành công!'); window.location.href='payment-method.php';</script>";
        } else {
            echo "<script>alert('Đặt phòng thất bại, vui lòng thử lại sau.'); window.location.href='booking.php';</script>";
        }
    }

    $stmt->close();
    $conn->close();
}
?>



<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Queenstown HTML5 Template | Choose Date</title>
    <!-- Stylesheets -->
    <link href="css\bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <link href="css\style.css" rel="stylesheet">
    <!--Favicon-->
    <link rel="shortcut icon" href="images\favicon.ico" type="image/x-icon">
    <link rel="icon" href="images\favicon.ico" type="image/x-icon">
    <!-- Responsive -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link href="css\responsive.css" rel="stylesheet">
    <!--[if lt IE 9]><script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.js"></script><![endif]-->
    <!--[if lt IE 9]><script src="js/respond.js"></script><![endif]-->

    <!-- Thêm jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Thêm jQuery UI -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

    <script>
        $(document).ready(function() {
            // Kích hoạt datepicker cho các trường Check In và Check Out
            $("#check_in").datepicker({
                dateFormat: 'yy-mm-dd', // Định dạng ngày (Y-m-d)
                minDate: 0, // Không cho chọn ngày trước ngày hiện tại
                onClose: function(selectedDate) {
                    // Khi người dùng chọn ngày ở Check In, cập nhật ngày Check Out có thể chọn
                    $("#check_out").datepicker("option", "minDate", selectedDate);
                }
            });

            $("#check_out").datepicker({
                dateFormat: 'yy-mm-dd', // Định dạng ngày (Y-m-d)
                minDate: 1, // Đảm bảo Check Out không thể chọn trước Check In
            });
        });
    </script>

</head>

<body>
    <div class="page-wrapper">

        <!-- Preloader -->
        <div class="preloader"></div>

        <!-- Main Header / Style Two-->
        <header class="main-header header-style-two">
            <!-- Header Top -->
            <div class="header-top">
                <div class="auto-container clearfix">
                    <!-- Top Left -->
                    <div class="top-left">
                        <ul class="clearfix">
                            <li><a href="#"><span class="icon fa fa-envelope"></span> support@queenstown.com</a></li>
                            <li><a href="#"><span class="icon fa fa-phone"></span> + 1-888-45678-890</a></li>
                        </ul>
                    </div>

                    <!-- Top Right -->
                    <div class="top-right">
                        <ul class="clearfix">
                            <!-- Nếu đã đăng nhập, hiển thị tài khoản và đăng xuất -->
                            <?php if ($is_logged_in): ?>
                                <li><a href="auth/account.php"><span class="icon fa fa-user"></span> Tài Khoản</a></li>
                                <li><a href="auth/logout.php"><span class="icon fa fa-sign-out"></span> Đăng xuất</a></li>
                                <!-- Nếu chưa đăng nhập, hiển thị đăng nhập và đăng ký -->
                            <?php else: ?>
                                <li><a href="auth/login.php"><span class="icon fa fa-sign-in"></span> Login</a></li>
                                <li><a href="auth/register.php"><span class="icon fa fa-user"></span> Register</a></li>
                            <?php endif; ?>
                            <li class="social-links">
                                <a class="fa fa-facebook" href="#"></a>
                                <a class="fa fa-twitter" href="#"></a>
                                <a class="fa fa-google-plus" href="#"></a>
                                <a class="fa fa-instagram" href="#"></a>
                                <a class="fa fa-behance" href="#"></a>
                            </li>
                        </ul>

                    </div>

                </div>
            </div><!-- Header Top End -->

            <!--Header-Upper-->
            <div class="header-upper">
                <div class="auto-container">
                    <div class="clearfix">

                        <div class="pull-left logo-outer">
                            <div class="logo"><a href="index.php"><img src="images\logo.jpg" alt="" title="Queenstown"></a></div>
                        </div>

                        <div class="pull-right right-menu clearfix">
                            <div class="nav-outer">
                                <!-- Main Menu -->
                                <nav class="main-menu">
                                    <div class="navbar-header">
                                        <!-- Toggle Button -->
                                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                                            <span class="icon-bar"></span>
                                            <span class="icon-bar"></span>
                                            <span class="icon-bar"></span>
                                        </button>
                                    </div>

                                    <div class="navbar-collapse collapse clearfix">
                                        <ul class="navigation clearfix">
                                            <li class=""><a href="rooms.php">Rooms</a>
                                            </li>
                                            <li class="dropdown">
                                                <a href="#">Rooms Type</a>
                                                <ul class="dropdown-menu">
                                                    <?php foreach ($room_types as $type): ?>
                                                        <li><a href="rooms.php?type=<?php echo $type['id']; ?>"><?php echo $type['type_name']; ?></a></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </li>
                                            <li class=""><a href="about.html">About Us</a>
                                            </li>
                                            <li><a href="contact.html">Contact Us</a></li>
                                        </ul>
                                    </div>
                                </nav>
                                <!-- Main Menu End-->

                                <!--Search Btn-->
                                <div class="search-btn search-box-btn"><span class="flaticon-search"></span></div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!--Bounce In Header-->
            <div class="bounce-in-header">
                <div class="auto-container clearfix">
                    <!--Logo-->
                    <div class="logo pull-left">
                        <a href="index.html" class="img-responsive"><img src="images\logo-small.png" alt="Queenstown"></a>
                    </div>

                    <!--Right Col-->
                    <div class="right-col pull-right">
                        <!-- Main Menu -->
                        <nav class="main-menu">
                            <div class="navbar-header">
                                <!-- Toggle Button -->
                                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                                    <span class="icon-bar"></span>
                                    <span class="icon-bar"></span>
                                    <span class="icon-bar"></span>
                                </button>
                            </div>
                        </nav>
                        <!-- Main Menu End-->
                    </div>

                </div>
            </div>

        </header>
        <!--End Main Header -->

        <!--Page Title-->
        <section class="page-title" style="background-image:url(images/background/bg-page-title-1.jpg);">
            <div class="auto-container">
                <h1>Đặt Phòng</h1>
            </div>
        </section>

        <!--Page Info-->
        <section class="page-info">
            <div class="auto-container clearfix">
                <div class="pull-left">
                    <ul class="bread-crumb clearfix">
                        <li><a href="index.php">Home</a></li>
                        <li>Đặt Phòng</li>
                    </ul>
                </div>
                <div class="pull-right">
                    <ul class="social-icon-four">
                        <li>Share :</li>
                        <li><a class="fa fa-facebook-f" href="#"></a></li>
                        <li><a class="fa fa-twitter" href="#"></a></li>
                        <li><a class="fa fa-google-plus" href="#"></a></li>
                        <li><a class="fa fa-instagram" href="#"></a></li>
                        <li><a class="fa fa-behance" href="#"></a></li>
                    </ul>
                </div>
            </div>
        </section>

        <!--Room Detail / Left Sidebar-->
        <div class="room-detail-container">

            <div class="auto-container">
                <div class="row clearfix">

                    <!--Sidebar-->
                    <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12">
                        <aside class="sidebar">
                            <!--Room Method-->
                            <div class="room-method active">
                                <div class="method-number">01</div>
                                <h3>Đặt Phòng</h3>
                            </div>

                            <!--Room Method-->
                            <div class="room-method">
                                <div class="method-number">02</div>
                                <h3>Thanh Toán</h3>
                            </div>

                            <!--Room Method-->
                            <div class="room-method">
                                <div class="method-number">03</div>
                                <h3>Xác Nhận</h3>
                            </div>

                        </aside>
                    </div>

                    <!--Booking Section-->
                    <div class="col-lg-9 col-md-8 col-sm-12 col-xs-12">
                        <section class="date-section">


                            <!--Availability Form Column-->
                            <div class="availability-form-column">

                                <section class="availability-form" style="background-image:url(images/background/6.jpg);">
                                    <!--Availability Inner-->
                                    <div class="availability-inner">
                                        <!--Title Box-->
                                        <div class="title-box">
                                            <h2>Đặt Phòng</h2>
                                            <div class="separator-icon"></div>
                                        </div>
                                        <div class="lower-box">
                                            <form method="post" action="booking.php">
                                                <div class="row clearfix">
                                                    <!-- Column / Form Group -->
                                                    <div class="column col-lg-6 col-sm-6 col-md-6 col-xs-12">
                                                        <div class="form-group">
                                                            <input class="datepicker" type="text" name="check_in" id="check_in" placeholder="Check In Date" required="">
                                                            <label class="flaticon-calendar"></label>
                                                        </div>
                                                    </div>

                                                    <!-- Column / Form Group -->
                                                    <div class="column col-lg-6 col-sm-6 col-md-6 col-xs-12">
                                                        <div class="form-group">
                                                            <input class="datepicker" type="text" name="check_out" id="check_out" placeholder="Check Out Date" required="">
                                                            <label class="flaticon-calendar"></label>
                                                        </div>
                                                    </div>
                                                    <!-- Column / Form Group -->
                                                    <div class="column col-lg-12 col-sm-12 col-md-12 col-xs-12">
                                                        <div class="form-group">
                                                            <select name="room_number" required>
                                                                <option value="" disabled selected>Chọn Phòng</option>
                                                                <?php
                                                                include 'config.php';
                                                                $result = $conn->query("SELECT room_number FROM rooms");
                                                                while ($row = $result->fetch_assoc()) {
                                                                    echo '<option value="' . htmlspecialchars($row['room_number']) . '">' . htmlspecialchars($row['room_number']) . '</option>';
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <!-- Column / Form Group - Trẻ em -->
                                                    <div class="column col-lg-6 col-sm-6 col-md-6 col-xs-12">
                                                        <div class="form-group">
                                                            <select name="children_count">
                                                                <option value="0">Childrens</option>
                                                                <option value="1">1 Children</option>
                                                                <option value="2">2 Childrens</option>
                                                                <option value="3">3 Childrens</option>
                                                                <option value="4">4 Childrens</option>
                                                                <option value="5">5 Childrens</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <!-- Column / Form Group - Người lớn -->
                                                    <div class="column col-lg-6 col-sm-6 col-md-6 col-xs-12">
                                                        <div class="form-group">
                                                            <select name="adults_count">
                                                                <option value="0">Adults</option>
                                                                <option value="1">1 Adult</option>
                                                                <option value="2">2 Adults</option>
                                                                <option value="3">3 Adults</option>
                                                                <option value="4">4 Adults</option>
                                                                <option value="5">5 Adults</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <!-- Thêm trường user_id nếu muốn gửi thông tin người dùng trong form -->
                                                    <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>" />
                                                    <!-- Column / Form Group - Nút xác nhận -->
                                                    <div class="column col-lg-12 col-sm-12 col-md-12 col-xs-12">
                                                        <div class="form-group">
                                                            <button type="submit" class="theme-btn btn-style-two">Xác Nhận</button>
                                                        </div>
                                                    </div>

                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </section>

                            </div>

                        </section>
                    </div>

                </div>

            </div>

        </div>

        <!--Main Footer / Footer Style three-->
        <footer class="main-footer footer-style-three">
            <div class="auto-container">
                <!--Footer Outer-->
                <div class="footer-outer">
                    <div class="row clearfix">

                        <!--Info Box-->
                        <div class="info-box col-md-4 col-sm-4 col-xs-12">
                            <div class="inner-box">
                                <div class="icon-box"><span class="flaticon-technology"></span></div>
                                <ul>
                                    <li>Contact us</li>
                                    <li><strong>(01) 123 786 4567</strong></li>
                                </ul>
                            </div>
                        </div>
                        <!--Info Box-->
                        <div class="info-box col-md-4 col-sm-4 col-xs-12">
                            <div class="inner-box">
                                <div class="icon-box"><span class="flaticon-pin"></span></div>
                                <ul>
                                    <li>Our Address</li>
                                    <li><strong>A09, New Orleans, USA</strong></li>
                                </ul>
                            </div>
                        </div>
                        <!--Info Box-->
                        <div class="info-box col-md-4 col-sm-4 col-xs-12">
                            <div class="inner-box">
                                <div class="icon-box"><span class="flaticon-timer"></span></div>
                                <ul>
                                    <li>Working Hours</li>
                                    <li><strong>Mon - Sat : 8:00 am to 7:00 pm</strong></li>
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="row clearfix">
                    <!-- Footer Widgets / Logo Widget -->
                    <div class="footer-column col-md-4 col-sm-6">
                        <div class="footer-widget logo-widget">
                            <!--Footer Title Two-->
                            <div class="footer-title-two">
                                <h2>About us</h2>
                            </div>
                            <div class="text">They call him Flipper Flipper faster than lightning no one you see is smarter than he black gold all of them had hair of gold like their mother the young one.</div>
                            <div class="footer-logo"><a href="index.html"><img src="images\footer-logo.png" alt="Queenstone"></a></div>
                        </div>
                    </div>

                    <!-- Footer Widgets / Temparature Widget -->
                    <div class="footer-column col-md-4 col-sm-6">
                        <div class="footer-widget temparature-widget">
                            <!--Footer Title Two-->
                            <div class="footer-title-two">
                                <h2>Temperature Links</h2>
                            </div>
                            <div class="icon-box"><img src="images\resource\cloud-icon.png" alt=""></div>
                            <!--Lower Box-->
                            <div class="lower-box clearfix">
                                <div class="country">United States <br><span>New Orleans</span></div>
                                <div class="temparature">
                                    25<sup>0</sup>C
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Widgets / Get Touch Widget -->
                    <div class="footer-column col-md-4 col-sm-12 col-xs-12">
                        <div class="footer-widget newsletter-form">
                            <!--Footer Title Two-->
                            <div class="footer-title-two">
                                <h2>Get in Touch</h2>
                            </div>

                            <!--Form-->
                            <form method="post" action="contact.html">
                                <div class="row clearfix">
                                    <!--Form Group-->
                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                        <div class="form-group">
                                            <input type="text" name="text" value="" placeholder="Name" required="">
                                        </div>
                                    </div>
                                    <!--Form Group-->
                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                        <div class="form-group">
                                            <input type="email" name="email" value="" placeholder="Email ID" required="">
                                        </div>
                                    </div>
                                    <!--Form Group-->
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <div class="form-group button-group">
                                            <button type="submit" class="btn-style-two theme-btn">SUBSCRIBE</button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>

                </div>

            </div>

            <!--Footer Bottom-->
            <div class="footer-bottom">
                <div class="auto-container">
                    <div class="row clearfix">
                        <!--Copyright -->
                        <div class="copyright col-md-6 col-sm-6 col-xs-12">
                            &copy; Copyrights 2016 Queenstown. All Rights Reserved
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12 text-right">
                            <ul class="social-icon-three">
                                <li><a class="fa fa-facebook-f" href="#"></a></li>
                                <li><a class="fa fa-twitter" href="#"></a></li>
                                <li><a class="fa fa-google-plus" href="#"></a></li>
                                <li><a class="fa fa-instagram" href="#"></a></li>
                                <li><a class="fa fa-behance" href="#"></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </footer>

    </div>
    <!--End pagewrapper-->

    <!--Scroll to top-->
    <div class="scroll-to-top scroll-to-target" data-target=".main-header"><span class="fa fa-long-arrow-up"></span></div>

    <!--Tìm kiếm-->
    <div id="search-popup" class="search-popup">
        <div class="close-search theme-btn"><span class="flaticon-unchecked"></span></div>
        <div class="popup-inner">
            <div class="search-form">
                <form method="get" action="search.php">
                    <div class="form-group">
                        <fieldset>
                            <input type="text" class="form-control" name="keyword" placeholder="Nhập từ khóa tìm kiếm..." required>
                            <input type="submit" value="Tìm kiếm" class="theme-btn">
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js\jquery.js"></script>
    <script src="js\bootstrap.min.js"></script>
    <script src="js\jquery.fancybox.pack.js"></script>
    <script src="js\owl.js"></script>
    <script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    <script src="js\jquery.countdown.js"></script>
    <script src="js\wow.js"></script>
    <script src="js\script.js"></script>
</body>

</html>