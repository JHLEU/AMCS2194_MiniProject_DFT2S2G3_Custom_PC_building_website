@extends('layouts.app')

@section('title', 'Custom Rig Configurator — GOPC')

@section('content')
<div class="container" style="padding-top: 36px; padding-bottom: 64px;">
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <h1 style="font-size: 1.75rem; font-weight: 600; color: var(--text-primary); margin: 0; letter-spacing: -0.02em;">
                    Custom Rig Configurator
                </h1>
                <span class="badge badge-primary font-mono">v2.4 Engine</span>
            </div>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">
                Select hardware components. Live engine verifies CPU socket compatibility, memory channel generation, and PSU power headroom.
            </p>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('calculator.index') }}" class="btn btn-secondary btn-sm">
                Wattage calculator
            </a>
            <button type="button" id="clear-build-btn" class="btn btn-outline btn-sm" style="color: var(--danger);" title="Reset current selection">
                Reset build
            </button>
        </div>
    </div>

    <!-- Main Configurator Grid -->
    <div class="builder-grid">
        <!-- Left: Component Selection Slots -->
        <div class="slots-container">
            @foreach($slotCategories as $catKey => $catInfo)
                @php
                    $preselectedId = $preselectedIds[$catKey] ?? null;
                @endphp
                <div class="slot-card" data-category="{{ $catKey }}" data-preselected-id="{{ $preselectedId }}">
                    <!-- Slot Header -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="font-mono" style="font-size: 0.75rem; color: var(--primary);">0{{ $loop->iteration }}</span>
                            <h3 style="font-size: 0.9375rem; font-weight: 600; color: var(--text-primary); margin: 0;">
                                {{ $catInfo['name'] }}
                            </h3>
                            <span style="color: var(--text-muted); font-size: 0.75rem;">• {{ $catInfo['desc'] }}</span>
                        </div>
                    </div>

                    <!-- State 1: Empty Slot -->
                    <div class="empty-slot-state" style="@if($preselectedId) display: none; @else display: block; @endif">
                        <div style="border: 1px dashed var(--border-color); border-radius: var(--radius-sm); padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; background: var(--bg-deep); flex-wrap: wrap; gap: 12px;">
                            <span style="color: var(--text-muted); font-size: 0.8125rem;">Slot unpopulated</span>
                            <button type="button" class="btn btn-secondary btn-sm open-picker-btn">
                                + Choose {{ $catInfo['name'] }}
                            </button>
                        </div>
                    </div>

                    <!-- State 2: Selected Component Card -->
                    <div class="selected-slot-state" style="@if($preselectedId) display: block; @else display: none; @endif">
                        <div style="background: var(--bg-deep); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 14px 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                            <div style="flex: 1; min-width: 220px;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 3px;">
                                    <span class="badge badge-neutral item-brand font-mono" style="font-size: 0.6875rem;">Brand</span>
                                </div>
                                <h4 class="item-title" style="color: var(--text-primary); font-size: 0.9375rem; font-weight: 600; margin: 0 0 2px 0;">
                                    Product Name
                                </h4>
                                <p class="item-specs font-mono" style="color: var(--text-muted); font-size: 0.75rem; margin: 0;"></p>
                            </div>
                            <div style="text-align: right; display: flex; align-items: center; gap: 14px;">
                                <div class="item-price font-mono" style="font-size: 1.0625rem; font-weight: 600; color: var(--text-primary);">
                                    RM 0.00
                                </div>
                                <div style="display: flex; gap: 6px;">
                                    <button type="button" class="btn btn-secondary btn-sm change-component-btn">
                                        Change
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm remove-component-btn" style="color: var(--danger); padding: 6px 10px;" title="Remove part">
                                        &times;
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Right: Build Summary & Real-time Power Metrics -->
        <div style="position: sticky; top: 80px;">
            <div class="card" style="padding: 20px; margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <h3 style="font-size: 1rem; font-weight: 600; color: var(--text-primary); margin: 0;">
                        Build specifications
                    </h3>
                    <span id="summary-item-count" class="font-mono" style="color: var(--text-muted); font-size: 0.75rem;">
                        0 / 8 Parts
                    </span>
                </div>

                <!-- Subtotal Price -->
                <div style="background: var(--bg-deep); border-radius: var(--radius-sm); padding: 14px 16px; margin: 16px 0; border: 1px solid var(--border-color);">
                    <div style="color: var(--text-muted); font-size: 0.6875rem; text-transform: none;">Estimated total (inclusive of assembly &amp; stress test)</div>
                    <div id="summary-subtotal" class="font-mono" style="font-size: 1.625rem; font-weight: 600; color: var(--text-primary); margin-top: 2px;">
                        RM 0.00
                    </div>
                </div>

                <!-- Dynamic Compatibility Status Box -->
                <div id="compatibility-status-box" style="margin-bottom: 16px;">
                    <div style="background: var(--bg-deep); border: 1px dashed var(--border-color); border-radius: var(--radius-sm); padding: 12px; text-align: center; font-size: 0.75rem; color: var(--text-muted);">
                        Select CPU, motherboard, and memory to initialize compatibility diagnostics.
                    </div>
                </div>

                <!-- 4-Parameter Rating Panel -->
                <div id="builder-performance-panel" style="background: var(--bg-deep); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 14px; margin-bottom: 18px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="font-size: 0.8125rem; font-weight: 600; color: var(--text-primary);">Hardware load benchmark</span>
                        <span id="summary-overall-badge" class="font-mono" style="font-size: 0.75rem; color: var(--text-muted);">0 / 100</span>
                    </div>

                    <!-- 1. CPU Processing Power -->
                    <div style="margin-bottom: 10px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; margin-bottom: 4px;">
                            <span style="color: var(--text-secondary);">CPU compute</span>
                            <span class="font-mono" id="summary-score-cpu" style="color: var(--text-muted);">0/100</span>
                        </div>
                        <div style="background: var(--bg-raised); height: 6px; border-radius: 3px; overflow: hidden;">
                            <div id="summary-bar-cpu" style="height: 100%; width: 0%; background-color: var(--primary); transition: width 0.3s ease;"></div>
                        </div>
                    </div>

                    <!-- 2. GPU Performance -->
                    <div style="margin-bottom: 10px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; margin-bottom: 4px;">
                            <span style="color: var(--text-secondary);">Graphics raster</span>
                            <span class="font-mono" id="summary-score-gpu" style="color: var(--text-muted);">0/100</span>
                        </div>
                        <div style="background: var(--bg-raised); height: 6px; border-radius: 3px; overflow: hidden;">
                            <div id="summary-bar-gpu" style="height: 100%; width: 0%; background-color: var(--primary); transition: width 0.3s ease;"></div>
                        </div>
                    </div>

                    <!-- 3. Memory -->
                    <div style="margin-bottom: 10px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; margin-bottom: 4px;">
                            <span style="color: var(--text-secondary);">Memory bandwidth</span>
                            <span class="font-mono" id="summary-score-ram" style="color: var(--text-muted);">0/100</span>
                        </div>
                        <div style="background: var(--bg-raised); height: 6px; border-radius: 3px; overflow: hidden;">
                            <div id="summary-bar-ram" style="height: 100%; width: 0%; background-color: var(--primary); transition: width 0.3s ease;"></div>
                        </div>
                    </div>

                    <!-- 4. Storage -->
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; margin-bottom: 4px;">
                            <span style="color: var(--text-secondary);">Storage IOPS</span>
                            <span class="font-mono" id="summary-score-storage" style="color: var(--text-muted);">0/100</span>
                        </div>
                        <div style="background: var(--bg-raised); height: 6px; border-radius: 3px; overflow: hidden;">
                            <div id="summary-bar-storage" style="height: 100%; width: 0%; background-color: var(--primary); transition: width 0.3s ease;"></div>
                        </div>
                    </div>
                </div>

                <!-- Checkout Form -->
                <form id="checkout-submission-form" action="{{ route('builder.checkout') }}" method="POST">
                    @csrf
                    <div id="checkout-product-inputs"></div>
                    <button type="submit" id="proceed-checkout-btn" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 0.875rem;" disabled>
                        Populate components to order
                    </button>
                </form>

                <div style="margin-top: 14px; text-align: center;">
                    <span class="font-mono" style="color: var(--text-muted); font-size: 0.6875rem;">
                        2-Year Warranty • 100% Genuine Components
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Component Picker Modal -->
<div id="component-picker-modal" class="modal-backdrop">
    <div class="modal-content-box">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 id="modal-category-title" style="color: var(--text-primary); font-size: 1rem; font-weight: 600; margin: 0;">Select Component</h3>
            <button type="button" class="close-modal-btn" style="background: none; border: none; color: var(--text-muted); font-size: 1.25rem; cursor: pointer;">&times;</button>
        </div>

        <div style="padding: 12px 20px; border-bottom: 1px solid var(--border-color);">
            <input type="text" id="modal-search-input" class="form-control" placeholder="Filter by brand, model, or specification...">
        </div>

        <div id="modal-product-list" style="padding: 16px 20px; overflow-y: auto; flex: 1;">
            <!-- Rendered dynamically by builder.js -->
        </div>

        <div style="padding: 12px 20px; border-top: 1px solid var(--border-color); text-align: right;">
            <button type="button" class="btn btn-secondary btn-sm close-modal-btn">Dismiss</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    window.catalogData = @json($catalog);
    window.appRoutes = {
        compatibilityEvaluate: "{{ route('api.compatibility.evaluate') }}",
        compatibleParts: "{{ route('api.compatibility.compatible-parts') }}"
    };
</script>
<script src="{{ asset('js/builder.js') }}"></script>
@endsection
