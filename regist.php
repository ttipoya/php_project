<?php
session_start();

// Подключение к базе данных
try {
    $conn = new PDO('pgsql:host=aws-0-eu-central-1.pooler.supabase.com;dbname=postgres', 'postgres.lzassfipydnfwrnybomz', '1234');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Проверка наличия пользователя с таким же именем
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        $error = "Пользователь с таким именем уже существует.";
    } else {
        // Добавление нового пользователя
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->execute();

        // Получение ID нового пользователя
        $user_id = $conn->lastInsertId();

        // Успешная регистрация и автоматический вход
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'user'; // Установите это значение в зависимости от вашей логики
        $_SESSION['is_admin'] = false; // Установите это значение в зависимости от вашей логики

        header("Location: main.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="register">
<div class="register-container">
    <h1>Регистрация</h1>
    <form action="regist.php" method="post">
        <label for="username">Имя пользователя:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Пароль:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Зарегистрироваться</button>
    </form>
    <?php if (isset($error)): ?>
        <p class="error-message"><?php echo $error; ?></p>
    <?php endif; ?>
</div>
</body>
</html>
