<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Supplier.php';
require_once __DIR__ . '/../models/SupplierMapping.php';
require_once __DIR__ . '/../models/Product.php';

class SupplierController extends BaseController {
    private Supplier $supplierModel;
    private SupplierMapping $mappingModel;
    private Product $productModel;

    public function __construct() {
        $this->supplierModel = new Supplier();
        $this->mappingModel = new SupplierMapping();
        $this->productModel = new Product();
    }

    public function index(): void {
        $suppliers = $this->supplierModel->getAll();

        $this->render('suppliers/index', [
            'pageTitle' => 'Supplier Directory & Vendor Profiles',
            'suppliers' => $suppliers,
        ]);
    }

    public function create(): void {
        $this->render('suppliers/create', [
            'pageTitle' => 'Register New Vendor / Supplier',
        ]);
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('suppliers/index');
        }

        $companyName   = trim($_POST['company_name'] ?? '');
        $contactPerson = trim($_POST['contact_person'] ?? '');
        $phone         = trim($_POST['phone'] ?? '');
        $email         = trim($_POST['email'] ?? '');
        $address       = trim($_POST['address'] ?? '');

        if ($companyName === '' || $contactPerson === '' || $email === '') {
            $this->setFlash('danger', 'Company Name, Contact Person, and Email are required.');
            $this->redirect('suppliers/create');
        }

        try {
            $id = $this->supplierModel->create([
                'company_name'   => $companyName,
                'contact_person' => $contactPerson,
                'phone'          => $phone,
                'email'          => $email,
                'address'        => $address,
            ]);
            $this->setFlash('success', "Supplier '{$companyName}' registered successfully.");
            $this->redirect('suppliers/mapping', ['id' => $id]);
        } catch (Exception $e) {
            $this->setFlash('danger', 'Failed to register supplier: ' . $e->getMessage());
            $this->redirect('suppliers/create');
        }
    }

    public function edit(): void {
        $id = (int)($_GET['id'] ?? 0);
        $supplier = $this->supplierModel->getById($id);

        if (!$supplier) {
            $this->setFlash('danger', 'Supplier not found.');
            $this->redirect('suppliers/index');
        }

        $this->render('suppliers/edit', [
            'pageTitle' => 'Edit Supplier: ' . $supplier['company_name'],
            'supplier'  => $supplier,
        ]);
    }

    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('suppliers/index');
        }

        $id            = (int)($_POST['id'] ?? 0);
        $companyName   = trim($_POST['company_name'] ?? '');
        $contactPerson = trim($_POST['contact_person'] ?? '');
        $phone         = trim($_POST['phone'] ?? '');
        $email         = trim($_POST['email'] ?? '');
        $address       = trim($_POST['address'] ?? '');

        if ($id <= 0 || $companyName === '' || $contactPerson === '' || $email === '') {
            $this->setFlash('danger', 'Please provide valid supplier profile details.');
            $this->redirect('suppliers/edit', ['id' => $id]);
        }

        try {
            $this->supplierModel->update($id, [
                'company_name'   => $companyName,
                'contact_person' => $contactPerson,
                'phone'          => $phone,
                'email'          => $email,
                'address'        => $address,
            ]);
            $this->setFlash('success', 'Supplier profile updated successfully.');
            $this->redirect('suppliers/index');
        } catch (Exception $e) {
            $this->setFlash('danger', 'Update failed: ' . $e->getMessage());
            $this->redirect('suppliers/edit', ['id' => $id]);
        }
    }

    public function delete(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('suppliers/index');
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $this->supplierModel->delete($id);
                $this->setFlash('success', 'Supplier removed.');
            } catch (Exception $e) {
                $this->setFlash('danger', 'Cannot remove supplier: ' . $e->getMessage());
            }
        }
        $this->redirect('suppliers/index');
    }

    /**
     * Module 4: Hardware Mapping UI
     * Assign and configure hardware parts provided by a vendor
     */
    public function mapping(): void {
        $supplierId = (int)($_GET['id'] ?? 0);
        $supplier = $this->supplierModel->getById($supplierId);

        if (!$supplier) {
            $this->setFlash('danger', 'Supplier not found.');
            $this->redirect('suppliers/index');
        }

        $mappedProducts = $this->mappingModel->getProductsBySupplier($supplierId);
        $unmappedProducts = $this->mappingModel->getUnmappedProducts($supplierId);

        $this->render('suppliers/mapping', [
            'pageTitle'        => 'Hardware Supply Mapping: ' . $supplier['company_name'],
            'supplier'         => $supplier,
            'mappedProducts'   => $mappedProducts,
            'unmappedProducts' => $unmappedProducts,
        ]);
    }

    public function saveMapping(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('suppliers/index');
        }

        $supplierId = (int)($_POST['supplier_id'] ?? 0);
        $productId  = (int)($_POST['product_id'] ?? 0);
        $sku        = trim($_POST['supplier_sku'] ?? '');
        $price      = (float)($_POST['agreed_unit_price'] ?? 0);
        $leadTime   = (int)($_POST['lead_time_days'] ?? 3);

        if ($supplierId <= 0 || $productId <= 0 || $price <= 0) {
            $this->setFlash('danger', 'Please provide a valid product and positive agreed price.');
            $this->redirect('suppliers/mapping', ['id' => $supplierId]);
        }

        try {
            $this->mappingModel->assignProduct($supplierId, $productId, $sku, $price, $leadTime);
            $this->setFlash('success', 'Hardware supply mapping saved successfully.');
        } catch (Exception $e) {
            $this->setFlash('danger', 'Failed to save mapping: ' . $e->getMessage());
        }

        $this->redirect('suppliers/mapping', ['id' => $supplierId]);
    }

    public function removeMapping(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('suppliers/index');
        }

        $supplierId = (int)($_POST['supplier_id'] ?? 0);
        $productId  = (int)($_POST['product_id'] ?? 0);

        if ($supplierId > 0 && $productId > 0) {
            $this->mappingModel->removeMapping($supplierId, $productId);
            $this->setFlash('success', 'Product unmapped from this supplier.');
        }

        $this->redirect('suppliers/mapping', ['id' => $supplierId]);
    }
}
