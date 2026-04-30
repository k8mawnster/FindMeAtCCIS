@extends('layouts.guest')

@section('title', 'Reset Password')

@section('content')
    <a href="{{ route('login') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Login
    </a>

    <div class="form-title">Reset Password</div>

    @if(session('error'))
        <p style="color: var(--color-red); text-align: center; margin-bottom: 15px; font-weight: bold;">
            {{ session('error') }}
        </p>
    @endif

    <form action="{{ route('password.reset.post') }}" method="POST">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="password" required minlength="6">
        </div>

        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="password_confirmation" required minlength="6">
        </div>

        <button type="submit" class="btn btn-login" style="color: white;">RESET PASSWORD</button>
    </form>
@endsection