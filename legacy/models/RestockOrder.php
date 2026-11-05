<?php
require_once __DIR__ . '/BaseModel.php';

class RestockOrder extends BaseModel {
    /**
     * Get restock orders with supplier info and optional status filter
     */
    public function getAll(array $filters = []): array {
        $sql = "SELECT ro.*,
                       s.company_name AS supplier_name,
                       s.contact_person AS supplier_contact,
                       COUNT(roi.id) AS total_line_items,
                       COALESCE(SUM(roi.quantity), 0) AS total_units_ordered
                FROM restock_orders ro
                JOIN suppliers s ON ro.supplier_id = s.id
                LEFT JOIN restock_order_items roi ON ro.id = roi.restock_order_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND ro.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['supplier_id'])) {
            $sql .= " AND ro.supplier_id = :supplier_id";
            $params['supplier_id'] = (int)$filters['supplier_id'];
        }

        if (!empty($filters['exclude_completed'])) {
            $sql .= " AND ro.status != 'Completed'";
        }

        $sql .= " GROUP BY ro.id ORDER BY ro.created_at DESC";
        return $this->fetchAll($sql, $params);
    }

    public function getById(int $id): ?array {
        $sql = "SELECT ro.*,
                       s.company_name AS supplier_name,
                       s.contact_person AS supplier_contact,
                       s.phone AS supplier_phone,
                       s.email AS supplier_email,
                       s.address AS supplier_address
                FROM restock_orders ro
                JOIN suppliers s ON ro.supplier_id = s.id
                WHERE ro.id = :id";
        return $this->fetchOne($sql, ['id' => $id]);
    }

    public function getOrderItems(int $orderId): array {
        $sql = "SELECT roi.*,
                       p.name AS product_name,
                       p.category AS product_category,
                       p.cost_price AS catalog_cost_price,
                       shm.supplier_sku
                FROM restock_order_items roi
                JOIN products p ON roi.product_id = p.id
                JOIN restock_orders ro ON roi.restock_order_id = ro.id
                LEFT JOIN supplier_hardware_mapping shm ON ro.supplier_id = shm.supplier_id AND roi.product_id = shm.product_id
                WHERE roi.restock_order_id = :order_id
                ORDER BY roi.id ASC";
        return $this->fetchAll($sql, ['order_id' => $orderId]);
    }

    /**
     * Create an atomic restock order with line items
     */
    public function createOrder(int $supplierId, string $orderDate, ?string $expectedDate, ?string $notes, array $items): int {
        $this->beginTransaction();
        try {
            $orderCode = 'RO-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));

            $totalAmount = 0.0;
            foreach ($items as $item) {
                $qty = (int)$item['quantity'];
                $cost = (float)$item['unit_cost'];
                if ($qty > 0) {
                    $totalAmount += ($qty * $cost);
                }
            }

            $sqlOrder = "INSERT INTO restock_orders (order_code, supplier_id, status, total_amount, order_date, expected_date, notes)
                         VALUES (:order_code, :supplier_id, 'Pending', :total_amount, :order_date, :expected_date, :notes)";
            $this->query($sqlOrder, [
                'order_code'    => $orderCode,
                'supplier_id'   => $supplierId,
                'total_amount'  => $totalAmount,
                'order_date'    => $orderDate ?: date('Y-m-d'),
                'expected_date' => $expectedDate ?: null,
                'notes'         => trim($notes ?? ''),
            ]);
            $orderId = (int)$this->lastInsertId();

            $sqlItem = "INSERT INTO restock_order_items (restock_order_id, product_id, quantity, unit_cost, subtotal)
                        VALUES (:restock_order_id, :product_id, :quantity, :unit_cost, :subtotal)";
            foreach ($items as $item) {
                $qty = (int)$item['quantity'];
                $cost = (float)$item['unit_cost'];
                if ($qty <= 0) continue;

                $this->query($sqlItem, [
                    'restock_order_id' => $orderId,
                    'product_id'       => (int)$item['product_id'],
                    'quantity'         => $qty,
                    'unit_cost'        => $cost,
                    'subtotal'         => ($qty * $cost),
                ]);
            }

            $this->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Update order status: Pending -> Approved -> Completed / Cancelled
     */
    public function updateStatus(int $id, string $status): bool {
        $validStatuses = ['Pending', 'Approved', 'Completed', 'Cancelled'];
        if (!in_array($status, $validStatuses, true)) {
            throw new InvalidArgumentException("Invalid status: {$status}");
        }

        $completedAt = ($status === 'Completed') ? date('Y-m-d H:i:s') : null;

        $sql = "UPDATE restock_orders
                SET status = :status,
                    completed_at = CASE WHEN :is_completed = 1 THEN :completed_at ELSE completed_at END
                WHERE id = :id";
        $this->query($sql, [
            'id'           => $id,
            'status'       => $status,
            'is_completed' => ($status === 'Completed' ? 1 : 0),
            'completed_at' => $completedAt,
        ]);
        return true;
    }

    /**
     * Module 4 Requirement: Purchasing History (Read-only view for past completed transactions)
     */
    public function getCompletedHistory(array $filters = []): array {
        $sql = "SELECT ro.*,
                       s.company_name AS supplier_name,
                       s.contact_person AS supplier_contact,
                       s.email AS supplier_email,
                       COUNT(roi.id) AS total_line_items,
                       COALESCE(SUM(roi.quantity), 0) AS total_units_received
                FROM restock_orders ro
                JOIN suppliers s ON ro.supplier_id = s.id
                LEFT JOIN restock_order_items roi ON ro.id = roi.restock_order_id
                WHERE ro.status = 'Completed'";
        $params = [];

        if (!empty($filters['supplier_id'])) {
            $sql .= " AND ro.supplier_id = :supplier_id";
            $params['supplier_id'] = (int)$filters['supplier_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND ro.order_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND ro.order_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (ro.order_code LIKE :search OR s.company_name LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " GROUP BY ro.id ORDER BY ro.completed_at DESC, ro.order_date DESC";
        return $this->fetchAll($sql, $params);
    }

    public function getCompletedPurchasingMetrics(): array {
        $sql = "SELECT COUNT(DISTINCT ro.id) AS total_completed_orders,
                       COALESCE(SUM(ro.total_amount), 0.00) AS total_spent,
                       COALESCE(SUM(roi.quantity), 0) AS total_items_procured
                FROM restock_orders ro
                LEFT JOIN restock_order_items roi ON ro.id = roi.restock_order_id
                WHERE ro.status = 'Completed'";
        return $this->fetchOne($sql);
    }
}
