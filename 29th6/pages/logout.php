<?php
session_start();
if (isset($_SESSION['user_id'])) {
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
    unset($_SESSION['user']);
    unset($_SESSION['cart']);
}
session_destroy();
header('Location: ../index.php');
exit;
?>
