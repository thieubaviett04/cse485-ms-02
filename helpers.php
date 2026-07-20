<?php

/**
 * Tính thành tiền của một sản phẩm (price * qty).
 *
 * @param array $product Mảng thông tin sản phẩm.
 * @return int Thành tiền.
 */
function lineTotal(array $product): int
{
    return $product['price'] * $product['qty'];
}

/**
 * Tính tổng giá trị toàn bộ kho hàng.
 *
 * @param array $products Danh sách sản phẩm.
 * @return int Tổng giá trị kho.
 */
function inventoryValue(array $products): int
{
    $total = 0;
    foreach ($products as $product) {
        $total += lineTotal($product);
    }
    return $total;
}

/**
 * Tìm kiếm sản phẩm theo mã SKU.
 *
 * @param array $products Danh sách sản phẩm.
 * @param string $sku Mã SKU cần tìm.
 * @return array|null Trả về sản phẩm nếu tìm thấy, ngược lại trả về null.
 */
function findProductBySku(array $products, string $sku): ?array
{
    foreach ($products as $product) {
        if ($product['sku'] === $sku) {
            return $product;
        }
    }
    return null;
}

/**
 * Tính tổng số lượng sản phẩm thuộc một danh mục cụ thể.
 *
 * @param array $products Danh sách sản phẩm.
 * @param int $categoryId ID danh mục cần đếm.
 * @return int Tổng số lượng tồn kho của danh mục đó.
 */
function countByCategory(array $products, int $categoryId): int
{
    $count = 0;
    foreach ($products as $product) {
        if ($product['category_id'] === $categoryId) {
            $count++;
        }
    }
    return $count;
}

/**
 * Đánh giá mức độ tồn kho của một sản phẩm.
 * - qty >= 5: "Du"
 * - qty >= 2: "Sap het"
 * - qty < 2: "Can nhap"
 *
 * @param array $product Mảng thông tin sản phẩm.
 * @return string Trạng thái tồn kho.
 */
function stockLevel(array $product): string
{
    $qty = $product['qty'];
    if ($qty >= 5) {
        return "Du";
    } elseif ($qty >= 2) {
        return "Sap het";
    } else {
        return "Can nhap";
    }
}

/**
 * Điểm kiểm tra (CheckPoint) lấy sản phẩm 'MN-02' và trả về mảng thông tin.
 *
 * @param array $products Danh sách sản phẩm.
 * @return array Mảng thông tin sản phẩm tìm thấy.
 */
function checkPoint(array $products): array
{
    $product = findProductBySku($products, 'MN-02');
    return $product !== null ? $product : [];
}

/**
 * Render trực tiếp HTML các dòng sản phẩm trong bảng.
 *
 * @param array $products Danh sách sản phẩm cần render.
 * @param array $categoryMap Mảng ánh xạ id => tên danh mục.
 * @return void
 */
function renderProductRows(array $products, array $categoryMap): void
{
    foreach ($products as $p) {
        $line_total = lineTotal($p);
        $cat_name = isset($categoryMap[$p['category_id']]) ? $categoryMap[$p['category_id']] : 'Chưa phân loại';
        $status = stockLevel($p);

        // Thiết lập class CSS cho trạng thái tồn kho
        $status_class = '';
        if ($status === 'Du' || $status === 'Dư') {
            $status_class = 'status-abundant';
        } elseif ($status === 'Sap het' || $status === 'Sắp hết') {
            $status_class = 'status-warning';
        } else {
            $status_class = 'status-danger';
        }

        // Thiết lập badge class cho danh mục
        $badge_class = 'badge-default';
        $cat_name_lower = mb_strtolower($cat_name, 'UTF-8');
        if (strpos($cat_name_lower, 'phim') !== false || strpos($cat_name_lower, 'phím') !== false) {
            $badge_class = 'badge-keyboard';
        } elseif (strpos($cat_name_lower, 'chuot') !== false || strpos($cat_name_lower, 'chuột') !== false) {
            $badge_class = 'badge-mouse';
        } elseif (strpos($cat_name_lower, 'hinh') !== false || strpos($cat_name_lower, 'hình') !== false) {
            $badge_class = 'badge-monitor';
        }

        echo '<tr>';
        echo '<td><span class="sku-text">' . htmlspecialchars($p['sku']) . '</span></td>';
        echo '<td><span class="badge ' . htmlspecialchars($badge_class) . '">' . htmlspecialchars($cat_name) . '</span></td>';
        echo '<td>' . htmlspecialchars($p['name']) . '</td>';
        echo '<td class="text-right price-col">' . htmlspecialchars(number_format($p['price'], 0, ',', '.')) . ' ₫</td>';
        echo '<td class="text-center">' . htmlspecialchars($p['qty']) . '</td>';
        echo '<td class="text-right total-col">' . htmlspecialchars(number_format($line_total, 0, ',', '.')) . ' ₫</td>';
        echo '<td class="text-center"><span class="status-badge ' . htmlspecialchars($status_class) . '">' . htmlspecialchars($status) . '</span></td>';
        echo '</tr>';
    }
}

/**
 * Lọc sản phẩm theo Category ID.
 */
function filterByCategory(array $products, ?int $categoryId): array
{
    if ($categoryId === null || $categoryId === 0) {
        return $products;
    }
    $filtered = [];
    foreach ($products as $product) {
        if ($product['category_id'] === $categoryId) {
            $filtered[] = $product;
        }
    }
    return $filtered;
}

/**
 * Đánh giá trạng thái tồn kho chung hoặc tương thích ngược.
 */
function rankInventory(int $totalValue): string
{
    if ($totalValue < 15_000_000) {
        return "Nho";
    } elseif ($totalValue < 35_000_000) {
        return "Trung binh";
    } else {
        return "Lon";
    }
}
