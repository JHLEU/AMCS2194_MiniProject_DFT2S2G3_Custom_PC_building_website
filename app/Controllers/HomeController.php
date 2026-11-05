<?php

namespace App\Controllers;

use App\Models\PcComponentModel;
use App\Models\ReviewModel;

class HomeController
{
    public function index()
    {
        $pcModel = new PcComponentModel();
        $reviewModel = new ReviewModel();

        $data = [
            'pageTitle'    => 'GOPC - 马来西亚高端定制电脑专家 | Custom PC Specialist',
            'activePage'   => 'home',
            'presets'      => $pcModel->getPresets(),
            'popularItems' => $pcModel->getPopularItems(),
            'reviewStats'  => $reviewModel->getStats(),
            'topReviews'   => array_slice($reviewModel->getAll('all'), 0, 4)
        ];

        return $this->render('home', $data);
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
