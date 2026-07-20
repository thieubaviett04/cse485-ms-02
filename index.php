<?php

require_once 'data.php';
require_once 'helpers.php';

// Map category_id sang tên danh mục
$categoryMap = [];

foreach ($categories as $category) {
    $categoryMap[$category['id']] = $category['name'];
}

// Đọc category_id từ URL để lọc
$categoryId = null;

if (isset($_GET['category_id']) && $_GET['category_id'] !== '') {
    $categoryId = filter_var(
        $_GET['category_id'],
        FILTER_VALIDATE_INT
    );

    // Nếu category_id không hợp lệ hoặc không tồn tại
    if ($categoryId === false || !isset($categoryMap[$categoryId])) {
        $categoryId = null;
    }
}

// Danh sách sản phẩm sau khi lọc theo Danh mục
$displayProducts = filterByCategory($products, $categoryId);

// Thống kê toàn bộ kho, không phụ thuộc bộ lọc danh mục
$productCount = count($products);
$totalInventoryValue = inventoryValue($products);
$totalCategoryCount = count($categories);
$inventoryRank = rankInventory($totalInventoryValue); // Đánh giá tồn dư kho chung

// Đọc SKU từ URL để lọc hộp cát (Sandbox Search)
$searchSku = '';
$searchResult = null;
$hasSearched = false;

if (isset($_GET['sku']) && trim($_GET['sku']) !== '') {
    $searchSku = trim($_GET['sku']);
    $searchResult = findProductBySku($products, $searchSku);
    $hasSearched = true;
}

