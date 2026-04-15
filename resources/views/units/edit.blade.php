@extends('layouts.app')

@section('title', 'Edit Unit')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <div class="card-header">
            <h4>Edit Unit</h4>
        </div>

        <div class="card-body">

            <form action="{{ route('units.update', $unit->id) }}" method="POST">

                @csrf
                @method('PUT')

                @include('units.form')

                <div class="mt-3">

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
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