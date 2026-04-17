@extends('layouts.app')

@section('title', 'Edit Category')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between align-items-center">

            <h4 class="mb-0">Edit Category</h4>

            <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>

        </div>

        <div class="card-body">

            <form action="{{ route('categories.update',$category) }}" method="POST">

                @csrf
                @method('PUT')

                @include('categories.form')

                <button class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Category
                </button>

            </form>

        </div>

    </div>

</div>

@endsection