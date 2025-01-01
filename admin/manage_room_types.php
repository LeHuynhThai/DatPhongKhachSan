<?php
include('../config.php');

// Biến để lưu thông báo
$notification = '';

// Thêm loại phòng
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addRoomType'])) {
    $typeName = $_POST['room_type_name'];

    // Kiểm tra nếu tên loại phòng bị trùng
    $checkQuery = "SELECT * FROM room_types WHERE type_name = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param('s', $typeName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['notification'] = "Tên loại phòng đã tồn tại!";
    } else {
        $insertTypeQuery = "INSERT INTO room_types (type_name) VALUES (?)";
        $stmt = $conn->prepare($insertTypeQuery);
        $stmt->bind_param('s', $typeName);

        if ($stmt->execute()) {
            $_SESSION['notification'] = "Thêm loại phòng thành công!";
        } else {
            $_SESSION['notification'] = "Lỗi khi thêm loại phòng: " . $conn->error;
        }
    }
}

// Xóa loại phòng
if (isset($_GET['action']) && $_GET['action'] == 'delete_type' && isset($_GET['id'])) {
    $typeId = $_GET['id'];

    $deleteTypeQuery = "DELETE FROM room_types WHERE id = ?";
    $stmt = $conn->prepare($deleteTypeQuery);
    $stmt->bind_param('i', $typeId);

    if ($stmt->execute()) {
        $_SESSION['notification'] = "Xóa loại phòng thành công!";
    } else {
        $_SESSION['notification'] = "Lỗi khi xóa loại phòng: " . $conn->error;
    }
    // Chuyển hướng về manage_room_types.php để hiển thị thông báo
    header("Location: admin_dashboard.php");
    exit();
}

// Lấy danh sách loại phòng
$fetchTypesQuery = "SELECT * FROM room_types";
$types = $conn->query($fetchTypesQuery);
