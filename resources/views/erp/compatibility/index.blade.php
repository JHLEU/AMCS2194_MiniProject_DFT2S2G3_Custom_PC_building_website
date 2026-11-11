@extends('layouts.erp')

@section('title', 'Hardware Compatibility & Performance Engine')
@section('page-title', 'Hardware Compatibility & Performance Rating Engine')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #fff; margin-bottom: 4px;">
            Compatibility & Performance Test Bench
        </h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            Validate CPU/Motherboard socket compatibility, DDR4/DDR5 RAM compatibility, and 4-parameter component performance ratings.
        </p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <span id="compatLoading" style="display: none; font-size: 0.85rem; color: var(--text-muted);">⏳ Re-evaluating…</span>
        <a href="{{ route('erp.compatibility.index') }}" class="btn btn-outline btn-sm">
            Reset Default Build
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
                    <div class="stat-val" style="color: var(--success); font-size: 1.35rem;">Compatible</div>
                @elseif($evaluation['status'] === 'warning')
                    <div class="stat-val" style="color: var(--warning); font-size: 1.35rem;"> Compatible (Warnings)</div>
                @else
                    <div class="stat-val" style="color: var(--danger); font-size: 1.35rem;">Conflict Detected</div>
                @endif
            </div>
        </div>
        <div class="stat-icon"></div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Overall Performance Rating</div>
            <div class="stat-val" id="liveOverallScore" style="color: var(--info);">
                {{ $performanceReport['overall_score'] ?? 0 }} / 100
            </div>
            <div id="liveVerdictInfo" style="font-size: 0.75rem; color: var(--text-muted);">
                {{ $performanceReport['verdict'] ?? 'Rated across 4 parameters' }}
            </div>
        </div>
        <div class="stat-icon"></div>
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
        <div class="stat-icon"></div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start;">
    <!-- Left Column: Component Selector Form -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">1. Select Hardware Configuration</h3>

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
                                @if($item->performance_score > 0) (Rating: {{ $item->performance_score }}/100) @endif
                                — RM {{ number_format($item->selling_price, 2) }}
                                (Stock: {{ $item->in_stock_count }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endforeach

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 8px;">
                Run Socket, RAM & Performance Check
            </button>
        </form>
    </div>

    <!-- Right Column: Diagnostic Results -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Compatibility Rules Report -->
        <div class="card">
            <h3 class="card-title" style="margin-bottom: 16px;">2. Socket & RAM Compatibility Report</h3>

            <div id="compatIssues">
                @if(count($evaluation['errors']) === 0 && count($evaluation['warnings']) === 0)
                    <div class="alert alert-success" style="margin-bottom: 16px;">
                        <strong>All Hardware Compatibility Checks Passed!</strong><br>
                        CPU socket type matches Motherboard socket type, and RAM generation (DDR4/DDR5) is compatible.
                    </div>
                @endif

                @if(count($evaluation['errors']) > 0)
                    <div style="margin-bottom: 16px;">
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--danger); margin-bottom: 8px; text-transform: uppercase;">
                            Critical Compatibility Conflicts ({{ count($evaluation['errors']) }})
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
                            Advisory Warnings ({{ count($evaluation['warnings']) }})
                        </div>
                        @foreach($evaluation['warnings'] as $warn)
                            <div class="alert" style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.35); color: #fcd34d; margin-bottom: 8px; padding: 12px; border-radius: 8px;">
                                <strong>[{{ $warn['code'] }}]</strong> {{ $warn['message'] }}
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Selected Components Breakdown Table -->
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Component</th>
                            <th>Socket / RAM</th>
                            <th>Performance Score</th>
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
                                    @if(!empty($part['performance_score']) && $part['performance_score'] > 0)
                                        <strong style="color: var(--success);">{{ $part['performance_score'] }} / 100</strong>
                                    @else
                                        <span style="color: var(--text-muted);">—</span>
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

        <!-- 4-Parameter Performance Rating Calculator Card -->
        <div class="card" id="bottleneckCard">
            <h3 class="card-title" style="margin-bottom: 12px;">
                3. 4-Parameter Performance Rating Calculator
            </h3>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 14px;">
                <div style="background: rgba(255,255,255,0.03); padding: 14px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <div style="font-size: 0.78rem; color: var(--text-muted);">CPU Processing Power</div>
                    <div id="bnCpuScore" style="font-size: 1.4rem; font-weight: 800; color: #60a5fa; margin: 4px 0;">
                        {{ $performanceReport['cpu_processing_power'] ?? 0 }} / 100
                    </div>
                    <div id="bnCpuName" style="font-size: 0.78rem; color: var(--text-light);">{{ $performanceReport['cpu_name'] ?? '—' }}</div>
                </div>
                <div style="background: rgba(255,255,255,0.03); padding: 14px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <div style="font-size: 0.78rem; color: var(--text-muted);">GPU Performance</div>
                    <div id="bnGpuScore" style="font-size: 1.4rem; font-weight: 800; color: #a78bfa; margin: 4px 0;">
                        {{ $performanceReport['gpu_performance'] ?? 0 }} / 100
                    </div>
                    <div id="bnGpuName" style="font-size: 0.78rem; color: var(--text-light);">{{ $performanceReport['gpu_name'] ?? '—' }}</div>
                </div>
                <div style="background: rgba(255,255,255,0.03); padding: 14px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <div style="font-size: 0.78rem; color: var(--text-muted);">Memory</div>
                    <div id="bnRamScore" style="font-size: 1.4rem; font-weight: 800; color: #10b981; margin: 4px 0;">
                        {{ $performanceReport['memory'] ?? 0 }} / 100
                    </div>
                    <div id="bnRamName" style="font-size: 0.78rem; color: var(--text-light);">{{ $performanceReport['ram_name'] ?? '—' }}</div>
                </div>
                <div style="background: rgba(255,255,255,0.03); padding: 14px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <div style="font-size: 0.78rem; color: var(--text-muted);">Storage</div>
                    <div id="bnStorageScore" style="font-size: 1.4rem; font-weight: 800; color: #f59e0b; margin: 4px 0;">
                        {{ $performanceReport['storage'] ?? 0 }} / 100
                    </div>
                    <div id="bnStorageName" style="font-size: 0.78rem; color: var(--text-light);">{{ $performanceReport['storage_name'] ?? '—' }}</div>
                </div>
            </div>

            <p id="bnAdvice" style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">
                {{ $performanceReport['advice'] ?? '' }}
            </p>
        </div>
    </div>
</div>
@endsection
