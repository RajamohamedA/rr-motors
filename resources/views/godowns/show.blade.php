@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h4 class="mb-1">
                <i class="bi bi-building"></i>
                Godown Details
            </h4>

            <small class="text-muted">
                View godown information
            </small>
        </div>

        <div class="d-flex gap-2">

            @can('godown.edit')
            <a href="{{ route('godowns.edit', $godown->id) }}"
                class="btn btn-primary">

                <i class="bi bi-pencil"></i>
                Edit

            </a>
            @endcan

            <a href="{{ route('godowns.index') }}"
                class="btn btn-secondary">

                <i class="bi bi-arrow-left"></i>
                Back

            </a>

        </div>

    </div>


    {{-- Godown Details --}}
    <div class="card shadow-sm">

        <div class="card-header">

            <h5 class="mb-0">
                <i class="bi bi-info-circle me-1"></i>
                Godown Information
            </h5>

        </div>


        <div class="card-body">

            <div class="row g-4">

                {{-- Company --}}
                <div class="col-md-6">

                    <label class="text-muted small">
                        Company
                    </label>

                    <div class="fw-semibold">

                        {{ $godown->company->company_name ?? '-' }}

                    </div>

                </div>


                {{-- Store --}}
                <div class="col-md-6">

                    <label class="text-muted small">
                        Store / Shop
                    </label>

                    <div class="fw-semibold">

                        {{ $godown->store->store_name ?? '-' }}

                    </div>

                </div>


                {{-- Godown Name --}}
                <div class="col-md-6">


                    <label class="text-muted small">
                        Godown Name
                    </label>

                    <div class="fw-semibold fs-5">

                        {{ $godown->godown_name }}

                    </div>

                </div>


                {{-- Godown Code --}}
                <div class="col-md-6">

                    <label class="text-muted small">
                        Godown Code
                    </label>

                    <div>

                        @if($godown->godown_code)

                        <span class="badge bg-secondary fs-6">

                            {{ $godown->godown_code }}

                        </span>

                        @else

                        <span class="text-muted">
                            Not provided
                        </span>

                        @endif

                    </div>

                </div>


                {{-- Address --}}
                <div class="col-md-12">

                    <label class="text-muted small">
                        Address
                    </label>

                    <div class="border rounded p-3 bg-light">

                        @if($godown->address)

                        {{ $godown->address }}

                        @else

                        <span class="text-muted">
                            No address provided
                        </span>

                        @endif

                    </div>

                </div>


                {{-- Status --}}
                <div class="col-md-6">

                    <label class="text-muted small">
                        Status
                    </label>

                    <div>

                        @if($godown->status)

                        <span class="badge bg-success">
                            Active
                        </span>

                        @else

                        <span class="badge bg-danger">
                            Inactive
                        </span>

                        @endif

                    </div>

                </div>


                {{-- Created --}}
                <div class="col-md-6">

                    <label class="text-muted small">
                        Created At
                    </label>

                    <div>

                        {{ $godown->created_at?->format('d-m-Y h:i A') ?? '-' }}

                    </div>

                </div>


                {{-- Updated --}}
                <div class="col-md-6">

                    <label class="text-muted small">
                        Last Updated
                    </label>

                    <div>

                        {{ $godown->updated_at?->format('d-m-Y h:i A') ?? '-' }}

                    </div>

                </div>

            </div>

        </div>


        {{-- Footer --}}
        <div class="card-footer d-flex justify-content-between">

            <a href="{{ route('godowns.index') }}"
                class="btn btn-secondary">

                <i class="bi bi-arrow-left"></i>
                Back to Godowns

            </a>


            @can('godown.delete')

            <form action="{{ route('godowns.destroy', $godown->id) }}"
                method="POST"
                onsubmit="return confirm('Are you sure you want to delete this godown?');">

                @csrf

                @method('DELETE')

                <button type="submit"
                    class="btn btn-danger">

                    <i class="bi bi-trash"></i>
                    Delete

                </button>

            </form>

            @endcan

        </div>

    </div>

</div>

@endsection