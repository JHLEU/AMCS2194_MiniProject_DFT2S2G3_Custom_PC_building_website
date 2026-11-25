@extends('layouts.app')

@section('title', 'GOPC | Custom gaming PCs and workstations')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
@endsection

@section('content')
<div class="gopc-home">
    <section class="container home-hero" aria-labelledby="home-title">
        <div class="home-intro">
            <h1 id="home-title">Custom gaming PCs and workstations</h1>
            <p>Choose your components with socket compatibility checks. GOPC assembles your system and runs a 24-hour thermal stress test before delivery.</p>

            <div class="home-actions">
                <a href="{{ route('builder.index') }}" class="btn btn-primary">Start a build</a>
                <a href="{{ route('calculator.index') }}">Estimate wattage and FPS</a>
            </div>

            <dl class="home-bench">
                <div><dt>Thermal stress test</dt><dd>24 hours</dd></div>
                <div><dt>Component matching</dt><dd>Socket checks</dd></div>
                <div><dt>Hardware warranty</dt><dd>2 years</dd></div>
            </dl>
            <a class="home-track" href="{{ route('orders.track') }}">Track an existing order &rarr;</a>
        </div>

        <figure class="home-case">
            <img src="{{ asset('pc-case-hero.jpg') }}" alt="Liquid-cooled desktop PC with its internal components visible" fetchpriority="high">
            <figcaption>
                <span>Bench configuration</span>
                <span class="font-mono">RTX 4080 Super / Ryzen 7 7800X3D</span>
            </figcaption>
        </figure>
    </section>

    <div class="container home-sections">
        <section aria-labelledby="home-builds">
            <div class="home-section-head">
                <h2 id="home-builds">Featured builds</h2>
                <a href="{{ route('builder.index') }}">Open configurator &rarr;</a>
            </div>
            <div class="home-build-grid">
                @foreach($featuredBuilds as $build)
                    <article class="home-build">
                        <h3>{{ $build['title'] }}</h3>
                        <p>{{ $build['specs_short'] }}</p>
                        <dl class="home-target">
                            <dt>Performance target</dt>
                            <dd>{{ $build['target_fps'] }}</dd>
                        </dl>
                        <div class="home-build-footer">
                            <div>
                                <span>Base price</span>
                                <strong class="font-mono">RM {{ number_format($build['price'], 2) }}</strong>
                            </div>
                            <a href="{{ route('builder.index', ['product_ids' => implode(',', $build['components'])]) }}" class="btn btn-outline btn-sm">Configure build</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section aria-labelledby="home-catalog">
            <div class="home-section-head"><h2 id="home-catalog">Hardware catalog</h2></div>
            <ul class="home-catalog">
                @foreach($categories as $cat)
                    <li>
                        <a href="{{ route('builder.index') }}">
                            <span>{{ $cat->name }}</span>
                            <span class="font-mono">{{ $cat->products_count }} {{ Str::plural('part', $cat->products_count) }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="home-consult" aria-labelledby="home-consult-title">
            <div>
                <h2 id="home-consult-title">Talk to the workshop</h2>
                <p>Planning a multi-GPU workstation or a custom liquid-cooling loop? Talk to our technicians at Plaza Low Yat.</p>
            </div>
            <div class="home-actions">
                <a href="{{ route('contact') }}" class="btn btn-outline">Contact a technician</a>
                <a href="{{ route('builder.index') }}">Open builder &rarr;</a>
            </div>
        </section>
    </div>
</div>
@endsection
