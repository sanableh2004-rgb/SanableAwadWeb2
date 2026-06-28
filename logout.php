<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// تدمير الجلسة
session_destroy();

// إعادة التوجيه لصفحة تسجيل الدخول
redirect('login.php');
?>

