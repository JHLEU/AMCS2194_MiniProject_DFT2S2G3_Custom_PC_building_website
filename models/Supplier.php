<?php
require_once __DIR__ . '/BaseModel.php';

class Supplier extends BaseModel {
    public function getAll(): array {
        $sql = "SELECT s.*,
                       COUNT(DISTINCT shm.product_id) AS mapped_products_count,
                       COUNT(DISTINCT ro.id) AS total_orders_count
                FROM suppliers s
                LEFT JOIN supplier_hardware_mapping shm ON s.id = shm.supplier_id
                LEFT JOIN restock_orders ro ON s.id = ro.supplier_id
                GROUP BY s.id
                ORDER BY s.company_name ASC";
        return $this->fetchAll($sql);
    }

    public function getById(int $id): ?array {
        $sql = "SELECT s.*,
                       COUNT(DISTINCT shm.product_id) AS mapped_products_count
                FROM suppliers s
                LEFT JOIN supplier_hardware_mapping shm ON s.id = shm.supplier_id
                WHERE s.id = :id
                GROUP BY s.id";
        return $this->fetchOne($sql, ['id' => $id]);
    }

    public function create(array $data): int {
        $sql = "INSERT INTO suppliers (company_name, contact_person, phone, email, address)
                VALUES (:company_name, :contact_person, :phone, :email, :address)";
        $this->query($sql, [
            'company_name'   => trim($data['company_name']),
            'contact_person' => trim($data['contact_person']),
            'phone'          => trim($data['phone']),
            'email'          => trim($data['email']),
            'address'        => trim($data['address'] ?? ''),
        ]);
        return (int)$this->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $sql = "UPDATE suppliers
                SET company_name = :company_name,
                    contact_person = :contact_person,
                    phone = :phone,
                    email = :email,
                    address = :address
                WHERE id = :id";
        $this->query($sql, [
            'id'             => $id,
            'company_name'   => trim($data['company_name']),
            'contact_person' => trim($data['contact_person']),
            'phone'          => trim($data['phone']),
            'email'          => trim($data['email']),
            'address'        => trim($data['address'] ?? ''),
        ]);
        return true;
    }

    public function delete(int $id): bool {
        $sql = "DELETE FROM suppliers WHERE id = :id";
        $this->query($sql, ['id' => $id]);
        return true;
    }
}
