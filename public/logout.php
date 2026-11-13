<?php
session_start();

// Очистить все данные сессии клиента
unset($_SESSION['customer_id']);
unset($_SESSION['customer_name']);
unset($_SESSION['customer_email']);

// Перенаправить на главную
header('Location: /rolex/public/index.php');
exit;
