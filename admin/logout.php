<?php
session_start();
session_destroy();
header('Location: ' . '/rolex/admin/login.php');
exit;
