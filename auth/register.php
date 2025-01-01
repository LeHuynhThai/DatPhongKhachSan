<?php
include '../config.php';

// Xử lý khi người dùng gửi thông tin đăng ký
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Kiểm tra mật khẩu và xác nhận mật khẩu
    if ($password !== $confirm_password) {
        $error = "Mật khẩu không khớp!";
    } else {
        $password = password_hash($password, PASSWORD_DEFAULT); // Mã hóa mật khẩu
        $profile_image = 'default.jpg'; // Hình ảnh mặc định

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

        // Kiểm tra email đã tồn tại
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Email này đã được đăng ký.";
        } else {
            // Thêm người dùng vào cơ sở dữ liệu
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, profile_image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $phone, $password, $profile_image);
            if ($stmt->execute()) {
                header("Location: login.php"); // Chuyển hướng đến trang đăng nhập sau khi đăng ký thành công
                exit();
            } else {
                $error = "Đăng ký không thành công. Vui lòng thử lại.";
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
    <title>Đăng ký</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .card {
            border-radius: 10px;
        }
        .container {
            max-width: 500px;
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
                <h2 class="text-center">Đăng ký tài khoản</h2>
                <form action="register.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Tên:</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Số điện thoại:</label>
                        <input type="text" class="form-control" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Mật khẩu:</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Xác nhận mật khẩu:</label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>
                    <div class="form-group">
                        <label for="profile_image">Ảnh đại diện:</label>
                        <input type="file" class="form-control" name="profile_image">
                        <small class="form-text text-muted">Chọn ảnh đại diện (tối đa 5MB, hỗ trợ jpg, png, gif). Nếu không chọn, hệ thống sẽ sử dụng ảnh mặc định.</small>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Đăng ký</button>
                </form>

                <?php
                if (isset($error)) {
                    echo "<div class='alert alert-danger mt-3'>$error</div>";
                }
                ?>
                
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
