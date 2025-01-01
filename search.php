<?php
require_once 'config.php'; // Kết nối cơ sở dữ liệu

// Lấy từ khóa tìm kiếm
$searchKeyword = $_GET['keyword'] ?? '';
$rooms = [];

if (!empty($searchKeyword)) {
    $stmt = $conn->prepare("
        SELECT * 
        FROM rooms 
        WHERE CAST(id AS CHAR) LIKE ? 
           OR room_number LIKE ? 
           OR CAST(price AS CHAR) LIKE ? 
           OR description LIKE ?
    ");
    $keyword = '%' . $conn->real_escape_string($searchKeyword) . '%';
    $stmt->bind_param("ssss", $keyword, $keyword, $keyword, $keyword);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $rooms = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        die("Lỗi SQL: " . $stmt->error);
    }
    $stmt->close();
}
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
                                                    <li class=""><a href="rooms.html">Rooms</a>
                                                    </li>
                                                    <li class="dropdown"><a href="#">Rooms Type</a>
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

            <!--Bounce In Header-->
            <div class="bounce-in-header">
                <div class="auto-container clearfix">
                    <!--Logo-->
                    <div class="logo pull-left">
                        <a href="index.html" class="img-responsive"><img src="images\logo-small.png"
                                alt="Queenstown"></a>
                    </div>

                    <!--Right Col-->
                    <div class="right-col pull-right">
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
                                <div class="navbar-collapse collapse clearfix">
                                    <ul class="navigation clearfix">
                                        <li class=""><a href="rooms.html">Rooms</a>
                                        </li>
                                        <li class=""><a href="#">Rooms Type</a>
                                        </li>
                                        <li class=""><a href="about.html">About Us</a>
                                        </li>
                                        <li><a href="contact.html">Contact Us</a></li>
                                    </ul>
                                </div>
                                </ul>
                            </div>
                        </nav><!-- Main Menu End-->
                    </div>

                </div>
            </div>

        </header>
        <!--End Main Header -->

        <!--Page Title-->
        <section class="page-title" style="background-image:url(images/background/bg-page-title-1.jpg);">
            <div class="auto-container">
                <h1>Kết Quả Tìm Kiếm</h1>
            </div>
        </section>

        <!--Page Info-->
        <section class="page-info">
            <div class="auto-container clearfix">
                <div class="pull-left">
                    <ul class="bread-crumb clearfix">
                        <li><a href="index.php">Home</a></li>
                        <li>Kết Quả Tìm Kiếm</li>
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
                <h1>Kết quả tìm kiếm</h1>
                <br>
                <div class="row clearfix">
                    <?php if (!empty($rooms)): ?>
                        <?php foreach ($rooms as $room): ?>
                            <div class="room-block col-md-4 col-sm-6 col-xs-12">
                                <div class="inner-box">
                                    <div class="image-box">
                                        <a href="room-details.php?id=<?php echo $room['id']; ?>">
                                            <img src="admin/<?php echo htmlspecialchars($room['image_path']); ?>" alt="Room Image">
                                        </a>
                                    </div>
                                    <div class="lower-content">
                                        <h3><a href="room-details.php?id=<?php echo $room['id']; ?>">Phòng <?php echo htmlspecialchars($room['room_number']); ?></a></h3>
                                        <div class="price">
                                            $<?php echo htmlspecialchars($room['price']); ?><span>PER<br>NIGHT</span>
                                        </div>
                                        <div class="description">
                                            <?php echo htmlspecialchars($room['description']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center;">Không tìm thấy kết quả phù hợp.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
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