<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $depth  = substr_count(dirname($_SERVER['SCRIPT_NAME']), '/') - 1;
    $prefix = str_repeat('../', $depth);
    header("Location: {$prefix}index.php");
    exit();
}
