<?php

namespace App\Models;

use Database;
use PDO;
use Throwable;

require_once __DIR__ . '/../../config/db.php';

class PcComponentModel
{
    public function getComponents(): array
    {
        $categories = [
            'cpu' => [
                'title' => '1. 处理器 (CPU)',
                'icon' => '💻',
                'db_cat' => 'CPU',
                'items' => [
                    [
                        'id' => 'cpu-1',
                        'name' => 'Intel Core i7-14700K',
                        'spec' => '20核 28线程 | 睿频 5.6GHz | LGA1700 | TDP 253W',
                        'price' => 1899,
                        'tdp' => 253,
                        'brand_icon' => '🟦',
                        'selected' => true
                    ],
                    [
                        'id' => 'cpu-2',
                        'name' => 'AMD Ryzen 7 7800X3D',
                        'spec' => '8核 16线程 | 游戏神器 3D V-Cache | AM5 | TDP 120W',
                        'price' => 1749,
                        'tdp' => 120,
                        'brand_icon' => '🟧',
                        'selected' => false
                    ],
                    [
                        'id' => 'cpu-3',
                        'name' => 'Intel Core i5-14600K',
                        'spec' => '14核 20线程 | 睿频 5.3GHz | LGA1700 | TDP 180W',
                        'price' => 1399,
                        'tdp' => 180,
                        'brand_icon' => '🟦',
                        'selected' => false
                    ],
                    [
                        'id' => 'cpu-4',
                        'name' => 'AMD Ryzen 5 7600X',
                        'spec' => '6核 12线程 | 睿频 5.3GHz | AM5 | TDP 105W',
                        'price' => 999,
                        'tdp' => 105,
                        'brand_icon' => '🟧',
                        'selected' => false
                    ]
                ]
            ],
            'gpu' => [
                'title' => '2. 显卡 (GPU)',
                'icon' => '🎮',
                'db_cat' => 'GPU',
                'items' => [
                    [
                        'id' => 'gpu-1',
                        'name' => 'NVIDIA RTX 4070 Ti Super 16G',
                        'spec' => '16GB GDDR6X | DLSS 3.5 | 4K 游戏高帧率 | TDP 285W',
                        'price' => 3899,
                        'tdp' => 285,
                        'brand_icon' => '🟩',
                        'selected' => true
                    ],
                    [
                        'id' => 'gpu-2',
                        'name' => 'NVIDIA RTX 4080 Super 16G',
                        'spec' => '16GB GDDR6X | 顶级画质与高阶 AI 算力 | TDP 320W',
                        'price' => 4899,
                        'tdp' => 320,
                        'brand_icon' => '🟩',
                        'selected' => false
                    ],
                    [
                        'id' => 'gpu-3',
                        'name' => 'NVIDIA RTX 4070 Super 12G',
                        'spec' => '12GB GDDR6X | 2K 极速电竞神卡 | TDP 220W',
                        'price' => 3099,
                        'tdp' => 220,
                        'brand_icon' => '🟩',
                        'selected' => false
                    ],
                    [
                        'id' => 'gpu-4',
                        'name' => 'NVIDIA RTX 4060 Ti 8G',
                        'spec' => '8GB GDDR6 | 甜品级 1080p/2K 超高能效 | TDP 160W',
                        'price' => 1899,
                        'tdp' => 160,
                        'brand_icon' => '🟩',
                        'selected' => false
                    ]
                ]
            ],
            'motherboard' => [
                'title' => '3. 主板 (Motherboard)',
                'icon' => '🔌',
                'db_cat' => 'Motherboard',
                'items' => [
                    [
                        'id' => 'mb-1',
                        'name' => 'ASUS ROG STRIX Z790-A GAMING WIFI',
                        'spec' => 'DDR5 | PCIe 5.0 | WiFi 6E | 吹雪系列白银高颜值',
                        'price' => 1499,
                        'tdp' => 50,
                        'brand_icon' => '⚙️',
                        'selected' => true
                    ],
                    [
                        'id' => 'mb-2',
                        'name' => 'ASUS ROG STRIX B650-A GAMING WIFI',
                        'spec' => 'AM5 | DDR5 | PCIe 5.0 M.2 | AMD 锐龙绝配',
                        'price' => 1199,
                        'tdp' => 50,
                        'brand_icon' => '⚙️',
                        'selected' => false
                    ],
                    [
                        'id' => 'mb-3',
                        'name' => 'MSI MAG B760 TOMAHAWK WIFI',
                        'spec' => 'LGA1700 | DDR5 7200MHz | 战斧导弹稳定耐久',
                        'price' => 999,
                        'tdp' => 45,
                        'brand_icon' => '⚙️',
                        'selected' => false
                    ]
                ]
            ],
            'ram' => [
                'title' => '4. 内存 (RAM)',
                'icon' => '⚡',
                'db_cat' => 'RAM',
                'items' => [
                    [
                        'id' => 'ram-1',
                        'name' => 'G.SKILL Trident Z5 RGB 32GB (16G x 2)',
                        'spec' => 'DDR5 6000MHz CL30 | 炫彩幻锋戟神光同步',
                        'price' => 659,
                        'tdp' => 20,
                        'brand_icon' => '🎛️',
                        'selected' => true
                    ],
                    [
                        'id' => 'ram-2',
                        'name' => 'Corsair Vengeance RGB 32GB (16G x 2)',
                        'spec' => 'DDR5 6000MHz CL36 | 美商海盗船高频低延迟',
                        'price' => 569,
                        'tdp' => 20,
                        'brand_icon' => '🎛️',
                        'selected' => false
                    ],
                    [
                        'id' => 'ram-3',
                        'name' => 'Kingston Fury Beast RGB 32GB (16G x 2)',
                        'spec' => 'DDR5 6000MHz | 极佳兼容性与稳健散热片',
                        'price' => 499,
                        'tdp' => 15,
                        'brand_icon' => '🎛️',
                        'selected' => false
                    ]
                ]
            ],
            'storage' => [
                'title' => '5. 固态硬盘 (Storage SSD)',
                'icon' => '💾',
                'db_cat' => 'Storage',
                'items' => [
                    [
                        'id' => 'ssd-1',
                        'name' => 'Samsung 990 PRO 2TB NVMe PCIe 4.0',
                        'spec' => '顺序读取 7450 MB/s | 旗舰速度 | 独立缓存',
                        'price' => 899,
                        'tdp' => 10,
                        'brand_icon' => '💿',
                        'selected' => true
                    ],
                    [
                        'id' => 'ssd-2',
                        'name' => 'Crucial T500 1TB Gen4 NVMe SSD',
                        'spec' => '顺序读取 7300 MB/s | 高能效低发热 | 原厂颗粒',
                        'price' => 419,
                        'tdp' => 8,
                        'brand_icon' => '💿',
                        'selected' => false
                    ]
                ]
            ],
            'cooling' => [
                'title' => '6. 散热器 (CPU Cooler)',
                'icon' => '❄️',
                'db_cat' => 'Cooling',
                'items' => [
                    [
                        'id' => 'cool-1',
                        'name' => 'DeepCool LT720 360mm 幻境一体式水冷',
                        'spec' => '360冷排 | 蒙德里安几何多维镜面 | 强劲解热300W+',
                        'price' => 499,
                        'tdp' => 30,
                        'brand_icon' => '🧊',
                        'selected' => true
                    ],
                    [
                        'id' => 'cool-2',
                        'name' => 'Thermalright PA120 SE 绝双刺客风冷',
                        'spec' => '双塔 6热管 | AGHP 逆重力 | 双12cm PWM静音风扇',
                        'price' => 179,
                        'tdp' => 15,
                        'brand_icon' => '🌪️',
                        'selected' => false
                    ]
                ]
            ],
            'psu' => [
                'title' => '7. 电源供应器 (Power Supply)',
                'icon' => '🔋',
                'db_cat' => 'PSU',
                'items' => [
                    [
                        'id' => 'psu-1',
                        'name' => 'Corsair RM850x 850W 80+ 金牌全模组',
                        'spec' => '原生 PCIe 5.0 12VHPWR | 全日系电容 | 零转速静音',
                        'price' => 629,
                        'tdp' => 0,
                        'brand_icon' => '⚡',
                        'selected' => true
                    ],
                    [
                        'id' => 'psu-2',
                        'name' => 'Seasonic Focus GX-750 750W 80+ 金牌',
                        'spec' => '全模组设计 | 10年质保 | 液压动态轴承静音',
                        'price' => 549,
                        'tdp' => 0,
                        'brand_icon' => '⚡',
                        'selected' => false
                    ]
                ]
            ],
            'case' => [
                'title' => '8. 机箱外壳 (PC Case)',
                'icon' => '🖥️',
                'db_cat' => 'Case',
                'items' => [
                    [
                        'id' => 'case-1',
                        'name' => 'NZXT H6 Flow RGB 双腔海景房机箱',
                        'spec' => '紧凑双腔设计 | 前侧斜角进风 | 预装3只ARGB风扇',
                        'price' => 569,
                        'tdp' => 0,
                        'brand_icon' => '📦',
                        'selected' => true
                    ],
                    [
                        'id' => 'case-2',
                        'name' => 'Lian Li O11 Vision 全透三面无界海景房',
                        'spec' => '三面一体化无柱钢化玻璃 | 模块化散热支架',
                        'price' => 699,
                        'tdp' => 0,
                        'brand_icon' => '📦',
                        'selected' => false
                    ]
                ]
            ]
        ];

        return $categories;
    }

