<?php

function lineTotal(array $product): int {
    return (int)($product['price'] * $product['qty']);
}

function inventoryValue(array $products): int {
    $sum = 0;
    foreach ($products as $p) {
        $sum += lineTotal($p);
    }
    return $sum;
}

function findProductBySku(array $products, string $sku): ?array {
    foreach ($products as $p) {
        if ($p['sku'] === $sku) {
            return $p;
        }
    }
    return null;
}

function countByCategory(array $products, int $categoryId): int {
    $count = 0;
    foreach ($products as $p) {
        if ($p['category_id'] === $categoryId) {
            $count++;
        }
    }
    return $count;
}

function stockLevel(array $product): string {
    if ($product['qty'] >= 5) {
        return "Du";
    } elseif ($product['qty'] >= 2) {
        return "Sap het";
    } else {
        return "Can nhap";
    }
}

function filterByCategory(array $products, ?int $categoryId): array {
    if ($categoryId === null || $categoryId === 0) {
        return $products;
    }
    $filtered = [];
    foreach ($products as $p) {
        if ($p['category_id'] === $categoryId) {
            $filtered[] = $p;
        }
    }
    return $filtered;
}

function rankInventory(int $totalValue): string {
    if ($totalValue < 15000000) {
        return "Nho";
    } elseif ($totalValue < 35000000) {
        return "Trung binh";
    } else {
        return "Lon";
    }
}

function renderProductRows(array $products, array $categoryMap): void {
    foreach ($products as $p) {
        $categoryName = $categoryMap[$p['category_id']] ?? 'Unknown';
        $level = stockLevel($p);
        $total = lineTotal($p);
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($p['sku']) . "</td>";
        echo "<td>" . htmlspecialchars($p['name']) . "</td>";
        echo "<td>" . htmlspecialchars($categoryName) . "</td>";
        echo "<td>" . htmlspecialchars((string)$p['price']) . "</td>";
        echo "<td>" . htmlspecialchars((string)$p['qty']) . "</td>";
        echo "<td>" . htmlspecialchars($level) . "</td>";
        echo "<td>" . htmlspecialchars((string)$total) . "</td>";
        echo "</tr>\n";
    }
}
