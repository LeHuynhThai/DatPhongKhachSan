<?php include('room_manager.php'); ?>
<?php include('user_manager.php'); ?>
<?php include('employee_manager.php'); ?>
<?php include('manage_room_types.php'); ?>
<?php


// Giả sử bạn đã có kết nối cơ sở dữ liệu được lưu trong biến $conn
$query = "SELECT * FROM employees"; // Thay thế bằng truy vấn SQL đúng của bạn
$employees = $conn->query($query);

if (!$employees) {
    die("Lỗi khi truy vấn danh sách nhân viên: " . $conn->error);
}

// Truy vấn danh sách đặt phòng
$bookings_query = "SELECT * FROM bookings ORDER BY created_at DESC";
$bookings = $conn->query($bookings_query);

if (!$bookings) {
    die("Lỗi khi truy vấn danh sách đặt phòng: " . $conn->error);
}


// Truy vấn danh sách phòng và loại phòng
$sql = "SELECT r.id, r.room_number, r.price, r.image_path, r.description,
GROUP_CONCAT(rt.type_name) AS room_types
FROM rooms r
LEFT JOIN room_type_assignments rta ON r.id = rta.room_id
LEFT JOIN room_types rt ON rta.room_type_id = rt.id
GROUP BY r.id";
$rooms = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            font-family: Arial, sans-serif;
        }

        .sidebar {
            min-width: 250px;
            background: linear-gradient(135deg, #007bff, #343a40);
            color: white;
            height: 100vh;
            position: fixed;
            box-shadow: 4px 0 8px rgba(0, 0, 0, 0.1);
        }

        .sidebar .nav-link {
            color: #d1d1d1;
            font-size: 1.1em;
            display: flex;
            align-items: center;
            padding: 10px;
            transition: background 0.3s, color 0.3s;
        }

        .sidebar .nav-link.active,
        .sidebar .nav-link:hover {
            background-color: #0056b3;
            color: white;
        }

        .sidebar .nav-link i {
            margin-right: 8px;
        }

        .content {
            margin-left: 250px;
            padding: 30px;
            width: 100%;
            background-color: #f8f9fa;
        }

        .section {
            display: none;
        }

        .section.active {
            display: block;
            padding: 20px;
            background: white;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            opacity: 1;
            /* Đảm bảo phần tử có độ trong suốt bình thường */
            visibility: visible;
            /* Đảm bảo phần tử không bị ẩn */
            transition: opacity 0.3s ease, visibility 0.3s ease;
            /* Thêm hiệu ứng mượt mà */
        }
    </style>
    <script>
        function showSection(sectionId) {
            // Ẩn tất cả các phần nội dung
            const sections = document.querySelectorAll('.section');
            sections.forEach(function(section) {
                section.style.display = 'none';
            });

            // Hiển thị phần nội dung cần
            const sectionToShow = document.getElementById(sectionId);
            sectionToShow.style.display = 'block';

            // Loại bỏ class 'active' khỏi tất cả các nav-link
            const links = document.querySelectorAll('.nav-link');
            links.forEach(function(link) {
                link.classList.remove('active');
            });

            // Thêm class 'active' vào nav-link tương ứng
            const activeLink = document.querySelector(`a[href='#'][onclick*="${sectionId}"]`);
            activeLink.classList.add('active');
        }
    </script>
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar p-3">
        <h4 class="text-center">Admin Panel</h4>
        <ul class="nav flex-column mt-4">
            <li class="nav-item">
                <a class="nav-link active" href="#" onclick="showSection('manageRooms')">
                    <i class="fas fa-bed"></i> Quản lý phòng
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="showSection('manageRoomTypes')">
                    <i class="fas fa-list"></i> Quản lý loại phòng
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="showSection('manageUsers')">
                    <i class="fas fa-users"></i> Quản lý người dùng
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="showSection('manageEmployees')">
                    <i class="fas fa-user-tie"></i> Quản lý nhân viên
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="showSection('manageBookings')">
                    <i class="fas fa-calendar-check"></i> Quản lý đặt phòng
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="showSection('manageRevenue')">
                    <i class="fas fa-chart-line"></i> Quản lý doanh thu
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../index.php">
                    <i class="fas fa-home"></i> Trang chủ
                </a>
            </li>
        </ul>
    </div>

    <!-- Content -->
    <div class="content">
        <!-- Quản lý phòng -->
        <div id="manageRooms" class="section active">
            <h2 class="text-center">Quản Lý Phòng</h2>
            <!-- Thông báo kết quả -->
            <?php if ($notification): ?>
                <div class="alert alert-info"><?php echo $notification; ?></div>
            <?php endif; ?>
            <!-- Thông báo xoá phòng -->
            <?php
            if (isset($_SESSION['notification'])) {
                echo "<div class='alert alert-info'>" . $_SESSION['notification'] . "</div>";
                unset($_SESSION['notification']); // Xóa thông báo sau khi hiển thị
            }
            ?>
            <form action="admin_dashboard.php" method="POST" class="mb-4" enctype="multipart/form-data">
                <h4>Thêm phòng mới</h4>
                <div class="form-group">
                    <label>Số phòng</label>
                    <input type="text" name="room_number" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Loại phòng</label>
                    <select name="room_types[]" class="form-control" multiple>
                        <?php
                        $room_types = $conn->query("SELECT * FROM room_types");
                        while ($type = $room_types->fetch_assoc()):
                        ?>
                            <option value="<?php echo $type['id']; ?>"><?php echo $type['type_name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Giá phòng</label>
                    <input type="number" name="price" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Mô tả phòng</label>
                    <textarea name="description" class="form-control" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label>Hình ảnh phòng</label>
                    <input type="file" name="image" class="form-control-file" required>
                </div>
                <button type="submit" name="addRoom" class="btn btn-primary">Thêm phòng</button>
            </form>
            <!-- Danh sách phòng hiện có -->
            <h4>Danh sách phòng</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Số phòng</th>
                        <th>Loại phòng</th>
                        <th>Giá</th>
                        <th>Mô tả</th>
                        <th>Hình ảnh</th>
                    </tr>
                </thead>
                <tbody id="roomTable">
                    <!-- Nội dung phòng sẽ được chèn bằng PHP -->
                    <?php while ($row = $rooms->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo $row['room_number']; ?></td>
                            <td><?php echo $row['room_types']; ?></td>
                            <td><?php echo number_format($row['price'], 0, ',', '.'); ?> VND</td>
                            <td><?php echo $row['description']; ?></td>
                            <td><img src="<?php echo $row['image_path']; ?>" alt="Room Image" width="100"></td>
                            <td>
                                <!-- Nút sửa -->
                                <a href="edit_room.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Sửa</a>

                                <!-- Nút xóa -->
                                <a href="room_manager.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa phòng này không?')">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Quản lý loại phòng -->
        <div id="manageRoomTypes" class="section">
            <h2 class="text-center">Quản Lý Loại Phòng</h2>
            <!-- Form thêm loại phòng -->
            <form action="admin_dashboard.php" method="POST" class="mb-4" enctype="multipart/form-data">
                <h4>Thêm loại phòng mới</h4>
                <div class="form-group">
                    <label>Tên loại phòng</label>
                    <input type="text" name="room_type_name" class="form-control" required>
                </div>
                <button type="submit" name="addRoomType" class="btn btn-primary">Thêm loại phòng</button>
            </form>

            <!-- Danh sách loại phòng -->
            <h4>Danh sách loại phòng</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên loại phòng</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Lấy danh sách loại phòng
                    $room_types = $conn->query("SELECT * FROM room_types");
                    while ($type = $room_types->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?php echo $type['id']; ?></td>
                            <td><?php echo $type['type_name']; ?></td>
                            <td>
                                <!-- Nút xóa -->
                                <a href="manage_room_types.php?action=delete_type&id=<?php echo $type['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa loại phòng này không?');">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <!-- Quản lý người dùng -->
        <div id="manageUsers" class="section">
            <!-- Thêm bảng hoặc các form để quản lý người dùng -->
            <h2 class="text-center">Quản Lý Người Dùng</h2>

            <!-- Thông báo kết quả -->
            <?php if ($notification): ?>
                <div class="alert alert-info"><?php echo $notification; ?></div>
            <?php endif; ?>

            <!-- Thông báo xoá người dùng -->
            <?php
            if (isset($_SESSION['notification'])) {
                echo "<div class='alert alert-info'>" . $_SESSION['notification'] . "</div>";
                unset($_SESSION['notification']); // Xóa thông báo sau khi hiển thị
            }
            ?>

            <!-- Danh sách người dùng hiện có -->
            <h4>Danh sách người dùng</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>Số điện thoại</th>
                        <th>Hình ảnh hồ sơ</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody id="userTable">
                    <?php while ($row = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php echo $row['phone']; ?></td>
                            <td>
                                <img src="../uploads/<?php echo $row['profile_image']; ?>" alt="Profile Image" width="50" height="50">
                            </td>
                            <td>
                                <!-- Nút sửa -->
                                <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Sửa</a>
                                <!-- Nút xoá -->
                                <a href="user_manager.php?action=delete&id=<?php echo $row['id']; ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này?');">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <!-- Quản lý nhân viên -->
        <div id="manageEmployees" class="section">
            <h2 class="text-center">Quản Lý Nhân Viên</h2>

            <!-- Thông báo kết quả -->
            <?php if ($notification): ?>
                <div class="alert alert-info"><?php echo $notification; ?></div>
            <?php endif; ?>
            <!-- Thông báo xoá phòng -->
            <?php
            if (isset($_SESSION['notification'])) {
                echo "<div class='alert alert-info'>" . $_SESSION['notification'] . "</div>";
                unset($_SESSION['notification']); // Xóa thông báo sau khi hiển thị
            }
            ?>
            <!-- Form thêm nhân viên -->
            <form action="admin_dashboard.php" method="POST" enctype="multipart/form-data" class="mb-4">
                <h4>Thêm nhân viên mới</h4>
                <div class="form-group">
                    <label>Mã nhân viên</label>
                    <input type="text" name="employee_code" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Tên nhân viên</label>
                    <input type="text" name="employee_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Ngày sinh</label>
                    <input type="date" name="dob" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Giới tính</label>
                    <select name="gender" class="form-control" required>
                        <option value="Nam">Nam</option>
                        <option value="Nữ">Nữ</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Vị trí</label>
                    <select name="position" class="form-control" required>
                        <option value="tiếp tân">Tiếp Tân</option>
                        <option value="bảo vệ">Bảo Vệ</option>
                        <option value="phục vụ">Phục Vụ</option>
                        <option value="quản lý">Quản Lý</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Lương</label>
                    <input type="number" name="salary" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Hình ảnh</label>
                    <input type="file" name="image" class="form-control-file" required>
                </div>
                <button type="submit" name="addEmployee" class="btn btn-primary">Thêm nhân viên</button>
            </form>

            <!-- Danh sách nhân viên -->
            <h4>Danh sách nhân viên</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Mã nhân viên</th>
                        <th>Tên nhân viên</th>
                        <th>Ngày sinh</th>
                        <th>Giới tính</th>
                        <th>Số điện thoại</th>
                        <th>Vị trí</th>
                        <th>Lương</th>
                        <th>Hình ảnh</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Danh sách nhân viên -->
                    <?php while ($row = $employees->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['employee_code']; ?></td>
                            <td><?php echo $row['employee_name']; ?></td>
                            <td><?php echo $row['dob']; ?></td>
                            <td><?php echo $row['gender']; ?></td>
                            <td><?php echo $row['phone']; ?></td>
                            <td><?php echo $row['position']; ?></td>
                            <td><?php echo number_format($row['salary'], 0, ',', '.'); ?> VND</td>
                            <td><img src="<?php echo $row['image_path']; ?>" alt="Employee Image" width="50"></td>
                            <td>
                                <!-- Nút sửa -->
                                <a href="edit_employee.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Sửa</a>

                                <!-- Nút xóa -->
                                <a href="employee_manager.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa nhân viên này không?')">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <!-- Quản lý đặt phòng -->
        <!-- Quản lý đặt phòng -->
        <div id="manageBookings" class="section">
            <h2 class="text-center">Quản Lý Đặt Phòng</h2>
            <br>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Số phòng</th>
                        <th>Ngày nhận phòng</th>
                        <th>Ngày trả phòng</th>
                        <th>Số người lớn</th>
                        <th>Số trẻ em</th>
                        <th>Ngày đặt</th>
                        <th>Số điện thoại</th>
                        <th>Tên người dùng</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Lấy danh sách đặt phòng từ bảng bookings
                    $bookings = $conn->query("SELECT * FROM bookings");
                    while ($booking = $bookings->fetch_assoc()):
                        // Tính số ngày lưu trú
                        $check_in = new DateTime($booking['check_in']);
                        $check_out = new DateTime($booking['check_out']);
                        $days_stayed = $check_out->diff($check_in)->days;

                        // Lấy giá phòng
                        $room = $conn->query("SELECT price FROM rooms WHERE room_number = '{$booking['room_number']}'")->fetch_assoc();
                        $price_per_day = $room['price'];
                        $total_price = $days_stayed * $price_per_day;
                    ?>
                        <tr>
                            <td><?php echo $booking['id']; ?></td>
                            <td><?php echo $booking['room_number']; ?></td>
                            <td><?php echo $booking['check_in']; ?></td>
                            <td><?php echo $booking['check_out']; ?></td>
                            <td><?php echo $booking['adults_count']; ?></td>
                            <td><?php echo $booking['children_count']; ?></td>
                            <td><?php echo $booking['created_at']; ?></td>
                            <td><?php echo $booking['phone_number']; ?></td>
                            <td><?php echo $booking['user_name']; ?></td>
                            <td>
                                <a href="pay_booking.php?id=<?php echo $booking['id']; ?>&amount=<?php echo $total_price; ?>" class="btn btn-success btn-sm" onclick="return confirm('Bạn có chắc chắn muốn thanh toán đặt phòng này?')">Thanh toán</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Quản lý doanh thu -->
        <div id="manageRevenue" class="section">
            <h2 class="text-center">Quản Lý Doanh Thu</h2>
            <h4>Tổng doanh thu</h4>
            <?php
            $revenue_result = $conn->query("SELECT SUM(amount) AS total_revenue FROM revenues");
            $total_revenue = $revenue_result->fetch_assoc()['total_revenue'];

            // Kiểm tra nếu $total_revenue là NULL, đặt về 0
            $total_revenue = $total_revenue ?? 0;
            ?>
            <p><?php echo number_format($total_revenue, 0, ',', '.'); ?> VND</p>
            <!-- Nút Reset -->
            <form method="post" action="pay_booking.php">
                <button type="submit" name="reset_revenue" class="btn btn-danger">Reset Doanh Thu</button>
            </form>
        </div>
    </div>
</body>
</html>