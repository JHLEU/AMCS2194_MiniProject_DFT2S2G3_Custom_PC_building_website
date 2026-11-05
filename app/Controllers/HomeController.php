<?php

namespace App\Controllers;

use App\Models\PcComponentModel;

class HomeController
{
    public function index()
    {
        $model = new PcComponentModel();

        $data = [
            'pageTitle'    => 'JUSPC - 官方主页 | Custom PC Specialist',
            'presets'      => $model->getPresets(),
            'popularItems' => $model->getPopularItems(),
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