@extends('layouts.app')

@section('title', 'Sign In — GOPC')

@section('content')
<div style="min-height: calc(100vh - 180px); display: flex; align-items: center; justify-content: center; padding: 48px 24px;">
    <div class="card" style="width: 100%; max-width: 420px; padding: 32px; margin-bottom: 0;">
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.6875rem; color: var(--primary); font-family: 'Geist Mono', monospace; margin-bottom: 12px;">
                <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--primary);"></span>
                Authentication Gateway
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 600; color: var(--text-primary); letter-spacing: -0.02em; margin: 0 0 6px;">
                Account sign in
            </h1>
            <p style="color: var(--text-muted); font-size: 0.8125rem; margin: 0;">
                Access customer order tracking or staff operations portal
            </p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger" style="margin-bottom: 16px;">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="login" class="form-label">Username or email</label>
                <input type="text" id="login" name="login" class="form-input" value="{{ old('login') }}" required autofocus placeholder="username@custompc.local">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-input" required placeholder="Enter password">
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; font-size: 0.8125rem;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: var(--text-secondary);">
                    <input type="checkbox" name="remember" value="1" style="accent-color: var(--primary);"> Remember session
                </label>
                @env(['local', 'testing'])
                    <span class="font-mono" style="color: var(--text-muted); font-size: 0.75rem;">pw: password123</span>
                @endenv
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 11px;">
                Sign in
            </button>
        </form>

        <div style="text-align: center; margin-top: 20px; font-size: 0.8125rem; color: var(--text-muted); border-top: 1px solid var(--border-color); padding-top: 16px;">
            New client? <a href="{{ route('register') }}" style="color: var(--primary); font-weight: 500;">Register account</a>
        </div>

        @env(['local', 'testing'])
        <div style="margin-top: 20px; padding: 12px; background: var(--bg-deep); border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
            <div class="font-mono" style="font-size: 0.6875rem; color: var(--text-muted); margin-bottom: 8px;">
                DEVELOPMENT LOGINS:
            </div>
            <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                <button type="button" class="btn btn-outline btn-sm chip" data-login="admin@custompc.local">Admin</button>
                <button type="button" class="btn btn-outline btn-sm chip" data-login="warehouse@custompc.local">Warehouse</button>
                <button type="button" class="btn btn-outline btn-sm chip" data-login="tech@custompc.local">Tech</button>
                <button type="button" class="btn btn-outline btn-sm chip" data-login="customer@custompc.local">Customer</button>
            </div>
        </div>
        @endenv
    </div>
</div>
@endsection
