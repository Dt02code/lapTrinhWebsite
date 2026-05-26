<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';

unset($_SESSION['user_id']);
flash('success', 'Đã đăng xuất.');
redirect('index.php');
