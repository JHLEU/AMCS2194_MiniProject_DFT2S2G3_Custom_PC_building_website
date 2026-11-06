@extends('layouts.erp')

@section('title', 'Hardware Compatibility & Power Engine')
@section('page-title', 'Hardware Compatibility & Power Estimation Engine')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #fff; margin-bottom: 4px;">
            ⚡ Compatibility & Power Test Bench
        </h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            Validate CPU/Motherboard sockets, DDR4/DDR5 RAM standards, total TDP wattage, PSU transient headroom, and CPU/GPU synergy.
        </p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <span id="compatLoading" style="display: none; font-size: 0.85rem; color: var(--text-muted);">⏳ Re-evaluating…</span>
        <a href="{{ route('erp.compatibility.index') }}" class="btn btn-outline btn-sm">
            🔄 Reset Default Build
        </a>
    </div>
</div>

<!-- Top KPI Cards -->
<div class="stat-grid" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div>
            <div class="stat-label">Compatibility Status</div>
            <div id="compatStatusVal">
                @if($evaluation['status'] === 'compatible')
                    <div class="stat-val" style="color: var(--success); font-size: 1.35rem;">✔ Compatible</div>
                @elseif($evaluation['status'] === 'warning')
                    <div class="stat-val" style="color: var(--warning); font-size: 1.35rem;">⚠ Compatible (Warnings)</div>
                @else
                    <div class="stat-val" style="color: var(--danger); font-size: 1.35rem;">✖ Conflict Detected</div>
                @endif
            </div>
        </div>
        <div class="stat-icon">🔌</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Estimated Peak TDP</div>
            <div class="stat-val" id="liveTotalWattage" style="color: var(--warning);">
                {{ $evaluation['power']['estimated_total_wattage'] }}W
            </div>
            <div id="liveWattageBreakdown" style="font-size: 0.75rem; color: var(--text-muted);">
                Components: {{ $evaluation['power']['component_tdp_wattage'] }}W + {{ $evaluation['power']['base_overhead_wattage'] }}W Base
            </div>
        </div>
        <div class="stat-icon">⚡</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Recommended PSU (1.25x)</div>
            <div class="stat-val" id="liveRecPsu" style="color: var(--info);">
                {{ $evaluation['power']['recommended_psu_wattage'] }}W+
            </div>
            <div id="livePsuInfo" style="font-size: 0.75rem; color: var(--text-muted);">
                Selected PSU: {{ $evaluation['power']['selected_psu_wattage'] ? $evaluation['power']['selected_psu_wattage'] . 'W' : 'None' }}
                @if($evaluation['power']['psu_load_percent'] !== null)
                    ({{ $evaluation['power']['psu_load_percent'] }}% load)
                @endif
            </div>
        </div>
        <div class="stat-icon">🔋</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Estimated Retail Total</div>
            <div class="stat-val" id="liveTotalPrice" style="color: var(--primary);">
                RM {{ number_format($evaluation['pricing']['estimated_total_price'], 2) }}
            </div>
            <div id="livePriceInfo" style="font-size: 0.75rem; color: var(--text-muted);">
                {{ $evaluation['pricing']['component_count'] }} components &bull;
                {{ $evaluation['pricing']['all_in_stock'] ? 'All In-Stock' : 'Stock Deficit' }}
            </div>
        </div>
        <div class="stat-icon">💰</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start;">
    <!-- Left Column: Component Selector Form -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">🛠️ 1. Select Hardware Configuration</h3>

        <form method="GET" action="{{ route('erp.compatibility.index') }}" id="compatibilityForm"
              data-evaluate-url="{{ route('api.compatibility.evaluate') }}"
              data-bottleneck-url="{{ route('api.compatibility.bottleneck') }}">
            @foreach($categories as $category)
                @php
                    $items = $groupedProducts[$category] ?? collect();
                    $fieldKey = strtolower($category) . '_id';
                    $currentSelectedId = $selectedIds[$category] ?? null;
                @endphp
                <div class="form-group" style="margin-bottom: 14px;">
                    <label for="select_{{ $fieldKey }}" style="display: flex; justify-content: space-between;">
                        <span>{{ $category }}</span>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $items->count() }} SKU(s)</span>
                    </label>
                    <select name="{{ $fieldKey }}" id="select_{{ $fieldKey }}" class="form-control component-selector" data-category="{{ $category }}">
                        <option value="">— None Selected —</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" {{ (int) $currentSelectedId === (int) $item->id ? 'selected' : '' }}>
                                {{ $item->name }}
                                @if($item->socket_type) [Socket: {{ $item->socket_type }}] @endif
                                @if($item->ram_type) [{{ $item->ram_type }}] @endif
                                @if($item->category === 'PSU')
                                    ({{ $item->tdp_wattage }}W Rated)
                                @elseif($item->tdp_wattage > 0)
                                    ({{ $item->tdp_wattage }}W TDP)
                                @endif
                                — RM {{ number_format($item->selling_price, 2) }}
                                (Stock: {{ $item->in_stock_count }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endforeach

            <div class="form-group" style="margin-bottom: 18px;">
                <label for="select_resolution">Target Display Resolution (For Bottleneck Check)</label>
                <select name="resolution" id="select_resolution" class="form-control">
                    <option value="1080" {{ $resolution === '1080' ? 'selected' : '' }}>1080p Full HD (CPU Intensive)</option>
                    <option value="1440" {{ $resolution === '1440' ? 'selected' : '' }}>1440p 2K QHD (Balanced)</option>
                    <option value="2160" {{ $resolution === '2160' ? 'selected' : '' }}>2160p 4K UHD (GPU Intensive)</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                🔍 Run Compatibility & Power Diagnostics
            </button>
        </form>
    </div>

    <!-- Right Column: Diagnostic Results -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Compatibility Rules Report -->
        <div class="card">
            <h3 class="card-title" style="margin-bottom: 16px;">📋 2. Compatibility & Power Headroom Report</h3>

            <div id="compatIssues">
                @if(count($evaluation['errors']) === 0 && count($evaluation['warnings']) === 0)
                    <div class="alert alert-success" style="margin-bottom: 16px;">
                        <strong>✔ All Hardware Compatibility Checks Passed!</strong><br>
                        CPU socket, Motherboard chipset, RAM generation, and PSU wattage headroom are 100% compatible.
                    </div>
                @endif

                @if(count($evaluation['errors']) > 0)
                    <div style="margin-bottom: 16px;">
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--danger); margin-bottom: 8px; text-transform: uppercase;">
                            ❌ Critical Compatibility Conflicts ({{ count($evaluation['errors']) }})
                        </div>
                        @foreach($evaluation['errors'] as $err)
                            <div class="alert alert-danger" style="margin-bottom: 8px;">
                                <strong>[{{ $err['code'] }}]</strong> {{ $err['message'] }}
                            </div>
                        @endforeach
                    </div>
                @endif

                @if(count($evaluation['warnings']) > 0)
                    <div style="margin-bottom: 16px;">
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--warning); margin-bottom: 8px; text-transform: uppercase;">
                            ⚠️ Advisory Warnings ({{ count($evaluation['warnings']) }})
                        </div>
                        @foreach($evaluation['warnings'] as $warn)
                            <div class="alert" style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.35); color: #fcd34d; margin-bottom: 8px; padding: 12px; border-radius: 8px;">
                                <strong>[{{ $warn['code'] }}]</strong> {{ $warn['message'] }}
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Cooling Recommendation -->
            <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                <div style="font-size: 0.8rem; font-weight: 700; color: var(--info); text-transform: uppercase; margin-bottom: 4px;">
                    ❄️ Thermal & Cooling Recommendation
                </div>
                <div id="coolingAdvice" style="font-size: 0.9rem; color: var(--text-main);">
                    {{ $evaluation['cooling_advice'] }}
                </div>
            </div>

            <!-- Selected Components Breakdown Table -->
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Component</th>
                            <th>Socket / RAM</th>
                            <th>TDP / Rating</th>
                            <th>Stock</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody id="compatComponentsBody">
                        @foreach($evaluation['components'] as $part)
                            <tr>
                                <td><span class="badge badge-info">{{ $part['category'] }}</span></td>
                                <td style="font-weight: 600;">{{ $part['name'] }}</td>
                                <td>
                                    @if($part['socket_type'])
                                        <span class="badge badge-secondary">{{ $part['socket_type'] }}</span>
                                    @endif
                                    @if($part['ram_type'])
                                        <span class="badge badge-primary">{{ $part['ram_type'] }}</span>
                                    @endif
                                    @if(!$part['socket_type'] && !$part['ram_type'])
                                        <span style="color: var(--text-muted);">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if(strcasecmp($part['category'], 'PSU') === 0)
                                        <strong style="color: var(--info);">{{ $part['tdp_wattage'] }}W Rated</strong>
                                    @else
                                        {{ $part['tdp_wattage'] }}W
                                    @endif
                                </td>
                                <td>
                                    @if($part['in_stock_count'] > 0)
                                        <span class="badge badge-success">{{ $part['in_stock_count'] }} In-Stock</span>
                                    @else
                                        <span class="badge badge-danger">Out of Stock</span>
                                    @endif
                                </td>
                                <td>RM {{ number_format($part['selling_price'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- CPU + GPU Bottleneck & Synergy Card -->
        <div class="card" id="bottleneckCard" style="{{ $bottleneckReport ? '' : 'display: none;' }}">
            <h3 class="card-title" style="margin-bottom: 12px;">
                🎮 3. CPU & GPU Synergy (<span id="bnResolution">{{ $bottleneckReport['resolution'] ?? $resolution }}</span>p Benchmark)
            </h3>
            @php
                $bnTier = $bottleneckReport['tier'] ?? 'optimal';
                $bnColor = $bnTier === 'optimal' ? 'var(--success)' : ($bnTier === 'minor' ? 'var(--warning)' : 'var(--danger)');
            @endphp
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 14px;">
                <div style="background: rgba(255,255,255,0.03); padding: 12px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Bottleneck Index</div>
                    <div id="bnPercent" style="font-size: 1.35rem; font-weight: 800; color: {{ $bnColor }};">
                        {{ $bottleneckReport['bottleneck_percent'] ?? 0 }}%
                    </div>
                    <div id="bnTag" style="font-size: 0.75rem;">{{ $bottleneckReport['tag'] ?? '' }}</div>
                </div>
                <div style="background: rgba(255,255,255,0.03); padding: 12px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Synergy Utilization</div>
                    <div id="bnUtil" style="font-size: 1.35rem; font-weight: 800; color: var(--info);">
                        {{ $bottleneckReport['utilization_percent'] ?? 0 }}%
                    </div>
                    <div id="bnScores" style="font-size: 0.75rem;">CPU {{ $bottleneckReport['cpu_score'] ?? 0 }} / GPU {{ $bottleneckReport['gpu_score'] ?? 0 }}</div>
                </div>
                <div style="background: rgba(255,255,255,0.03); padding: 12px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Suggested PSU</div>
                    <div id="bnPsu" style="font-size: 1.35rem; font-weight: 800; color: var(--warning);">
                        {{ $bottleneckReport['recommended_psu_wattage'] ?? 0 }}W
                    </div>
                    <div style="font-size: 0.75rem;">80+ Gold Modular</div>
                </div>
            </div>

            <p id="bnAdvice" style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 14px;">
                {{ $bottleneckReport['advice'] ?? '' }}
            </p>

            <div id="bnGames" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 10px;">
                @foreach($bottleneckReport['games_fps'] ?? [] as $game)
                    <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); border-radius: 6px; padding: 10px 12px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-size: 0.8rem; font-weight: 600; color: #fff;">{{ $game['name'] }}</div>
                            <div style="font-size: 0.7rem; color: var(--text-muted);">{{ $game['tag'] }}</div>
                        </div>
                        <div style="font-size: 1rem; font-weight: 800; color: var(--success);">
                            {{ $game['fps'] }} <span style="font-size: 0.7rem; color: var(--text-muted);">FPS</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
