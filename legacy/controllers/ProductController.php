<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/SupplierMapping.php';

class ProductController extends BaseController {
    private Product $productModel;
    private SupplierMapping $mappingModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->mappingModel = new SupplierMapping();
    }

    public function index(): void {
        $products = $this->productModel->getAllWithStockCounts();
        $categories = $this->productModel->getCategories();

        $this->render('products/index', [
            'pageTitle'  => 'Hardware Catalog & Compatibility Specs',
            'products'   => $products,
            'categories' => $categories,
        ]);
    }

    public function create(): void {
        $this->render('products/create', [
            'pageTitle'  => 'Register New Hardware Component',
            'categories' => $this->productModel->getCategories(),
        ]);
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('products/index');
        }

        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $costPrice = (float)($_POST['cost_price'] ?? 0);
        $sellingPrice = (float)($_POST['selling_price'] ?? 0);
        $threshold = (int)($_POST['low_stock_threshold'] ?? 5);
        $specs = trim($_POST['specs'] ?? '');

        if ($name === '' || $category === '') {
            $this->setFlash('danger', 'Product Name and Category are required fields.');
            $this->redirect('products/create');
        }

        try {
            $id = $this->productModel->create([
                'name'                => $name,
                'category'            => $category,
                'cost_price'          => $costPrice,
                'selling_price'       => $sellingPrice,
                'low_stock_threshold' => $threshold,
                'specs'               => $specs,
            ]);
            $this->setFlash('success', "Product '{$name}' created successfully (ID #{$id}).");
            $this->redirect('products/index');
        } catch (Exception $e) {
            $this->setFlash('danger', 'Error creating product: ' . $e->getMessage());
            $this->redirect('products/create');
        }
    }

    public function edit(): void {
        $id = (int)($_GET['id'] ?? 0);
        $product = $this->productModel->getById($id);

        if (!$product) {
            $this->setFlash('danger', 'Product not found.');
            $this->redirect('products/index');
        }

        $suppliers = $this->mappingModel->getSuppliersByProduct($id);

        $this->render('products/edit', [
            'pageTitle'  => 'Edit Product: ' . $product['name'],
            'product'    => $product,
            'categories' => $this->productModel->getCategories(),
            'suppliers'  => $suppliers,
        ]);
    }

    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('products/index');
        }

        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $costPrice = (float)($_POST['cost_price'] ?? 0);
        $sellingPrice = (float)($_POST['selling_price'] ?? 0);
        $threshold = (int)($_POST['low_stock_threshold'] ?? 5);
        $specs = trim($_POST['specs'] ?? '');

        if ($id <= 0 || $name === '' || $category === '') {
            $this->setFlash('danger', 'Invalid product data submitted.');
            $this->redirect('products/edit', ['id' => $id]);
        }

        try {
            $this->productModel->update($id, [
                'name'                => $name,
                'category'            => $category,
                'cost_price'          => $costPrice,
                'selling_price'       => $sellingPrice,
                'low_stock_threshold' => $threshold,
                'specs'               => $specs,
            ]);
            $this->setFlash('success', "Product '{$name}' updated successfully.");
            $this->redirect('products/index');
        } catch (Exception $e) {
            $this->setFlash('danger', 'Error updating product: ' . $e->getMessage());
            $this->redirect('products/edit', ['id' => $id]);
        }
    }

    public function delete(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('products/index');
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $this->productModel->delete($id);
                $this->setFlash('success', 'Product deleted successfully.');
            } catch (Exception $e) {
                $this->setFlash('danger', 'Cannot delete product: ' . $e->getMessage());
            }
        }
        $this->redirect('products/index');
    }
}
