@extends('layouts.app')

@section('title','Stores')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">

    <h3>Stores</h3>

    <a href="{{ route('stores.create') }}"
       class="btn btn-primary">

        <i class="bi bi-plus-circle"></i>

        Add Store

    </a>

</div>

<div class="card">

    <div class="card-body">

        <table class="table table-bordered table-hover">

            <thead>

                <tr>

                    <th>#</th>

                    <th>Store Code</th>

                    <th>Store Name</th>

                    <th>Phone</th>

                    <th>Status</th>

                    <th width="180">Action</th>

                </tr>

            </thead>

            <tbody>

                <tr>

                    <td colspan="6" class="text-center">

                        No Stores Found

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>

@endsection