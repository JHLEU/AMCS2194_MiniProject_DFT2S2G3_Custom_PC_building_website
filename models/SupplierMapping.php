<?php
require_once __DIR__ . '/BaseModel.php';

class SupplierMapping extends BaseModel {
    /**
     * Get all hardware mapped to a specific supplier
     */
    public function getProductsBySupplier(int $supplierId): array {
        $sql = "SELECT shm.*,
                       p.name AS product_name,
                       p.category AS product_category,
                       p.cost_price AS catalog_cost_price,
                       p.selling_price
                FROM supplier_hardware_mapping shm
                JOIN products p ON shm.product_id = p.id
                WHERE shm.supplier_id = :supplier_id
                ORDER BY p.category ASC, p.name ASC";
        return $this->fetchAll($sql, ['supplier_id' => $supplierId]);
    }

    /**
     * Get suppliers capable of supplying a specific product
     */
    public function getSuppliersByProduct(int $productId): array {
        $sql = "SELECT shm.*,
                       s.company_name,
                       s.contact_person,
                       s.phone,
                       s.email
                FROM supplier_hardware_mapping shm
                JOIN suppliers s ON shm.supplier_id = s.id
                WHERE shm.product_id = :product_id
                ORDER BY shm.agreed_unit_price ASC";
        return $this->fetchAll($sql, ['product_id' => $productId]);
    }

    /**
     * Get all unmapped products for a supplier (to populate assignment dropdown)
     */
    public function getUnmappedProducts(int $supplierId): array {
        $sql = "SELECT p.*
                FROM products p
                WHERE p.id NOT IN (
                    SELECT product_id FROM supplier_hardware_mapping WHERE supplier_id = :supplier_id
                )
                ORDER BY p.category ASC, p.name ASC";
        return $this->fetchAll($sql, ['supplier_id' => $supplierId]);
    }

    /**
     * Assign or update hardware supply mapping
     */
    public function assignProduct(int $supplierId, int $productId, ?string $sku, float $agreedPrice, int $leadTimeDays = 3): bool {
        $sql = "INSERT INTO supplier_hardware_mapping (supplier_id, product_id, supplier_sku, agreed_unit_price, lead_time_days)
                VALUES (:supplier_id, :product_id, :sku, :price, :lead_time)
                ON DUPLICATE KEY UPDATE
                    supplier_sku = VALUES(supplier_sku),
                    agreed_unit_price = VALUES(agreed_unit_price),
                    lead_time_days = VALUES(lead_time_days)";
        $this->query($sql, [
            'supplier_id' => $supplierId,
            'product_id'  => $productId,
            'sku'         => trim($sku ?? ''),
            'price'       => $agreedPrice,
            'lead_time'   => $leadTimeDays,
        ]);
        return true;
    }

    public function removeMapping(int $supplierId, int $productId): bool {
        $sql = "DELETE FROM supplier_hardware_mapping
                WHERE supplier_id = :supplier_id AND product_id = :product_id";
        $this->query($sql, [
            'supplier_id' => $supplierId,
            'product_id'  => $productId,
        ]);
        return true;
    }
}
