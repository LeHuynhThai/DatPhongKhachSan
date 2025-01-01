<?php
// Kết nối tới cơ sở dữ liệu
include '../config.php';

$notification = "";

// Lấy ID người dùng từ URL
$userId = $_GET['id'];

// Lấy dữ liệu người dùng từ cơ sở dữ liệu
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Người dùng không tồn tại.");
}

// Xử lý cập nhật thông tin người dùng khi form được submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $profileImage = $user['profile_image']; // Giữ lại hình ảnh hiện tại nếu không có hình mới

    // Kiểm tra và xử lý upload hình ảnh mới nếu có
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $targetDir = "uploads/profile_images/";
        $targetFile = $targetDir . basename($_FILES['profile_image']['name']);
        move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile);
        $profileImage = $targetFile; // Cập nhật đường dẫn hình ảnh mới
    }

    // Kiểm tra mật khẩu
    $password = $_POST['password'];
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Mã hóa mật khẩu
        $updateQuery = "UPDATE users SET name = ?, email = ?, phone = ?, profile_image = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sssssi", $name, $email, $phone, $profileImage, $hashedPassword, $userId);
    } else {
        $updateQuery = "UPDATE users SET name = ?, email = ?, phone = ?, profile_image = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssssi", $name, $email, $phone, $profileImage, $userId);
    }

    if ($stmt->execute()) {
        $notification = "Cập nhật người dùng thành công.";
        // Lấy lại dữ liệu người dùng sau khi cập nhật để hiển thị
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        echo "<script>alert('Cập nhật người dùng thành công.'); window.location.href='admin_dashboard.php';</script>";
    } else {
        $notification = "Có lỗi xảy ra khi cập nhật.";
    }
}


?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Người Dùng</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>

    <div class="container mt-5">
        <div class="card mx-auto shadow-sm p-4" style="max-width: 500px;">
            <h4 class="text-center mb-4">Sửa Người Dùng</h4>

            <?php if ($notification): ?>
                <div class="alert alert-info"><?php echo $notification; ?></div>
            <?php endif; ?>

            <form action="edit_user.php?id=<?php echo $userId; ?>" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="name" class="form-label">Tên</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Số điện thoại</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>
                <div class="mb-4">
                    <label for="profile_image" class="form-label">Hình ảnh hồ sơ hiện tại</label>
                    <div class="text-center mb-2">
                        <img src="../uploads/<?php echo $user['profile_image']; ?>" alt="Profile Image" class="img-thumbnail" width="100">
                    </div>
                    <input type="file" id="profile_image" name="profile_image" class="form-control form-control-sm">
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Cập nhật người dùng</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>