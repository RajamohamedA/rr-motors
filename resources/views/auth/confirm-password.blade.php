@extends('layouts.guest')

@section('title', 'Confirm Password')

@section('content')

<div class="card shadow-lg border-0">

    <div class="card-header bg-primary text-white text-center">
        <h3 class="mb-0">RR Motors ERP</h3>
        <small>Confirm Password</small>
    </div>

    <div class="card-body">

        <div class="alert alert-info">
            Please confirm your password before continuing.
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Password</label>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    required
                    autofocus>
            </div>

            <button class="btn btn-primary w-100">
                Confirm Password
            </button>

        </form>

    </div>

</div>

@endsection