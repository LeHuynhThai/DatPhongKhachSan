<?php
session_start();
include_once "config.php";
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

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Queenstown HTML5 Template | Homepage Style Two</title>
    <!-- Stylesheets -->
    <link href="css\bootstrap.css" rel="stylesheet">
    <link href="css\revolution-slider.css" rel="stylesheet">
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

        <!--Main Slider-->
        <section class="main-slider revolution-slider">
            <div class="tp-banner-container">
                <div class="tp-banner">
                    <ul>
                        <li data-transition="fade" data-slotamount="1" data-masterspeed="1000" data-thumb="images/main-slider/1.jpg" data-saveperformance="off" data-title="Awesome Title Here">
                            <img src="images/main-slider/1.jpg" alt="" data-bgposition="center top" data-bgfit="cover" data-bgrepeat="no-repeat">
                            <div class="tp-caption sfl sfb tp-resizeme" data-x="center" data-hoffset="0" data-y="center" data-voffset="-50" data-speed="1500" data-start="500" data-easing="easeOutExpo" data-splitin="none" data-splitout="none" data-elementdelay="0.01" data-endelementdelay="0.3" data-endspeed="1200" data-endeasing="Power4.easeIn">
                                <h3 class="welcome-heading">Welcome to Queenstown</h3>
                            </div>
                            <div class="tp-caption sfr sfb tp-resizeme" data-x="center" data-hoffset="15" data-y="center" data-voffset="50" data-speed="1500" data-start="1000" data-easing="easeOutExpo" data-splitin="none" data-splitout="none" data-elementdelay="0.01" data-endelementdelay="0.3" data-endspeed="1200" data-endeasing="Power4.easeIn">
                                <h1>Travel Brings Power & Love<br>back to your Life</h1>
                            </div>
                        </li>
                        <li data-transition="fade" data-slotamount="1" data-masterspeed="1000" data-thumb="images/main-slider/2.jpg" data-saveperformance="off" data-title="Awesome Title Here">
                            <img src="images/main-slider/2.jpg" alt="" data-bgposition="center top" data-bgfit="cover" data-bgrepeat="no-repeat">
                            <div class="tp-caption sfl sfb tp-resizeme" data-x="center" data-hoffset="0" data-y="center" data-voffset="-50" data-speed="1500" data-start="500" data-easing="easeOutExpo" data-splitin="none" data-splitout="none" data-elementdelay="0.01" data-endelementdelay="0.3" data-endspeed="1200" data-endeasing="Power4.easeIn">
                                <h3 class="welcome-heading">Welcome to Queenstown</h3>
                            </div>
                            <div class="tp-caption sfr sfb tp-resizeme" data-x="center" data-hoffset="15" data-y="center" data-voffset="50" data-speed="1500" data-start="1000" data-easing="easeOutExpo" data-splitin="none" data-splitout="none" data-elementdelay="0.01" data-endelementdelay="0.3" data-endspeed="1200" data-endeasing="Power4.easeIn">
                                <h1>Travel is the only thing that makes<br>you Happier Ever</h1>
                            </div>
                        </li>
                        <li data-transition="fade" data-slotamount="1" data-masterspeed="1000" data-thumb="images/main-slider/3.jpg" data-saveperformance="off" data-title="Awesome Title Here">
                            <img src="images/main-slider/3.jpg" alt="" data-bgposition="center top" data-bgfit="cover" data-bgrepeat="no-repeat">

                            <div class="tp-caption sfl sfb tp-resizeme" data-x="center" data-hoffset="0" data-y="center" data-voffset="-50" data-speed="1500" data-start="500" data-easing="easeOutExpo" data-splitin="none" data-splitout="none" data-elementdelay="0.01" data-endelementdelay="0.3" data-endspeed="1200" data-endeasing="Power4.easeIn">
                                <h3 class="welcome-heading">Welcome to Queenstown</h3>
                            </div>

                            <div class="tp-caption sfr sfb tp-resizeme" data-x="center" data-hoffset="15" data-y="center" data-voffset="50" data-speed="1500" data-start="1000" data-easing="easeOutExpo" data-splitin="none" data-splitout="none" data-elementdelay="0.01" data-endelementdelay="0.3" data-endspeed="1200" data-endeasing="Power4.easeIn">
                                <h1>Life is a Journey make the<br>most of it</h1>
                            </div>
                        </li>
                    </ul>
                    <div class="tp-bannertimer"></div>
                </div>
            </div>
        </section>
        <!--End Main Slider-->

        <!--Choose Us-->
        <section class="why-us-section">
            <div class="auto-container">
                <div class="row clearfix">
                    <!--Content Column-->
                    <div class="content-column col-lg-5 col-md-6 col-sm-12 col-xs-12">
                        <div class="sec-title-three">
                            <h2>Why Choose Us</h2>
                            <div class="text">Flipper Flipper faster than lightning no one you see is smarter</div>
                        </div>
                        <!--Icon Box One-->
                        <div class="icon-box-one">
                            <div class="inner-box">
                                <div class="icon-box">
                                    <span class="icon flaticon-pizza-slice"></span>
                                </div>
                                <h3>Tasty Restaurant</h3>
                                <div class="text">The smarter than he black gold all of them had hair of gold like their mother the young one.</div>
                                <a class="read-more">Read More <span class="arow fa fa-angle-double-right"></span></a>
                            </div>
                        </div>

                        <!--Icon Box One-->
                        <div class="icon-box-one">
                            <div class="inner-box">
                                <div class="icon-box">
                                    <span class="icon flaticon-shape-1"></span>
                                </div>
                                <h3>Purified Water</h3>
                                <div class="text">The smarter than he black gold all of them had hair of gold like their mother the young one.</div>
                                <a class="read-more">Read More <span class="arow fa fa-angle-double-right"></span></a>
                            </div>
                        </div>

                        <!--Icon Box One-->
                        <div class="icon-box-one">
                            <div class="inner-box">
                                <div class="icon-box">
                                    <span class="icon flaticon-wifi-connection-signal-symbol"></span>
                                </div>
                                <h3>High Speed Wifi</h3>
                                <div class="text">The smarter than he black gold all of them had hair of gold like their mother the young one.</div>
                                <a class="read-more">Read More <span class="arow fa fa-angle-double-right"></span></a>
                            </div>
                        </div>

                    </div>

                    <!--Form Column-->
                    <div class="form-column col-lg-7 col-md-6 col-sm-12 col-xs-12 clearfix">

                        <figure class="image-box wow fadeInRight" data-wow-delay="0ms" data-wow-duration="1500ms">
                            <img src="images\resource\man.png" alt="">
                        </figure>

                        <!--Form Box-->
                        <div class="form-box">

                            <!--Availability Form-->
                            <div class="availability-form">
                                <!--Availability Inner-->
                                <div class="availability-inner">
                                    <!--Title Box-->
                                    <div class="title-box">
                                        <h2>Check Availability</h2>
                                    </div>
                                    <div class="lower-box">
                                        <form method="post" action="contact.html">
                                            <!-- Column / Form Group -->
                                            <div class="column">
                                                <div class="form-group">
                                                    <input type="text" placeholder="Choose your Destination">
                                                </div>
                                            </div>

                                            <!-- Column / Form Group -->
                                            <div class="column">
                                                <div class="form-group">
                                                    <input class="datepicker" type="text" placeholder="Check In Date">
                                                    <label class="flaticon-calendar"></label>
                                                </div>
                                            </div>

                                            <!-- Column / Form Group -->
                                            <div class="column">
                                                <div class="form-group">
                                                    <input class="datepicker" type="text" placeholder="Check Out Date">
                                                    <label class="flaticon-calendar"></label>
                                                </div>
                                            </div>

                                            <!-- Column / Form Group -->
                                            <div class="column">
                                                <div class="form-group">
                                                    <select>
                                                        <option>Rooms</option>
                                                        <option>Luxury Room</option>
                                                        <option>Deluxe Room</option>
                                                        <option>Modern Room</option>
                                                        <option>Family Room</option>
                                                        <option>Villa Room</option>
                                                        <option>Double Room</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Column / Form Group -->
                                            <div class="column">
                                                <div class="form-group">
                                                    <select>
                                                        <option>Childrens</option>
                                                        <option>1</option>
                                                        <option>2</option>
                                                        <option>3</option>
                                                        <option>4</option>
                                                        <option>5</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Column / Form Group -->
                                            <div class="column">
                                                <div class="form-group">
                                                    <select>
                                                        <option>Adults</option>
                                                        <option>1</option>
                                                        <option>2</option>
                                                        <option>3</option>
                                                        <option>4</option>
                                                        <option>5</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Column / Form Group -->
                                            <div class="column">
                                                <div class="form-group text-center">
                                                    <button type="submit" class="theme-btn btn-style-one">Search</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!--End Availability Form-->

                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!--Spots Section Two-->
        <section class="spots-section-two">
            <div class="auto-container">

                <div class="sec-title-two">
                    <h2>Nearby Attractive Spots</h2>
                    <div class="separator-icon"></div>
                    <div class="text">Flipper Flipper faster than lightning no one you see is smarter than he black gold young one in curls never heard the word </div>
                </div>

                <div class="clearfix">

                    <!--Left Column-->
                    <div class="column col-lg-6 col-md-12 col-sm-12 col-xs-12">
                        <!-- Post Style One / Content Column / Image Bottom-->
                        <div class="post-style-one image-bottom">
                            <div class="inner-box">
                                <!--Content Box-->
                                <div class="content-box">
                                    <div class="inner-box">
                                        <!--Sec Title Three-->
                                        <div class="sec-title-three">
                                            <h2>Awesome Deals</h2>
                                            <div class="text">They call him Flipper Flipper faster than lightning no one you see is smarter than he black gold all of them had hair of gold like their mother the young one in curls never heard the word.</div>
                                            <a href="room-details.html" class="theme-btn btn-style-one">Read More</a>
                                        </div>
                                    </div>
                                </div>
                                <!--Image Box-->
                                <figure class="image-box">
                                    <img src="images\resource\post-1.jpg" alt="">
                                </figure>
                            </div>
                        </div>
                    </div>

                    <!--Right Column-->
                    <div class="column col-lg-6 col-md-12 col-sm-12 col-xs-12">

                        <!-- Post Style One / Content Column / Image Left -->
                        <div class="post-style-one image-left">
                            <div class="inner-box">
                                <div class="clearfix">

                                    <!--Content Box-->
                                    <div class="content-box small-box pull-right col-md-6 col-sm-6 col-xs-12">
                                        <div class="inner-box">
                                            <!--Sec Title Three-->
                                            <div class="sec-title-three">
                                                <h2>Dora Beach</h2>
                                                <div class="text">They call him Flipper Flipper faster than lightning no one you see is smarter than he black gold.</div>
                                                <a href="room-details.html" class="theme-btn btn-style-one">Read More</a>
                                            </div>
                                        </div>
                                    </div>

                                    <!--Image Box-->
                                    <figure class="image-box pull-left col-md-6 col-sm-6 col-xs-12">
                                        <img src="images\resource\post-2.jpg" alt="">
                                    </figure>

                                </div>
                            </div>
                        </div>

                        <!-- Post Style One / Content Column / Image Left -->
                        <div class="post-style-one image-right">
                            <div class="inner-box">
                                <div class="clearfix">

                                    <!--Content Box-->
                                    <div class="content-box small-box pull-left col-md-6 col-sm-6 col-xs-12">
                                        <div class="inner-box">
                                            <!--Sec Title Three-->
                                            <div class="sec-title-three">
                                                <h2>Gatewinds</h2>
                                                <div class="text">They call him Flipper Flipper faster than lightning no one you see is smarter than he black gold.</div>
                                                <a href="room-details.html" class="theme-btn btn-style-one">Read More</a>
                                            </div>
                                        </div>
                                    </div>

                                    <!--Image Box-->
                                    <figure class="image-box pull-right col-md-6 col-sm-6 col-xs-12">
                                        <img src="images\resource\post-3.jpg" alt="">
                                    </figure>

                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </section>

        <!--Danh sách phòng-->
        <section class="offered-section">
            <!--Offer Title-->
            <div class="offer-title" style="background-image: url(images/background/5.jpg);">
                <div class="sec-title-four">
                    <h2>Rooms We Offered</h2>
                    <div class="separator-icon"></div>
                    <div class="text">Flipper Flipper faster than lightning no one you see is smarter than he black gold young</div>
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


        <!--Call To Action-->
        <section class="call-to-action">
            <div class="auto-container">
                <div class="row clearfix">
                    <div class="column col-lg-7 col-md-12 col-xs-12">
                        <div class="text">DONT HESITATE ? FEEL FREE TO MAKE A CALL WITH US <span>+1-45-568-1256</span></div>
                    </div>
                    <div class="column col-lg-5 col-sm-12 col-xs-12 text-right">
                        <a href="contact.html" class="theme-btn btn-style-two">Contact Us</a>
                    </div>
                </div>
            </div>
        </section>

        <!--Default Section-->
        <section class="default-section">
            <div class="auto-container">
                <div class="row clearfix">
                    <!--Sponsor Column-->
                    <div class="sponsor-column col-md-5 col-sm-6 col-xs-12">

                        <!--Sponsers Style Two-->
                        <div class="sponsors-style-two">
                            <div class="clearfix">
                                <div class="column col-md-6 col-sm-6 col-xs-12">
                                    <div class="image-box">
                                        <a href="#"><img src="images\clients\6.png" alt=""></a>
                                    </div>
                                </div>
                                <div class="column col-md-6 col-sm-6 col-xs-12">
                                    <div class="image-box">
                                        <a href="#"><img src="images\clients\7.png" alt=""></a>
                                    </div>
                                </div>
                                <div class="column col-md-6 col-sm-6 col-xs-12">
                                    <div class="image-box">
                                        <a href="#"><img src="images\clients\8.png" alt=""></a>
                                    </div>
                                </div>
                                <div class="column col-md-6 col-sm-6 col-xs-12">
                                    <div class="image-box">
                                        <a href="#"><img src="images\clients\9.png" alt=""></a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!--Testimonial Carousel-->
                    <div class="testimonial-carousel col-md-7 col-sm-6 col-xs-12">

                        <div class="sec-title-three">
                            <h2>Our Happy Clients</h2>
                        </div>

                        <div class="single-item-carousel">

                            <!--testimonial block-->
                            <div class="testimonial-block-one">
                                <!--inner-box-->
                                <div class="inner-box">
                                    <div class="content-box">
                                        <div class="text"><span class="quote-left fa fa-quote-left"></span>They call him Flipper Flipper faster than lightning no one you see is smarter than he black gold all of them had hair of gold like their mother the young one in curls never heard the word impossible you than lightning no one you see is smarter than he black gold all of them had hair of gold like their mother the young one in curls never heard the word. <span class="quote-right fa fa-quote-right"></span></div>
                                    </div>
                                    <div class="author-info">
                                        <div class="author-thumb">
                                            <img src="images\resource\author-thumb-1.jpg" alt="">
                                        </div>
                                        <h4>ANGELO MATHEWS</h4>
                                        <div class="designation">Mixwix, Founder</div>
                                    </div>
                                </div>
                            </div>

                            <!--testimonial block-->
                            <div class="testimonial-block-one">
                                <!--inner-box-->
                                <div class="inner-box">
                                    <div class="content-box">
                                        <div class="text"><span class="quote-left fa fa-quote-left"></span>They call him Flipper Flipper faster than lightning no one you see is smarter than he black gold all of them had hair of gold like their mother the young one in curls never heard the word impossible you than lightning no one you see is smarter than he black gold all of them had hair of gold like their mother the young one in curls never heard the word. <span class="quote-right fa fa-quote-right"></span></div>
                                    </div>
                                    <div class="author-info">
                                        <div class="author-thumb">
                                            <img src="images\resource\author-thumb-1.jpg" alt="">
                                        </div>
                                        <h4>DAVID WILSON</h4>
                                        <div class="designation">Mixwix, Founder</div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!--Main Footer / Footer Style Two-->
        <footer class="main-footer footer-style-two">
            <div class="auto-container">

                <!--Subscribe Form-->
                <div class="subscribe-form">
                    <div class="news-letter">
                        <h2>Subscribe to Our Newsletters</h2>
                        <!--News Letter Style One-->
                        <form method="post" action="contact.html">
                            <div class="form-group">
                                <input type="email" name="email" value="" placeholder="Enter your Email Id here..." required="">
                                <button type="submit" class="theme-btn btn-style-two">SUBSCRIBE</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row clearfix">
                    <!-- Footer Widgets / Logo Widget -->
                    <div class="footer-column col-md-4 col-sm-6">
                        <div class="footer-widget logo-widget">
                            <div class="footer-logo"><a href="index.html"><img src="images\footer-logo.png" alt="Queenstone"></a></div>
                            <div class="text">They call him Flipper Flipper faster than lightning no one you see is smarter than he black gold all of them had hair of gold like their mother the young one.</div>
                            <!--Social Style Two-->
                            <ul class="social-icon-three">
                                <li><a class="fa fa-facebook-f" href="#"></a></li>
                                <li><a class="fa fa-twitter" href="#"></a></li>
                                <li><a class="fa fa-google-plus" href="#"></a></li>
                                <li><a class="fa fa-instagram" href="#"></a></li>
                                <li><a class="fa fa-behance" href="#"></a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Footer Widgets / Info Widget -->
                    <div class="footer-column col-md-4 col-sm-6">
                        <div class="footer-widget info-widget">
                            <!--Footer Title Two-->
                            <div class="footer-title-two">
                                <h2>Information Links</h2>
                            </div>

                            <div class="row clearfix">
                                <ul class="col-md-6 col-sm-6 col-xs-12">
                                    <li><a href="#">Site Map</a></li>
                                    <li><a href="#">Search Terms</a></li>
                                    <li><a href="#">Advanced Search</a></li>
                                    <li><a href="#">Reservations</a></li>
                                    <li><a href="#">Contact us</a></li>
                                </ul>
                                <ul class="col-md-6 col-sm-6 col-xs-12">
                                    <li><a href="#">Accommodations</a></li>
                                    <li><a href="#">Photos & Videos</a></li>
                                    <li><a href="#">Services & Amenities</a></li>
                                    <li><a href="#">Restaurants</a></li>
                                    <li><a href="#">Destinations</a></li>
                                </ul>
                            </div>

                        </div>
                    </div>

                    <!-- Footer Widgets / Get Touch Widget -->
                    <div class="footer-column col-md-4 col-sm-6">
                        <div class="footer-widget get-touch-widget">
                            <!--Footer Title Two-->
                            <div class="footer-title-two">
                                <h2>Get in Touch</h2>
                            </div>

                            <!--Contact Widget Box-->
                            <ul class="contact-widget-box">
                                <li><span class="icon flaticon-technology"></span> Contact us <br>
                                    <div class="text">(01) 123 786 4567</div>
                                </li>
                                <li><span class="icon flaticon-pin"></span>Our Address <br>
                                    <div class="text">A0 Lashley St, Victoria, Australia.</div>
                                </li>
                                <li><span class="icon flaticon-timer"></span>Working Hours <br>
                                    <div class="text">Mon - Sat : 8:00 am to 7:00 pm</div>
                                </li>
                            </ul>

                        </div>
                    </div>

                </div>

                <!--Copyright-->
                <div class="copyright">
                    &copy; Copyrights 2016 Queenstown. All Rights Reserved
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
    <script src="js\revolution.min.js"></script>
    <script src="js\jquery.fancybox.pack.js"></script>
    <script src="js\owl.js"></script>
    <script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    <script src="js\jquery.countdown.js"></script>
    <script src="js\wow.js"></script>
    <script src="js\script.js"></script>
</body>

</html>