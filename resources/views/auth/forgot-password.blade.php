@extends('layouts.guest')

@section('title', 'Forgot Password')

@section('content')
    <a href="{{ route('login') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Login
    </a>

    <div class="form-title">Forgot Password</div>

    <div style="text-align: center; padding: 20px; background: #f9f9f9; border-radius: 10px; margin-top: 15px;">
        <i class="fas fa-envelope" style="font-size: 2em; color: var(--color-green-button-light); margin-bottom: 15px;"></i>
        <p style="font-size: 0.95em; color: #444; line-height: 1.6;">
            Please email us at <strong>findme-support@ccis.edu.ph</strong> with a subject
            <strong>"FindMe@CCIS - Password Reset"</strong> and attach a copy of your
            <strong>University ID</strong>.
        </p>
        <p style="margin-top: 15px; font-size: 0.9em; color: #888;">
            Our admin will reset your password within 24 hours.
        </p>
    </div>
@endsection