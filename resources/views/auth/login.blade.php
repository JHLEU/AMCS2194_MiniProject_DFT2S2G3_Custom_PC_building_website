@extends('layouts.app')

@section('title', 'Sign In - Custom PC & ERP')

@section('content')
<div class="auth-wrapper">
    <div class="auth-box">
        <div class="auth-header">
            <h1>Account Login</h1>
            <p>Access your customer account or staff ERP portal</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="login" class="form-label">Username or Email</label>
                <input type="text" id="login" name="login" class="form-input" value="{{ old('login') }}" required autofocus placeholder="admin@custompc.local or username">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-input" required placeholder="Enter password">
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; font-size: 0.85rem;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: var(--text-light);">
                    <input type="checkbox" name="remember" value="1"> Remember me
                </label>
                @env(['local', 'testing'])
                    <span style="color: var(--text-muted);">Default pass: password123</span>
                @endenv
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Sign In
            </button>
        </form>

        <div style="text-align: center; margin-top: 20px; font-size: 0.85rem; color: var(--text-muted);">
            Don't have an account? <a href="{{ route('register') }}">Create customer account</a>
        </div>

        @env(['local', 'testing'])
        <!-- Dev Quick-fill chips -->
        <div class="dev-chips">
            <p>⚡ Quick Test Credentials (Click to fill):</p>
            <div class="chips-container">
                <button type="button" class="chip" data-login="admin@custompc.local">👑 Admin</button>
                <button type="button" class="chip" data-login="warehouse@custompc.local">📦 Warehouse</button>
                <button type="button" class="chip" data-login="tech@custompc.local">🛠️ Technician</button>
                <button type="button" class="chip" data-login="customer@custompc.local">👤 Customer</button>
            </div>
        </div>
        @endenv
    </div>
</div>
@endsection
