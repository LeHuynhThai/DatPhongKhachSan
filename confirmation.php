<?php
session_start();

// Kiểm tra nếu người dùng đã chọn phương thức thanh toán
$payment_status = "Chưa xác định"; // Mặc định

if (isset($_GET['payment_method'])) {
    $payment_method = $_GET['payment_method'];
    if ($payment_method === 'bank_transfer') {
        $payment_status = "Đã Thanh Toán";
    } elseif ($payment_method === 'direct_payment') {
        $payment_status = "Chưa Thanh Toán";
    }
}

// Kết nối cơ sở dữ liệu
require_once 'config.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
$is_logged_in = isset($_SESSION['user_id']) || isset($_COOKIE['user_id']);

// Kiểm tra cookie để tự động đăng nhập
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id']) && isset($_COOKIE['user_name'])) {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
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

// Kiểm tra và lấy booking_id từ URL
if (!isset($_GET['booking_id']) || empty($_GET['booking_id'])) {
    die("Không tìm thấy thông tin booking_id.");
}
$booking_id = intval($_GET['booking_id']);

// Lấy thông tin đặt phòng và phòng
try {
    // Truy vấn thông tin đặt phòng
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();

    if (!$booking) {
        die("Không tìm thấy thông tin đặt phòng.");
    }

    // Truy vấn thông tin phòng
    $stmt = $conn->prepare("SELECT * FROM rooms WHERE room_number = ?");
    $stmt->bind_param("s", $booking['room_number']);
    $stmt->execute();
    $room = $stmt->get_result()->fetch_assoc();

    if (!$room) {
        die("Không tìm thấy thông tin phòng.");
    }

    // Tính tổng tiền
    $checkIn = new DateTime($booking['check_in']);
    $checkOut = new DateTime($booking['check_out']);
    $days = $checkOut->diff($checkIn)->days;
    $totalPrice = $days * $room['price'];
} catch (Exception $e) {
    die("Lỗi xử lý: " . $e->getMessage());
}
?>




<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Queenstown HTML5 Template | Confirmation</title>
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
    <style>
        .booking-details-container {
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .booking-title,
        .room-title {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }

        .booking-info,
        .room-info {
            margin-bottom: 20px;
        }

        .booking-info p,
        .room-info p {
            font-size: 16px;
            color: #555;
            margin: 5px 0;
        }

        .room-image {
            width: 100%;
            max-width: 400px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .total-amount {
            font-size: 18px;
            color: #007bff;
            font-weight: bold;
            margin-top: 20px;
        }

        .payment-status {
            font-size: 16px;
            font-weight: bold;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
        }

        .payment-status.paid {
            background-color: #d4edda;
            color: #155724;
        }

        .payment-status.unpaid {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
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

        </header>
        <!--End Main Header -->

        <!--Page Title-->
        <section class="page-title" style="background-image:url(images/background/bg-page-title-1.jpg);">
            <div class="auto-container">
                <h1>Xác Nhận</h1>
            </div>
        </section>

        <!--Page Info-->
        <section class="page-info">
            <div class="auto-container clearfix">
                <div class="pull-left">
                    <ul class="bread-crumb clearfix">
                        <li><a href="index.php">Home</a></li>
                        <li>Confirmation</li>
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
        <div class="booking-details-container">
            <h1 class="booking-title">Thông tin đặt phòng</h1>
            <div class="booking-info">
                <p><strong>Mã đặt phòng:</strong> <?= htmlspecialchars($booking['id']); ?></p>
                <p><strong>Tên khách:</strong> <?= htmlspecialchars($booking['user_name']); ?></p>
                <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($booking['phone_number']); ?></p>
                <p><strong>Ngày nhận phòng:</strong> <?= htmlspecialchars($booking['check_in']); ?></p>
                <p><strong>Ngày trả phòng:</strong> <?= htmlspecialchars($booking['check_out']); ?></p>
            </div>

            <h1 class="room-title">Chi tiết phòng</h1>
            <div class="room-info">
                <img src="admin/<?= htmlspecialchars($room['image_path']); ?>" alt="Ảnh phòng" class="room-image">
                <p><strong>Tên phòng:</strong> <?= htmlspecialchars($room['room_number']); ?></p>
                <p><strong>Giá phòng:</strong> $<?= htmlspecialchars($room['price']); ?>/đêm</p>
                <p><strong>Mô tả:</strong> <?= htmlspecialchars($room['description']); ?></p>
            </div>

            <div class="total-amount">
                Tổng tiền: $<?= htmlspecialchars($totalPrice); ?>
                <span style="font-size: 14px;">(Thời gian lưu trú: <?= $days; ?> ngày)</span>
            </div>

            <div class="payment-status <?= $payment_status === 'Đã Thanh Toán' ? 'paid' : 'unpaid'; ?>">
                Trạng thái thanh toán: <?= htmlspecialchars($payment_status); ?>
            </div>
        </div>
    </div>
    <br>
    <br>
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
                        <div class="text">They call him Flipper Flipper faster than lightning no one you see is
                            smarter than he black gold all of them had hair of gold like their mother the young one.
                        </div>
                        <div class="footer-logo"><a href="index.html"><img src="images\footer-logo.png"
                                    alt="Queenstone"></a></div>
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
                                        <input type="email" name="email" value="" placeholder="Email ID"
                                            required="">
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
    <div class="scroll-to-top scroll-to-target" data-target=".main-header"><span class="fa fa-long-arrow-up"></span>
    </div>

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