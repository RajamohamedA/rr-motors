@extends('layouts.app')

@section('title','Edit Brand')

@section('content')

<div class="container-fluid mt-4">

    <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between align-items-center">

            <h4>Edit Brand</h4>

            <a href="{{ route('brands.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>

        </div>

        <div class="card-body">

            <form action="{{ route('brands.update',$brand) }}" method="POST">

                @csrf
                @method('PUT')

                @include('brands.form')

                <button class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Update Brand
                </button>

            </form>

        </div>

    </div>

</div>

@endsection