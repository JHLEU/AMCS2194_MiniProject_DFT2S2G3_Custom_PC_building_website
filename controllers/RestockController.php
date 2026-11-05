<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/RestockOrder.php';
require_once __DIR__ . '/../models/Supplier.php';
require_once __DIR__ . '/../models/SupplierMapping.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/SerialNumber.php';

class RestockController extends BaseController {
    private RestockOrder $orderModel;
    private Supplier $supplierModel;
    private SupplierMapping $mappingModel;
    private Product $productModel;
    private SerialNumber $snModel;

    public function __construct() {
        $this->orderModel = new RestockOrder();
        $this->supplierModel = new Supplier();
        $this->mappingModel = new SupplierMapping();
        $this->productModel = new Product();
        $this->snModel = new SerialNumber();
    }

    /**
     * Active restock orders pipeline
     */
    public function index(): void {
        $status = $_GET['status'] ?? '';
        $supplierId = (int)($_GET['supplier_id'] ?? 0);

        $orders = $this->orderModel->getAll([
            'status'      => $status,
            'supplier_id' => $supplierId,
        ]);
        $suppliers = $this->supplierModel->getAll();

        $this->render('restock/index', [
            'pageTitle'        => 'Restock Purchase Orders Pipeline',
            'orders'           => $orders,
            'suppliers'        => $suppliers,
            'selectedStatus'   => $status,
            'selectedSupplier' => $supplierId,
        ]);
    }

    /**
     * Create Restock Order Interface
     */
    public function create(): void {
        $suppliers = $this->supplierModel->getAll();
        $allProducts = $this->productModel->getAllWithStockCounts();
        $preselectedSupplier = (int)($_GET['supplier_id'] ?? 0);
        $preselectedProduct = (int)($_GET['product_id'] ?? 0);

        $this->render('restock/create', [
            'pageTitle'           => 'Draft Restock Purchase Order',
            'suppliers'           => $suppliers,
            'allProducts'         => $allProducts,
            'preselectedSupplier' => $preselectedSupplier,
            'preselectedProduct'  => $preselectedProduct,
        ]);
    }

    /**
     * AJAX endpoint: Get mapped products for a selected supplier
     */
    public function getSupplierProducts(): void {
        $supplierId = (int)($_GET['supplier_id'] ?? 0);
        if ($supplierId <= 0) {
            $this->json(['success' => false, 'products' => []]);
        }

        $mapped = $this->mappingModel->getProductsBySupplier($supplierId);
        $this->json(['success' => true, 'products' => $mapped]);
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('restock/index');
        }

        $supplierId   = (int)($_POST['supplier_id'] ?? 0);
        $orderDate    = trim($_POST['order_date'] ?? date('Y-m-d'));
        $expectedDate = trim($_POST['expected_date'] ?? '');
        $notes        = trim($_POST['notes'] ?? '');
        $rawItems     = $_POST['items'] ?? [];

        if ($supplierId <= 0 || empty($rawItems)) {
            $this->setFlash('danger', 'Please choose a supplier and add at least one line item.');
            $this->redirect('restock/create');
        }

        $validItems = [];
        foreach ($rawItems as $item) {
            $prodId = (int)($item['product_id'] ?? 0);
            $qty = (int)($item['quantity'] ?? 0);
            $cost = (float)($item['unit_cost'] ?? 0);

            if ($prodId > 0 && $qty > 0 && $cost >= 0) {
                $validItems[] = [
                    'product_id' => $prodId,
                    'quantity'   => $qty,
                    'unit_cost'  => $cost,
                ];
            }
        }

        if (empty($validItems)) {
            $this->setFlash('danger', 'Please specify valid products and quantities for this order.');
            $this->redirect('restock/create');
        }

        try {
            $orderId = $this->orderModel->createOrder($supplierId, $orderDate, $expectedDate, $notes, $validItems);
            $this->setFlash('success', "Restock order created successfully (ID #{$orderId}).");
            $this->redirect('restock/view', ['id' => $orderId]);
        } catch (Exception $e) {
            $this->setFlash('danger', 'Order creation failed: ' . $e->getMessage());
            $this->redirect('restock/create');
        }
    }

    public function view(): void {
        $id = (int)($_GET['id'] ?? 0);
        $order = $this->orderModel->getById($id);

        if (!$order) {
            $this->setFlash('danger', 'Restock order not found.');
            $this->redirect('restock/index');
        }

        $items = $this->orderModel->getOrderItems($id);

        $this->render('restock/view', [
            'pageTitle' => "Restock Order: {$order['order_code']}",
            'order'     => $order,
            'items'     => $items,
        ]);
    }

    /**
     * Update status (e.g. Pending -> Approved -> Completed / Cancelled)
     */
    public function updateStatus(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('restock/index');
        }

        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $autoGenerateSN = !empty($_POST['auto_generate_sn']);

        $order = $this->orderModel->getById($id);
        if (!$order) {
            $this->setFlash('danger', 'Restock order not found.');
            $this->redirect('restock/index');
        }

        try {
            $this->orderModel->updateStatus($id, $status);

            // If order completed and auto-provisioning SNs is selected, generate In-Stock serial numbers
            if ($status === 'Completed' && $autoGenerateSN) {
                $items = $this->orderModel->getOrderItems($id);
                $generatedTotal = 0;

                foreach ($items as $item) {
                    $prodId = (int)$item['product_id'];
                    $qty = (int)$item['quantity'];
                    $cost = (float)$item['unit_cost'];

                    $codes = [];
                    for ($i = 1; $i <= $qty; $i++) {
                        $uniqueSuffix = strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
                        $codes[] = "SN-P{$prodId}-" . date('ymd') . "-{$uniqueSuffix}";
                    }

                    $res = $this->snModel->bulkCreate($prodId, $codes, $cost, $id);
                    $generatedTotal += $res['inserted'];
                }

                $this->setFlash('success', "Order #{$order['order_code']} marked Completed. Automatically provisioned {$generatedTotal} 'In-Stock' serial number(s).");
            } else {
                $this->setFlash('success', "Order #{$order['order_code']} status changed to '{$status}'.");
            }

            $this->redirect('restock/view', ['id' => $id]);
        } catch (Exception $e) {
            $this->setFlash('danger', 'Status update failed: ' . $e->getMessage());
            $this->redirect('restock/view', ['id' => $id]);
        }
    }

    /**
     * Module 4: Purchasing History (Read-only view for past completed transactions)
     */
    public function history(): void {
        $supplierId = (int)($_GET['supplier_id'] ?? 0);
        $dateFrom   = trim($_GET['date_from'] ?? '');
        $dateTo     = trim($_GET['date_to'] ?? '');
        $search     = trim($_GET['search'] ?? '');

        $history = $this->orderModel->getCompletedHistory([
            'supplier_id' => $supplierId,
            'date_from'   => $dateFrom,
            'date_to'     => $dateTo,
            'search'      => $search,
        ]);

        $metrics = $this->orderModel->getCompletedPurchasingMetrics();
        $suppliers = $this->supplierModel->getAll();

        $this->render('restock/history', [
            'pageTitle'        => 'Purchasing History & Transaction Archive',
            'history'          => $history,
            'metrics'          => $metrics,
            'suppliers'        => $suppliers,
            'selectedSupplier' => $supplierId,
            'dateFrom'         => $dateFrom,
            'dateTo'           => $dateTo,
            'search'           => $search,
        ]);
    }
}
