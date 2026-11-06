@extends('layouts.app')

@section('title', 'Interactive Custom PC Builder')

@section('styles')
<style>
    .builder-grid {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 28px;
        align-items: start;
    }

    @media (max-width: 992px) {
        .builder-grid {
            grid-template-columns: 1fr;
        }
    }

    .slot-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 20px;
        margin-bottom: 16px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .slot-card:hover {
        border-color: rgba(59, 130, 246, 0.4);
    }

    .slot-card.has-selection {
        border-left: 4px solid #3b82f6;
    }

    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.75);
        backdrop-filter: blur(4px);
        z-index: 1000;
        display: none;
    }

    .modal-content-box {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: var(--bg-darker);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        width: 90%;
        max-width: 760px;
        max-height: 85vh;
        z-index: 1001;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
    }

    .modal-product-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 16px 20px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.15s;
    }

    .modal-product-card:hover {
        border-color: var(--primary);
        background: rgba(30, 41, 59, 0.8);
    }

    .modal-product-card.selected {
        border-color: #10b981;
        background: rgba(16, 185, 129, 0.08);
    }
</style>
@endsection

@section('content')
<div class="container" style="padding-top: 40px; padding-bottom: 60px;">
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span style="font-size: 1.8rem;">⚡</span>
                <h1 style="font-size: 2rem; font-weight: 800; color: #fff; margin: 0;">Interactive PC Builder</h1>
                <span class="badge badge-primary">v2.0 Configurator</span>
            </div>
            <p style="color: var(--text-muted); font-size: 0.95rem; margin: 0;">
                Select your core components below. The algorithm continuously verifies socket alignment, memory standard, and PSU wattage buffer in real-time.
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('calculator.index') }}" class="btn btn-secondary btn-sm">🎯 Bottleneck Calculator</a>
            <button type="button" id="clear-build-btn" class="btn btn-outline btn-sm">Reset All Slots</button>
        </div>
    </div>

    <!-- Main Configurator Grid -->
    <div class="builder-grid">
        <!-- Left: 8 Component Selection Slots -->
        <div class="slots-container">
            @foreach($slotCategories as $catKey => $catInfo)
                @php
                    $preselectedId = $preselectedIds[$catKey] ?? null;
                @endphp
                <div class="slot-card" data-category="{{ $catKey }}" data-preselected-id="{{ $preselectedId }}">
                    <!-- Slot Header -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 1.4rem;">{{ $catInfo['icon'] }}</span>
                            <div>
                                <h3 style="font-size: 1.05rem; font-weight: 700; color: #fff; margin: 0;">{{ $catInfo['name'] }}</h3>
                                <small style="color: var(--text-muted); font-size: 0.75rem;">{{ $catInfo['desc'] }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- State 1: Empty Slot -->
                    <div class="empty-slot-state" style="{{ $preselectedId ? 'display: none;' : 'display: block;' }}">
                        <div style="border: 1px dashed var(--border-color); border-radius: 8px; padding: 18px; text-align: center; background: rgba(0,0,0,0.15);">
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0 0 12px 0;">No component selected for this slot.</p>
                            <button type="button" class="btn btn-secondary btn-sm open-picker-btn">
                                + Choose {{ $catInfo['name'] }}
                            </button>
                        </div>
                    </div>

                    <!-- State 2: Selected Component Card -->
                    <div class="selected-slot-state" style="{{ $preselectedId ? 'display: block;' : 'display: none;' }}">
                        <div style="background: rgba(0,0,0,0.3); border: 1px solid var(--border-color); border-radius: 8px; padding: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                            <div style="flex: 1; min-width: 220px;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                    <span class="badge badge-info item-brand" style="font-size: 0.7rem;">Brand</span>
                                </div>
                                <h4 class="item-title" style="color: #fff; font-size: 1rem; font-weight: 700; margin: 0 0 4px 0;">Product Name</h4>
                                <p class="item-specs" style="color: var(--text-muted); font-size: 0.8rem; margin: 0; line-height: 1.4;"></p>
                            </div>
                            <div style="text-align: right; display: flex; align-items: center; gap: 16px;">
                                <div class="item-price" style="font-size: 1.2rem; font-weight: 800; color: var(--accent);">RM 0.00</div>
                                <div style="display: flex; gap: 6px;">
                                    <button type="button" class="btn btn-secondary btn-sm change-component-btn" style="padding: 6px 12px; font-size: 0.75rem;">
                                        Change
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm remove-component-btn" style="padding: 6px 10px; color: #ef4444;" title="Remove part">
                                        ✕
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Right: Sticky Build Summary Panel & Power Meter -->
        <div style="position: sticky; top: 20px;">
            <div class="card" style="padding: 24px; box-shadow: var(--shadow-lg);">
                <h3 style="font-size: 1.15rem; font-weight: 700; color: #fff; margin-bottom: 4px;">
                    🧾 Build Summary
                </h3>
                <div id="summary-item-count" style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 18px;">
                    0 / 8 Parts Selected
                </div>

                <!-- Subtotal Price -->
                <div style="background: rgba(0,0,0,0.3); border-radius: 8px; padding: 16px; margin-bottom: 20px; text-align: center; border: 1px solid var(--border-color);">
                    <small style="color: var(--text-muted); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px;">Estimated Build Subtotal</small>
                    <div id="summary-subtotal" style="font-size: 1.8rem; font-weight: 800; color: var(--accent); margin-top: 4px;">
                        RM 0.00
                    </div>
                </div>

                <!-- Dynamic Compatibility Status Box -->
                <div id="compatibility-status-box" style="margin-bottom: 20px;">
                    <div style="background: rgba(255,255,255,0.03); border: 1px dashed var(--border-color); border-radius: 8px; padding: 14px; text-align: center; font-size: 0.85rem; color: var(--text-muted);">
                        Select components on the left to activate real-time compatibility checks.
                    </div>
                </div>

                <!-- PSU Wattage Headroom Meter -->
                <div id="psu-meter-container" style="background: rgba(0,0,0,0.25); border: 1px solid var(--border-color); border-radius: 8px; padding: 16px; margin-bottom: 24px; display: none;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 0.85rem; font-weight: 600; color: #fff;">⚡ Power & Headroom</span>
                        <div id="psu-status-badge"></div>
                    </div>

                    <!-- Graphical Progress Bar -->
                    <div style="background: rgba(255,255,255,0.1); height: 8px; border-radius: 4px; overflow: hidden; margin-bottom: 12px;">
                        <div id="psu-headroom-bar" style="height: 100%; width: 0%; transition: width 0.3s, background-color 0.3s; background-color: #10b981;"></div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 0.75rem; color: var(--text-muted);">
                        <div>Estimated TDP: <strong id="meter-tdp" style="color: #fff;">0 W</strong></div>
                        <div>Recommended PSU: <strong id="meter-recommended-psu" style="color: #60a5fa;">0 W</strong></div>
                        <div>Selected PSU: <strong id="meter-selected-psu" style="color: #fff;">-</strong></div>
                        <div>Headroom: <strong id="psu-headroom-pct" style="color: #10b981;">-</strong></div>
                    </div>
                </div>

                <!-- Checkout Form -->
                <form id="checkout-submission-form" action="{{ route('builder.checkout') }}" method="POST">
                    @csrf
                    <div id="checkout-product-inputs"></div>
                    <button type="submit" id="proceed-checkout-btn" class="btn btn-primary" style="width: 100%; padding: 12px 20px; font-weight: 700; font-size: 1rem;" disabled>
                        Select Parts to Proceed
                    </button>
                </form>

                <div style="margin-top: 16px; text-align: center;">
                    <small style="color: var(--text-muted); font-size: 0.75rem;">
                        🛡️ 2-Year Direct Warranty • 100% Genuine Components
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Component Picker Modal -->
<div id="component-picker-modal" class="modal-backdrop">
    <div class="modal-content-box">
        <!-- Modal Header -->
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 id="modal-category-title" style="color: #fff; font-size: 1.25rem; font-weight: 700; margin: 0;">Choose Component</h3>
            <button type="button" class="close-modal-btn" style="background: none; border: none; color: var(--text-muted); font-size: 1.5rem; cursor: pointer;">✕</button>
        </div>

        <!-- Search Bar -->
        <div style="padding: 16px 24px; border-bottom: 1px solid rgba(255,255,255,0.06);">
            <input type="text" id="modal-search-input" class="form-control" placeholder="Search by model name, brand, or specification keyword...">
        </div>

        <!-- Product List -->
        <div id="modal-product-list" style="padding: 20px 24px; overflow-y: auto; flex: 1;">
            <!-- Rendered by builder.js -->
        </div>

        <!-- Modal Footer -->
        <div style="padding: 16px 24px; border-top: 1px solid var(--border-color); text-align: right;">
            <button type="button" class="btn btn-secondary close-modal-btn">Close</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Global catalog and routes data injected for builder.js
    window.catalogData = @json($catalog);
    window.appRoutes = {
        compatibilityEvaluate: "{{ route('api.compatibility.evaluate') }}",
        compatibleParts: "{{ route('api.compatibility.compatible-parts') }}"
    };
</script>
<script src="{{ asset('js/builder.js') }}"></script>
@endsection
