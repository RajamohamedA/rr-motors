@extends('layouts.app')

@section('title', 'Edit Store')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Edit Store</h4>

            <a href="{{ route('stores.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <div class="card-body">

            <form action="{{ route('stores.update',$store) }}"
                  method="POST">

                @csrf
                @method('PUT')

                @include('stores.form')

                <button class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Store
                </button>

            </form>

        </div>

    </div>

</div>

@endsection