<?php

namespace App\Controllers;

use App\Models\PcComponentModel;

class PcBuilderController
{
    // Custom PC 组装页面
    public function index()
    {
        $model = new PcComponentModel();

        $data = [
            'pageTitle'  => 'JUSPC Custom PC Configurator | 组装电脑配置器',
            'components' => $model->getComponents(),
            'presets'    => $model->getPresets(),
        ];

        return $this->render('custom_pc', $data);
    }

    // 全部配件目录页面
    public function listAll()
    {
        $model = new PcComponentModel();

        $data = [
            'pageTitle'  => '硬件产品目录 - JUSPC',
            'components' => $model->getComponents(),
        ];

        return $this->render('components_list', $data);
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
