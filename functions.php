<?php
// functions.php
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

function checkAdminAuth() {
    checkAuth();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: index.php');
        exit();
    }
}

function uploadImage($file, $destination_folder = 'uploads/') {
    if ($file['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $file['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $newname = uniqid() . "." . $ext;
            $destination = $destination_folder . $newname;
            
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                return $destination;
            }
        }
    }
    return false;
}

function formatThaiDate($date) {
    $thai_months = [
        1 => 'มกราคม',
        2 => 'กุมภาพันธ์',
        3 => 'มีนาคม',
        4 => 'เมษายน',
        5 => 'พฤษภาคม',
        6 => 'มิถุนายน',
        7 => 'กรกฎาคม',
        8 => 'สิงหาคม',
        9 => 'กันยายน',
        10 => 'ตุลาคม',
        11 => 'พฤศจิกายน',
        12 => 'ธันวาคม'
    ];
    
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = $thai_months[date('n', $timestamp)];
    $year = date('Y', $timestamp) + 543;
    
    return "$day $month $year";
}

// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $error_message = date('Y-m-d H:i:s') . " - Error [$errno]: $errstr in $errfile on line $errline\n";
    error_log($error_message, 3, 'logs/error.log');
    
    if (ini_get('display_errors')) {
        echo "<div class='alert alert-danger'>เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง</div>";
    }
    
    return true;
}
set_error_handler('customErrorHandler');
?>