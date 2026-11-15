@extends('layouts.app')

@section('title', 'Client Registration — GOPC')

@section('content')
<div style="min-height: calc(100vh - 180px); display: flex; align-items: center; justify-content: center; padding: 48px 24px;">
    <div class="card" style="width: 100%; max-width: 440px; padding: 32px; margin-bottom: 0;">
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.6875rem; color: var(--primary); font-family: 'Geist Mono', monospace; margin-bottom: 12px;">
                <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--primary);"></span>
                Client Intake
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 600; color: var(--text-primary); letter-spacing: -0.02em; margin: 0 0 6px;">
                Create client account
            </h1>
            <p style="color: var(--text-muted); font-size: 0.8125rem; margin: 0;">
                Register to save custom builds, track orders, and register hardware warranties
            </p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger" style="margin-bottom: 16px;">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('register.post') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name" class="form-label">Full name</label>
                <input type="text" id="name" name="name" class="form-input" value="{{ old('name') }}" required placeholder="Alexander Tan">
            </div>

            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-input" value="{{ old('username') }}" required placeholder="alextan">
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email address</label>
                <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}" required placeholder="alex@domain.com">
            </div>

            <div class="form-group">
                <label for="phone" class="form-label">Phone number</label>
                <input type="text" id="phone" name="phone" class="form-input" value="{{ old('phone') }}" placeholder="+60 12-345 6789">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-input" required placeholder="Minimum 8 characters">
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirm password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required placeholder="Re-enter password">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 11px; margin-top: 8px;">
                Complete registration
            </button>
        </form>

        <div style="text-align: center; margin-top: 20px; font-size: 0.8125rem; color: var(--text-muted); border-top: 1px solid var(--border-color); padding-top: 16px;">
            Already have an account? <a href="{{ route('login') }}" style="color: var(--primary); font-weight: 500;">Sign in</a>
        </div>
    </div>
</div>
@endsection
