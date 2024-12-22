<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Подключение к базе данных
try {
    $conn = new PDO('pgsql:host=aws-0-eu-central-1.pooler.supabase.com;dbname=postgres', 'postgres.lzassfipydnfwrnybomz', '1234');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];
    $parent_id = isset($_POST['parent_id']) ? $_POST['parent_id'] : null;

    $stmt = $conn->prepare("INSERT INTO comments (product_id, user_id, parent_id, content) VALUES (:product_id, :user_id, :parent_id, :content)");
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
    $stmt->bindParam(':content', $content, PDO::PARAM_STR);
    $stmt->execute();

    header("Location: detail.php?id=$product_id");
    exit();
}
?>
