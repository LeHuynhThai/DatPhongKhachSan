<?php
// Kết nối cơ sở dữ liệu
include('../config.php');

// Lấy ID nhân viên từ URL
if (isset($_GET['id'])) {
    $employee_id = $_GET['id'];

    // Lấy thông tin nhân viên từ cơ sở dữ liệu
    $select_sql = "SELECT * FROM employees WHERE id = ?";
    $stmt = $conn->prepare($select_sql);
    $stmt->bind_param('i', $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();

    // Kiểm tra xem nhân viên có tồn tại không
    if (!$employee) {
        echo "<script>alert('Nhân viên không tồn tại.'); window.location.href='admin_dashboard.php';</script>";
        exit;
    }

    // Xử lý khi người dùng cập nhật thông tin nhân viên
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $employee_code = $_POST['employee_code'];
        $employee_name = $_POST['employee_name'];
        $dob = $_POST['dob'];
        $gender = $_POST['gender'];
        $phone = $_POST['phone'];
        $position = $_POST['position'];
        $salary = $_POST['salary'];
        $image_path = $employee['image_path'];  // Giữ nguyên hình ảnh hiện tại

        // Kiểm tra nếu người dùng tải lên hình ảnh mới
        if ($_FILES['image']['name']) {
            // Xử lý upload hình ảnh mới
            $image_path = 'uploads/' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
        }

        // Cập nhật thông tin nhân viên
        $update_sql = "UPDATE employees SET employee_code = ?, employee_name = ?, dob = ?, gender = ?, phone = ?, position = ?, salary = ?, image_path = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param('ssssssssi', $employee_code, $employee_name, $dob, $gender, $phone, $position, $salary, $image_path, $employee_id);

        if ($stmt->execute()) {
            echo "<script>alert('Cập nhật nhân viên thành công.'); window.location.href='admin_dashboard.php';</script>";
        } else {
            echo "<script>alert('Cập nhật nhân viên thất bại.');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Nhân Viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    <!-- Form sửa nhân viên -->
    <div class="container mt-5">
        <div class="card mx-auto shadow-sm p-4" style="max-width: 500px;">
            <h4 class="text-center mb-4">Sửa Nhân Viên</h4>
            <form action="edit_employee.php?id=<?php echo $employee['id']; ?>" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="employee_code" class="form-label">Mã Nhân Viên</label>
                    <input type="text" id="employee_code" name="employee_code" class="form-control" value="<?php echo $employee['employee_code']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="employee_name" class="form-label">Tên Nhân Viên</label>
                    <input type="text" id="employee_name" name="employee_name" class="form-control" value="<?php echo $employee['employee_name']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="dob" class="form-label">Ngày Sinh</label>
                    <input type="date" id="dob" name="dob" class="form-control" value="<?php echo $employee['dob']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="gender" class="form-label">Giới Tính</label>
                    <select id="gender" name="gender" class="form-select">
                        <option value="Nam" <?php echo $employee['gender'] == 'Nam' ? 'selected' : ''; ?>>Nam</option>
                        <option value="Nữ" <?php echo $employee['gender'] == 'Nữ' ? 'selected' : ''; ?>>Nữ</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Số Điện Thoại</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="<?php echo $employee['phone']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="position" class="form-label">Vị Trí</label>
                    <select id="position" name="position" class="form-select">
                        <option value="tiếp tân" <?php echo $employee['position'] == 'tiếp tân' ? 'selected' : ''; ?>>Tiếp Tân</option>
                        <option value="bảo vệ" <?php echo $employee['position'] == 'bảo vệ' ? 'selected' : ''; ?>>Bảo Vệ</option>
                        <option value="phục vụ" <?php echo $employee['position'] == 'phục vụ' ? 'selected' : ''; ?>>Phục Vụ</option>
                        <option value="quản lý" <?php echo $employee['position'] == 'quản lý' ? 'selected' : ''; ?>>Quản Lý</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="salary" class="form-label">Lương (VND)</label>
                    <input type="number" id="salary" name="salary" class="form-control" value="<?php echo $employee['salary']; ?>" required>
                </div>
                <div class="mb-4">
                    <label for="image" class="form-label">Ảnh Nhân Viên</label>
                    <div class="text-center mb-2">
                        <img src="<?php echo $employee['image_path']; ?>" alt="Employee Image" class="img-thumbnail" width="100">
                    </div>
                    <input type="file" id="image" name="image" class="form-control form-control-sm">
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Cập nhật nhân viên</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>