<?php
require_once __DIR__ . '/BaseModel.php';

class Product extends BaseModel {
    /**
     * Get all products with live In-Stock SN counts and low stock alert flags
     */
    public function getAllWithStockCounts(): array {
        $sql = "SELECT p.*,
                       COUNT(CASE WHEN sn.status = 'In-Stock' THEN 1 END) AS in_stock_count,
                       COUNT(CASE WHEN sn.status = 'Sold' THEN 1 END) AS sold_count,
                       COUNT(CASE WHEN sn.status = 'RMA' THEN 1 END) AS rma_count,
                       (COUNT(CASE WHEN sn.status = 'In-Stock' THEN 1 END) <= p.low_stock_threshold) AS is_low_stock
                FROM products p
                LEFT JOIN serial_numbers sn ON p.id = sn.product_id
                GROUP BY p.id
                ORDER BY p.category ASC, p.name ASC";
        return $this->fetchAll($sql);
    }

    public function getById(int $id): ?array {
        $sql = "SELECT p.*,
                       COUNT(CASE WHEN sn.status = 'In-Stock' THEN 1 END) AS in_stock_count
                FROM products p
                LEFT JOIN serial_numbers sn ON p.id = sn.product_id
                WHERE p.id = :id
                GROUP BY p.id";
        return $this->fetchOne($sql, ['id' => $id]);
    }

    public function create(array $data): int {
        $sql = "INSERT INTO products (name, category, specs, cost_price, selling_price, low_stock_threshold)
                VALUES (:name, :category, :specs, :cost_price, :selling_price, :low_stock_threshold)";
        $this->query($sql, [
            'name'                => trim($data['name']),
            'category'            => trim($data['category']),
            'specs'               => trim($data['specs'] ?? ''),
            'cost_price'          => (float)($data['cost_price'] ?? 0),
            'selling_price'       => (float)($data['selling_price'] ?? 0),
            'low_stock_threshold' => (int)($data['low_stock_threshold'] ?? 5),
        ]);
        return (int)$this->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $sql = "UPDATE products
                SET name = :name,
                    category = :category,
                    specs = :specs,
                    cost_price = :cost_price,
                    selling_price = :selling_price,
                    low_stock_threshold = :low_stock_threshold
                WHERE id = :id";
        $this->query($sql, [
            'id'                  => $id,
            'name'                => trim($data['name']),
            'category'            => trim($data['category']),
            'specs'               => trim($data['specs'] ?? ''),
            'cost_price'          => (float)($data['cost_price'] ?? 0),
            'selling_price'       => (float)($data['selling_price'] ?? 0),
            'low_stock_threshold' => (int)($data['low_stock_threshold'] ?? 5),
        ]);
        return true;
    }

    public function delete(int $id): bool {
        $sql = "DELETE FROM products WHERE id = :id";
        $this->query($sql, ['id' => $id]);
        return true;
    }

    /**
     * Get products whose In-Stock count is <= low_stock_threshold
     */
    public function getLowStockProducts(): array {
        $sql = "SELECT p.*,
                       COUNT(CASE WHEN sn.status = 'In-Stock' THEN 1 END) AS in_stock_count,
                       (p.low_stock_threshold - COUNT(CASE WHEN sn.status = 'In-Stock' THEN 1 END)) AS deficit
                FROM products p
                LEFT JOIN serial_numbers sn ON p.id = sn.product_id
                GROUP BY p.id
                HAVING in_stock_count <= p.low_stock_threshold
                ORDER BY deficit DESC, p.name ASC";
        return $this->fetchAll($sql);
    }

    public function getCategories(): array {
        return ['CPU', 'GPU', 'Motherboard', 'RAM', 'Storage', 'PSU', 'Case', 'Cooling', 'Accessories'];
    }
}
