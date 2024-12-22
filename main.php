<?php
session_start();

// Подключение к базе данных
try {
    $conn = new PDO('pgsql:host=aws-0-eu-central-1.pooler.supabase.com;dbname=postgres', 'postgres.lzassfipydnfwrnybomz', '1234');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

// Получение списка категорий для навигации
$stmt = $conn->prepare("SELECT * FROM categories WHERE parent_id IS NULL");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получение списка товаров с основным изображением
$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : null;
$query = "
    SELECT p.*, i.path AS main_image
    FROM products p
    LEFT JOIN (
        SELECT product_id, path
        FROM images
        WHERE id IN (
            SELECT MIN(id)
            FROM images
            GROUP BY product_id
        )
    ) i ON p.id = i.product_id
";
if ($category_id) {
    $query .= " WHERE p.category_id = :category_id OR p.category_id IN (SELECT id FROM categories WHERE parent_id = :category_id)";
}
$stmt = $conn->prepare($query);
if ($category_id) {
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
}
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Продукция</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="header">
    <div class="category-navigation">
        <h2>Категории</h2>
        <ul>
            <?php foreach ($categories as $category): ?>
                <li>
                    <a href="main.php?category_id=<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></a>
                    <?php
                    // Получение подкатегорий
                    $stmt = $conn->prepare("SELECT * FROM categories WHERE parent_id = :parent_id");
                    $stmt->bindParam(':parent_id', $category['id'], PDO::PARAM_INT);
                    $stmt->execute();
                    $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <?php if (!empty($subcategories)): ?>
                        <ul>
                            <?php foreach ($subcategories as $subcategory): ?>
                                <li><a href="main.php?category_id=<?php echo $subcategory['id']; ?>"><?php echo htmlspecialchars($subcategory['name']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="user-info">
            <?php if (isset($_SESSION['username'])): ?>
                <p>Добро пожаловать, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                <a href="logout.php">Выйти из аккаунта</a>
            <?php else: ?>
                <p>Пожалуйста <a href="login.php">войдите в аккаунт</a> для доступа к полному функционалу.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="product-list">
    <?php foreach ($products as $product): ?>
        <div class="product-item">
            <a href="detail.php?id=<?php echo $product['id']; ?>">
                <?php if (!empty($product['main_image'])): ?>
                    <img src="images/<?php echo htmlspecialchars($product['main_image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <?php else: ?>
                    <img src="images/default.jpg" alt="Default Image">
                <?php endif; ?>
            </a>
            <h2></h2>
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <p><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
            <p class="price">Цена: <?php echo htmlspecialchars($product['price']); ?> руб.</p>
        </div>
    <?php endforeach; ?>
</div>
<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <div class="add-product-button-container">
        <a href="add_product.php" class="add-product-button">Добавить товар</a>
    </div>
<?php endif; ?>
</body>
</html>