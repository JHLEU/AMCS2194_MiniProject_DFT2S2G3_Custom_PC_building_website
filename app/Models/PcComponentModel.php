<?php

namespace App\Models;

class PcComponentModel
{
    public function getComponents(): array
    {
        return [
            'cpu' => [
                'title' => '1. 处理器 (CPU)',
                'icon' => '💻',
                'items' => [
                    [
                        'id' => 'cpu-1',
                        'name' => 'Intel Core i7-14700K',
                        'spec' => '20核 28线程 | 睿频 5.6GHz | LGA1700',
                        'price' => 1899,
                        'brand_icon' => '🟦',
                        'selected' => true
                    ],
                    [
                        'id' => 'cpu-2',
                        'name' => 'AMD Ryzen 7 7800X3D',
                        'spec' => '8核 16线程 | 游戏神器 3D V-Cache | AM5',
                        'price' => 1749,
                        'brand_icon' => '🟧',
                        'selected' => false
                    ]
                ]
            ],
            'gpu' => [
                'title' => '2. 显卡 (GPU)',
                'icon' => '🎮',
                'items' => [
                    [
                        'id' => 'gpu-1',
                        'name' => 'NVIDIA RTX 4070 Ti Super 16G',
                        'spec' => '16GB GDDR6X | DLSS 3.5 | 4K 游戏强力支持',
                        'price' => 3899,
                        'brand_icon' => '🟩',
                        'selected' => true
                    ],
                    [
                        'id' => 'gpu-2',
                        'name' => 'NVIDIA RTX 4080 Super 16G',
                        'spec' => '16GB GDDR6X | 顶级画质与 AI 算力',
                        'price' => 4999,
                        'brand_icon' => '🟩',
                        'selected' => false
                    ]
                ]
            ],
            'motherboard' => [
                'title' => '3. 主板 (Motherboard)',
                'icon' => '🔌',
                'items' => [
                    [
                        'id' => 'mb-1',
                        'name' => 'ASUS ROG STRIX Z790-A GAMING WIFI',
                        'spec' => 'DDR5 | PCIe 5.0 | WiFi 6E | 吹雪系列白配色',
                        'price' => 1499,
                        'brand_icon' => '⚙️',
                        'selected' => true
                    ]
                ]
            ],
            'ram' => [
                'title' => '4. 内存 (RAM)',
                'icon' => '⚡',
                'items' => [
                    [
                        'id' => 'ram-1',
                        'name' => 'G.SKILL Trident Z5 RGB 32GB (16GB x 2)',
                        'spec' => 'DDR5 6000MHz CL30 | 炫彩 RGB',
                        'price' => 659,
                        'brand_icon' => '🎛️',
                        'selected' => true
                    ]
                ]
            ]
        ];
    }

    public function getPresets(): array
    {
        return [
            ['name' => '🎮 入门电竞', 'msg' => '已加载：电竞入门级配置'],
            ['name' => '🔥 3A 旗舰', 'msg' => '已加载：3A 游戏旗舰配置'],
            ['name' => '💻 剪辑工作站', 'msg' => '已加载：生产力剪辑工作站']
        ];
    }

    public function getPopularItems(): array
    {
        return [
            ['name' => 'NVIDIA RTX 4080 Super', 'category' => '显卡', 'price' => 4999],
            ['name' => 'Intel i7-14700K', 'category' => '处理器', 'price' => 1899],
            ['name' => 'AMD Ryzen 7 7800X3D', 'category' => '处理器', 'price' => 1749],
        ];
    }
}