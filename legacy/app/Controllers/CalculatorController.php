<?php

namespace App\Controllers;

use App\Models\PcComponentModel;

class CalculatorController
{
    private PcComponentModel $componentModel;

    public function __construct()
    {
        $this->componentModel = new PcComponentModel();
    }

    public function index()
    {
        $cpuList = [
            ['id' => 'i7-14700K', 'name' => 'Intel Core i7-14700K', 'brand' => 'Intel', 'cores' => '20核28线程', 'tdp' => 253, 'score' => 96, 'price' => 1899],
            ['id' => '7800X3D', 'name' => 'AMD Ryzen 7 7800X3D', 'brand' => 'AMD', 'cores' => '8核16线程 (3D V-Cache)', 'tdp' => 120, 'score' => 99, 'price' => 1749],
            ['id' => 'i9-14900K', 'name' => 'Intel Core i9-14900K', 'brand' => 'Intel', 'cores' => '24核32线程', 'tdp' => 300, 'score' => 99, 'price' => 2699],
            ['id' => 'i5-14600K', 'name' => 'Intel Core i5-14600K', 'brand' => 'Intel', 'cores' => '14核20线程', 'tdp' => 180, 'score' => 88, 'price' => 1399],
            ['id' => '7600X', 'name' => 'AMD Ryzen 5 7600X', 'brand' => 'AMD', 'cores' => '6核12线程', 'tdp' => 105, 'score' => 84, 'price' => 999],
            ['id' => 'i5-13400F', 'name' => 'Intel Core i5-13400F', 'brand' => 'Intel', 'cores' => '10核16线程', 'tdp' => 65, 'score' => 75, 'price' => 749],
        ];

        $gpuList = [
            ['id' => '4080s', 'name' => 'NVIDIA RTX 4080 Super 16GB', 'brand' => 'NVIDIA', 'tdp' => 320, 'score' => 98, 'price' => 4899],
            ['id' => '4070tis', 'name' => 'NVIDIA RTX 4070 Ti Super 16GB', 'brand' => 'NVIDIA', 'tdp' => 285, 'score' => 91, 'price' => 3899],
            ['id' => '4070s', 'name' => 'NVIDIA RTX 4070 Super 12GB', 'brand' => 'NVIDIA', 'tdp' => 220, 'score' => 84, 'price' => 3099],
            ['id' => '4060ti', 'name' => 'NVIDIA RTX 4060 Ti 8GB', 'brand' => 'NVIDIA', 'tdp' => 160, 'score' => 68, 'price' => 1899],
            ['id' => '4090', 'name' => 'NVIDIA RTX 4090 24GB Flagship', 'brand' => 'NVIDIA', 'tdp' => 450, 'score' => 100, 'price' => 8899],
            ['id' => '7800xt', 'name' => 'AMD Radeon RX 7800 XT 16GB', 'brand' => 'AMD', 'tdp' => 263, 'score' => 82, 'price' => 2599],
        ];

        $data = [
            'pageTitle'  => 'CPU 算力、瓶颈与整机功耗智能测算工具 - JUSPC',
            'activePage' => 'cpu-calc',
            'cpuList'    => $cpuList,
            'gpuList'    => $gpuList,
        ];

        return $this->render('cpu_calculator', $data);
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
