@extends('layouts.guest')

@section('title', 'Verify Email')

@section('content')

<div class="card shadow-lg border-0">

    <div class="card-header bg-primary text-white text-center">
        <h3 class="mb-0">RR Motors ERP</h3>
        <small>Email Verification</small>
    </div>

    <div class="card-body">

        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success">
                A new verification link has been sent to your email address.
            </div>
        @endif

        <div class="alert alert-info">
            Before continuing, please verify your email address by clicking the link we emailed you.
            If you didn't receive the email, you can request another verification link below.
        </div>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <button type="submit" class="btn btn-primary w-100 mb-3">
                Resend Verification Email
            </button>

        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="btn btn-outline-secondary w-100">
                Logout
            </button>

        </form>

    </div>

</div>

@endsection