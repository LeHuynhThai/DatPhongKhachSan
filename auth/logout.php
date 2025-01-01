<?php
session_start();  // Khởi động lại session

// Hủy các session
session_unset(); 
session_destroy();  // Hủy session

// Hủy cookie nếu có
setcookie("user_id", "", time() - 3600, "/"); 
setcookie("user_name", "", time() - 3600, "/"); 

// Chuyển hướng về trang đăng nhập hoặc trang chủ
header("Location: ../index.php");
exit;
?>
