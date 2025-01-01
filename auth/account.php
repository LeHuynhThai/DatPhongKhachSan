<?php
include '../config.php';

// Lấy thông tin tài khoản người dùng từ cơ sở dữ liệu (sử dụng session để xác định người dùng)
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();


// Xử lý xóa tài khoản
if (isset($_POST['delete_account'])) {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        session_destroy();  // Hủy session để đăng xuất người dùng
        header("Location: ../index.php"); // Quay lại trang index
        exit();
    } else {
        $error = "Xóa tài khoản không thành công. Vui lòng thử lại.";
    }
    $stmt->close();
}

// Xử lý cập nhật thông tin tài khoản
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['delete_account'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $profile_image = $user['profile_image']; // Giữ nguyên ảnh cũ nếu không chọn ảnh mới

    if (!empty($password)) {
        if ($password !== $confirm_password) {
            $error = "Mật khẩu mới và xác nhận mật khẩu không khớp!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Mã hóa mật khẩu
        }
    }    

    if (!isset($error)) {
        // Kiểm tra nếu người dùng chọn ảnh
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $image = $_FILES['profile_image'];
            $image_name = $image['name'];
            $image_tmp = $image['tmp_name'];
            $image_type = pathinfo($image_name, PATHINFO_EXTENSION);
            $image_size = $image['size'];

            // Kiểm tra định dạng ảnh
            if (in_array(strtolower($image_type), ['jpg', 'jpeg', 'png', 'gif']) && $image_size < 5000000) {
                $new_image_name = uniqid('profile_') . '.' . $image_type;
                $upload_dir = '../uploads/';
                move_uploaded_file($image_tmp, $upload_dir . $new_image_name);
                $profile_image = $new_image_name;
            } else {
                $error = "Ảnh không hợp lệ hoặc quá lớn (kích thước tối đa 5MB).";
            }
        }

        // Cập nhật thông tin tài khoản trong cơ sở dữ liệu nếu không có lỗi
        if (!isset($error)) {
            if (!empty($password)) {
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, profile_image = ?, password = ? WHERE id = ?");
                $stmt->bind_param("sssssi", $name, $email, $phone, $profile_image, $hashed_password, $user_id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, profile_image = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $name, $email, $phone, $profile_image, $user_id);
            }
        
            if ($stmt->execute()) {
                $success = "Cập nhật thông tin thành công!";
            } else {
                $error = "Cập nhật không thành công. Vui lòng thử lại.";
            }
            $stmt->close();
        }        
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang tài khoản</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .card {
            border-radius: 10px;
        }
        .container {
            max-width: 600px;
            margin-top: 50px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .form-group label {
            font-weight: bold;
        }
        .alert {
            margin-top: 20px;
        }
        .form-text {
            color: #6c757d;
        }
        .profile-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 20px;
        }
        .profile-container {
            text-align: center;
            margin-bottom: 30px;
        }
        .link-back {
            margin-top: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="text-center">Thông tin tài khoản</h2>

                <div class="profile-container">
                    <img src="../uploads/<?php echo $user['profile_image']; ?>" class="profile-image" alt="Ảnh đại diện">
                    <h4><?php echo $user['name']; ?></h4>
                    <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
                    <p><strong>Số điện thoại:</strong> <?php echo $user['phone']; ?></p>
                </div>

                <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
                <?php if (isset($success)) { echo "<div class='alert alert-success'>$success</div>"; } ?>

                <form action="account.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Tên:</label>
                        <input type="text" class="form-control" name="name" value="<?php echo $user['name']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control" name="email" value="<?php echo $user['email']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Số điện thoại:</label>
                        <input type="text" class="form-control" name="phone" value="<?php echo $user['phone']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Mật khẩu mới:</label>
                        <input type="password" class="form-control" name="password">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Xác nhận mật khẩu:</label>
                        <input type="password" class="form-control" name="confirm_password">
                    </div>
                    <div class="form-group">
                        <label for="profile_image">Ảnh đại diện:</label>
                        <input type="file" class="form-control" name="profile_image">
                        <small class="form-text text-muted">Chọn ảnh đại diện mới (tối đa 5MB, jpg, png, gif).</small>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Cập nhật thông tin</button>
                </form>

                <form action="account.php" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài khoản?');">
                    <button type="submit" name="delete_account" class="btn btn-danger btn-block mt-3">Xóa tài khoản</button>
                </form>

                <div class="link-back">
                    <a href="../index.php" class="btn btn-link">Quay lại trang chủ</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
