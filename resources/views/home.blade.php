@extends('layouts.app')

@section('title', 'Next-Gen Custom PC Builder & Hardware Solutions')

@section('content')
<div style="max-width: 1200px; margin: 40px auto; padding: 0 24px;">
    <!-- Hero Banner -->
    <div style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.9), rgba(15, 23, 42, 0.95)), radial-gradient(circle at top right, rgba(59, 130, 246, 0.3), transparent); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 60px 40px; text-align: center; margin-bottom: 48px; box-shadow: var(--shadow-lg);">
        <h1 style="font-size: 2.5rem; font-weight: 800; color: #fff; margin-bottom: 16px; letter-spacing: -0.5px;">
            Build Your Ultimate Rig with Live Compatibility & Wattage
        </h1>
        <p style="color: var(--text-light); font-size: 1.1rem; max-width: 700px; margin: 0 auto 32px;">
            High-performance custom gaming & workstation PCs. Powered by an enterprise inventory & warranty tracking ERP engine.
        </p>
        <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('login') }}" class="btn btn-primary" style="padding: 12px 28px; font-size: 1rem;">
                🚀 Launch PC Builder (Sign In)
            </a>
            <a href="{{ route('login') }}" class="btn btn-outline" style="padding: 12px 28px; font-size: 1rem;">
                🛡️ ERP Staff Portal
            </a>
        </div>
    </div>

    <!-- Features Overview -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
        <div class="card" style="margin-bottom: 0;">
            <div style="font-size: 2rem; margin-bottom: 12px;">⚡</div>
            <h3 style="color: #fff; margin-bottom: 8px;">Real-Time Compatibility</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem;">
                Instant socket validation (LGA1700, AM5), DDR4/DDR5 checks, and PSU wattage headroom calculation.
            </p>
        </div>

        <div class="card" style="margin-bottom: 0;">
            <div style="font-size: 2rem; margin-bottom: 12px;">🔍</div>
            <h3 style="color: #fff; margin-bottom: 8px;">Order & Warranty Tracking</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem;">
                Every component is tracked by individual hardware serial number with end-to-end warranty records.
            </p>
        </div>

        <div class="card" style="margin-bottom: 0;">
            <div style="font-size: 2rem; margin-bottom: 12px;">📊</div>
            <h3 style="color: #fff; margin-bottom: 8px;">Integrated ERP Backend</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem;">
                Warehouse inventory, supplier purchase orders, cash flow accounting, and technician dispatch.
            </p>
        </div>
    </div>
</div>
@endsection
