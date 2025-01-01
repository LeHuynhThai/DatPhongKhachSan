<?php
session_start();
// Import file config.php
include 'config.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
$is_logged_in = isset($_SESSION['user_id']) || isset($_COOKIE['user_id']);

// Kiểm tra cookie để tự động đăng nhập
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id']) && isset($_COOKIE['user_name'])) {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['user_name'] = $_COOKIE['user_name'];
}

// Lấy type_id từ URL nếu có
$type_id = isset($_GET['type']) ? (int)$_GET['type'] : null;

// Số phòng hiển thị trên mỗi trang
$items_per_page = 6;

// Lấy trang hiện tại
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page);

// Tính offset
$offset = ($current_page - 1) * $items_per_page;

// Xây dựng câu query cơ bản
$base_query = "FROM rooms 
    JOIN room_type_assignments ON rooms.id = room_type_assignments.room_id 
    JOIN room_types ON room_type_assignments.room_type_id = room_types.id";
if ($type_id) {
    $base_query .= " WHERE room_type_assignments.room_type_id = ?";
}

// Lấy tổng số phòng thực sự có dữ liệu
$total_query = "SELECT COUNT(DISTINCT rooms.id) as total " . $base_query;
$stmt = $conn->prepare($total_query);
if ($type_id) {
    $stmt->bind_param('i', $type_id);
}
$stmt->execute();
$total_result = $stmt->get_result();
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $items_per_page);

// Truy vấn lấy danh sách phòng có phân trang
$sql = "SELECT rooms.*, room_types.type_name as room_type_name
        $base_query
        GROUP BY rooms.id
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if ($type_id) {
    $stmt->bind_param('iii', $type_id, $items_per_page, $offset);
} else {
    $stmt->bind_param('ii', $items_per_page, $offset);
}
$stmt->execute();
$result = $stmt->get_result();


// Kiểm tra và xử lý kết quả
$rooms = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }
}

// Lấy thông tin loại phòng hiện tại nếu có
$current_room_type = null;
if ($type_id) {
    $type_query = "SELECT * FROM room_types WHERE id = ?";
    $stmt = $conn->prepare($type_query);
    $stmt->bind_param('i', $type_id);
    $stmt->execute();
    $current_room_type = $stmt->get_result()->fetch_assoc();
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

$conn->close();
?>



<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Queenstown HTML5 Template | Rooms</title>
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
        .pagination-wrapper {
            text-align: center;
            margin: 40px 0;
        }

        .pagination {
            display: inline-flex;
            background: #fff;
            padding: 8px;
            border-radius: 50px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
        }

        .pagination li {
            list-style: none;
            line-height: 45px;
            text-align: center;
            font-size: 18px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .pagination li.numb {
            border-radius: 50%;
            height: 45px;
            width: 45px;
            margin: 0 3px;
        }

        .pagination li.dots {
            font-size: 22px;
            cursor: default;
            padding: 0 10px;
        }

        .pagination li.btn {
            padding: 0 20px;
        }

        .pagination li.prev {
            border-radius: 25px 5px 5px 25px;
        }

        .pagination li.next {
            border-radius: 5px 25px 25px 5px;
        }

        .pagination li.active,
        .pagination li.numb:hover,
        .pagination li.btn:hover {
            color: #fff;
            background: #c5a496;
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
                <h1><?php echo $current_room_type ? htmlspecialchars($current_room_type['type_name']) : 'Tất cả phòng'; ?></h1>
            </div>
        </section>

        <!--Page Info-->
        <section class="page-info">
            <div class="auto-container clearfix">
                <div class="pull-left">
                    <ul class="bread-crumb clearfix">
                        <li><a href="index.php">Home</a></li>
                        <li>Rooms</li>
                        <?php if ($current_room_type): ?>
                            <li><?php echo htmlspecialchars($current_room_type['type_name']); ?></li>
                        <?php endif; ?>
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

        <!--Rooms Section-->
        <section class="rooms-section">
            <div class="auto-container">
                <div class="row clearfix">
                    <?php foreach ($rooms as $room): ?>
                        <!--Room Block-->
                        <div class="room-block col-md-4 col-sm-6 col-xs-12">
                            <div class="inner-box">
                                <div class="image-box">
                                    <?php
                                    $imagePath = htmlspecialchars($room['image_path']);
                                    ?>
                                    <a href="#">
                                        <img src="admin/<?php echo htmlspecialchars($room['image_path']); ?>" alt="Room Image">
                                    </a>
                                    <div class="overlay-box">
                                        <a href="room-details.php?id=<?php echo $room['id']; ?>" class="view-more theme-btn btn-style-two">View More</a>
                                    </div>
                                </div>

                                <div class="lower-content">
                                    <h3><a href="room-details.php?id=<?php echo $room['id']; ?>">Room <?php echo htmlspecialchars($room['room_number']); ?></a></h3>
                                    <div class="clearfix">
                                        <div class="price"><?php echo htmlspecialchars($room['price']); ?> VND<span>PER<br>NIGHT</span></div>
                                        <div class="description">
                                            <?php echo htmlspecialchars($room['description']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Pagination -->
        <div class="pagination-wrapper">
            <?php if ($total_pages > 1): ?>
                <ul class="pagination">
                    <?php if ($current_page > 1): ?>
                        <li class="btn prev">
                            <a href="?page=<?php echo $current_page - 1; ?>">Prev</a>
                        </li>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);

                    if ($start_page > 1) {
                        echo '<li class="numb"><a href="?page=1">1</a></li>';
                        if ($start_page > 2) {
                            echo '<li class="dots">...</li>';
                        }
                    }

                    for ($i = $start_page; $i <= $end_page; $i++) {
                        if ($i == $current_page) {
                            echo '<li class="numb active"><span>' . $i . '</span></li>';
                        } else {
                            echo '<li class="numb"><a href="?page=' . $i . '">' . $i . '</a></li>';
                        }
                    }

                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo '<li class="dots">...</li>';
                        }
                        echo '<li class="numb"><a href="?page=' . $total_pages . '">' . $total_pages . '</a></li>';
                    }
                    ?>

                    <?php if ($current_page < $total_pages): ?>
                        <li class="btn next">
                            <a href="?page=<?php echo $current_page + 1; ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            <?php endif; ?>
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