@extends('layouts.app')

@section('title', 'Next-Gen Custom PC Builder & Hardware Solutions')

@section('content')
<div style="max-width: 1200px; margin: 40px auto; padding: 0 24px;">
    <!-- 1. Hero Banner -->
    <div style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.98)), radial-gradient(circle at 80% 20%, rgba(59, 130, 246, 0.35), transparent); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 70px 48px; text-align: center; margin-bottom: 56px; box-shadow: var(--shadow-lg); position: relative; overflow: hidden;">
        <span class="badge badge-info" style="margin-bottom: 16px; font-size: 0.85rem; padding: 6px 14px; text-transform: uppercase; letter-spacing: 1px;">
            ⚡ Live Compatibility & Power Estimation Engine
        </span>
        <h1 style="font-size: 2.8rem; font-weight: 800; color: #fff; margin-bottom: 18px; letter-spacing: -0.5px; line-height: 1.2;">
            Engineered For Performance.<br>
            <span style="background: linear-gradient(135deg, #60a5fa, #a78bfa); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                Built Exactly Your Way.
            </span>
        </h1>
        <p style="color: var(--text-light); font-size: 1.15rem; max-width: 720px; margin: 0 auto 36px; line-height: 1.6;">
            Design your dream gaming or workstation PC. Our real-time hardware compatibility engine prevents socket mismatches, verifies DDR4/DDR5 memory, and calculates true PSU wattage headroom.
        </p>
        <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('builder.index') }}" class="btn btn-primary" style="padding: 14px 32px; font-size: 1.05rem; font-weight: 600; box-shadow: 0 4px 14px rgba(59, 130, 246, 0.4);">
                ⚡ Launch Custom PC Builder
            </a>
            <a href="{{ route('calculator.index') }}" class="btn btn-secondary" style="padding: 14px 28px; font-size: 1.05rem;">
                🎯 Performance Calculator
            </a>
            <a href="{{ route('orders.track') }}" class="btn btn-outline" style="padding: 14px 24px; font-size: 1.05rem;">
                📦 Track Order
            </a>
        </div>
    </div>

    <!-- 2. Store Trust & Quality Badges -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 56px;">
        <div class="card" style="margin-bottom: 0; padding: 22px; border-left: 4px solid #3b82f6;">
            <div style="font-size: 2rem; margin-bottom: 10px;">🛡️</div>
            <h4 style="font-size: 1.05rem; font-weight: 600; color: #fff; margin-bottom: 6px;">2-Year Direct Warranty</h4>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0; line-height: 1.5;">
                Every component is serialized and protected under official store and distributor warranties.
            </p>
        </div>
        <div class="card" style="margin-bottom: 0; padding: 22px; border-left: 4px solid #10b981;">
            <div style="font-size: 2rem; margin-bottom: 10px;">🔬</div>
            <h4 style="font-size: 1.05rem; font-weight: 600; color: #fff; margin-bottom: 6px;">24-Hr Thermal Stress Testing</h4>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0; line-height: 1.5;">
                Bench-tested with FurMark, 3DMark, and Cinebench to ensure rock-solid stability before dispatch.
            </p>
        </div>
        <div class="card" style="margin-bottom: 0; padding: 22px; border-left: 4px solid #f59e0b;">
            <div style="font-size: 2rem; margin-bottom: 10px;">⚡</div>
            <h4 style="font-size: 1.05rem; font-weight: 600; color: #fff; margin-bottom: 6px;">Smart Compatibility</h4>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0; line-height: 1.5;">
                Live algorithm verifies socket compatibility, RAM timings, and PSU wattage headroom.
            </p>
        </div>
        <div class="card" style="margin-bottom: 0; padding: 22px; border-left: 4px solid #8b5cf6;">
            <div style="font-size: 2rem; margin-bottom: 10px;">🚚</div>
            <h4 style="font-size: 1.05rem; font-weight: 600; color: #fff; margin-bottom: 6px;">Insured Shockproof Transit</h4>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0; line-height: 1.5;">
                Custom instapak expandable foam protects the GPU and internal components during courier transit.
            </p>
        </div>
    </div>

    <!-- 3. Featured Pre-Configured Rigs -->
    <div style="margin-bottom: 56px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
            <div>
                <span class="badge badge-primary" style="margin-bottom: 8px;">Featured Systems</span>
                <h2 style="font-size: 1.8rem; font-weight: 700; color: #fff; margin: 0;">Pre-Engineered Flagship Battle Rigs</h2>
                <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 4px;">
                    Hand-crafted configurations optimized for zero bottlenecks and maximum frame rates.
                </p>
            </div>
            <a href="{{ route('builder.index') }}" class="btn btn-secondary">
                Custom Configurator →
            </a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
            @foreach($featuredBuilds as $build)
                <div class="card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between; border: 1px solid var(--border-color); transition: transform 0.2s, border-color 0.2s;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                            <span class="badge badge-info" style="font-size: 0.75rem;">{{ $build['tag'] }}</span>
                            <span style="font-size: 1.4rem;">🖥️</span>
                        </div>
                        <h3 style="font-size: 1.3rem; font-weight: 700; color: #fff; margin-bottom: 8px;">
                            {{ $build['title'] }}
                        </h3>
                        <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 16px; line-height: 1.5;">
                            {{ $build['specs_short'] }}
                        </p>
                        <div style="background: rgba(0,0,0,0.25); border-radius: 6px; padding: 10px 14px; margin-bottom: 18px; font-size: 0.85rem; color: #10b981;">
                            <strong>Target Performance:</strong> {{ $build['target_fps'] }}
                        </div>
                    </div>

                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 16px; margin-top: 12px;">
                            <div>
                                <small style="color: var(--text-muted); display: block;">Total Selling Price</small>
                                <span style="font-size: 1.3rem; font-weight: 800; color: var(--accent);">
                                    RM {{ number_format($build['price'], 2) }}
                                </span>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <a href="{{ route('builder.index', ['product_ids' => implode(',', $build['components'])]) }}" class="btn btn-primary btn-sm">
                                    Customize Build
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 4. Hardware Component Categories Grid -->
    <div style="margin-bottom: 56px;">
        <h2 style="font-size: 1.6rem; font-weight: 700; color: #fff; margin-bottom: 6px;">Browse Hardware Components</h2>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 24px;">
            100% genuine inventory directly mapped from certified authorized suppliers.
        </p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
            @php
                $catIcons = [
                    'CPU'         => '🔲',
                    'GPU'         => '🎮',
                    'Motherboard' => '🔌',
                    'RAM'         => '⚡',
                    'Storage'     => '💾',
                    'PSU'         => '🔋',
                    'Cooling'     => '❄️',
                    'Case'        => '🖥️',
                    'Accessories' => '🎧',
                ];
            @endphp
            @foreach($categories as $cat)
                <a href="{{ route('builder.index') }}" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; text-decoration: none; display: flex; flex-direction: column; align-items: center; text-align: center; transition: all 0.2s;">
                    <div style="font-size: 2rem; margin-bottom: 8px;">{{ $catIcons[$cat->name] ?? '📦' }}</div>
                    <div style="font-weight: 600; color: #fff; margin-bottom: 4px;">{{ $cat->name }}</div>
                    <small style="color: var(--text-muted);">{{ $cat->products_count }} Products</small>
                </a>
            @endforeach
        </div>
    </div>

    <!-- 5. Bottom Call to Action Strip -->
    <div style="background: linear-gradient(135deg, rgba(37, 99, 235, 0.2), rgba(30, 41, 59, 0.8)); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: var(--radius-lg); padding: 40px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div>
            <h3 style="font-size: 1.4rem; font-weight: 700; color: #fff; margin-bottom: 6px;">
                Ready to assemble your custom battlestation?
            </h3>
            <p style="color: var(--text-light); margin: 0; font-size: 0.95rem;">
                Try our interactive builder to assemble parts with zero compatibility headaches.
            </p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('builder.index') }}" class="btn btn-primary" style="padding: 12px 28px;">
                ⚡ Open PC Builder
            </a>
            <a href="{{ route('contact') }}" class="btn btn-outline" style="padding: 12px 24px;">
                💬 Speak to Specialist
            </a>
        </div>
    </div>
</div>
@endsection
