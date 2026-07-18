@extends('layouts.guest')

@section('title', 'Forgot Password')

@section('content')

<div class="card shadow-lg border-0">

    <div class="card-header bg-primary text-white text-center">
        <h3 class="mb-0">RR Motors ERP</h3>
        <small>Forgot Password</small>
    </div>

    <div class="card-body p-4">

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <p class="text-muted">
            Enter your email address and we'll send you a password reset link.
        </p>

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email"
                       name="email"
                       class="form-control"
                       value="{{ old('email') }}"
                       required
                       autofocus>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Send Password Reset Link
            </button>

            <div class="text-center mt-3">
                <a href="{{ route('login') }}">Back to Login</a>
            </div>

        </form>

    </div>

</div>

@endsection