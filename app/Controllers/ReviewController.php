<?php

namespace App\Controllers;

use App\Models\ReviewModel;

class ReviewController
{
    private ReviewModel $reviewModel;

    public function __construct()
    {
        $this->reviewModel = new ReviewModel();
    }

    public function index()
    {
        $filter = $_GET['filter'] ?? 'all';
        $reviews = $this->reviewModel->getAll($filter);
        $stats = $this->reviewModel->getStats();

        $data = [
            'pageTitle'  => 'Google 客户真实评价与装机晒单 (Reviews) - JUSPC',
            'activePage' => 'reviews',
            'reviews'    => $reviews,
            'stats'      => $stats,
            'filter'     => $filter
        ];

        return $this->render('reviews', $data);
    }

    public function create()
    {
        header('Content-Type: application/json; charset=utf-8');

        $name = trim($_POST['author_name'] ?? '');
        $rating = (int)($_POST['rating'] ?? 5);
        $text = trim($_POST['review_text'] ?? '');
        $specs = trim($_POST['specs_built'] ?? 'Custom Gaming PC');

        if (empty($name) || empty($text)) {
            echo json_encode(['success' => false, 'message' => '请填写您的姓名与评价内容！']);
            exit;
        }

        $id = $this->reviewModel->create([
            'author_name' => $name,
            'rating'      => $rating,
            'review_text' => $text,
            'specs_built' => $specs,
            'is_verified' => 1
        ]);

        echo json_encode([
            'success' => $id > 0,
            'message' => '感谢您的评价！您的 Google Review 已即时公布。',
            'id' => $id
        ]);
        exit;
    }

    public function update()
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['author_name'] ?? '');
        $rating = (int)($_POST['rating'] ?? 5);
        $text = trim($_POST['review_text'] ?? '');
        $specs = trim($_POST['specs_built'] ?? '');

        if ($id <= 0 || empty($name) || empty($text)) {
            echo json_encode(['success' => false, 'message' => '评价参数不完整！']);
            exit;
        }

        $ok = $this->reviewModel->update($id, [
            'author_name' => $name,
            'rating'      => $rating,
            'review_text' => $text,
            'specs_built' => $specs
        ]);

        echo json_encode(['success' => $ok, 'message' => '评价已成功修改！']);
        exit;
    }

    public function delete()
    {
        $id = (int)($_POST['id'] ?? ($_GET['id'] ?? 0));
        if ($id > 0) {
            $this->reviewModel->delete($id);
        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true]);
            exit;
        }

        header('Location: index.php?page=reviews');
        exit;
    }

    public function like()
    {
        header('Content-Type: application/json; charset=utf-8');
        $id = (int)($_POST['id'] ?? 0);
        $ok = ($id > 0) ? $this->reviewModel->like($id) : false;
        echo json_encode(['success' => $ok]);
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
