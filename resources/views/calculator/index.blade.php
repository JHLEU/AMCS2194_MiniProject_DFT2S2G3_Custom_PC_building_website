@extends('layouts.app')

@section('title', 'CPU & GPU Bottleneck & Gaming FPS Calculator')

@section('content')
<div class="container" style="padding-top: 40px; padding-bottom: 60px; max-width: 960px;">
    <!-- Title & Header -->
    <div style="text-align: center; margin-bottom: 36px;">
        <span class="badge badge-primary" style="margin-bottom: 8px;">Hardware Synergy Estimator</span>
        <h1 style="font-size: 2.2rem; font-weight: 800; color: #fff; margin-bottom: 10px;">
            🎯 CPU & GPU Bottleneck Calculator
        </h1>
        <p style="color: var(--text-muted); font-size: 1.05rem; max-width: 620px; margin: 0 auto;">
            Ensure your processor and graphics card complement each other without leaving performance on the table.
        </p>
    </div>

    <!-- Selection Card -->
    <div class="card" style="padding: 28px; margin-bottom: 32px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
            <!-- CPU Dropdown -->
            <div class="form-group">
                <label for="calc-cpu" class="form-label" style="font-weight: 700; color: #fff;">
                    🔲 Select Processor (CPU)
                </label>
                <select id="calc-cpu" class="form-control" style="font-size: 0.95rem;">
                    @foreach($cpus as $cpu)
                        <option value="{{ $cpu->id }}" {{ $loop->first ? 'selected' : '' }}>
                            {{ $cpu->name }} [{{ $cpu->brand }}] - RM {{ number_format($cpu->selling_price, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- GPU Dropdown -->
            <div class="form-group">
                <label for="calc-gpu" class="form-label" style="font-weight: 700; color: #fff;">
                    🎮 Select Graphics Card (GPU)
                </label>
                <select id="calc-gpu" class="form-control" style="font-size: 0.95rem;">
                    @foreach($gpus as $gpu)
                        <option value="{{ $gpu->id }}" {{ $loop->first ? 'selected' : '' }}>
                            {{ $gpu->name }} [{{ $gpu->brand }}] - RM {{ number_format($gpu->selling_price, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Resolution Selector -->
        <div style="margin-bottom: 24px;">
            <label class="form-label" style="font-weight: 700; color: #fff; margin-bottom: 8px; display: block;">
                🖥️ Target Gaming Resolution
            </label>
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <button type="button" class="btn res-btn btn-secondary" data-res="1080p">
                    1080p (FHD Competitive)
                </button>
                <button type="button" class="btn res-btn btn-primary" data-res="1440p">
                    1440p (2K Ultra Gaming)
                </button>
                <button type="button" class="btn res-btn btn-secondary" data-res="4k">
                    2160p (4K UHD Immersion)
                </button>
            </div>
            <small style="color: var(--text-muted); display: block; margin-top: 8px;">
                Higher resolutions shift the performance load more heavily onto the GPU.
            </small>
        </div>

        <button type="button" id="evaluate-calc-btn" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: 700; font-size: 1rem;">
            Calculate Bottleneck & FPS →
        </button>
    </div>

    <!-- Results Display -->
    <div id="calc-results-card" class="card" style="padding: 32px; display: none;">
        <div style="text-align: center; margin-bottom: 28px;">
            <div style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-bottom: 4px;">
                System Synergy Score
            </div>
            <div id="synergy-score-display" style="font-size: 3.2rem; font-weight: 800; color: #10b981; line-height: 1;">
                95%
            </div>
            <div id="verdict-badge-container" style="margin-top: 10px;">
                <span class="badge badge-success" style="font-size: 0.9rem; padding: 6px 14px;">Optimal Hardware Balance</span>
            </div>
            <p id="advice-text" style="color: var(--text-light); max-width: 580px; margin: 14px auto 0; font-size: 0.95rem; line-height: 1.5;">
                This CPU and GPU combination works in near-perfect equilibrium.
            </p>
        </div>

        <!-- FPS Estimates Grid -->
        <h3 style="font-size: 1.15rem; font-weight: 700; color: #fff; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            🎮 Estimated Gaming Frame Rates
        </h3>
        <div id="fps-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 28px;">
            <!-- Populated via AJAX -->
        </div>

        <!-- Action: Open Builder with this combo -->
        <div style="border-top: 1px solid var(--border-color); padding-top: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <h4 style="color: #fff; font-size: 1rem; font-weight: 700; margin: 0 0 4px 0;">Build with this pair</h4>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">
                    Carry this CPU and GPU directly into the PC Builder to pick matching motherboard, RAM, and PSU.
                </p>
            </div>
            <a id="build-with-combo-btn" href="{{ route('builder.index') }}" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600;">
                ⚡ Launch Builder with this Combo →
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let activeResolution = '1440p';

        // Resolution pills
        document.querySelectorAll('.res-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.res-btn').forEach(b => {
                    b.classList.remove('btn-primary');
                    b.classList.add('btn-secondary');
                });
                this.classList.remove('btn-secondary');
                this.classList.add('btn-primary');
                activeResolution = this.dataset.res;
                performCalculation();
            });
        });

        // Trigger on button click or dropdown change
        document.getElementById('evaluate-calc-btn').addEventListener('click', performCalculation);
        document.getElementById('calc-cpu').addEventListener('change', performCalculation);
        document.getElementById('calc-gpu').addEventListener('change', performCalculation);

        function performCalculation() {
            const cpuId = document.getElementById('calc-cpu').value;
            const gpuId = document.getElementById('calc-gpu').value;

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
                    resolution: activeResolution
                })
            })
            .then(res => res.json())
            .then(data => {
                renderResults(data, cpuId, gpuId);
            })
            .catch(err => {
                console.error('Error calculating bottleneck:', err);
            });
        }

        function renderResults(data, cpuId, gpuId) {
            const resultsCard = document.getElementById('calc-results-card');
            resultsCard.style.display = 'block';

            const scoreDisplay = document.getElementById('synergy-score-display');
            const verdictContainer = document.getElementById('verdict-badge-container');
            const adviceText = document.getElementById('advice-text');

            const score = data.synergy_score || 90;
            scoreDisplay.textContent = score + '%';

            let badgeClass = 'badge-success';
            let color = '#10b981';

            if (score < 75) {
                badgeClass = 'badge-danger';
                color = '#ef4444';
            } else if (score < 88) {
                badgeClass = 'badge-warning';
                color = '#f59e0b';
            }

            scoreDisplay.style.color = color;
            verdictContainer.innerHTML = `<span class="badge ${badgeClass}" style="font-size: 0.9rem; padding: 6px 14px;">${data.verdict}</span>`;
            adviceText.textContent = data.advice || 'Hardware operates smoothly under selected resolution.';

            // Render FPS cards
            const fpsGrid = document.getElementById('fps-grid');
            fpsGrid.innerHTML = '';

            const games = data.fps_estimates || {
                'Cyberpunk 2077 (Ultra)': '95 FPS',
                'Call of Duty: Warzone': '165 FPS',
                'Valorant / CS2': '320+ FPS',
                'Apex Legends': '180 FPS'
            };

            Object.entries(games).forEach(([game, fps]) => {
                const card = document.createElement('div');
                card.style.background = 'rgba(0,0,0,0.25)';
                card.style.border = '1px solid var(--border-color)';
                card.style.borderRadius = '8px';
                card.style.padding = '14px 18px';
                card.style.textAlign = 'center';

                card.innerHTML = `
                    <small style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-bottom: 4px;">${game}</small>
                    <div style="font-size: 1.4rem; font-weight: 800; color: #60a5fa;">${fps}</div>
                `;
                fpsGrid.appendChild(card);
            });

            // Update "Build with this Combo" link
            const buildBtn = document.getElementById('build-with-combo-btn');
            buildBtn.href = "{{ route('builder.index') }}?cpu_id=" + cpuId + "&gpu_id=" + gpuId;

            // Smooth scroll to results
            resultsCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Run initial calculation
        performCalculation();
    });
</script>
@endsection
