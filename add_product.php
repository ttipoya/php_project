<?php
session_start();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Подключение к базе данных
    try {
        $conn = new PDO('pgsql:host=aws-0-eu-central-1.pooler.supabase.com;dbname=postgres', 'postgres.lzassfipydnfwrnybomz', '1234');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
    }

    $name = $_POST['name'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];

    $stmt = $conn->prepare("INSERT INTO products (name, description, category_id, price) VALUES (:name, :description, :category_id, :price)");
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->bindParam(':price', $price, PDO::PARAM_INT);
    $stmt->execute();

    $product_id = $conn->lastInsertId();

    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $file_name = $_FILES['images']['name'][$key];
            $file_size = $_FILES['images']['size'][$key];
            $file_tmp = $_FILES['images']['tmp_name'][$key];
            $file_type = $_FILES['images']['type'][$key];
            $file_ext = strtolower(end(explode('.', $_FILES['images']['name'][$key])));

            $extensions = array("jpeg", "jpg", "png");

            if (in_array($file_ext, $extensions) === false) {
                $errors[] = "Extension not allowed, please choose a JPEG or PNG file.";
            }

            if ($file_size > 2097152) {
                $errors[] = 'Размер файла больше чем 2 MB';
            }

            if (empty($errors) == true) {
                move_uploaded_file($file_tmp, "uploads/" . $file_name);
                $stmt = $conn->prepare("INSERT INTO images (product_id, path) VALUES (:product_id, :path)");
                $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                $stmt->bindParam(':path', $file_name, PDO::PARAM_STR);
                $stmt->execute();
            } else {
                print_r($errors);
            }
        }
    }

    header('Location: main.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить товар</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="add-product">
<div class="add-product-container">
    <h1>Добавить новый товар</h1>
    <form action="add_product.php" method="post" enctype="multipart/form-data">
        <label for="name">Название товара:</label>
        <input type="text" id="name" name="name" required>
        <label for="description">Описание:</label>
        <textarea id="description" name="description" rows="4" cols="50" required></textarea>
        <label for="category_id">Категория ID:</label>
        <input type="number" id="category_id" name="category_id" required>
        <label for="price">Цена:</label>
        <input type="number" id="price" name="price" step="0.01" required>
        <label for="images">Изображения:</label>
        <input type="file" id="images" name="images[]" multiple required>
        <button type="submit">Добавить</button>
    </form>
</div>
</body>
</html>


