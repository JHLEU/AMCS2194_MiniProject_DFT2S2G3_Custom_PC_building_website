<?php

namespace App\Models;

use Database;
use PDO;
use Throwable;

require_once __DIR__ . '/../../config/db.php';

class OrderModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT o.*, p.PaymentMethod, p.PaymentStatus, p.TransactionID
                FROM Orders o
                LEFT JOIN Payments p ON o.OrderID = p.OrderID
                ORDER BY o.CreatedAt DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    public function getByOrderNumber(string $orderNumber): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT o.*, p.PaymentMethod, p.PaymentStatus, p.TransactionID, p.PaymentDate
                FROM Orders o
                LEFT JOIN Payments p ON o.OrderID = p.OrderID
                WHERE o.OrderNumber = ?
            ");
            $stmt->execute([trim($orderNumber)]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($order) {
                // Fetch items
                $itemStmt = $this->db->prepare("
                    SELECT oi.*, pr.name, pr.category, pr.brand, pr.specs
                    FROM OrderItems oi
                    LEFT JOIN products pr ON oi.ProductID = pr.id
                    WHERE oi.OrderID = ?
                ");
                $itemStmt->execute([$order['OrderID']]);
                $order['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

                return $order;
            }
            return null;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function searchByPhoneOrCode(string $query): array
    {
        try {
            $q = '%' . trim($query) . '%';
            $stmt = $this->db->prepare("
                SELECT o.*, p.PaymentMethod, p.PaymentStatus, p.TransactionID
                FROM Orders o
                LEFT JOIN Payments p ON o.OrderID = p.OrderID
                WHERE o.OrderNumber LIKE ? OR o.CustomerPhone LIKE ? OR o.CustomerEmail LIKE ?
                ORDER BY o.CreatedAt DESC
            ");
            $stmt->execute([$q, $q, $q]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    public function createOrder(array $data): array
    {
        $this->db->beginTransaction();
        try {
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
            $customerName = trim($data['customer_name'] ?? 'GOPC Valued Customer');
            $customerPhone = trim($data['customer_phone'] ?? '');
            $customerEmail = trim($data['customer_email'] ?? '');
            $shippingAddress = trim($data['shipping_address'] ?? 'Self Collection / Standard Shipping');
            $totalSelling = (float)($data['total_amount'] ?? 0.00);
            $totalCost = round($totalSelling * 0.78, 2); // Estimated hardware cost
            $status = $data['status'] ?? 'Paid';
            $trackingNumber = 'JT-MY' . date('ymd') . rand(1000, 9999) . 'MY';
            $carrier = 'J&T Express Cargo';

            $stmt = $this->db->prepare("
                INSERT INTO Orders (
                    CustomerID, CustomerName, CustomerPhone, CustomerEmail, 
                    ShippingAddress, OrderNumber, TotalSellingAmount, TotalCostAmount, 
                    OrderStatus, TrackingNumber, Carrier, CreatedAt
                ) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $customerName,
                $customerPhone,
                $customerEmail,
                $shippingAddress,
                $orderNumber,
                $totalSelling,
                $totalCost,
                $status,
                $trackingNumber,
                $carrier
            ]);

            $orderId = (int)$this->db->lastInsertId();

            // Insert Order Items if provided
            if (!empty($data['items']) && is_array($data['items'])) {
                $itemInsert = $this->db->prepare("
                    INSERT INTO OrderItems (OrderID, ProductID, Quantity, UnitPrice)
                    VALUES (?, ?, ?, ?)
                ");
                foreach ($data['items'] as $item) {
                    $prodId = (int)($item['product_id'] ?? 1);
                    $qty = max(1, (int)($item['quantity'] ?? 1));
                    $price = (float)($item['price'] ?? 0);
                    $itemInsert->execute([$orderId, $prodId, $qty, $price]);
                }
            }

            // Create Payment record
            $paymentMethod = $data['payment_method'] ?? 'FPX Online Banking';
            $txnId = 'TXN-' . date('Ymd') . '-' . rand(100000, 999999);
            $payStatus = ($status === 'Pending') ? 'Pending' : 'Success';

            $payStmt = $this->db->prepare("
                INSERT INTO Payments (OrderID, PaymentMethod, TransactionID, Amount, PaymentStatus, PaymentDate)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $payStmt->execute([$orderId, $paymentMethod, $txnId, $totalSelling, $payStatus]);

            $this->db->commit();

            return [
                'success' => true,
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'transaction_id' => $txnId,
                'total_amount' => $totalSelling,
                'tracking_number' => $trackingNumber,
                'carrier' => $carrier,
                'status' => $status
            ];
        } catch (Throwable $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function updateStatus(int $orderId, string $status, ?string $trackingNumber = null, ?string $carrier = null): bool
    {
        $stmt = $this->db->prepare("
            UPDATE Orders SET 
                OrderStatus = ?,
                TrackingNumber = COALESCE(?, TrackingNumber),
                Carrier = COALESCE(?, Carrier),
                UpdatedAt = NOW()
            WHERE OrderID = ?
        ");
        return $stmt->execute([$status, $trackingNumber, $carrier, $orderId]);
    }

    public function updateDetails(int $orderId, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE Orders SET 
                CustomerName = ?,
                CustomerPhone = ?,
                CustomerEmail = ?,
                ShippingAddress = ?,
                OrderStatus = ?,
                TrackingNumber = ?,
                Carrier = ?,
                UpdatedAt = NOW()
            WHERE OrderID = ?
        ");

        return $stmt->execute([
            trim($data['customer_name'] ?? ''),
            trim($data['customer_phone'] ?? ''),
            trim($data['customer_email'] ?? ''),
            trim($data['shipping_address'] ?? ''),
            $data['order_status'] ?? 'Paid',
            trim($data['tracking_number'] ?? ''),
            trim($data['carrier'] ?? ''),
            $orderId
        ]);
    }

    public function deleteOrder(int $orderId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM Orders WHERE OrderID = ?");
        return $stmt->execute([$orderId]);
    }
}
