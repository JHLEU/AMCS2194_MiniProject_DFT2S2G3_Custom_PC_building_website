<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/SerialNumber.php';
require_once __DIR__ . '/../models/Supplier.php';

class InventoryController extends BaseController {
    private Product $productModel;
    private SerialNumber $snModel;
    private Supplier $supplierModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->snModel = new SerialNumber();
        $this->supplierModel = new Supplier();
    }

    /**
     * Main ERP Supply Chain & Inventory Overview Dashboard
     */
    public function dashboard(): void {
        $costingSummary = $this->snModel->getInventoryCostingSummary();
        $lowStockProducts = $this->productModel->getLowStockProducts();
        $allProducts = $this->productModel->getAllWithStockCounts();
        $suppliers = $this->supplierModel->getAll();

        $this->render('inventory/dashboard', [
            'pageTitle'        => 'Supply Chain & Inventory Management Dashboard',
            'costing'          => $costingSummary,
            'lowStockProducts' => $lowStockProducts,
            'totalProducts'    => count($allProducts),
            'totalSuppliers'   => count($suppliers),
        ]);
    }

    /**
     * Module 3: Low-Stock Alert Dashboard View
     * Counts 'In-Stock' SNs per product and highlights those falling below low_stock_threshold
     */
    public function lowStock(): void {
        $lowStockItems = $this->productModel->getLowStockProducts();

        $this->render('inventory/low_stock', [
            'pageTitle'     => 'Low-Stock Real-Time Alerts',
            'lowStockItems' => $lowStockItems,
        ]);
    }

    /**
     * Module 3: Inventory Costing & Valuation
     * Displays total inventory value based on batch cost prices of In-Stock hardware
     */
    public function costing(): void {
        $costingData = $this->snModel->getInventoryCostingSummary();

        $this->render('inventory/costing', [
            'pageTitle'   => 'Inventory Costing & Valuation Analysis',
            'costingData' => $costingData,
        ]);
    }

    /**
     * Module 3: SN Tracking & Registration
     * View, search, and manage unique serial numbers
     */
    public function serials(): void {
        $status = $_GET['status'] ?? '';
        $productId = (int)($_GET['product_id'] ?? 0);
        $search = trim($_GET['search'] ?? '');

        $serials = $this->snModel->getAll([
            'status'     => $status,
            'product_id' => $productId,
            'search'     => $search,
        ]);

        $products = $this->productModel->getAllWithStockCounts();

        $this->render('serials/index', [
            'pageTitle'       => 'Physical Serial Number (SN) Tracking',
            'serials'         => $serials,
            'products'        => $products,
            'selectedStatus'  => $status,
            'selectedProduct' => $productId,
            'search'          => $search,
        ]);
    }

    public function createSerial(): void {
        $products = $this->productModel->getAllWithStockCounts();
        $preselectedProduct = (int)($_GET['product_id'] ?? 0);

        $this->render('serials/create', [
            'pageTitle'          => 'Register Hardware Serial Numbers',
            'products'           => $products,
            'preselectedProduct' => $preselectedProduct,
        ]);
    }

    public function storeSerial(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('inventory/serials');
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        $mode = $_POST['mode'] ?? 'single'; // 'single' or 'bulk'
        $batchCost = (float)($_POST['batch_cost'] ?? 0);
        $status = $_POST['status'] ?? 'In-Stock';

        if ($productId <= 0) {
            $this->setFlash('danger', 'Please select a valid hardware product.');
            $this->redirect('inventory/create-serial');
        }

        // Fetch product default cost if not provided
        if ($batchCost <= 0) {
            $prod = $this->productModel->getById($productId);
            if ($prod) {
                $batchCost = (float)$prod['cost_price'];
            }
        }

        try {
            if ($mode === 'bulk') {
                $rawCodes = trim($_POST['bulk_sn_codes'] ?? '');
                $snList = preg_split('/[\r\n,]+/', $rawCodes, -1, PREG_SPLIT_NO_EMPTY);
                $result = $this->snModel->bulkCreate($productId, $snList, $batchCost);

                $msg = "Batch registered: {$result['inserted']} SN(s) added.";
                if ($result['skipped'] > 0) {
                    $msg .= " ({$result['skipped']} duplicate(s) skipped).";
                }
                $this->setFlash('success', $msg);
            } else {
                $snCode = trim($_POST['sn_code'] ?? '');
                if ($snCode === '') {
                    $this->setFlash('danger', 'Serial number code cannot be empty.');
                    $this->redirect('inventory/create-serial');
                }

                if ($this->snModel->exists($snCode)) {
                    $this->setFlash('danger', "Serial number '{$snCode}' already exists in the system.");
                    $this->redirect('inventory/create-serial');
                }

                $this->snModel->create($snCode, $productId, $status, $batchCost);
                $this->setFlash('success', "Serial number '{$snCode}' registered successfully.");
            }
            $this->redirect('inventory/serials');
        } catch (Exception $e) {
            $this->setFlash('danger', 'Error registering SN: ' . $e->getMessage());
            $this->redirect('inventory/create-serial');
        }
    }

    public function updateSerialStatus(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('inventory/serials');
        }

        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if ($id > 0 && in_array($status, ['In-Stock', 'Sold', 'RMA'], true)) {
            $this->snModel->updateStatus($id, $status);
            $this->setFlash('success', "Serial number status changed to '{$status}'.");
        } else {
            $this->setFlash('danger', 'Invalid status update request.');
        }

        $this->redirect('inventory/serials');
    }

    public function deleteSerial(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('inventory/serials');
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->snModel->delete($id);
            $this->setFlash('success', 'Serial number removed from tracking.');
        }

        $this->redirect('inventory/serials');
    }
}
