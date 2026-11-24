@extends('layouts.app')

@section('title', 'PC Performance Calculator')

@section('content')
<div class="container" style="padding-top: 40px; padding-bottom: 60px; max-width: 960px;">
    <!-- Title & Header -->
    <div style="text-align: center; margin-bottom: 36px;">
        <span class="badge badge-primary" style="margin-bottom: 8px;">Hardware Performance Rating Calculator</span>
        <h1 style="font-size: 2.2rem; font-weight: 800; color: #fff; margin-bottom: 10px;">
            PC Performance Calculator
        </h1>
        <p style="color: var(--text-muted); font-size: 1.05rem; max-width: 640px; margin: 0 auto;">
            Evaluate your build across 4 core performance parameters: CPU Processing Power, GPU Performance, Memory, and Storage.
        </p>
    </div>

    <!-- Component Selection Card -->
    <div class="card" style="padding: 28px; margin-bottom: 32px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap: 20px; margin-bottom: 24px;">
            <!-- 1. CPU Dropdown -->
            <div class="form-group">
                <label for="calc-cpu" class="form-label" style="font-weight: 700; color: #fff;">
                    1. CPU Processing Power (Processor)
                </label>
                <select id="calc-cpu" class="form-control" style="font-size: 0.95rem;">
                    @foreach($cpus as $cpu)
                        <option value="{{ $cpu->id }}" data-score="{{ $cpu->performance_score }}" {{ $loop->first ? 'selected' : '' }}>
                            {{ $cpu->name }} (Rating: {{ $cpu->performance_score }}/100) — RM {{ number_format($cpu->selling_price, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- 2. GPU Dropdown -->
            <div class="form-group">
                <label for="calc-gpu" class="form-label" style="font-weight: 700; color: #fff;">
                    2. GPU Performance (Graphics Card)
                </label>
                <select id="calc-gpu" class="form-control" style="font-size: 0.95rem;">
                    @foreach($gpus as $gpu)
                        <option value="{{ $gpu->id }}" data-score="{{ $gpu->performance_score }}" {{ $loop->first ? 'selected' : '' }}>
                            {{ $gpu->name }} (Rating: {{ $gpu->performance_score }}/100) — RM {{ number_format($gpu->selling_price, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- 3. Memory (RAM) Dropdown -->
            <div class="form-group">
                <label for="calc-ram" class="form-label" style="font-weight: 700; color: #fff;">
                    3. Memory (RAM)
                </label>
                <select id="calc-ram" class="form-control" style="font-size: 0.95rem;">
                    @foreach($rams as $ram)
                        <option value="{{ $ram->id }}" data-score="{{ $ram->performance_score }}" {{ $loop->first ? 'selected' : '' }}>
                            {{ $ram->name }} (Rating: {{ $ram->performance_score }}/100) — RM {{ number_format($ram->selling_price, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- 4. Storage Dropdown -->
            <div class="form-group">
                <label for="calc-storage" class="form-label" style="font-weight: 700; color: #fff;">
                    4. Storage (SSD)
                </label>
                <select id="calc-storage" class="form-control" style="font-size: 0.95rem;">
                    @foreach($storages as $storage)
                        <option value="{{ $storage->id }}" data-score="{{ $storage->performance_score }}" {{ $loop->first ? 'selected' : '' }}>
                            {{ $storage->name }} (Rating: {{ $storage->performance_score }}/100) — RM {{ number_format($storage->selling_price, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-top: 8px;">
            <button type="button" id="evaluate-calc-btn" class="btn btn-primary" style="padding: 12px 32px; font-weight: 600; font-size: 0.95rem; box-shadow: 0 4px 14px rgba(34,197,94,0.25);">
                Calculate Benchmark Scores &rarr;
            </button>
        </div>
    </div>

    <!-- Results Display: 4 Parameters -->
    <div id="calc-results-card" class="card" style="padding: 32px; display: none;">
        <!-- Overall Score Header -->
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-bottom: 4px;">
                Overall Performance Score
            </div>
            <div id="overall-score-display" style="font-size: 3.2rem; font-weight: 800; color: #10b981; line-height: 1;">
                0 / 100
            </div>
            <div id="verdict-badge-container" style="margin-top: 10px;">
                <span id="verdict-badge" class="badge badge-success" style="font-size: 0.9rem; padding: 6px 14px;">High Performance Balance</span>
            </div>
            <p id="advice-text" style="color: var(--text-light); max-width: 600px; margin: 12px auto 0; font-size: 0.95rem; line-height: 1.5;"></p>
        </div>

        <!-- 4 Core Performance Parameters -->
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #fff; margin: 0;">
                4 Core Component Performance Parameters
            </h3>
            <div style="display: flex; gap: 12px; font-size: 0.78rem;">
                <span style="color: #ef4444; font-weight: 700;">● Poor (Red)</span>
                <span style="color: #f59e0b; font-weight: 700;">● Average (Yellow)</span>
                <span style="color: #10b981; font-weight: 700;">● Good (Green)</span>
                <span style="color: #06b6d4; font-weight: 700;">● Excellent (Cyan)</span>
            </div>
        </div>

        <div id="parameters-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 18px; margin-bottom: 28px;">
            <!-- Parameter 1: CPU Processing Power -->
            <div style="background: rgba(0,0,0,0.28); border: 1px solid var(--border-color); border-radius: 10px; padding: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <span style="font-weight: 700; color: #fff; font-size: 1rem;">CPU Processing Power</span>
                    <span>
                        <span id="param-cpu-score" style="font-size: 1.25rem; font-weight: 800; color: #06b6d4;">0 / 100</span>
                        <span id="param-cpu-tier" style="font-size: 0.75rem; font-weight: 700; margin-left: 6px; padding: 2px 8px; border-radius: 4px;"></span>
                    </span>
                </div>
                <div id="param-cpu-name" style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 12px;">—</div>
                <div style="position: relative; background: rgba(255,255,255,0.08); height: 10px; border-radius: 5px; overflow: hidden;">
                    <div id="param-cpu-bar" style="height: 100%; width: 0%; background-color: #06b6d4; transition: width 0.3s, background-color 0.3s;"></div>
                </div>
            </div>

            <!-- Parameter 2: GPU Performance -->
            <div style="background: rgba(0,0,0,0.28); border: 1px solid var(--border-color); border-radius: 10px; padding: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <span style="font-weight: 700; color: #fff; font-size: 1rem;">GPU Performance</span>
                    <span>
                        <span id="param-gpu-score" style="font-size: 1.25rem; font-weight: 800; color: #06b6d4;">0 / 100</span>
                        <span id="param-gpu-tier" style="font-size: 0.75rem; font-weight: 700; margin-left: 6px; padding: 2px 8px; border-radius: 4px;"></span>
                    </span>
                </div>
                <div id="param-gpu-name" style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 12px;">—</div>
                <div style="position: relative; background: rgba(255,255,255,0.08); height: 10px; border-radius: 5px; overflow: hidden;">
                    <div id="param-gpu-bar" style="height: 100%; width: 0%; background-color: #06b6d4; transition: width 0.3s, background-color 0.3s;"></div>
                </div>
            </div>

            <!-- Parameter 3: Memory -->
            <div style="background: rgba(0,0,0,0.28); border: 1px solid var(--border-color); border-radius: 10px; padding: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <span style="font-weight: 700; color: #fff; font-size: 1rem;">Memory</span>
                    <span>
                        <span id="param-ram-score" style="font-size: 1.25rem; font-weight: 800; color: #06b6d4;">0 / 100</span>
                        <span id="param-ram-tier" style="font-size: 0.75rem; font-weight: 700; margin-left: 6px; padding: 2px 8px; border-radius: 4px;"></span>
                    </span>
                </div>
                <div id="param-ram-name" style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 12px;">—</div>
                <div style="position: relative; background: rgba(255,255,255,0.08); height: 10px; border-radius: 5px; overflow: hidden;">
                    <div id="param-ram-bar" style="height: 100%; width: 0%; background-color: #06b6d4; transition: width 0.3s, background-color 0.3s;"></div>
                </div>
            </div>

            <!-- Parameter 4: Storage -->
            <div style="background: rgba(0,0,0,0.28); border: 1px solid var(--border-color); border-radius: 10px; padding: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <span style="font-weight: 700; color: #fff; font-size: 1rem;">Storage</span>
                    <span>
                        <span id="param-storage-score" style="font-size: 1.25rem; font-weight: 800; color: #06b6d4;">0 / 100</span>
                        <span id="param-storage-tier" style="font-size: 0.75rem; font-weight: 700; margin-left: 6px; padding: 2px 8px; border-radius: 4px;"></span>
                    </span>
                </div>
                <div id="param-storage-name" style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 12px;">—</div>
                <div style="position: relative; background: rgba(255,255,255,0.08); height: 10px; border-radius: 5px; overflow: hidden;">
                    <div id="param-storage-bar" style="height: 100%; width: 0%; background-color: #06b6d4; transition: width 0.3s, background-color 0.3s;"></div>
                </div>
            </div>
        </div>

        <!-- Action: Open Builder with this combo -->
        <div style="border-top: 1px solid var(--border-color); padding-top: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <h4 style="color: #fff; font-size: 1rem; font-weight: 700; margin: 0 0 4px 0;">Build with these 4 components</h4>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">
                    Carry your selected CPU, GPU, Memory, and Storage directly into the PC Builder.
                </p>
            </div>
            <a id="build-with-combo-btn" href="{{ route('builder.index') }}" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600;">
                Launch Builder with this Combo →
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cpuSelect = document.getElementById('calc-cpu');
        const gpuSelect = document.getElementById('calc-gpu');
        const ramSelect = document.getElementById('calc-ram');
        const storageSelect = document.getElementById('calc-storage');

        document.getElementById('evaluate-calc-btn').addEventListener('click', performCalculation);
        cpuSelect.addEventListener('change', performCalculation);
        gpuSelect.addEventListener('change', performCalculation);
        ramSelect.addEventListener('change', performCalculation);
        storageSelect.addEventListener('change', performCalculation);

        function performCalculation() {
            const cpuId = cpuSelect.value;
            const gpuId = gpuSelect.value;
            const ramId = ramSelect.value;
            const storageId = storageSelect.value;

            if (!cpuId || !gpuId) return;

            fetch("{{ route('calculator.calculate') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    cpu_id: cpuId,
                    gpu_id: gpuId,
                    ram_id: ramId,
                    storage_id: storageId
                })
            })
            .then(res => res.json())
            .then(data => {
                renderResults(data, cpuId, gpuId, ramId, storageId);
            })
            .catch(err => {
                console.error('Error calculating performance:', err);
            });
        }

        function getTierStyle(score) {
            const s = parseInt(score, 10) || 0;
            if (s <= 0) return { label: 'None', color: '#64748b', bg: 'rgba(255,255,255,0.06)' };
            if (s >= 85) return { label: 'Excellent', color: '#06b6d4', bg: 'rgba(6, 182, 212, 0.16)' }; // Cyan
            if (s >= 70) return { label: 'Good', color: '#10b981', bg: 'rgba(16, 185, 129, 0.16)' };      // Green
            if (s >= 50) return { label: 'Average', color: '#f59e0b', bg: 'rgba(245, 158, 11, 0.16)' };   // Yellow
            return { label: 'Poor', color: '#ef4444', bg: 'rgba(239, 68, 68, 0.16)' };                        // Red
        }

        function applyParam(prefix, score, name) {
            const tier = getTierStyle(score);
            const scoreEl = document.getElementById(`param-${prefix}-score`);
            const tierEl = document.getElementById(`param-${prefix}-tier`);
            const barEl = document.getElementById(`param-${prefix}-bar`);

            scoreEl.textContent = score + ' / 100';
            scoreEl.style.color = tier.color;
            tierEl.textContent = tier.label;
            tierEl.style.color = tier.color;
            tierEl.style.background = tier.bg;
            document.getElementById(`param-${prefix}-name`).textContent = name || '';
            barEl.style.width = Math.min(100, score) + '%';
            barEl.style.backgroundColor = tier.color;
        }

        function renderResults(data, cpuId, gpuId, ramId, storageId) {
            const resultsCard = document.getElementById('calc-results-card');
            resultsCard.style.display = 'block';

            const overallScore = data.overall_score ?? data.synergy_score ?? 0;
            const overallTier = getTierStyle(overallScore);
            const scoreDisplay = document.getElementById('overall-score-display');
            const verdictBadge = document.getElementById('verdict-badge');
            const adviceText = document.getElementById('advice-text');

            scoreDisplay.textContent = overallScore + ' / 100';
            scoreDisplay.style.color = overallTier.color;
            verdictBadge.textContent = data.verdict || overallTier.label;
            verdictBadge.style.color = overallTier.color;
            verdictBadge.style.background = overallTier.bg;
            adviceText.textContent = data.advice || '';

            // Update the 4 parameters with 4-tier colors
            applyParam('cpu', data.cpu_processing_power ?? data.cpu_score ?? 0, data.cpu_name);
            applyParam('gpu', data.gpu_performance ?? data.gpu_score ?? 0, data.gpu_name);
            applyParam('ram', data.memory ?? data.memory_score ?? 0, data.ram_name);
            applyParam('storage', data.storage ?? data.storage_score ?? 0, data.storage_name);

            // Update "Build with this Combo" link
            const params = new URLSearchParams();
            if (cpuId) params.set('cpu_id', cpuId);
            if (gpuId) params.set('gpu_id', gpuId);
            if (ramId) params.set('ram_id', ramId);
            if (storageId) params.set('storage_id', storageId);

            document.getElementById('build-with-combo-btn').href = "{{ route('builder.index') }}?" + params.toString();
        }

        // Run initial calculation on page load
        performCalculation();
    });
</script>
@endsection
