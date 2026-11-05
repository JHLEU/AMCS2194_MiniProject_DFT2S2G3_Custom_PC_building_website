<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\SavedBuildModel;
use App\Models\PcComponentModel;

class PaymentController
{
    private OrderModel $orderModel;
    private SavedBuildModel $savedBuildModel;
    private PcComponentModel $pcModel;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->savedBuildModel = new SavedBuildModel();
        $this->pcModel = new PcComponentModel();
    }

    public function index()
    {
        $buildId = isset($_GET['build_id']) ? (int)$_GET['build_id'] : 0;
        $items = [];
        $total = 0;
        $customerName = 'Gamer Valued Customer';
        $customerPhone = '+60 12-345 6789';
        $customerEmail = 'gamer@juspc.my';
        $buildTitle = 'JUSPC 高性能电竞主机定制整机';

        if ($buildId > 0) {
            $build = $this->savedBuildModel->getById($buildId);
            if ($build) {
                $buildTitle = $build['BuildName'] ?? $buildTitle;
                $customerName = $build['CustomerName'] ?? $customerName;
                $customerPhone = $build['ContactPhone'] ?? $customerPhone;
                $total = (float)$build['EstimatedPrice'];
                if (!empty($build['components'])) {
                    foreach ($build['components'] as $c) {
                        $items[] = [
                            'name' => $c['name'] ?? 'Hardware Component',
                            'price' => (float)($c['price'] ?? 0),
                            'quantity' => 1
                        ];
                    }
                }
            }
        }

        // Default sample items if none loaded
        if (empty($items)) {
            $items = [
                ['name' => 'Intel Core i7-14700K 20核28线程', 'price' => 1899.00, 'quantity' => 1],
                ['name' => 'NVIDIA RTX 4070 Ti Super 16GB GDDR6X', 'price' => 3899.00, 'quantity' => 1],
                ['name' => 'ASUS ROG STRIX Z790-A GAMING WIFI', 'price' => 1499.00, 'quantity' => 1],
                ['name' => 'G.SKILL Trident Z5 RGB 32GB (16G*2)', 'price' => 659.00, 'quantity' => 1],
                ['name' => 'Samsung 990 PRO 2TB NVMe PCIe 4.0 SSD', 'price' => 899.00, 'quantity' => 1],
                ['name' => 'DeepCool LT720 360mm 幻境一体式水冷', 'price' => 499.00, 'quantity' => 1],
                ['name' => 'Corsair RM850x 850W 80+ 金牌全模组', 'price' => 629.00, 'quantity' => 1],
                ['name' => 'NZXT H6 Flow RGB 双腔海景房机箱', 'price' => 569.00, 'quantity' => 1]
            ];
            $total = 10554.00;
        }

        $data = [
            'pageTitle'     => '安全收银台 & 在线支付 (Payment Gateway) - JUSPC',
            'activePage'    => 'payment',
            'buildTitle'    => $buildTitle,
            'items'         => $items,
            'total'         => $total,
            'customerName'  => $customerName,
            'customerPhone' => $customerPhone,
            'customerEmail' => $customerEmail,
        ];

        return $this->render('payment', $data);
    }

    public function process()
    {
        header('Content-Type: application/json; charset=utf-8');

        $isRealApi = !empty($_POST['is_real_api']) && $_POST['is_real_api'] === 'true';
        $apiKey = trim($_POST['api_key'] ?? '');
        $paymentMethod = trim($_POST['payment_method'] ?? 'FPX Online Banking');
        $customerName = trim($_POST['customer_name'] ?? 'GOPC Valued Customer');
        $customerPhone = trim($_POST['customer_phone'] ?? '');
        $customerEmail = trim($_POST['customer_email'] ?? '');
        $shippingAddress = trim($_POST['shipping_address'] ?? 'Plaza Low Yat Showroom Pickup');
        $totalAmount = (float)($_POST['total_amount'] ?? 0.0);
        $itemsRaw = $_POST['items'] ?? '[]';
        $items = is_string($itemsRaw) ? json_decode($itemsRaw, true) : $itemsRaw;

        // Create the actual order in database
        $result = $this->orderModel->createOrder([
            'customer_name'    => $customerName,
            'customer_phone'   => $customerPhone,
            'customer_email'   => $customerEmail,
            'shipping_address' => $shippingAddress,
            'total_amount'     => $totalAmount,
            'payment_method'   => $paymentMethod . ($isRealApi ? ' (Live API)' : ' (Demo Sandbox)'),
            'status'           => 'Paid',
            'items'            => $items
        ]);

        if (!empty($result['success'])) {
            echo json_encode([
                'success'        => true,
                'mode'           => $isRealApi ? 'Live API Gateway' : 'Demo Sandbox Instant',
                'order_number'   => $result['order_number'],
                'transaction_id' => $result['transaction_id'],
                'tracking_number'=> $result['tracking_number'],
                'total_amount'   => $result['total_amount'],
                'message'        => '支付成功！订单已进入系统流水，正在派发至装机技术工程师。'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => '订单创建失败：' . ($result['error'] ?? '请检查数据库连接')
            ]);
        }
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
