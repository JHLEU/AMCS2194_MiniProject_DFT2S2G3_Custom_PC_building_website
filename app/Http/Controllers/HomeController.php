<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('products')->get();

        // Curated featured PC configurations based on seeded products
        $featuredBuilds = [
            [
                'id'          => 'esports-starter',
                'title'       => 'Esports Champion Build',
                'tag'         => '1080p Competitive High FPS',
                'specs_short' => 'AMD Ryzen 5 7600X • RTX 4070 • 32GB DDR5 • 1TB Gen4 NVMe',
                'target_fps'  => '240+ FPS in CS2 & Valorant',
                'price'       => 4684.00,
                'cpu_name'    => 'Ryzen 5 7600X',
                'gpu_name'    => 'RTX 4070 Dual OC',
                'components'  => [2, 4, 6, 7, 8, 11, 12, 13], // product IDs
            ],
            [
                'id'          => '4k-enthusiast',
                'title'       => 'Apex 4K Ultra Rig',
                'tag'         => '4K Ray Tracing & VR Master',
                'specs_short' => 'AMD Ryzen 7 7800X3D • RTX 4090 OC • 32GB DDR5 • 2TB Gen4 NVMe',
                'target_fps'  => '120+ FPS in Cyberpunk 4K Ultra',
                'price'       => 8899.00,
                'cpu_name'    => 'Ryzen 7 7800X3D',
                'gpu_name'    => 'RTX 4090 Rog Strix',
                'components'  => [1, 3, 5, 7, 8, 11, 12, 13],
            ],
            [
                'id'          => 'creator-studio',
                'title'       => 'Studio Pro Workstation',
                'tag'         => '8K Video Editing & 3D Render',
                'specs_short' => 'Intel Core i7-14700K • RTX 4080 Super • 64GB DDR5 • 2TB Gen4 NVMe',
                'target_fps'  => 'Blender & Premiere Pro Realtime',
                'price'       => 7499.00,
                'cpu_name'    => 'Core i7-14700K',
                'gpu_name'    => 'RTX 4080 Super',
                'components'  => [1, 4, 6, 7, 8, 11, 12, 13],
            ],
        ];

        // Customer Reviews & Testimonials
        $testimonials = [
            [
                'name'    => 'Marcus Tan',
                'role'    => 'Esports Competitor',
                'rating'  => 5,
                'comment' => 'Custom PC arrived flawlessly packaged with immaculate cable management. Thermal benchmarks passed with zero throttling under 24-hour stress test. Top-tier service!',
                'date'    => 'Reviewed on Google Maps (Kuala Lumpur)',
            ],
            [
                'name'    => 'Sarah Lee',
                'role'    => '3D Architectural Visualizer',
                'rating'  => 5,
                'comment' => 'The online compatibility engine prevented me from ordering the wrong RAM kit. Received tracking within 48 hours and the 2-year warranty gives complete peace of mind.',
                'date'    => 'Reviewed on Google Maps (Petaling Jaya)',
            ],
            [
                'name'    => 'Ahmad Fauzi',
                'role'    => 'Software Engineer & Gamer',
                'rating'  => 5,
                'comment' => 'Exceptional build quality. Cleanest cable routing I have seen in Malaysia. Their technician even provided benchmark charts in the delivery manifest.',
                'date'    => 'Reviewed on Google Maps (Penang)',
            ],
        ];

        return view('home', compact('categories', 'featuredBuilds', 'testimonials'));
    }
}
