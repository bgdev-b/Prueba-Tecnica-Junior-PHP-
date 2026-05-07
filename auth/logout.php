<?php
require_once __DIR__ . "/../app/config/db.php";
session_start();
session_unset();
session_destroy();
header("Location: /");
exit();
