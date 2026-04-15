@extends('layouts.app')

@section('title', 'Add Unit')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <div class="card-header">
            <h4>Add Unit</h4>
        </div>

        <div class="card-body">

            <form action="{{ route('units.store') }}" method="POST">

                @csrf

                @include('units.form')

                <div class="mt-3">

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save
                    </button>

                    <a href="{{ route('units.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection