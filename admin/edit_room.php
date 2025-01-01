<?php
// Kết nối cơ sở dữ liệu
include('../config.php');

// Lấy ID phòng từ URL
if (isset($_GET['id'])) {
    $room_id = $_GET['id'];

    // Lấy thông tin phòng từ bảng `rooms`
    $select_sql = "SELECT * FROM rooms WHERE id = ?";
    $stmt = $conn->prepare($select_sql);
    $stmt->bind_param('i', $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $room = $result->fetch_assoc();

    // Kiểm tra xem phòng có tồn tại không
    if (!$room) {
        echo "<script>alert('Phòng không tồn tại.'); window.location.href='admin_dashboard.php';</script>";
        exit;
    }

    // Lấy danh sách tất cả các loại phòng từ bảng `room_types`
    $types_sql = "SELECT id, type_name FROM room_types";
    $types_result = $conn->query($types_sql);
    $room_types = $types_result->fetch_all(MYSQLI_ASSOC);

    // Lấy danh sách các loại phòng đã gán cho phòng từ bảng `room_type_assignments`
    $assigned_sql = "SELECT room_type_id FROM room_type_assignments WHERE room_id = ?";
    $stmt = $conn->prepare($assigned_sql);
    $stmt->bind_param('i', $room_id);
    $stmt->execute();
    $assigned_result = $stmt->get_result();
    $assigned_types = array_column($assigned_result->fetch_all(MYSQLI_ASSOC), 'room_type_id');

    // Xử lý khi người dùng cập nhật thông tin phòng
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $room_number = $_POST['room_number'];
        $price = $_POST['price'];
        $description = $_POST['description']; // Lấy mô tả từ form
        $room_type_ids = $_POST['room_type'] ?? []; // Lấy danh sách loại phòng được chọn
        $image_path = $room['image_path'];  // Mặc định giữ nguyên hình ảnh hiện tại

        // Kiểm tra nếu người dùng tải lên hình ảnh mới
        if ($_FILES['image']['name']) {
            $image_path = 'uploads/' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
        }

        // Cập nhật thông tin phòng
        $update_sql = "UPDATE rooms SET room_number = ?, price = ?, description = ?, image_path = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param('sissi', $room_number, $price, $description, $image_path, $room_id);

        if ($stmt->execute()) {
            // Xóa các loại phòng cũ
            $delete_sql = "DELETE FROM room_type_assignments WHERE room_id = ?";
            $stmt = $conn->prepare($delete_sql);
            $stmt->bind_param('i', $room_id);
            $stmt->execute();

            // Gán lại loại phòng mới
            $insert_sql = "INSERT INTO room_type_assignments (room_id, room_type_id) VALUES (?, ?)";
            $stmt = $conn->prepare($insert_sql);
            foreach ($room_type_ids as $type_id) {
                $stmt->bind_param('ii', $room_id, $type_id);
                $stmt->execute();
            }

            echo "<script>alert('Cập nhật phòng thành công.'); window.location.href='admin_dashboard.php';</script>";
        } else {
            echo "<script>alert('Cập nhật phòng thất bại.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Room</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-5">
        <div class="card mx-auto shadow-sm p-4" style="max-width: 500px;">
            <h4 class="text-center mb-4">Sửa Phòng</h4>
            <form action="edit_room.php?id=<?php echo $room['id']; ?>" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="room_number" class="form-label">Số phòng</label>
                    <input type="text" id="room_number" name="room_number" class="form-control" value="<?php echo htmlspecialchars($room['room_number']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="room_type" class="form-label">Loại phòng</label>
                    <select id="room_type" name="room_type[]" class="form-select" multiple required>
                        <?php foreach ($room_types as $type): ?>
                            <option value="<?php echo $type['id']; ?>" <?php echo in_array($type['id'], $assigned_types) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type['type_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Giá phòng (VND)</label>
                    <input type="number" id="price" name="price" class="form-control" value="<?php echo htmlspecialchars($room['price']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Mô tả phòng</label>
                    <textarea id="description" name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($room['description']); ?></textarea>
                </div>
                <div class="mb-4">
                    <label for="image" class="form-label">Hình ảnh hiện tại</label>
                    <div class="text-center mb-2">
                        <img src="<?php echo htmlspecialchars($room['image_path']); ?>" alt="Room Image" class="img-thumbnail" width="100">
                    </div>
                    <input type="file" id="image" name="image" class="form-control form-control-sm">
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Cập nhật phòng</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
