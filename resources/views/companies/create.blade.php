@extends('layouts.app')

@section('title', 'Add Company')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Add Company</h4>

            <a href="{{ route('companies.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <div class="card-body">

            <form action="{{ route('companies.store') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                @include('companies.form')

                <hr>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Company
                </button>

            </form>

        </div>

    </div>

</div>

@endsection