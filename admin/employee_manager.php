<?php
// Kết nối cơ sở dữ liệu
include('../config.php');

// Kiểm tra thêm nhân viên
if (isset($_POST['addEmployee'])) {
    $employee_code = $_POST['employee_code'];
    $employee_name = $_POST['employee_name'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $phone = $_POST['phone'];
    $position = $_POST['position'];
    $salary = $_POST['salary'];

    // Xử lý hình ảnh
    $image_path = "uploads/" . basename($_FILES["image"]["name"]);
    move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);

    // Kiểm tra mã nhân viên có bị trùng không
    $check_code_query = "SELECT * FROM employees WHERE employee_code = ?";
    $stmt = $conn->prepare($check_code_query);
    $stmt->bind_param("s", $employee_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $notification = "Mã nhân viên đã tồn tại. Vui lòng chọn mã khác.";
    } else {
        // Thêm nhân viên vào cơ sở dữ liệu
        $insert_query = "INSERT INTO employees (employee_code, employee_name, dob, gender, phone, position, salary, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssssssss", $employee_code, $employee_name, $dob, $gender, $phone, $position, $salary, $image_path);
        if ($stmt->execute()) {
            $notification = "Thêm nhân viên thành công!";
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $notification = "Có lỗi xảy ra khi thêm nhân viên.";
        }
    }
}

// Xử lý xóa nhân viên
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = $_GET['id'];

    // Xóa nhân viên khỏi cơ sở dữ liệu
    $delete_query = "DELETE FROM employees WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $notification = "Xóa nhân viên thành công!";
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $notification = "Có lỗi xảy ra khi xóa nhân viên.";
    }
}

// Lấy danh sách nhân viên
$employees_query = "SELECT * FROM employees";
$employees_result = $conn->query($employees_query);
