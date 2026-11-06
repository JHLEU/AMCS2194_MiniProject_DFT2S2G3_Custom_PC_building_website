@extends('layouts.app')

@section('title', 'Register Customer Account - Custom PC Builder')

@section('content')
<div class="auth-wrapper">
    <div class="auth-box">
        <div class="auth-header">
            <h1>Create Customer Account</h1>
            <p>Save custom PC builds, order online, and track shipments</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul style="padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.post') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" id="name" name="name" class="form-input" value="{{ old('name') }}" required placeholder="e.g. Alex Tan">
            </div>

            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-input" value="{{ old('username') }}" required placeholder="e.g. alextan">
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}" required placeholder="e.g. alex@example.com">
            </div>

            <div class="form-group">
                <label for="phone" class="form-label">Phone Number (Optional)</label>
                <input type="text" id="phone" name="phone" class="form-input" value="{{ old('phone') }}" placeholder="e.g. +6012-3456789">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-input" required placeholder="Minimum 8 characters">
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required placeholder="Repeat password">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Create Account
            </button>
        </form>

        <div style="text-align: center; margin-top: 20px; font-size: 0.85rem; color: var(--text-muted);">
            Already have an account? <a href="{{ route('login') }}">Sign in here</a>
        </div>
    </div>
</div>
@endsection