// Tính toán tổng giá trị theo từng danh mục
$categoryValues = [];
foreach ($categories as $cat) {
    $categoryValues[$cat['id']] = 0;
}
foreach ($products as $p) {
    $catId = $p['category_id'];
    if (isset($categoryValues[$catId])) {
        $categoryValues[$catId] += lineTotal($p);
    }
}

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Hệ thống quản lý kho sản phẩm MiniShop - Buổi 2">
    <title>MiniShop — Dashboard (Buoi 2)</title>
    <!-- Nhúng file style.css -->
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container">
        <header>
            <h1 id="shop-title">MiniShop Dashboard</h1>
            <p>Hệ thống Quản lý Kho Hàng & Sản Phẩm (Nhiệm vụ 2)</p>
        </header>

        <!-- Hộp thống kê chung ở trên cùng -->
        <div class="stats-grid">
            <div class="stat-card" id="stat-total-products">
                <span class="stat-label">Tổng số sản phẩm</span>
                <span class="stat-value highlight"><?php echo htmlspecialchars($productCount); ?></span>
            </div>
            <div class="stat-card" id="stat-total-value">
                <span class="stat-label">Tổng giá trị kho</span>
                <span class="stat-value"><?php echo htmlspecialchars(number_format($totalInventoryValue, 0, ',', '.')); ?> ₫</span>
            </div>
            <div class="stat-card" id="stat-total-categories">
                <span class="stat-label">Tổng số danh mục</span>
                <span class="stat-value"><?php echo htmlspecialchars($totalCategoryCount); ?></span>
            </div>
            <div class="stat-card" id="stat-inventory-rank">
                <span class="stat-label">Quy mô kho</span>
                <span class="stat-value"><?php echo htmlspecialchars($inventoryRank); ?></span>
            </div>
        </div>

        <!-- Hộp cát Tìm kiếm theo SKU (Sandbox Search) -->
        <div class="search-sandbox-card" id="search-sandbox">
            <h2 class="table-title">Tìm kiếm sản phẩm SKU</h2>
            <form action="index.php" method="GET" class="search-form">
                <!-- Lưu lại bộ lọc category_id nếu có -->
                <?php if ($categoryId !== null): ?>
                    <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($categoryId); ?>">
                <?php endif; ?>
                <input type="text" name="sku" class="search-input" placeholder="Nhập mã SKU cần lọc (Ví dụ: MN-02, KB-01, MS-02...)" value="<?php echo htmlspecialchars($searchSku); ?>">
                <button type="submit" class="search-btn">Tìm kiếm</button>
                <?php if ($hasSearched): ?>
                    <a href="index.php<?php echo $categoryId !== null ? '?category_id=' . $categoryId : ''; ?>" class="search-clear-btn">Xóa lọc</a>
                <?php endif; ?>
            </form>

            <?php if ($hasSearched): ?>
                <div class="search-result-container">
                    <h3 class="search-result-title">Kết quả tìm kiếm cho SKU: "<?php echo htmlspecialchars($searchSku); ?>"</h3>
                    <?php if ($searchResult):
                        $lineTotal = lineTotal($searchResult);
                        $prodStatus = stockLevel($searchResult);
                        $status_class = ($prodStatus === 'Du' || $prodStatus === 'Dư') ? 'status-abundant' : (($prodStatus === 'Sap het' || $prodStatus === 'Sắp hết') ? 'status-warning' : 'status-danger');
                        $catName = isset($categoryMap[$searchResult['category_id']]) ? $categoryMap[$searchResult['category_id']] : 'Chưa phân loại';
                    ?>
                        <div class="search-result-detail">
                            <div class="search-result-item">
                                <div class="search-result-label">Mã SKU</div>
                                <div class="search-result-value sku-text"><?php echo htmlspecialchars($searchResult['sku']); ?></div>
                            </div>
                            <div class="search-result-item">
                                <div class="search-result-label">Tên sản phẩm</div>
                                <div class="search-result-value"><?php echo htmlspecialchars($searchResult['name']); ?></div>
                            </div>
                            <div class="search-result-item">
                                <div class="search-result-label">Danh mục</div>
                                <div class="search-result-value"><?php echo htmlspecialchars($catName); ?></div>
                            </div>
                            <div class="search-result-item">
                                <div class="search-result-label">Đơn giá</div>
                                <div class="search-result-value"><?php echo htmlspecialchars(number_format($searchResult['price'], 0, ',', '.')); ?> ₫</div>
                            </div>
                            <div class="search-result-item">
                                <div class="search-result-label">Số lượng</div>
                                <div class="search-result-value"><?php echo htmlspecialchars($searchResult['qty']); ?></div>
                            </div>
                            <div class="search-result-item">
                                <div class="search-result-label">Thành tiền</div>
                                <div class="search-result-value total-col"><?php echo htmlspecialchars(number_format($lineTotal, 0, ',', '.')); ?> ₫</div>
                            </div>
                            <div class="search-result-item">
                                <div class="search-result-label">Tồn dư kho</div>
                                <div class="search-result-value">
                                    <span class="status-badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($prodStatus); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="search-result-empty">❌ Không tìm thấy sản phẩm nào trùng khớp với SKU "<?php echo htmlspecialchars($searchSku); ?>" trong kho.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Phân chia bố cục: Bảng sản phẩm chính và Thống kê danh mục -->
        <div class="layout-grid">
            <!-- Bảng danh sách sản phẩm -->
            <div class="table-card" id="catalog-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
                    <h2 class="table-title" style="margin-bottom: 0;">Danh sách sản phẩm chi tiết</h2>
                    <!-- Bộ lọc theo danh mục -->
                    <nav class="category-filter-nav" style="margin-bottom: 0;">
                        <a href="index.php<?php echo $hasSearched ? '?sku=' . urlencode($searchSku) : ''; ?>" class="category-filter-btn <?php echo $categoryId === null ? 'active' : ''; ?>">Tất cả</a>
                        <?php foreach ($categories as $cat): ?>
                            <a href="index.php?category_id=<?php echo $cat['id']; ?><?php echo $hasSearched ? '&sku=' . urlencode($searchSku) : ''; ?>"
                                class="category-filter-btn <?php echo $categoryId === $cat['id'] ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                </div>

                <div class="table-wrapper">
                    <table id="product-table">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Danh mục</th>
                                <th>Tên sản phẩm</th>
                                <th class="text-right">Đơn giá</th>
                                <th class="text-center">Số lượng</th>
                                <th class="text-right">Thành tiền</th>
                                <th class="text-center">Tồn dư kho</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (count($displayProducts) > 0) {
                                // Gọi hàm helper render HTML rows bằng renderProductRows
                                renderProductRows($displayProducts, $categoryMap);
                            } else {
                                echo '<tr><td colspan="7" class="text-center" style="color: var(--text-secondary); padding: 2rem;">Không có sản phẩm thuộc danh mục này.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Cột bên phải: Tổng hợp theo danh mục (tổng các danh mục) -->
            <div class="table-card" id="category-summary-card">
                <h2 class="table-title">Tổng hợp báo cáo</h2>
                <div class="category-totals-list">
                    <?php foreach ($categories as $cat):
                        // Gọi hàm countByCategory để tính tổng số lượng tồn kho của danh mục
                        $catQty = countByCategory($products, $cat['id']);
                        $catVal = isset($categoryValues[$cat['id']]) ? $categoryValues[$cat['id']] : 0;
                    ?>
                        <div class="category-total-item">
                            <div class="cat-info">
                                <span class="cat-name"><?php echo htmlspecialchars($cat['name']); ?></span>
                                <span class="cat-desc"><?php echo htmlspecialchars($cat['description']); ?></span>
                            </div>
                            <div class="cat-stats">
                                <span class="cat-qty">SL: <strong><?php echo htmlspecialchars($catQty); ?></strong></span>
                                <div class="cat-val"><?php echo htmlspecialchars(number_format($catVal, 0, ',', '.')); ?> ₫</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Điểm kiểm tra (CheckPoint Console) in ra findProductBySku($products, 'MN-02') -->
        <div class="debug-card" id="checkpoint-section">
            <h3 class="debug-title" style="color: #10b981;">Điểm kiểm tra hệ thống (CheckPoint Console)</h3>
            <p style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.75rem;">
                Kết quả của lệnh gọi <code>checkPoint($products)</code> được in dưới dạng <code>var_dump</code>:
            </p>
            <pre class="checkpoint-debug"><?php var_dump(checkPoint($products)); ?></pre>
        </div>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> MiniShop. Được xây dựng trên chuẩn đầu ra CLO khóa học CSE485.</p>
        </footer>
    </div>

</body>

</html>
<!-- MS_EXPECT product_count=8 inventory_value=41380000 -->