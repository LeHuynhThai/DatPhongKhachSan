<?php
session_start();

// Kết nối cơ sở dữ liệu
include 'config.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
$is_logged_in = isset($_SESSION['user_id']) || isset($_COOKIE['user_id']);

// Kiểm tra cookie để tự động đăng nhập
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id']) && isset($_COOKIE['user_name'])) {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['user_name'] = $_COOKIE['user_name'];
}

// Lấy thông tin người dùng đang đăng nhập
$user_name = $_SESSION['user_name'];

// Truy vấn thông tin đặt phòng của người dùng
$sql = "SELECT * FROM bookings WHERE user_name = ? ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $booking = $result->fetch_assoc();
} else {
    echo "<p>Không tìm thấy đặt phòng nào.</p>";
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Queenstown HTML5 Template | Payment Method</title>
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
        .invoice-payment-wrapper {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-top: 20px;
        }

        .invoice,
        .payment-section {
            flex: 1;
            /* Cân bằng kích thước */
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            background: #f9f9f9;
        }

        .invoice {
            margin-right: 20px;
        }

        .payment-options ul {
            list-style: none;
            padding: 0;
        }

        .payment-options li {
            margin-bottom: 15px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .next-back-btns {
            display: flex;
            justify-content: center;
            /* Căn giữa theo chiều ngang */
            margin-top: 20px;
        }

        .theme-btn.confirm-btn {
            padding: 10px 20px;
            background-color: #007bff;
            /* Màu nền của nút */
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .theme-btn.confirm-btn:hover {
            background-color: #0056b3;
            /* Màu khi hover */
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
                            <div class="logo"><a href="index.php"><img src="images\logo.jpg" alt=""
                                        title="Queenstown"></a></div>
                        </div>

                        <div class="pull-right right-menu clearfix">
                            <div class="nav-outer">
                                <!-- Main Menu -->
                                <nav class="main-menu">
                                    <div class="navbar-header">
                                        <!-- Toggle Button -->
                                        <button type="button" class="navbar-toggle" data-toggle="collapse"
                                            data-target=".navbar-collapse">
                                            <span class="icon-bar"></span>
                                            <span class="icon-bar"></span>
                                            <span class="icon-bar"></span>
                                        </button>
                                    </div>

                                    <div class="navbar-collapse collapse clearfix">
                                        <ul class="navigation clearfix">
                                            <li class="dropdown"><a href="#">About Us</a>
                                            </li>
                                            <li class="current dropdown"><a href="#">Rooms</a>
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
                <h1>Thanh Toán</h1>
            </div>
        </section>

        <!--Page Info-->
        <section class="page-info">
            <div class="auto-container clearfix">
                <div class="pull-left">
                    <ul class="bread-crumb clearfix">
                        <li><a href="index.php">Home</a></li>
                        <li>Payment Mode</li>
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
                            <div class="room-method">
                                <div class="method-number">01</div>
                                <h3>Đặt Phòng</h3>
                            </div>
                            <!--Room Method-->
                            <div class="room-method active">
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
                    <!-- Nội dung hóa đơn và phương thức thanh toán -->
                    <div class="col-lg-9 col-md-8 col-sm-12 col-xs-12">
                        <div class="invoice-payment-wrapper">
                            <!-- Phần Hóa Đơn -->
                            <?php if (isset($booking)): ?>
                                <div class="invoice">
                                    <h3>Thông Tin Hóa Đơn</h3>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Mã Đặt Phòng</th>
                                            <td><?= $booking['id']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Họ Tên</th>
                                            <td><?= htmlspecialchars($booking['user_name']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Số Điện Thoại</th>
                                            <td><?= htmlspecialchars($booking['phone_number']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Phòng</th>
                                            <td><?= htmlspecialchars($booking['room_number']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Ngày Nhận Phòng</th>
                                            <td><?= htmlspecialchars($booking['check_in']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Ngày Trả Phòng</th>
                                            <td><?= htmlspecialchars($booking['check_out']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Số Người Lớn</th>
                                            <td><?= htmlspecialchars($booking['adults_count']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Số Trẻ Em</th>
                                            <td><?= htmlspecialchars($booking['children_count']); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            <?php endif; ?>
                            <!-- Phần Phương Thức Thanh Toán -->
                            <div class="payment-section">
                                <div class="payment-options">
                                    <ul>
                                        <li>
                                            <div class="radio-option">
                                                <input type="radio" name="payment-group" id="payment-2" value="bank_transfer" checked>
                                                <label for="payment-2">Chuyển Khoản
                                                    <span class="small-text">Vui lòng chuyển khoản trực tiếp vào tài khoản ngân hàng của chúng tôi. Đơn hàng sẽ không được xử lý cho đến khi chúng tôi nhận được thanh toán.</span>
                                                </label>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="radio-option">
                                                <input type="radio" name="payment-group" id="payment-1" value="direct_payment">
                                                <label for="payment-1">Thanh Toán Trực Tiếp</label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <!--Next Back Btns-->
                                <div class="next-back-btns clearfix">
                                    <a class="theme-btn pull-right" id="confirm-btn">CONFIRM</a>
                                </div>
                            </div>
                        </div>
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
    <script>
        document.getElementById('confirm-btn').addEventListener('click', function() {
            // Lấy giá trị của tùy chọn được chọn
            const paymentMethod = document.querySelector('input[name="payment-group"]:checked').value;

            // Lấy URL hiện tại và thêm thông tin vào query string
            const bookingId = "<?= $booking['id']; ?>"; // Giá trị booking_id từ PHP
            const confirmationUrl = `confirmation.php?booking_id=${bookingId}&payment_method=${paymentMethod}`;

            // Chuyển hướng đến URL mới
            window.location.href = confirmationUrl;
        });
    </script>

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