<?php
// =========================================
// KẾT NỐI CƠ SỞ DỮ LIỆU
// =========================================
$servername = "localhost";
$username   = "root";
$password   = "123456";
$dbname     = "cellphone_k";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Không kết nối được với MySQL: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
