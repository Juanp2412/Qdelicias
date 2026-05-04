<?php
session_start();

$_SESSION = [];

session_unset();
session_destroy();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

header("Location: /Qdelicias/index.php");
exit;