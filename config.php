<?php
// Cấu hình kết nối cơ sở dữ liệu
$servername = "localhost"; // Địa chỉ máy chủ cơ sở dữ liệu
$username = "root";        // Tên người dùng của cơ sở dữ liệu
$password = "";            // Mật khẩu của người dùng cơ sở dữ liệu
$dbname = "hotel-booking";  // Tên cơ sở dữ liệu

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>
