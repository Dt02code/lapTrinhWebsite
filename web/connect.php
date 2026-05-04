<?php
$conn = mysqli_connect("127.0.0.1", "root", "", "cellphone_k", 3307);

if (!$conn) {
    echo "<b style='color:red'>Loi ket noi </b>" . mysqli_connect_error();
    exit();
}
