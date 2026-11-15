@extends('layouts.app')

@section('title', 'GOPC — Custom High-Performance PC Engineering')

@section('content')
<!-- Hero Stage with Interactive Scroll-Responsive Gaming PC Chassis -->
<section class="hero-stage">
    <div class="container">
        <div class="hero-layout">
            <!-- Left: Quiet, authoritative headline & actions -->
            <div>
                <div style="display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: var(--primary); display: inline-block;"></span>
                    <span style="font-family: 'Geist Mono', monospace; font-size: 0.75rem; color: var(--text-muted); letter-spacing: 0.04em;">
                        Workstation &amp; Gaming Rig Architecture
                    </span>
                </div>

                <h1 style="font-size: 3.25rem; font-weight: 600; line-height: 1.08; letter-spacing: -0.03em; color: var(--text-primary); margin-bottom: 20px;">
                    Precision hardware.<br>
                    Zero compromises.
                </h1>

                <p style="font-size: 1.0625rem; line-height: 1.6; color: var(--text-secondary); max-width: 540px; margin-bottom: 32px;">
                    Hand-crafted custom systems with socket-verified component matching, 24-hour thermal bench testing, and direct component warranty.
                </p>

                <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <a href="{{ route('builder.index') }}" class="btn btn-primary btn-lg">
                        Configure build
                    </a>
                    <a href="{{ route('calculator.index') }}" class="btn btn-secondary btn-lg">
                        Calculate wattage &amp; FPS
                    </a>
                    <a href="{{ route('orders.track') }}" class="btn btn-outline btn-lg">
                        Track order
                    </a>
                </div>

                <!-- Engineering highlights bar -->
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 48px; padding-top: 28px; border-top: 1px solid var(--border-color);">
                    <div>
                        <div class="font-mono" style="font-size: 1.25rem; font-weight: 600; color: var(--text-primary);">24h</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">Stress bench testing</div>
                    </div>
                    <div>
                        <div class="font-mono" style="font-size: 1.25rem; font-weight: 600; color: var(--primary);">0%</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">Socket mismatch risk</div>
                    </div>
                    <div>
                        <div class="font-mono" style="font-size: 1.25rem; font-weight: 600; color: var(--text-primary);">2 Years</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">Hardware warranty</div>
                    </div>
                </div>
            </div>

            <!-- Right: Interactive 3D Parallax Chassis that moves on scroll & cursor -->
            <div class="hero-case-viewport" id="heroCaseViewport">
                <div class="hero-glow-indicator"></div>
                
                <img 
                    src="{{ asset('pc-case-hero.jpg') }}" 
                    alt="Custom liquid-cooled esports PC build" 
                    class="hero-case-object" 
                    id="heroInteractiveCase"
                    loading="eager"
                />

                <div class="hero-specs-badge">
                    <span style="color: var(--primary);">●</span> Bench Ref: RTX 4080S / 7800X3D
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Content Sections: Featured Builds & Catalog -->
<div class="container" style="padding-top: 64px; padding-bottom: 64px;">

    <!-- Featured Configured Systems -->
    <div style="margin-bottom: 64px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
            <div>
                <span class="badge badge-neutral" style="margin-bottom: 8px;">Factory Calibrated</span>
                <h2 style="font-size: 1.5rem; font-weight: 600; color: var(--text-primary); margin: 0; letter-spacing: -0.02em;">
                    Engineered builds
                </h2>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin-top: 4px;">
                    Zero-bottleneck hardware combinations tested for sustained load.
                </p>
            </div>
            <a href="{{ route('builder.index') }}" class="btn btn-outline btn-sm">
                Open custom configurator &rarr;
            </a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
            @foreach($featuredBuilds as $build)
                <div class="card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between; padding: 20px;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                            <span class="badge badge-primary">{{ $build['tag'] }}</span>
                            <span class="font-mono" style="font-size: 0.75rem; color: var(--text-muted);">REF-{{ substr(md5($build['title']), 0, 4) }}</span>
                        </div>
                        <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--text-primary); margin-bottom: 6px; letter-spacing: -0.01em;">
                            {{ $build['title'] }}
                        </h3>
                        <p style="color: var(--text-secondary); font-size: 0.8125rem; margin-bottom: 16px; line-height: 1.5;">
                            {{ $build['specs_short'] }}
                        </p>
                        <div style="background: var(--bg-deep); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 8px 12px; margin-bottom: 16px; font-size: 0.75rem; display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-muted);">Target output:</span>
                            <span class="font-mono" style="color: var(--primary); font-weight: 500;">{{ $build['target_fps'] }}</span>
                        </div>
                    </div>

                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 14px; margin-top: 12px;">
                            <div>
                                <span style="font-size: 0.6875rem; color: var(--text-muted); display: block;">Base price</span>
                                <span class="font-mono" style="font-size: 1.125rem; font-weight: 600; color: var(--text-primary);">
                                    RM {{ number_format($build['price'], 2) }}
                                </span>
                            </div>
                            <a href="{{ route('builder.index', ['product_ids' => implode(',', $build['components'])]) }}" class="btn btn-primary btn-sm">
                                Configure build
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Hardware Catalog Categories Grid -->
    <div style="margin-bottom: 64px;">
        <div style="margin-bottom: 20px;">
            <h2 style="font-size: 1.375rem; font-weight: 600; color: var(--text-primary); margin-bottom: 4px; letter-spacing: -0.01em;">
                Hardware catalog
            </h2>
            <p style="color: var(--text-muted); font-size: 0.8125rem;">
                Official serialized inventory directly mapped from authorized distributors.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px;">
            @foreach($categories as $cat)
                <a href="{{ route('builder.index') }}" class="card" style="margin-bottom: 0; padding: 16px 14px; text-decoration: none; display: flex; flex-direction: column; align-items: flex-start; justify-content: space-between; transition: border-color 140ms ease;">
                    <span class="font-mono" style="font-size: 0.6875rem; color: var(--primary); margin-bottom: 12px;">
                        0{{ $loop->iteration }}
                    </span>
                    <div>
                        <div style="font-weight: 600; color: var(--text-primary); font-size: 0.875rem; margin-bottom: 2px;">{{ $cat->name }}</div>
                        <span class="font-mono" style="color: var(--text-muted); font-size: 0.6875rem;">{{ $cat->products_count }} parts</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Bottom Engineering Consultation Strip -->
    <div class="card" style="margin-bottom: 0; padding: 32px 36px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 24px; background: var(--bg-deep);">
        <div>
            <h3 style="font-size: 1.1875rem; font-weight: 600; color: var(--text-primary); margin-bottom: 4px; letter-spacing: -0.01em;">
                Custom architectural consultation
            </h3>
            <p style="color: var(--text-secondary); margin: 0; font-size: 0.875rem; max-width: 600px;">
                Looking for a multi-GPU deep learning workstation or liquid-loop build? Speak directly with our workshop engineers at Plaza Low Yat.
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('builder.index') }}" class="btn btn-primary">
                Open builder
            </a>
            <a href="{{ route('contact') }}" class="btn btn-outline">
                Contact technician
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Smooth scroll and 3D parallax responsive motion for the custom PC chassis
    document.addEventListener('DOMContentLoaded', function () {
        const pcCase = document.getElementById('heroInteractiveCase');
        const viewport = document.getElementById('heroCaseViewport');
        if (!pcCase || !viewport) return;

        let ticking = false;
        let mouseX = 0, mouseY = 0;
        let scrollY = window.pageYOffset || 0;

        function updateTransform() {
            // Calculate scroll progression
            const scrollRatio = Math.min(scrollY / 600, 1);
            
            // Subtle 3D perspective rotation combined from scroll & mouse offset
            const rotateX = (mouseY * -6) + (scrollRatio * 8);
            const rotateY = (mouseX * 8) - (scrollRatio * 14);
            const translateY = (scrollRatio * 42);
            const scale = 1 - (scrollRatio * 0.05);

            pcCase.style.transform = `
                translate3d(0, ${translateY}px, 0)
                rotateX(${rotateX}deg)
                rotateY(${rotateY}deg)
                scale(${scale})
            `;
            ticking = false;
        }

        window.addEventListener('scroll', function () {
            scrollY = window.pageYOffset || document.documentElement.scrollTop;
            if (!ticking) {
                window.requestAnimationFrame(updateTransform);
                ticking = true;
            }
        }, { passive: true });

        viewport.addEventListener('mousemove', function (e) {
            const rect = viewport.getBoundingClientRect();
            mouseX = ((e.clientX - rect.left) / rect.width) * 2 - 1;
            mouseY = ((e.clientY - rect.top) / rect.height) * 2 - 1;
            if (!ticking) {
                window.requestAnimationFrame(updateTransform);
                ticking = true;
            }
        });

        viewport.addEventListener('mouseleave', function () {
            mouseX = 0;
            mouseY = 0;
            if (!ticking) {
                window.requestAnimationFrame(updateTransform);
                ticking = true;
            }
        });

        // Trigger initial paint
        updateTransform();
    });
</script>
@endsection
