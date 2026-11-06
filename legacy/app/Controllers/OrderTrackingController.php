<?php

namespace App\Controllers;

use App\Models\OrderModel;

class OrderTrackingController
{
    private OrderModel $orderModel;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
    }

    public function index()
    {
        $orderNo = trim($_GET['order_no'] ?? '');
        $trackedOrder = null;
        $searched = false;

        if (!empty($orderNo)) {
            $searched = true;
            $trackedOrder = $this->orderModel->getByOrderNumber($orderNo);
            if (!$trackedOrder) {
                // Try searching by phone
                $results = $this->orderModel->searchByPhoneOrCode($orderNo);
                if (!empty($results)) {
                    $trackedOrder = $this->orderModel->getByOrderNumber($results[0]['OrderNumber']);
                }
            }
        }

        $allOrders = $this->orderModel->getAll();

        $data = [
            'pageTitle'    => '订单组装流水与物流实时追踪 (Order Tracking) - JUSPC',
            'activePage'   => 'tracking',
            'orderNo'      => $orderNo,
            'trackedOrder' => $trackedOrder,
            'searched'     => $searched,
            'allOrders'    => $allOrders
        ];

        return $this->render('order_tracking', $data);
    }

    // CRUD: 创建新订单
    public function create()
    {
        header('Content-Type: application/json; charset=utf-8');

        $name = trim($_POST['customer_name'] ?? '');
        $phone = trim($_POST['customer_phone'] ?? '');
        $email = trim($_POST['customer_email'] ?? '');
        $address = trim($_POST['shipping_address'] ?? '');
        $amount = (float)($_POST['total_amount'] ?? 4500.0);
        $method = trim($_POST['payment_method'] ?? 'FPX Online Banking');

        if (empty($name) || empty($phone)) {
            echo json_encode(['success' => false, 'message' => '请填写客户姓名与联系电话！']);
            exit;
        }

        $res = $this->orderModel->createOrder([
            'customer_name'    => $name,
            'customer_phone'   => $phone,
            'customer_email'   => $email,
            'shipping_address' => $address,
            'total_amount'     => $amount,
            'payment_method'   => $method,
            'status'           => 'Paid'
        ]);

        echo json_encode($res);
        exit;
    }

    // CRUD: 更新订单信息与状态
    public function update()
    {
        header('Content-Type: application/json; charset=utf-8');

        $orderId = (int)($_POST['order_id'] ?? 0);
        if ($orderId <= 0) {
            echo json_encode(['success' => false, 'message' => '无效的订单 ID']);
            exit;
        }

        $ok = $this->orderModel->updateDetails($orderId, [
            'customer_name'    => trim($_POST['customer_name'] ?? ''),
            'customer_phone'   => trim($_POST['customer_phone'] ?? ''),
            'customer_email'   => trim($_POST['customer_email'] ?? ''),
            'shipping_address' => trim($_POST['shipping_address'] ?? ''),
            'order_status'     => trim($_POST['order_status'] ?? 'Paid'),
            'tracking_number'  => trim($_POST['tracking_number'] ?? ''),
            'carrier'          => trim($_POST['carrier'] ?? 'J&T Express')
        ]);

        echo json_encode(['success' => $ok, 'message' => '订单信息与进度状态已成功更新！']);
        exit;
    }

    // CRUD: 取消或删除订单
    public function delete()
    {
        $orderId = (int)($_POST['order_id'] ?? ($_GET['order_id'] ?? 0));
        if ($orderId > 0) {
            $this->orderModel->deleteOrder($orderId);
        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true]);
            exit;
        }

        header('Location: index.php?page=tracking');
        exit;
    }

    private function render(string $view, array $data = [])
    {
        extract($data);
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';

        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo "View file [{$view}] not found.";
        }
    }
}
