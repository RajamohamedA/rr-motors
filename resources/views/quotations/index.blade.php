@extends('layouts.app')

@section('title','Quotation List')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">

            <div class="d-flex justify-content-between align-items-center">

                <h5 class="mb-0">
                    <i class="fas fa-file-signature"></i>
                    Quotation List
                </h5>

                <a href="{{ route('quotations.create') }}"
                    class="btn btn-light btn-sm">

                    <i class="fas fa-plus"></i>

                    New Quotation

                </a>

            </div>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover table-striped align-middle">

                    <thead class="table-dark">

                        <tr>

                            <th width="60">#</th>

                            <th>Quotation No</th>

                            <th>Date</th>

                            <th>Customer</th>

                            <th class="text-end">Grand Total</th>

                            <th>Status</th>

                            <th width="260">Action</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($quotations as $quotation)

                        <tr>

                            <td>{{ $loop->iteration }}</td>

                            <td>{{ $quotation->quotation_no }}</td>

                            <td>{{ date('d-m-Y',strtotime($quotation->quotation_date)) }}</td>

                            <td>{{ $quotation->customer->customer_name }}</td>

                            <td class="text-end">

                                {{ number_format($quotation->grand_total,2) }}

                            </td>

                            <td>

                                @switch($quotation->status)

                                @case('Draft')
                                <span class="badge bg-secondary">Draft</span>
                                @break

                                @case('Pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                                @break

                                @case('Approved')
                                <span class="badge bg-success">Approved</span>
                                @break

                                @case('Rejected')
                                <span class="badge bg-danger">Rejected</span>
                                @break

                                @case('Converted')
                                <span class="badge bg-info">Converted</span>
                                @break

                                @default
                                <span class="badge bg-secondary">
                                    {{ $quotation->status }}
                                </span>

                                @endswitch

                            </td>

                            <td>

                                <a href="{{ route('quotations.show',$quotation->id) }}"
                                    class="btn btn-info btn-sm">

                                    <i class="fas fa-eye"></i>

                                </a>

                                <a href="{{ route('quotations.edit',$quotation->id) }}"
                                    class="btn btn-warning btn-sm">

                                    <i class="fas fa-edit"></i>

                                </a>

                                <a href="{{ route('quotations.print',$quotation->id) }}"
                                    class="btn btn-secondary btn-sm"
                                    target="_blank">

                                    <i class="fas fa-print"></i>

                                </a>

                                <a href="{{ route('quotations.convert',$quotation->id) }}"
                                    class="btn btn-success btn-sm">

                                    <i class="fas fa-exchange-alt"></i>

                                </a>

                                <form action="{{ route('quotations.destroy',$quotation->id) }}"
                                    method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('Delete this quotation?')">

                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-danger btn-sm">

                                        <i class="fas fa-trash"></i>

                                    </button>

                                </form>

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="7" class="text-center">

                                No Quotations Found

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-3">

                {{ $quotations->links() }}

            </div>

        </div>

    </div>

</div>

@endsection