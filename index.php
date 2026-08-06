<?php
require_once 'data.php';
require_once 'helpers.php';

$rawCatId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$categoryId = $rawCatId > 0 ? $rawCatId : null;

$filteredProducts = filterByCategory($products, $categoryId);
$totalInventoryValue = inventoryValue($products);
$inventoryRank = rankInventory($totalInventoryValue);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Minishop 02</title>
</head>
<body>
    <h1>Quản lý Kho (Phiếu 02)</h1>
    <!-- MS_EXPECT inventory_value=41380000 rank=Lon -->

    <?php
    $p = findProductBySku($products, 'MN-02');
    var_dump($p['name'] ?? 'NOT FOUND');
    ?>

    <div class="nav">
        <a href="index.php">Tat ca</a>
        <?php foreach ($categories as $id => $name): ?>
            | <a href="index.php?category_id=<?= htmlspecialchars((string)$id) ?>"><?= htmlspecialchars($name) ?></a>
        <?php endforeach; ?>
    </div>

    <h2>Danh sách sản phẩm</h2>
    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Tên sản phẩm</th>
                <th>Danh mục</th>
                <th>Giá</th>
                <th>Số lượng</th>
                <th>Muc ton</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php renderProductRows($filteredProducts, $categories); ?>
        </tbody>
    </table>

    <h2>Báo cáo kho hàng</h2>
    <p>Tổng giá trị kho (toàn bộ): <?= htmlspecialchars((string)$totalInventoryValue) ?></p>
    <p>Quy mo kho: <?= htmlspecialchars($inventoryRank) ?></p>
    
    <table>
        <thead>
            <tr>
                <th>Danh muc</th>
                <th>So SP</th>
                <th>Tong gia tri</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $id => $name): ?>
                <?php 
                    $count = countByCategory($products, $id);
                    $sumValue = 0;
                    foreach ($products as $p) {
                        if ($p['category_id'] === $id) {
                            $sumValue += lineTotal($p);
                        }
                    }
                ?>
                <tr>
                    <td><?= htmlspecialchars($name) ?></td>
                    <td><?= htmlspecialchars((string)$count) ?></td>
                    <td><?= htmlspecialchars((string)$sumValue) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>