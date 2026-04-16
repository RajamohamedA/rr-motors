@extends('layouts.app')

@section('title','Edit Company')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between">
            <h4>Edit Company</h4>

            <a href="{{ route('companies.index') }}" class="btn btn-secondary">
                Back
            </a>
        </div>

        <div class="card-body">

            <form action="{{ route('companies.update',$company->id) }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf
                @method('PUT')

                @include('companies.form')

                <button class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Company
                </button>

            </form>

        </div>

    </div>

</div>

@endsection