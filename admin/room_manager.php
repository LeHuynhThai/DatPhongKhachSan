<?php
session_start();
include("../config.php");


// Biến để lưu thông báo
$notification = '';

// Xử lý thêm phòng
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addRoom'])) {
    $room_number = $_POST['room_number'];
    $room_types = $_POST['room_types'];
    $price = $_POST['price'];
    $description = isset($_POST['description']) ? $_POST['description'] : ''; // Kiểm tra trước khi gán
    $image = $_FILES['image'];

    // Kiểm tra số phòng đã tồn tại
    $checkRoomQuery = "SELECT * FROM rooms WHERE room_number = '$room_number'";
    $result = $conn->query($checkRoomQuery);

    if ($result->num_rows > 0) {
        $notification = "Số phòng đã tồn tại, vui lòng chọn số khác.";
    } else {
        // Di chuyển hình ảnh
        $imagePath = 'uploads/' . basename($image['name']);

        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        if (move_uploaded_file($image['tmp_name'], $imagePath)) {
            // Thêm phòng vào cơ sở dữ liệu
            $insertRoomQuery = "INSERT INTO rooms (room_number, price, description, image_path) 
                                VALUES ('$room_number', '$price', '$description', '$imagePath')";

            if ($conn->query($insertRoomQuery) === TRUE) {
                $roomId = $conn->insert_id;

                // Gán loại phòng
                foreach ($room_types as $room_type_id) {
                    $insertAssignmentQuery = "INSERT INTO room_type_assignments (room_id, room_type_id) 
                                               VALUES ('$roomId', '$room_type_id')";
                    if (!$conn->query($insertAssignmentQuery)) {
                        $notification = "Lỗi khi gán loại phòng: " . $conn->error;
                        break;
                    }
                }

                $notification = "Phòng đã được thêm thành công!";
            } else {
                $notification = "Lỗi khi thêm phòng: " . $conn->error;
            }
        } else {
            $notification = "Lỗi khi tải lên hình ảnh.";
        }
    }
}



if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $room_id = $_GET['id'];
    
    // Xóa phòng khỏi cơ sở dữ liệu
    $delete_sql = "DELETE FROM rooms WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param('i', $room_id);

    if ($stmt->execute()) {
        // Xóa thành công, lưu thông báo vào session
        $_SESSION['notification'] = "Phòng đã được xóa thành công!";
    } else {
        // Thất bại, lưu thông báo vào session
        $_SESSION['notification'] = "Xóa phòng thất bại.";
    }

    // Chuyển hướng về admin_dashboard.php để hiển thị thông báo
    header("Location: admin_dashboard.php");
    exit();
}
// Lấy danh sách phòng từ cơ sở dữ liệu
$fetchRoomsQuery = "SELECT * FROM rooms";
$rooms = $conn->query($fetchRoomsQuery);
?>