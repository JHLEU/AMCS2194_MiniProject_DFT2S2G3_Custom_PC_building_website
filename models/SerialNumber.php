<?php
require_once __DIR__ . '/BaseModel.php';

class SerialNumber extends BaseModel {
    /**
     * Get all serial numbers with product details and optional status/product filter
     */
    public function getAll(array $filters = []): array {
        $sql = "SELECT sn.*, p.name AS product_name, p.category AS product_category, ro.order_code
                FROM serial_numbers sn
                JOIN products p ON sn.product_id = p.id
                LEFT JOIN restock_orders ro ON sn.restock_order_id = ro.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND sn.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['product_id'])) {
            $sql .= " AND sn.product_id = :product_id";
            $params['product_id'] = (int)$filters['product_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (sn.sn_code LIKE :search OR p.name LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY sn.created_at DESC";
        return $this->fetchAll($sql, $params);
    }

    public function getById(int $id): ?array {
        $sql = "SELECT sn.*, p.name AS product_name, p.category, p.cost_price AS product_default_cost
                FROM serial_numbers sn
                JOIN products p ON sn.product_id = p.id
                WHERE sn.id = :id";
        return $this->fetchOne($sql, ['id' => $id]);
    }

    public function exists(string $snCode, ?int $excludeId = null): bool {
        $sql = "SELECT id FROM serial_numbers WHERE sn_code = :sn_code";
        $params = ['sn_code' => trim($snCode)];
        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        return $this->fetchOne($sql, $params) !== null;
    }

    public function create(string $snCode, int $productId, string $status = 'In-Stock', float $batchCost = 0.0, ?int $restockOrderId = null): int {
        $sql = "INSERT INTO serial_numbers (sn_code, product_id, status, batch_cost, restock_order_id)
                VALUES (:sn_code, :product_id, :status, :batch_cost, :restock_order_id)";
        $this->query($sql, [
            'sn_code'          => trim($snCode),
            'product_id'       => $productId,
            'status'           => $status,
            'batch_cost'       => $batchCost,
            'restock_order_id' => $restockOrderId,
        ]);
        return (int)$this->lastInsertId();
    }

    /**
     * Batch register serial numbers (e.g. from restock reception)
     */
    public function bulkCreate(int $productId, array $snCodes, float $batchCost = 0.0, ?int $restockOrderId = null): array {
        $inserted = 0;
        $skipped = 0;

        foreach ($snCodes as $code) {
            $cleanCode = trim($code);
            if ($cleanCode === '') continue;

            if ($this->exists($cleanCode)) {
                $skipped++;
                continue;
            }

            $this->create($cleanCode, $productId, 'In-Stock', $batchCost, $restockOrderId);
            $inserted++;
        }

        return ['inserted' => $inserted, 'skipped' => $skipped];
    }

    public function update(int $id, string $snCode, string $status, float $batchCost): bool {
        $sql = "UPDATE serial_numbers
                SET sn_code = :sn_code, status = :status, batch_cost = :batch_cost
                WHERE id = :id";
        $this->query($sql, [
            'id'         => $id,
            'sn_code'    => trim($snCode),
            'status'     => $status,
            'batch_cost' => $batchCost,
        ]);
        return true;
    }

    public function updateStatus(int $id, string $status): bool {
        $sql = "UPDATE serial_numbers SET status = :status WHERE id = :id";
        $this->query($sql, ['id' => $id, 'status' => $status]);
        return true;
    }

    public function delete(int $id): bool {
        $sql = "DELETE FROM serial_numbers WHERE id = :id";
        $this->query($sql, ['id' => $id]);
        return true;
    }

    /**
     * Module 3 Requirement: Inventory Costing & Valuation
     * Calculates total inventory value based on batch cost prices of all In-Stock units
     */
    public function getInventoryCostingSummary(): array {
        // Overall total
        $totalSql = "SELECT COUNT(id) AS total_units,
                            COALESCE(SUM(batch_cost), 0.00) AS total_valuation,
                            COALESCE(AVG(batch_cost), 0.00) AS avg_unit_cost
                     FROM serial_numbers
                     WHERE status = 'In-Stock'";
        $overall = $this->fetchOne($totalSql);

        // Grouped by Category
        $byCategorySql = "SELECT p.category,
                                 COUNT(sn.id) AS in_stock_count,
                                 COALESCE(SUM(sn.batch_cost), 0.00) AS category_valuation
                          FROM products p
                          LEFT JOIN serial_numbers sn ON p.id = sn.product_id AND sn.status = 'In-Stock'
                          GROUP BY p.category
                          HAVING in_stock_count > 0
                          ORDER BY category_valuation DESC";
        $byCategory = $this->fetchAll($byCategorySql);

        // Grouped by Product Item
        $byProductSql = "SELECT p.id AS product_id,
                                p.name AS product_name,
                                p.category,
                                COUNT(sn.id) AS in_stock_count,
                                COALESCE(MIN(sn.batch_cost), p.cost_price) AS min_batch_cost,
                                COALESCE(MAX(sn.batch_cost), p.cost_price) AS max_batch_cost,
                                COALESCE(AVG(sn.batch_cost), p.cost_price) AS avg_batch_cost,
                                COALESCE(SUM(sn.batch_cost), 0.00) AS total_product_valuation
                         FROM products p
                         JOIN serial_numbers sn ON p.id = sn.product_id AND sn.status = 'In-Stock'
                         GROUP BY p.id
                         ORDER BY total_product_valuation DESC";
        $byProduct = $this->fetchAll($byProductSql);

        return [
            'overall'     => $overall,
            'by_category' => $byCategory,
            'by_product'  => $byProduct,
        ];
    }
}
