@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <div class="form-title">System Login</div>

    @if(session('success'))
        <p style="color: var(--color-green-button-light); text-align: center; margin-bottom: 15px; font-weight: bold;">
            {{ session('success') }}
        </p>
    @endif

    @if(session('error'))
        <p style="color: var(--color-red); text-align: center; margin-bottom: 15px; font-weight: bold;">
            {{ session('error') }}
        </p>
    @endif

    <form action="{{ route('login.post') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="student_id">User ID</label>
            <input type="text" id="student_id" name="student_id"
                placeholder="XX-XXXXXX" required value="{{ old('student_id') }}">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="login-options">
            <div>
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember Me</label>
            </div>
            <a href="{{ route('forgot.password') }}">Forgot Password?</a>
        </div>

        <div class="register-link" style="text-align: center; margin-top: 25px; margin-bottom: 20px; font-size: 0.9em;">
            New Student? <a href="{{ route('register') }}">Register Here</a>
        </div>

        <button type="submit" class="btn btn-login" style="color: var(--color-white);">LOG IN</button>
    </form>
@endsection