    public function getPresets(): array
    {
        return [
            [
                'name' => '🎮 进阶电竞 (RTX 4070S)',
                'msg' => '已加载：进阶 2K 电竞配置 (i5-14600K + RTX 4070S)',
                'build' => [
                    'cpu' => 'cpu-3',
                    'gpu' => 'gpu-3',
                    'motherboard' => 'mb-3',
                    'ram' => 'ram-3',
                    'storage' => 'ssd-2',
                    'cooling' => 'cool-2',
                    'psu' => 'psu-2',
                    'case' => 'case-1'
                ]
            ],
            [
                'name' => '🔥 3A 旗舰 (4K 游戏王)',
                'msg' => '已加载：顶级 3A 旗舰配置 (7800X3D + RTX 4080S)',
                'build' => [
                    'cpu' => 'cpu-2',
                    'gpu' => 'gpu-2',
                    'motherboard' => 'mb-2',
                    'ram' => 'ram-1',
                    'storage' => 'ssd-1',
                    'cooling' => 'cool-1',
                    'psu' => 'psu-1',
                    'case' => 'case-2'
                ]
            ],
            [
                'name' => '💻 创作者工作站 (剪辑/渲染)',
                'msg' => '已加载：专业剪辑创作工作站 (i7-14700K + RTX 4070TiS)',
                'build' => [
                    'cpu' => 'cpu-1',
                    'gpu' => 'gpu-1',
                    'motherboard' => 'mb-1',
                    'ram' => 'ram-1',
                    'storage' => 'ssd-1',
                    'cooling' => 'cool-1',
                    'psu' => 'psu-1',
                    'case' => 'case-1'
                ]
            ],
            [
                'name' => '⚪ 吹雪纯白海景房',
                'msg' => '已加载：纯白海景房高颜值主题装机',
                'build' => [
                    'cpu' => 'cpu-1',
                    'gpu' => 'gpu-1',
                    'motherboard' => 'mb-1',
                    'ram' => 'ram-1',
                    'storage' => 'ssd-1',
                    'cooling' => 'cool-1',
                    'psu' => 'psu-1',
                    'case' => 'case-2'
                ]
            ]
        ];
    }

    public function getPopularItems(): array
    {
        return [
            ['name' => 'NVIDIA RTX 4080 Super 16G', 'category' => '显卡', 'price' => 4899, 'tag' => '🔥 4K 旗舰'],
            ['name' => 'AMD Ryzen 7 7800X3D', 'category' => '处理器', 'price' => 1749, 'tag' => '👑 游戏神U'],
            ['name' => 'Intel Core i7-14700K', 'category' => '处理器', 'price' => 1899, 'tag' => '⚡ 生产力全能'],
            ['name' => 'DeepCool LT720 360mm 水冷', 'category' => '散热', 'price' => 499, 'tag' => '❄️ 极速降温'],
            ['name' => 'Samsung 990 PRO 2TB SSD', 'category' => '存储', 'price' => 899, 'tag' => '🚀 7450MB/s'],
            ['name' => 'NZXT H6 Flow RGB 海景房', 'category' => '机箱', 'price' => 569, 'tag' => '✨ 双腔透亮']
        ];
    }
}
