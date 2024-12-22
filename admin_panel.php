<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="admin">
<div class="admin-container">
    <h1>Admin Panel</h1>
    <a href="add_product.php" class="admin-button">Добавить товар</a>
    <a href="admin_logout.php" class="admin-button">Выйти из аккаунта</a>
</div>
</body>
</html>