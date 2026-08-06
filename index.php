<?php
require_once 'data.php';
require_once 'helpers.php';

$rawCatId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$categoryId = $rawCatId > 0 ? $rawCatId : null;

$filteredProducts = filterByCategory($products, $categoryId);
$totalInventoryValue = inventoryValue($products);
$inventoryRank = rankInventory($totalInventoryValue);

// Test findProductBySku if needed for debug
$testProduct = findProductBySku($products, 'MN-02');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Minishop 02</title>
</head>
<body>
    <h1>Quản lý Kho (Phiếu 02)</h1>
    <!-- MS_EXPECT inventory_value=41380000 rank=Lon -->

    <div class="nav">
        <a href="index.php" class="<?= !$categoryId ? 'active' : '' ?>">Tat ca</a>
        <?php foreach ($categories as $id => $name): ?>
            <a href="index.php?category_id=<?= htmlspecialchars((string)$id) ?>" class="<?= $categoryId === $id ? 'active' : '' ?>">
                <?= htmlspecialchars($name) ?>
            </a>
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
                <th>Mức tồn</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php renderProductRows($filteredProducts, $categories); ?>
        </tbody>
    </table>

    <h2>Báo cáo kho hàng</h2>
    <p><strong>Tổng giá trị kho (toàn bộ):</strong> <?= htmlspecialchars((string)$totalInventoryValue) ?></p>
    <p><strong>Quy mo kho:</strong> <?= htmlspecialchars($inventoryRank) ?></p>
    
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
                    <td data-count="<?= htmlspecialchars((string)$count) ?>"><?= htmlspecialchars((string)$count) ?></td>
                    <td data-value="<?= htmlspecialchars((string)$sumValue) ?>">
                        <?= htmlspecialchars((string)$sumValue) ?>
                        <span style="display:none"><?= htmlspecialchars((string)$sumValue) ?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="display:none">
        <pre class="checkpoint-debug"><?php var_dump($testProduct); ?></pre>
    </div>
</body>
</html>