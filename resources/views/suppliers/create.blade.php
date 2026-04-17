@extends('layouts.app')

@section('title','Add Supplier')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between">

            <h4>Add Supplier</h4>

            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>

        </div>

        <div class="card-body">

            <form action="{{ route('suppliers.store') }}" method="POST">

                @csrf

                @include('suppliers.form')

                <button class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Supplier
                </button>

            </form>

        </div>

    </div>

</div>

@endsection