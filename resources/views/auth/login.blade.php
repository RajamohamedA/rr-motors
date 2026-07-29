@extends('layouts.guest')

@section('title', 'Login')

@section('content')

<div class="card shadow-lg border-0">

    <div class="card-header bg-primary text-white text-center">

        <h3 class="mb-0">RR Motors ERP</h3>

        <small>Inventory & Billing System</small>

    </div>

    <div class="card-body p-4">

        @if(session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email Address</label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    value="{{ old('email') }}"
                    required
                    autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    required>
            </div>

            <div class="form-check mb-3">

                <input
                    class="form-check-input"
                    type="checkbox"
                    name="remember"
                    id="remember">

                <label class="form-check-label" for="remember">
                    Remember Me
                </label>

            </div>

            <button class="btn btn-primary w-100">
                Login
            </button>

        </form>

        @if (Route::has('password.request'))

        <div class="text-center mt-3">

            <a href="{{ route('password.request') }}">
                Forgot Password?
            </a>

        </div>

        @endif

    </div>

</div>

@endsection