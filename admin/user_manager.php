<?php
include("../config.php"); // Kết nối cơ sở dữ liệu

// Kiểm tra và xử lý yêu cầu xóa người dùng
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $delete_sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param('i', $user_id);

    if ($stmt->execute()) {
        $_SESSION['notification'] = "Người dùng đã được xóa thành công!";
    } else {
        $_SESSION['notification'] = "Xóa người dùng thất bại: " . $conn->error;
        $redirect_url = isset($_GET['redirect']) ? $_GET['redirect'] : 'admin_dashboard.php?section=manageRooms';
    }
    header("Location: admin_dashboard.php");
    exit();
}
// Lấy danh sách người dùng
$fetchUsersQuery = "SELECT id, name, email, phone, profile_image FROM users";
$users = $conn->query($fetchUsersQuery);
?>