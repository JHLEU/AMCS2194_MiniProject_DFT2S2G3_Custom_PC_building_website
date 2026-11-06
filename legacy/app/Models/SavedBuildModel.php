<?php

namespace App\Models;

use Database;
use PDO;
use Throwable;

require_once __DIR__ . '/../../config/db.php';

class SavedBuildModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        try {
            $stmt = $this->db->query("SELECT * FROM SavedBuilds ORDER BY CreatedAt DESC");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$row) {
                if (!empty($row['ComponentsJSON'])) {
                    $row['components'] = json_decode($row['ComponentsJSON'], true) ?: [];
                } else {
                    $row['components'] = [];
                }
            }
            return $rows;
        } catch (Throwable $e) {
            return [];
        }
    }

    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM SavedBuilds WHERE BuildID = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $row['components'] = !empty($row['ComponentsJSON']) ? json_decode($row['ComponentsJSON'], true) : [];
                return $row;
            }
            return null;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO SavedBuilds (
                CustomerID, BuildName, CustomerName, ContactPhone, 
                ComponentsJSON, TotalWattage, EstimatedPrice, Notes, CreatedAt
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $customerId = !empty($data['customer_id']) ? (int)$data['customer_id'] : null;
        $buildName = !empty($data['build_name']) ? trim($data['build_name']) : '玩家专属配机方案';
        $customerName = !empty($data['customer_name']) ? trim($data['customer_name']) : 'Guest Gamer';
        $phone = !empty($data['phone']) ? trim($data['phone']) : null;
        $componentsJson = is_array($data['components']) ? json_encode($data['components'], JSON_UNESCAPED_UNICODE) : $data['components'];
        $wattage = (int)($data['wattage'] ?? 450);
        $price = (float)($data['price'] ?? 0.00);
        $notes = $data['notes'] ?? null;

        $stmt->execute([
            $customerId,
            $buildName,
            $customerName,
            $phone,
            $componentsJson,
            $wattage,
            $price,
            $notes
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE SavedBuilds SET 
                BuildName = ?,
                CustomerName = ?,
                ContactPhone = ?,
                ComponentsJSON = ?,
                TotalWattage = ?,
                EstimatedPrice = ?,
                Notes = ?,
                UpdatedAt = NOW()
            WHERE BuildID = ?
        ");

        $buildName = !empty($data['build_name']) ? trim($data['build_name']) : '玩家专属配机方案';
        $customerName = !empty($data['customer_name']) ? trim($data['customer_name']) : 'Guest Gamer';
        $phone = !empty($data['phone']) ? trim($data['phone']) : null;
        $componentsJson = is_array($data['components']) ? json_encode($data['components'], JSON_UNESCAPED_UNICODE) : $data['components'];
        $wattage = (int)($data['wattage'] ?? 450);
        $price = (float)($data['price'] ?? 0.00);
        $notes = $data['notes'] ?? null;

        return $stmt->execute([
            $buildName,
            $customerName,
            $phone,
            $componentsJson,
            $wattage,
            $price,
            $notes,
            $id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM SavedBuilds WHERE BuildID = ?");
        return $stmt->execute([$id]);
    }
}
