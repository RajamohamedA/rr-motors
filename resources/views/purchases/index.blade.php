@extends('layouts.app')

@section('title','Purchase List')

@section('content')

<div class="container-fluid mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h3>Purchase List</h3>

        <a href="{{ route('purchases.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Purchase
        </a>

    </div>

    <div class="card">

        <div class="card-body">

            <table class="table table-bordered table-striped">

                <thead>

                <tr>

                    <th>#</th>
                    <th>Purchase No</th>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th>Grand Total</th>
                    <th>Status</th>
                    <th width="150">Action</th>

                </tr>

                </thead>

                <tbody>

                @forelse($purchases as $purchase)

                    <tr>

                        <td>{{ $loop->iteration }}</td>

                        <td>{{ $purchase->purchase_no }}</td>

                        <td>{{ $purchase->purchase_date }}</td>

                        <td>{{ $purchase->supplier->supplier_name ?? '' }}</td>

                        <td>{{ number_format($purchase->grand_total,2) }}</td>

                        <td>
                            @if($purchase->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>

                        <td>

                            <a href="{{ route('purchases.edit',$purchase->id) }}"
                               class="btn btn-warning btn-sm">

                                <i class="fas fa-edit"></i>

                            </a>

                            <form action="{{ route('purchases.destroy', $purchase->id) }}"
                                method="POST"
                                class="d-inline"
                                onsubmit="return confirm('Are you sure you want to delete this purchase?')">

                                @csrf
                                @method('DELETE')

                                <button class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>

                            </form>

                            <a href="{{ route('purchases.print', $purchase->id) }}"
                                class="btn btn-secondary btn-sm"
                                target="_blank">

                                    <i class="fas fa-print"></i>

                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="7" class="text-center">

                            No Purchase Found

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

            {{ $purchases->links() }}

        </div>

    </div>

</div>

@endsection