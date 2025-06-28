<?php
// dashboard_logout.php
session_start();
$_SESSION = [];
session_destroy();
header('Location: home.php');
exit;
