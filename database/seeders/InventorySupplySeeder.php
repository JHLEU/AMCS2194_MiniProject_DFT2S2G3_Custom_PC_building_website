<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\RestockOrder;
use App\Models\RestockOrderItem;
use App\Models\SerialNumber;
use App\Models\Supplier;
use App\Models\SupplierHardwareMapping;
use App\Models\SupportTicket;
use App\Models\TechSchedule;
use App\Models\User;
use App\Models\Warranty;
use App\Services\WarrantyService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InventorySupplySeeder extends Seeder
{
    /**
     * Seed the supply chain & inventory tables based on database.sql.
     */
    public function run(): void
    {
        // 1. Categories
        $categoryNames = ['CPU', 'GPU', 'Motherboard', 'RAM', 'Storage', 'PSU', 'Case', 'Cooling', 'Accessories'];
        $categoryMap = [];

        foreach ($categoryNames as $catName) {
            $category = Category::firstOrCreate(
                ['name' => $catName],
                [
                    'slug'        => Str::slug($catName),
                    'description' => "{$catName} hardware components",
                ]
            );
            $categoryMap[$catName] = $category->id;
        }

        // 2. Suppliers
        $suppliersData = [
            [
                'company_name'   => 'TechData Distribution Asia',
                'contact_person' => 'Marcus Vance',
                'phone'          => '+60 12-345 6789',
                'email'          => 'marcus.v@techdata.my',
                'address'        => 'Lot 45, Jalan Subang Utama, 47500 Subang Jaya, Selangor',
            ],
            [
                'company_name'   => 'Synnex Metrodata Components',
                'contact_person' => 'Sarah Tan',
                'phone'          => '+60 16-987 6543',
                'email'          => 'orders@synnex-metro.com',
                'address'        => 'Suite 12-B, Wisma Damansara, 50490 Kuala Lumpur',
            ],
            [
                'company_name'   => 'ASUS Direct Enterprise APAC',
                'contact_person' => 'Kevin Lee',
                'phone'          => '+60 11-2345 8899',
                'email'          => 'apac_sales@asus-direct.com',
                'address'        => 'Tech Park North, 11900 Bayan Lepas, Penang',
            ],
        ];

        $suppliers = [];
        foreach ($suppliersData as $index => $supData) {
            $suppliers[$index + 1] = Supplier::firstOrCreate(
                ['email' => $supData['email']],
                $supData
            );
        }

        // 3. Products Catalog
        $productsData = [
            1 => [
                'name'                => 'AMD Ryzen 7 7800X3D',
                'category'            => 'CPU',
                'supplier_idx'        => 1,
                'brand'               => 'AMD',
                'specs'               => 'Socket: AM5, Cores: 8, Threads: 16, TDP: 120W, PCIe 5.0, 3D V-Cache',
                'tdp_wattage'         => 120,
                'socket_type'         => 'AM5',
                'ram_type'            => 'DDR5',
                'cost_price'          => 320.00,
                'selling_price'       => 399.00,
                'warranty_months'     => 36,
                'low_stock_threshold' => 5,
            ],
            2 => [
                'name'                => 'Intel Core i7-14700K',
                'category'            => 'CPU',
                'supplier_idx'        => 1,
                'brand'               => 'Intel',
                'specs'               => 'Socket: LGA1700, Cores: 20 (8P+12E), TDP: 125W/253W Boost',
                'tdp_wattage'         => 253,
                'socket_type'         => 'LGA1700',
                'ram_type'            => 'DDR5',
                'cost_price'          => 360.00,
                'selling_price'       => 429.00,
                'warranty_months'     => 36,
                'low_stock_threshold' => 4,
            ],
            3 => [
                'name'                => 'NVIDIA GeForce RTX 4070 Super 12GB',
                'category'            => 'GPU',
                'supplier_idx'        => 1,
                'brand'               => 'NVIDIA',
                'specs'               => 'Length: 242mm, Slots: 2, Power: 220W, 1x 16-pin 12VHPWR',
                'tdp_wattage'         => 220,
                'socket_type'         => null,
                'ram_type'            => null,
                'cost_price'          => 510.00,
                'selling_price'       => 599.00,
                'warranty_months'     => 36,
                'low_stock_threshold' => 3,
            ],
            4 => [
                'name'                => 'ASUS ROG STRIX B650-A GAMING WIFI',
                'category'            => 'Motherboard',
                'supplier_idx'        => 3,
                'brand'               => 'ASUS',
                'specs'               => 'Form Factor: ATX, Socket: AM5, RAM: 4x DDR5, M.2 Slots: 3',
                'tdp_wattage'         => 50,
                'socket_type'         => 'AM5',
                'ram_type'            => 'DDR5',
                'cost_price'          => 190.00,
                'selling_price'       => 239.00,
                'warranty_months'     => 36,
                'low_stock_threshold' => 4,
            ],
            5 => [
                'name'                => 'Corsair Vengeance RGB 32GB (2x16GB)',
                'category'            => 'RAM',
                'supplier_idx'        => 2,
                'brand'               => 'Corsair',
                'specs'               => 'Type: DDR5, Speed: 6000MHz, Latency: CL30, Voltage: 1.35V',
                'tdp_wattage'         => 20,
                'socket_type'         => null,
                'ram_type'            => 'DDR5',
                'cost_price'          => 95.00,
                'selling_price'       => 129.00,
                'warranty_months'     => 60,
                'low_stock_threshold' => 6,
            ],
            6 => [
                'name'                => 'Samsung 990 PRO 2TB NVMe SSD',
                'category'            => 'Storage',
                'supplier_idx'        => 2,
                'brand'               => 'Samsung',
                'specs'               => 'Form: M.2 2280, Interface: PCIe 4.0 x4, Read: 7450 MB/s, Write: 6900 MB/s',
                'tdp_wattage'         => 10,
                'socket_type'         => null,
                'ram_type'            => null,
                'cost_price'          => 140.00,
                'selling_price'       => 179.00,
                'warranty_months'     => 60,
                'low_stock_threshold' => 5,
            ],
            7 => [
                'name'                => 'Corsair RM850x 850W Fully Modular',
                'category'            => 'PSU',
                'supplier_idx'        => 2,
                'brand'               => 'Corsair',
                'specs'               => 'Wattage: 850W, Efficiency: 80+ Gold, Form Factor: ATX',
                'tdp_wattage'         => 850,
                'socket_type'         => null,
                'ram_type'            => null,
                'cost_price'          => 110.00,
                'selling_price'       => 149.00,
                'warranty_months'     => 120,
                'low_stock_threshold' => 4,
            ],
            8 => [
                'name'                => 'ASUS ROG STRIX Z790-A GAMING WIFI',
                'category'            => 'Motherboard',
                'supplier_idx'        => 3,
                'brand'               => 'ASUS',
                'specs'               => 'Form Factor: ATX, Socket: LGA1700, RAM: 4x DDR5, PCIe 5.0',
                'tdp_wattage'         => 55,
                'socket_type'         => 'LGA1700',
                'ram_type'            => 'DDR5',
                'cost_price'          => 240.00,
                'selling_price'       => 309.00,
                'warranty_months'     => 36,
                'low_stock_threshold' => 3,
            ],
            9 => [
                'name'                => 'MSI B550M PRO-VDH WIFI',
                'category'            => 'Motherboard',
                'supplier_idx'        => 1,
                'brand'               => 'MSI',
                'specs'               => 'Form Factor: mATX, Socket: AM4, RAM: 4x DDR4, M.2 PCIe 4.0',
                'tdp_wattage'         => 40,
                'socket_type'         => 'AM4',
                'ram_type'            => 'DDR4',
                'cost_price'          => 85.00,
                'selling_price'       => 119.00,
                'warranty_months'     => 36,
                'low_stock_threshold' => 4,
            ],
            10 => [
                'name'                => 'Kingston Fury Beast 16GB (2x8GB) DDR4',
                'category'            => 'RAM',
                'supplier_idx'        => 2,
                'brand'               => 'Kingston',
                'specs'               => 'Type: DDR4, Speed: 3200MHz, Latency: CL16, Voltage: 1.35V',
                'tdp_wattage'         => 12,
                'socket_type'         => null,
                'ram_type'            => 'DDR4',
                'cost_price'          => 38.00,
                'selling_price'       => 59.00,
                'warranty_months'     => 60,
                'low_stock_threshold' => 5,
            ],
            11 => [
                'name'                => 'DeepCool LT720 360mm AIO Liquid Cooler',
                'category'            => 'Cooling',
                'supplier_idx'        => 2,
                'brand'               => 'DeepCool',
                'specs'               => 'Radiator: 360mm, Fans: 3x 120mm FDB, Max Thermal Dissipation: 300W+',
                'tdp_wattage'         => 30,
                'socket_type'         => null,
                'ram_type'            => null,
                'cost_price'          => 95.00,
                'selling_price'       => 139.00,
                'warranty_months'     => 60,
                'low_stock_threshold' => 3,
            ],
            12 => [
                'name'                => 'NZXT H6 Flow RGB Dual-Chamber Case',
                'category'            => 'Case',
                'supplier_idx'        => 2,
                'brand'               => 'NZXT',
                'specs'               => 'Form Factor: ATX Mid-Tower, Dual-Chamber Panoramic Glass, 3x 120mm ARGB Fans',
                'tdp_wattage'         => 15,
                'socket_type'         => null,
                'ram_type'            => null,
                'cost_price'          => 85.00,
                'selling_price'       => 125.00,
                'warranty_months'     => 24,
                'low_stock_threshold' => 3,
            ],
            13 => [
                'name'                => 'Corsair CV550 550W 80+ Bronze PSU',
                'category'            => 'PSU',
                'supplier_idx'        => 2,
                'brand'               => 'Corsair',
                'specs'               => 'Wattage: 550W, Efficiency: 80+ Bronze, Non-Modular ATX',
                'tdp_wattage'         => 550,
                'socket_type'         => null,
                'ram_type'            => null,
                'cost_price'          => 45.00,
                'selling_price'       => 69.00,
                'warranty_months'     => 36,
                'low_stock_threshold' => 3,
            ],
        ];

        $products = [];
        foreach ($productsData as $key => $prodData) {
            $supplierId = $suppliers[$prodData['supplier_idx']]->id ?? null;
            unset($prodData['supplier_idx']);

            $products[$key] = Product::firstOrCreate(
                ['name' => $prodData['name']],
                array_merge($prodData, [
                    'category_id' => $categoryMap[$prodData['category']] ?? null,
                    'supplier_id' => $supplierId,
                ])
            );
        }

        // 4. Supplier Hardware Mappings
        $mappings = [
            ['sup' => 1, 'prod' => 1, 'sku' => 'TD-CPU-AM5-7800X3D', 'price' => 315.00, 'lead' => 2],
            ['sup' => 1, 'prod' => 2, 'sku' => 'TD-CPU-1700-14700K', 'price' => 355.00, 'lead' => 2],
            ['sup' => 1, 'prod' => 3, 'sku' => 'TD-GPU-4070S-12G', 'price' => 505.00, 'lead' => 4],
            ['sup' => 2, 'prod' => 5, 'sku' => 'SYN-RAM-COR-DDR5-32G', 'price' => 92.00, 'lead' => 1],
            ['sup' => 2, 'prod' => 6, 'sku' => 'SYN-SSD-SAM-990P-2T', 'price' => 135.00, 'lead' => 2],
            ['sup' => 2, 'prod' => 7, 'sku' => 'SYN-PSU-RM850X', 'price' => 108.00, 'lead' => 3],
            ['sup' => 3, 'prod' => 4, 'sku' => 'ASUS-MB-B650A-STRIX', 'price' => 185.00, 'lead' => 3],
        ];

        foreach ($mappings as $m) {
            SupplierHardwareMapping::updateOrCreate(
                [
                    'supplier_id' => $suppliers[$m['sup']]->id,
                    'product_id'  => $products[$m['prod']]->id,
                ],
                [
                    'supplier_sku'      => $m['sku'],
                    'agreed_unit_price' => $m['price'],
                    'lead_time_days'    => $m['lead'],
                ]
            );
        }

        // 5. Restock Orders & Line Items
        $ordersData = [
            1 => [
                'order_code'   => 'RO-20261101-001',
                'supplier_id'  => $suppliers[1]->id,
                'status'       => RestockOrder::STATUS_COMPLETED,
                'total_amount' => 2680.00,
                'order_date'   => '2026-11-01',
                'completed_at' => '2026-11-03 14:30:00',
                'notes'        => 'Initial batch restocking for Ryzen 7800X3D and RTX 4070S',
                'items'        => [
                    ['prod' => 1, 'qty' => 4, 'cost' => 315.00, 'sub' => 1260.00],
                    ['prod' => 3, 'qty' => 2, 'cost' => 505.00, 'sub' => 1010.00],
                    ['prod' => 2, 'qty' => 1, 'cost' => 410.00, 'sub' => 410.00],
                ],
            ],
            2 => [
                'order_code'   => 'RO-20261104-002',
                'supplier_id'  => $suppliers[2]->id,
                'status'       => RestockOrder::STATUS_APPROVED,
                'total_amount' => 1144.00,
                'order_date'   => '2026-11-04',
                'completed_at' => null,
                'notes'        => 'Expedited order for DDR5 RAM and Samsung SSD units',
                'items'        => [
                    ['prod' => 5, 'qty' => 6, 'cost' => 92.00, 'sub' => 552.00],
                    ['prod' => 6, 'qty' => 4, 'cost' => 135.00, 'sub' => 540.00],
                    ['prod' => 7, 'qty' => 2, 'cost' => 26.00, 'sub' => 52.00],
                ],
            ],
            3 => [
                'order_code'   => 'RO-20261105-003',
                'supplier_id'  => $suppliers[3]->id,
                'status'       => RestockOrder::STATUS_PENDING,
                'total_amount' => 740.00,
                'order_date'   => '2026-11-05',
                'completed_at' => null,
                'notes'        => 'Stocking ASUS ROG B650 motherboards for weekend PC builds',
                'items'        => [
                    ['prod' => 4, 'qty' => 4, 'cost' => 185.00, 'sub' => 740.00],
                ],
            ],
        ];

        $orders = [];
        foreach ($ordersData as $idx => $roData) {
            $items = $roData['items'];
            unset($roData['items']);

            $order = RestockOrder::firstOrCreate(
                ['order_code' => $roData['order_code']],
                $roData
            );
            $orders[$idx] = $order;

            if ($order->items()->count() === 0) {
                foreach ($items as $item) {
                    RestockOrderItem::create([
                        'restock_order_id' => $order->id,
                        'product_id'       => $products[$item['prod']]->id,
                        'quantity'         => $item['qty'],
                        'unit_cost'        => $item['cost'],
                        'subtotal'         => $item['sub'],
                    ]);
                }
            }
        }

        // 6. Serial Numbers
        $serials = [
            ['sn' => 'SN-7800X3D-001', 'prod' => 1, 'status' => 'In-Stock', 'cost' => 320.00, 'ro' => 1],
            ['sn' => 'SN-7800X3D-002', 'prod' => 1, 'status' => 'In-Stock', 'cost' => 320.00, 'ro' => 1],
            ['sn' => 'SN-7800X3D-003', 'prod' => 1, 'status' => 'In-Stock', 'cost' => 320.00, 'ro' => 1],
            ['sn' => 'SN-7800X3D-004', 'prod' => 1, 'status' => 'Sold',     'cost' => 320.00, 'ro' => 1],
            ['sn' => 'SN-7800X3D-005', 'prod' => 1, 'status' => 'RMA',      'cost' => 320.00, 'ro' => 1],
            ['sn' => 'SN-14700K-001',  'prod' => 2, 'status' => 'In-Stock', 'cost' => 360.00, 'ro' => 1],
            ['sn' => 'SN-14700K-002',  'prod' => 2, 'status' => 'In-Stock', 'cost' => 360.00, 'ro' => 1],
            ['sn' => 'SN-4070S-001',   'prod' => 3, 'status' => 'In-Stock', 'cost' => 510.00, 'ro' => 1],
            ['sn' => 'SN-4070S-002',   'prod' => 3, 'status' => 'In-Stock', 'cost' => 510.00, 'ro' => 1],
            ['sn' => 'SN-B650A-001',   'prod' => 4, 'status' => 'In-Stock', 'cost' => 190.00, 'ro' => 3],
            ['sn' => 'SN-B650A-002',   'prod' => 4, 'status' => 'In-Stock', 'cost' => 190.00, 'ro' => 3],
            ['sn' => 'SN-RAM32G-001',  'prod' => 5, 'status' => 'In-Stock', 'cost' => 95.00,  'ro' => 2],
            ['sn' => 'SN-RAM32G-002',  'prod' => 5, 'status' => 'In-Stock', 'cost' => 95.00,  'ro' => 2],
            ['sn' => 'SN-RAM32G-003',  'prod' => 5, 'status' => 'In-Stock', 'cost' => 95.00,  'ro' => 2],
            ['sn' => 'SN-RAM32G-004',  'prod' => 5, 'status' => 'In-Stock', 'cost' => 95.00,  'ro' => 2],
            ['sn' => 'SN-990P-001',    'prod' => 6, 'status' => 'In-Stock', 'cost' => 140.00, 'ro' => 2],
            ['sn' => 'SN-990P-002',    'prod' => 6, 'status' => 'In-Stock', 'cost' => 140.00, 'ro' => 2],
            ['sn' => 'SN-990P-003',    'prod' => 6, 'status' => 'In-Stock', 'cost' => 140.00, 'ro' => 2],
            ['sn' => 'SN-RM850X-001',  'prod' => 7, 'status' => 'In-Stock', 'cost' => 110.00, 'ro' => 2],
            ['sn' => 'SN-RM850X-002',  'prod' => 7, 'status' => 'In-Stock', 'cost' => 110.00, 'ro' => 2],
            ['sn' => 'SN-RM850X-003',  'prod' => 7, 'status' => 'In-Stock', 'cost' => 110.00, 'ro' => 2],
            ['sn' => 'SN-Z790A-001',   'prod' => 8, 'status' => 'In-Stock', 'cost' => 240.00, 'ro' => 3],
            ['sn' => 'SN-B550M-001',   'prod' => 9, 'status' => 'In-Stock', 'cost' => 85.00,  'ro' => 1],
            ['sn' => 'SN-RAM16D4-001', 'prod' => 10, 'status' => 'In-Stock', 'cost' => 38.00, 'ro' => 2],
            ['sn' => 'SN-LT720-001',   'prod' => 11, 'status' => 'In-Stock', 'cost' => 95.00, 'ro' => 2],
            ['sn' => 'SN-H6FLOW-001',  'prod' => 12, 'status' => 'In-Stock', 'cost' => 85.00, 'ro' => 2],
            ['sn' => 'SN-CV550-001',   'prod' => 13, 'status' => 'In-Stock', 'cost' => 45.00, 'ro' => 2],
        ];

        foreach ($serials as $s) {
            SerialNumber::firstOrCreate(
                ['sn_code' => $s['sn']],
                [
                    'product_id'       => $products[$s['prod']]->id,
                    'status'           => $s['status'],
                    'batch_cost'       => $s['cost'],
                    'restock_order_id' => $orders[$s['ro']]->id ?? null,
                ]
            );
        }

        // 7. Customer Orders, Items & Payments
        $customerUser = User::where('email', 'customer@custompc.local')->first();

        $customerOrdersData = [
            1 => [
                'customer_id'          => $customerUser?->id,
                'customer_name'        => 'Bryan Tan (Gamer)',
                'customer_phone'       => '+60 12-345 6789',
                'customer_email'       => 'bryan.tan@example.com',
                'shipping_address'     => '12A, Jalan Kiara 3, Mont Kiara, 50480 Kuala Lumpur',
                'order_number'         => 'ORD-20261102-001',
                'total_selling_amount' => 1565.00,
                'total_cost_amount'    => 1225.00,
                'order_status'         => Order::STATUS_COMPLETED,
                'tracking_number'      => 'JT-MY2611021234MY',
                'carrier'              => 'J&T Express Cargo',
                'payment'              => [
                    'method' => Payment::METHOD_DUMMY_CARD,
                    'txn'    => 'TXN-20261102-882190',
                    'status' => Payment::STATUS_SUCCESS,
                    'date'   => '2026-11-02 10:15:00',
                ],
                'items' => [
                    ['prod' => 1, 'qty' => 1, 'price' => 399.00],
                    ['prod' => 3, 'qty' => 1, 'price' => 599.00],
                    ['prod' => 4, 'qty' => 1, 'price' => 239.00],
                    ['prod' => 5, 'qty' => 1, 'price' => 129.00],
                    ['prod' => 6, 'qty' => 1, 'price' => 179.00],
                ],
                'sold_sn' => ['SN-7800X3D-004'],
            ],
            2 => [
                'customer_id'          => $customerUser?->id,
                'customer_name'        => 'Darren Lim',
                'customer_phone'       => '+60 16-888 2345',
                'customer_email'       => 'darren.lim@example.com',
                'shipping_address'     => 'Unit 8-2, The Peak Residences, 11800 Tanjung Tokong, Penang',
                'order_number'         => 'ORD-20261105-002',
                'total_selling_amount' => 887.00,
                'total_cost_amount'    => 710.00,
                'order_status'         => Order::STATUS_ASSEMBLING,
                'tracking_number'      => 'JT-MY2611055678MY',
                'carrier'              => 'J&T Express Cargo',
                'payment'              => [
                    'method' => Payment::METHOD_DUMMY_FPX,
                    'txn'    => 'TXN-20261105-331902',
                    'status' => Payment::STATUS_SUCCESS,
                    'date'   => '2026-11-05 16:40:00',
                ],
                'items' => [
                    ['prod' => 2, 'qty' => 1, 'price' => 429.00],
                    ['prod' => 8, 'qty' => 1, 'price' => 309.00],
                    ['prod' => 7, 'qty' => 1, 'price' => 149.00],
                ],
                'reserved_sn' => ['SN-14700K-001', 'SN-Z790A-001', 'SN-RM850X-001'],
            ],
            3 => [
                'customer_id'          => null,
                'customer_name'        => 'Sarah Wong (Guest)',
                'customer_phone'       => '+60 19-777 4433',
                'customer_email'       => 'sarah.wong@example.com',
                'shipping_address'     => 'No. 33, Jalan Sutera 4, Taman Sutera Utama, 81300 Skudai, Johor',
                'order_number'         => 'ORD-20261106-003',
                'total_selling_amount' => 577.00,
                'total_cost_amount'    => 443.00,
                'order_status'         => Order::STATUS_SHIPPED,
                'tracking_number'      => 'JT-MY2611069012MY',
                'carrier'              => 'J&T Express Cargo',
                'payment'              => [
                    'method' => Payment::METHOD_DUMMY_SANDBOX,
                    'txn'    => 'TXN-20261106-440192',
                    'status' => Payment::STATUS_SUCCESS,
                    'date'   => '2026-11-06 09:20:00',
                ],
                'items' => [
                    ['prod' => 1, 'qty' => 1, 'price' => 399.00],
                    ['prod' => 9, 'qty' => 1, 'price' => 119.00],
                    ['prod' => 10, 'qty' => 1, 'price' => 59.00],
                ],
                'reserved_sn' => ['SN-7800X3D-002', 'SN-B550M-001', 'SN-RAM16D4-001'],
            ],
        ];

        foreach ($customerOrdersData as $coData) {
            $paymentInfo = $coData['payment'];
            $items = $coData['items'];
            $soldSns = $coData['sold_sn'] ?? [];
            $reservedSns = $coData['reserved_sn'] ?? [];
            unset($coData['payment'], $coData['items'], $coData['sold_sn'], $coData['reserved_sn']);

            $custOrder = Order::firstOrCreate(
                ['order_number' => $coData['order_number']],
                $coData
            );

            if ($custOrder->items()->count() === 0) {
                foreach ($items as $item) {
                    OrderItem::create([
                        'order_id'   => $custOrder->id,
                        'product_id' => $products[$item['prod']]->id,
                        'quantity'   => $item['qty'],
                        'unit_price' => $item['price'],
                    ]);
                }
            }

            if (!$custOrder->payment) {
                Payment::create([
                    'order_id'       => $custOrder->id,
                    'payment_method' => $paymentInfo['method'],
                    'transaction_id' => $paymentInfo['txn'],
                    'amount'         => $custOrder->total_selling_amount,
                    'payment_status' => $paymentInfo['status'],
                    'payment_date'   => $paymentInfo['date'],
                ]);
            }

            // Link serial numbers
            foreach ($soldSns as $snCode) {
                SerialNumber::where('sn_code', $snCode)->update([
                    'order_id' => $custOrder->id,
                    'status'   => SerialNumber::STATUS_SOLD,
                ]);
            }

            foreach ($reservedSns as $snCode) {
                SerialNumber::where('sn_code', $snCode)->update([
                    'order_id' => $custOrder->id,
                    'status'   => SerialNumber::STATUS_RESERVED,
                ]);
            }

            if ($custOrder->order_status === Order::STATUS_COMPLETED) {
                app(WarrantyService::class)->registerWarrantiesForOrder($custOrder);
            }
        }

        // 7. Seed Initial Support Ticket & Technician Dispatch Schedule
        $firstWarranty = Warranty::with('order')->first();
        $techUser = User::where('email', 'tech@custompc.local')->first();

        if ($firstWarranty && $techUser && SupportTicket::count() === 0) {
            $sampleTicket = SupportTicket::create([
                'customer_id'       => $firstWarranty->order->customer_id ?? 4,
                'warranty_id'       => $firstWarranty->id,
                'subject'           => 'GPU display artifacts under heavy gaming load',
                'issue_description' => 'When running intensive benchmarks or games, pink and green checkerboard artifacts appear across the screen before crashing with driver timeout error.',
                'status'            => SupportTicket::STATUS_DIAGNOSING,
            ]);

            TechSchedule::create([
                'support_ticket_id'        => $sampleTicket->id,
                'technician_id'            => $techUser->id,
                'scheduled_datetime'       => now()->addDay()->setTime(14, 0),
                'estimated_duration_hours' => 2,
                'notes'                    => 'Mount onto test bench rig, run FurMark stress cycle, verify PCIe 4.0 lanes.',
            ]);
        }
    }
}
