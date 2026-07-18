@extends('layouts.guest')

@section('title', 'Reset Password')

@section('content')

<div class="card shadow-lg border-0">

    <div class="card-header bg-primary text-white text-center">
        <h3 class="mb-0">RR Motors ERP</h3>
        <small>Reset Password</small>
    </div>

    <div class="card-body">

        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.store') }}">

            @csrf

            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <div class="mb-3">
                <label>Email Address</label>

                <input
                    type="email"
                    name="email"
                    value="{{ old('email', request('email')) }}"
                    class="form-control"
                    required>
            </div>

            <div class="mb-3">
                <label>New Password</label>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    required>
            </div>

            <div class="mb-3">
                <label>Confirm Password</label>

                <input
                    type="password"
                    name="password_confirmation"
                    class="form-control"
                    required>
            </div>

            <button class="btn btn-primary w-100">
                Reset Password
            </button>

        </form>

    </div>

</div>

@endsection