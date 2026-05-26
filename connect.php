<?php
$conn = mysqli_connect("localhost", "root", "", "cellphone_k");

if (!$conn) {
    echo "<b style='color:red'>Loi ket noi </b>" . mysqli_connect_error();
    exit();
}
