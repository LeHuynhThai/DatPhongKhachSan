<?php
include('../config.php');

if (isset($_GET['id']) && isset($_GET['amount'])) {
    $booking_id = $_GET['id'];
    $amount = $_GET['amount'];

    // Xóa đặt phòng
    $delete_query = "DELETE FROM bookings WHERE id = $booking_id";
    if ($conn->query($delete_query)) {
        // Cộng tiền vào doanh thu
        $insert_query = "INSERT INTO revenues (amount) VALUES ($amount)";
        $conn->query($insert_query);

        // Quay lại trang admin_dashboard
        header('Location: admin_dashboard.php');
        exit;
    } else {
        echo "Lỗi khi xóa đặt phòng: " . $conn->error;
    }
}

if (isset($_POST['reset_revenue'])) {
    // Xóa toàn bộ dữ liệu trong bảng revenues
    $reset_query = "DELETE FROM revenues";
    if ($conn->query($reset_query) === TRUE) {
        echo "<script>alert('Doanh thu đã được đặt lại về 0!'); window.location.href='admin_dashboard.php';</script>";
    } else {
        echo "<script>alert('Có lỗi xảy ra khi đặt lại doanh thu: " . $conn->error . "');</script>";
    }
}
