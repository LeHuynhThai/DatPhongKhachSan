<!-- auth/login.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f6f9;
        }

        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .login-container h3 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .form-group label {
            font-weight: 500;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border-radius: 5px;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .form-check-label {
            font-size: 14px;
        }

        .text-center a {
            color: #007bff;
        }

        .text-center a:hover {
            text-decoration: underline;
        }

        .forgot-password {
            font-size: 14px;
            margin-top: 10px;
        }

        .register-link {
            font-size: 14px;
            margin-top: 10px;
        }

        .text-danger {
            font-size: 14px;
        }
    </style>
</head>

<body>

    <div class="login-container">
        <h3>Đăng nhập tài khoản</h3>
        <form action="login.php" method="post">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" class="form-control" placeholder="Nhập email" required>
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu:</label>
                <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu" required>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
            </div>
            <button type="submit" class="btn btn-primary">Đăng nhập</button>

            <?php
            include '../config.php';
            session_start();

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $email = $_POST['email'];
                $password = $_POST['password'];
                $remember = isset($_POST['remember']);

                // Kiểm tra nếu tài khoản là admin
                if ($email === 'admin@gmail.com' && $password === '123') {
                    $_SESSION['user_role'] = 'admin';
                    $_SESSION['user_name'] = 'Admin';
                    header("Location: ../admin/admin_dashboard.php");  // Chuyển hướng đến trang admin
                    exit();
                } else {
                    // Kiểm tra thông tin người dùng trong cơ sở dữ liệu
                    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $stmt->store_result();
                    $stmt->bind_result($id, $name, $hashed_password);

                    if ($stmt->num_rows > 0) {
                        $stmt->fetch();
                        if (password_verify($password, $hashed_password)) {
                            $_SESSION['user_id'] = $id;
                            $_SESSION['user_name'] = $name;

                            // Ghi nhớ đăng nhập bằng cookie nếu có chọn
                            if ($remember) {
                                setcookie("user_id", $id, time() + (86400 * 30), "/");  // Cookie lưu trong 30 ngày
                                setcookie("user_name", $name, time() + (86400 * 30), "/");
                            }
                            header("Location: ../index.php");  // Chuyển hướng về trang chủ
                            exit;
                        } else {
                            echo "<div class='text-center text-danger mt-2'>Sai mật khẩu.</div>";
                        }
                    } else {
                        echo "<div class='text-center text-danger mt-2'>Không tìm thấy người dùng.</div>";
                    }
                }
            }
            ?>
            <div class="text-center register-link">
                <span>Không có tài khoản? <a href="register.php">Đăng ký</a></span>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>