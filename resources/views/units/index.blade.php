@extends('layouts.app')

@section('title', 'Units')

@section('content')

<div class="container-fluid">

    <div class="row mb-3">

        <div class="col-md-6">
            <h3 class="fw-bold">Units</h3>
        </div>

        <div class="col-md-6 text-end">
            <a href="{{ route('units.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Unit
            </a>
        </div>

    </div>

    <div class="card shadow-sm">

        <div class="card-body">

            <!-- Search -->

            <!-- Table -->

            <!-- Pagination -->

        </div>

    </div>

</div>

@endsection