@extends('layouts.app')

@section('title', 'Interactive Custom PC Builder')

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
                Select your components below. The compatibility engine verifies CPU &amp; Motherboard socket alignment and RAM (DDR4/DDR5) compatibility in real-time.
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('calculator.index') }}" class="btn btn-secondary btn-sm">🎯 Performance Calculator</a>
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
                    <div class="empty-slot-state" style="@if($preselectedId) display: none; @else display: block; @endif">
                        <div style="border: 1px dashed var(--border-color); border-radius: 8px; padding: 18px; text-align: center; background: rgba(0,0,0,0.15);">
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0 0 12px 0;">No component selected for this slot.</p>
                            <button type="button" class="btn btn-secondary btn-sm open-picker-btn">
                                + Choose {{ $catInfo['name'] }}
                            </button>
                        </div>
                    </div>

                    <!-- State 2: Selected Component Card -->
                    <div class="selected-slot-state" style="@if($preselectedId) display: block; @else display: none; @endif">
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
                        Select CPU, Motherboard, and RAM to check socket &amp; memory compatibility.
                    </div>
                </div>

                <!-- 4-Parameter Performance Rating Panel (Horizontal Bars + 4 Tiers) -->
                <div id="builder-performance-panel" style="background: rgba(0,0,0,0.28); border: 1px solid var(--border-color); border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="font-size: 0.9rem; font-weight: 700; color: #fff;">📊 Performance Rating</span>
                        <span id="summary-overall-badge" style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted);">0 / 100</span>
                    </div>

                    <!-- 4-Tier Legend: Poor (Red), Average (Yellow), Good (Green), Excellent (Cyan) -->
                    <div style="display: flex; justify-content: space-between; gap: 6px; flex-wrap: wrap; padding: 6px 10px; background: rgba(255,255,255,0.03); border-radius: 6px; margin-bottom: 14px; font-size: 0.7rem;">
                        <span style="color: #ef4444; font-weight: 600;">● Poor</span>
                        <span style="color: #f59e0b; font-weight: 600;">● Average</span>
                        <span style="color: #10b981; font-weight: 600;">● Good</span>
                        <span style="color: #06b6d4; font-weight: 600;">● Excellent</span>
                    </div>

                    <!-- 1. CPU Processing Power -->
                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; margin-bottom: 5px;">
                            <span style="color: #fff; font-weight: 600;">🔲 CPU Processing Power</span>
                            <span>
                                <strong id="summary-score-cpu" style="color: var(--text-muted);">0/100</strong>
                                <span id="summary-tier-cpu" style="font-size: 0.7rem; margin-left: 4px; padding: 2px 6px; border-radius: 4px; background: rgba(255,255,255,0.06); color: var(--text-muted);">None</span>
                            </span>
                        </div>
                        <div style="position: relative; background: rgba(255,255,255,0.08); height: 10px; border-radius: 5px; overflow: hidden;">
                            <div id="summary-bar-cpu" style="height: 100%; width: 0%; background-color: #64748b; transition: width 0.3s ease, background-color 0.3s ease;"></div>
                            <!-- 4-tier visual segment dividers -->
                            <div style="position: absolute; top: 0; left: 49%; width: 1px; height: 100%; background: rgba(15,23,42,0.7);"></div>
                            <div style="position: absolute; top: 0; left: 69%; width: 1px; height: 100%; background: rgba(15,23,42,0.7);"></div>
                            <div style="position: absolute; top: 0; left: 84%; width: 1px; height: 100%; background: rgba(15,23,42,0.7);"></div>
                        </div>
                    </div>

                    <!-- 2. GPU Performance -->
                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; margin-bottom: 5px;">
                            <span style="color: #fff; font-weight: 600;">🎮 GPU Performance</span>
                            <span>
                                <strong id="summary-score-gpu" style="color: var(--text-muted);">0/100</strong>
                                <span id="summary-tier-gpu" style="font-size: 0.7rem; margin-left: 4px; padding: 2px 6px; border-radius: 4px; background: rgba(255,255,255,0.06); color: var(--text-muted);">None</span>
                            </span>
                        </div>
                        <div style="position: relative; background: rgba(255,255,255,0.08); height: 10px; border-radius: 5px; overflow: hidden;">
                            <div id="summary-bar-gpu" style="height: 100%; width: 0%; background-color: #64748b; transition: width 0.3s ease, background-color 0.3s ease;"></div>
                            <div style="position: absolute; top: 0; left: 49%; width: 1px; height: 100%; background: rgba(15,23,42,0.7);"></div>
                            <div style="position: absolute; top: 0; left: 69%; width: 1px; height: 100%; background: rgba(15,23,42,0.7);"></div>
                            <div style="position: absolute; top: 0; left: 84%; width: 1px; height: 100%; background: rgba(15,23,42,0.7);"></div>
                        </div>
                    </div>

                    <!-- 3. Memory -->
                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; margin-bottom: 5px;">
                            <span style="color: #fff; font-weight: 600;">⚡ Memory</span>
                            <span>
                                <strong id="summary-score-ram" style="color: var(--text-muted);">0/100</strong>
                                <span id="summary-tier-ram" style="font-size: 0.7rem; margin-left: 4px; padding: 2px 6px; border-radius: 4px; background: rgba(255,255,255,0.06); color: var(--text-muted);">None</span>
                            </span>
                        </div>
                        <div style="position: relative; background: rgba(255,255,255,0.08); height: 10px; border-radius: 5px; overflow: hidden;">
                            <div id="summary-bar-ram" style="height: 100%; width: 0%; background-color: #64748b; transition: width 0.3s ease, background-color 0.3s ease;"></div>
                            <div style="position: absolute; top: 0; left: 49%; width: 1px; height: 100%; background: rgba(15,23,42,0.7);"></div>
                            <div style="position: absolute; top: 0; left: 69%; width: 1px; height: 100%; background: rgba(15,23,42,0.7);"></div>
                            <div style="position: absolute; top: 0; left: 84%; width: 1px; height: 100%; background: rgba(15,23,42,0.7);"></div>
                        </div>
                    </div>

                    <!-- 4. Storage -->
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; margin-bottom: 5px;">
                            <span style="color: #fff; font-weight: 600;">💾 Storage</span>
                            <span>
                                <strong id="summary-score-storage" style="color: var(--text-muted);">0/100</strong>
                                <span id="summary-tier-storage" style="font-size: 0.7rem; margin-left: 4px; padding: 2px 6px; border-radius: 4px; background: rgba(255,255,255,0.06); color: var(--text-muted);">None</span>
                            </span>
                        </div>
                        <div style="position: relative; background: rgba(255,255,255,0.08); height: 10px; border-radius: 5px; overflow: hidden;">
                            <div id="summary-bar-storage" style="height: 100%; width: 0%; background-color: #64748b; transition: width 0.3s ease, background-color 0.3s ease;"></div>
                            <div style="position: absolute; top: 0; left: 49%; width: 1px; height: 100%; background: rgba(15,23,42,0.7);"></div>
                            <div style="position: absolute; top: 0; left: 69%; width: 1px; height: 100%; background: rgba(15,23,42,0.7);"></div>
                            <div style="position: absolute; top: 0; left: 84%; width: 1px; height: 100%; background: rgba(15,23,42,0.7);"></div>
                        </div>
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
