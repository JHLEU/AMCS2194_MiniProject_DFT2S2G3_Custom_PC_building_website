<?php

namespace App\Controllers;

use App\Models\PcComponentModel;
use App\Models\SavedBuildModel;

class PcBuilderController
{
    private PcComponentModel $componentModel;
    private SavedBuildModel $savedBuildModel;

    public function __construct()
    {
        $this->componentModel = new PcComponentModel();
        $this->savedBuildModel = new SavedBuildModel();
    }

    // Custom PC 组装主界面
    public function index()
    {
        $loadId = isset($_GET['load_id']) ? (int)$_GET['load_id'] : null;
        $loadedBuild = null;
        if ($loadId) {
            $loadedBuild = $this->savedBuildModel->getById($loadId);
        }

        $data = [
            'pageTitle'    => 'JUSPC Custom PC Configurator | 配件组装配置器',
            'activePage'   => 'builder',
            'components'   => $this->componentModel->getComponents(),
            'presets'      => $this->componentModel->getPresets(),
            'loadedBuild'  => $loadedBuild,
        ];

        return $this->render('custom_pc', $data);
    }

    // 全部配件目录页面
    public function listAll()
    {
        $data = [
            'pageTitle'    => '硬件产品目录 - JUSPC',
            'activePage'   => 'components',
            'components'   => $this->componentModel->getComponents(),
        ];

        return $this->render('components_list', $data);
    }

    // 我的保存配置清单列表 (CRUD - Read)
    public function savedBuilds()
    {
        $data = [
            'pageTitle'    => '我的专属 PC 配置方案库 (Saved Builds) - JUSPC',
            'activePage'   => 'saved-builds',
            'builds'       => $this->savedBuildModel->getAll(),
        ];

        return $this->render('saved_builds', $data);
    }

    // 保存或更新配置 (CRUD - Create / Update)
    public function saveBuild()
    {
        header('Content-Type: application/json; charset=utf-8');

        $buildId = isset($_POST['build_id']) && is_numeric($_POST['build_id']) ? (int)$_POST['build_id'] : null;
        $buildName = trim($_POST['build_name'] ?? '玩家专属旗舰配置');
        $customerName = trim($_POST['customer_name'] ?? 'Gamer');
        $phone = trim($_POST['phone'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $wattage = (int)($_POST['wattage'] ?? 450);
        $price = (float)($_POST['price'] ?? 0.0);
        $componentsRaw = $_POST['components'] ?? '[]';

        $components = is_string($componentsRaw) ? json_decode($componentsRaw, true) : $componentsRaw;

        $data = [
            'build_name'    => $buildName,
            'customer_name' => $customerName,
            'phone'         => $phone,
            'notes'         => $notes,
            'wattage'       => $wattage,
            'price'         => $price,
            'components'    => $components ?: [],
        ];

        if ($buildId && $buildId > 0) {
            $ok = $this->savedBuildModel->update($buildId, $data);
            echo json_encode([
                'success' => $ok,
                'message' => '配置方案已成功更新！',
                'build_id' => $buildId
            ]);
        } else {
            $newId = $this->savedBuildModel->create($data);
            echo json_encode([
                'success' => $newId > 0,
                'message' => '专属配置方案已成功保存至配置库！',
                'build_id' => $newId
            ]);
        }
        exit;
    }

    // 删除配置方案 (CRUD - Delete)
    public function deleteBuild()
    {
        $id = (int)($_POST['id'] ?? ($_GET['id'] ?? 0));
        if ($id > 0) {
            $this->savedBuildModel->delete($id);
        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true]);
            exit;
        }

        header('Location: index.php?page=saved-builds&msg=deleted');
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
