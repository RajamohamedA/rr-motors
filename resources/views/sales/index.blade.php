@extends('layouts.app')

@section('title', 'Sales Invoice List')

@section('content')

<div class="container-fluid">

    {{-- ============================================================
         PAGE HEADER
    ============================================================ --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h3 class="mb-1">
                <i class="fas fa-file-invoice-dollar text-success"></i>
                Sales Invoice List
            </h3>

            <small class="text-muted">
                Manage sales invoices, payments and customer balances
            </small>
        </div>

        @can('sales.create')

        <a href="{{ route('sales.create') }}"
            class="btn btn-success">

            <i class="fas fa-plus-circle"></i>

            New Sales Invoice

        </a>

        @endcan

    </div>


    {{-- ============================================================
         ALERTS
    ============================================================ --}}

    @if(session('success'))

    <div class="alert alert-success alert-dismissible fade show shadow-sm">

        <i class="fas fa-check-circle me-1"></i>

        {{ session('success') }}

        <button type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>

    </div>

    @endif


    @if(session('error'))

    <div class="alert alert-danger alert-dismissible fade show shadow-sm">

        <i class="fas fa-exclamation-circle me-1"></i>

        {{ session('error') }}

        <button type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>

    </div>

    @endif


    {{-- ============================================================
         SUMMARY CARDS
    ============================================================ --}}

    <div class="row g-3 mb-3">

        {{-- Total Invoices --}}
        <div class="col-xl-3 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <small class="text-muted text-uppercase fw-semibold">
                                Total Invoices
                            </small>

                            <h4 class="mb-0 mt-1">
                                {{ $sales->total() }}
                            </h4>

                        </div>

                        <div class="bg-success bg-opacity-10
                                    text-success rounded-circle
                                    p-3">

                            <i class="fas fa-file-invoice fa-lg"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Current Page --}}
        <div class="col-xl-3 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <small class="text-muted text-uppercase fw-semibold">
                                Showing
                            </small>

                            <h4 class="mb-0 mt-1">

                                {{ $sales->count() }}

                            </h4>

                            <small class="text-muted">
                                invoices on this page
                            </small>

                        </div>

                        <div class="bg-primary bg-opacity-10
                                    text-primary rounded-circle
                                    p-3">

                            <i class="fas fa-list fa-lg"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Outstanding --}}
        <div class="col-xl-3 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <small class="text-muted text-uppercase fw-semibold">
                                Outstanding
                            </small>

                            <h4 class="mb-0 mt-1 text-danger">

                                ₹{{ number_format(
                                    $sales->sum('balance_amount'),
                                    2
                                ) }}

                            </h4>

                        </div>

                        <div class="bg-danger bg-opacity-10
                                    text-danger rounded-circle
                                    p-3">

                            <i class="fas fa-exclamation-triangle fa-lg"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Collection --}}
        <div class="col-xl-3 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <small class="text-muted text-uppercase fw-semibold">
                                Collection
                            </small>

                            <h4 class="mb-0 mt-1 text-success">

                                ₹{{ number_format(
                                    $sales->sum('paid_amount'),
                                    2
                                ) }}

                            </h4>

                        </div>

                        <div class="bg-success bg-opacity-10
                                    text-success rounded-circle
                                    p-3">

                            <i class="fas fa-money-bill-wave fa-lg"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================
         MAIN CARD
    ============================================================ --}}

    <div class="card shadow-sm border-0">

        {{-- Header --}}
        <div class="card-header bg-success text-white">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <h5 class="mb-0">

                        <i class="fas fa-receipt me-1"></i>

                        Sales Invoices

                    </h5>

                </div>

                <span class="badge bg-white text-success">

                    {{ $sales->total() }} Records

                </span>

            </div>

        </div>


        {{-- ========================================================
             FILTER / SEARCH BAR
        ========================================================= --}}

        <div class="card-body border-bottom bg-light">

            <form method="GET"
                action="{{ route('sales.index') }}">

                <div class="row g-2">

                    {{-- Search --}}
                    <div class="col-md-4">

                        <label class="form-label small fw-semibold">

                            Search Invoice / Customer

                        </label>

                        <div class="input-group">

                            <span class="input-group-text">

                                <i class="fas fa-search"></i>

                            </span>

                            <input type="text"
                                name="search"
                                value="{{ request('search') }}"
                                class="form-control"
                                placeholder="Invoice number or customer">

                        </div>

                    </div>


                    {{-- Date From --}}
                    <div class="col-md-2">

                        <label class="form-label small fw-semibold">

                            From Date

                        </label>

                        <input type="date"
                            name="from_date"
                            value="{{ request('from_date') }}"
                            class="form-control">

                    </div>


                    {{-- Date To --}}
                    <div class="col-md-2">

                        <label class="form-label small fw-semibold">

                            To Date

                        </label>

                        <input type="date"
                            name="to_date"
                            value="{{ request('to_date') }}"
                            class="form-control">

                    </div>


                    {{-- Status --}}
                    <div class="col-md-2">

                        <label class="form-label small fw-semibold">

                            Status

                        </label>

                        <select name="status"
                            class="form-select">

                            <option value="">
                                All Status
                            </option>

                            <option value="Completed"
                                {{ request('status') == 'Completed' ? 'selected' : '' }}>
                                Completed
                            </option>

                            <option value="Pending"
                                {{ request('status') == 'Pending' ? 'selected' : '' }}>
                                Pending
                            </option>

                            <option value="Cancelled"
                                {{ request('status') == 'Cancelled' ? 'selected' : '' }}>
                                Cancelled
                            </option>

                        </select>

                    </div>


                    {{-- Buttons --}}
                    <div class="col-md-2">

                        <label class="form-label small fw-semibold d-block">

                            &nbsp;

                        </label>

                        <div class="d-flex gap-1">

                            <button type="submit"
                                class="btn btn-success">

                                <i class="fas fa-search"></i>

                            </button>

                            <a href="{{ route('sales.index') }}"
                                class="btn btn-outline-secondary">

                                <i class="fas fa-redo"></i>

                            </a>

                        </div>

                    </div>

                </div>

            </form>

        </div>


        {{-- ========================================================
             TABLE
        ========================================================= --}}

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle mb-0">

                    <thead class="table-dark">

                        <tr>

                            <th width="50">
                                #
                            </th>

                            <th>
                                Invoice
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Customer
                            </th>

                            <th>
                                Company
                            </th>

                            <th>
                                Store
                            </th>

                            <th class="text-end">
                                Grand Total
                            </th>

                            <th class="text-end">
                                Paid
                            </th>

                            <th class="text-end">
                                Balance
                            </th>

                            <th class="text-center">
                                Status
                            </th>

                            <th width="170"
                                class="text-center">

                                Action

                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($sales as $sale)

                        <tr>

                            {{-- Number --}}
                            <td class="text-muted">

                                {{ $loop->iteration +
                                        ($sales->currentPage() - 1)
                                        * $sales->perPage() }}

                            </td>


                            {{-- Invoice --}}
                            <td>

                                <div class="fw-bold text-success">

                                    {{ $sale->invoice_no }}

                                </div>

                                <small class="text-muted">

                                    #{{ $sale->id }}

                                </small>

                            </td>


                            {{-- Date --}}
                            <td>

                                <span class="fw-semibold">

                                    {{ date(
                                            'd-m-Y',
                                            strtotime($sale->invoice_date)
                                        ) }}

                                </span>

                            </td>


                            {{-- Customer --}}
                            <td>

                                <div class="fw-semibold">

                                    {{ $sale->customer->customer_name ?? '-' }}

                                </div>

                                @if(isset($sale->customer->mobile))

                                <small class="text-muted">

                                    <i class="fas fa-phone-alt"></i>

                                    {{ $sale->customer->mobile }}

                                </small>

                                @endif

                            </td>


                            {{-- Company --}}
                            <td>

                                {{ $sale->company->company_name
                                        ?? $sale->company->name
                                        ?? '-' }}

                            </td>


                            {{-- Store --}}
                            <td>

                                {{ $sale->store->store_name
                                        ?? $sale->store->name
                                        ?? '-' }}

                            </td>


                            {{-- Grand Total --}}
                            <td class="text-end">

                                <strong>

                                    ₹{{ number_format(
                                            $sale->grand_total,
                                            2
                                        ) }}

                                </strong>

                            </td>


                            {{-- Paid --}}
                            <td class="text-end">

                                <span class="text-success fw-bold">

                                    ₹{{ number_format(
                                            $sale->paid_amount,
                                            2
                                        ) }}

                                </span>

                            </td>


                            {{-- Balance --}}
                            <td class="text-end">

                                @if($sale->balance_amount > 0)

                                <span class="text-danger fw-bold">

                                    ₹{{ number_format(
                                                $sale->balance_amount,
                                                2
                                            ) }}

                                </span>

                                @else

                                <span class="text-success fw-bold">

                                    ₹0.00

                                </span>

                                @endif

                            </td>


                            {{-- Status --}}
                            <td class="text-center">

                                @if($sale->status == 'Completed')

                                <span class="badge bg-success px-2 py-1">

                                    <i class="fas fa-check-circle"></i>

                                    Completed

                                </span>

                                @elseif($sale->status == 'Pending')

                                <span class="badge bg-warning text-dark px-2 py-1">

                                    <i class="fas fa-clock"></i>

                                    Pending

                                </span>

                                @else

                                <span class="badge bg-danger px-2 py-1">

                                    <i class="fas fa-times-circle"></i>

                                    Cancelled

                                </span>

                                @endif

                            </td>


                            {{-- Actions --}}
                            <td class="text-center">

                                <div class="btn-group btn-group-sm"
                                    role="group">


                                    @can('sales.show')

                                    <a href="{{ route(
                                                'sales.show',
                                                $sale->id
                                            ) }}"
                                        class="btn btn-info"
                                        title="View Invoice">

                                        <i class="fas fa-eye"></i>

                                    </a>

                                    @endcan


                                    @can('sales.edit')

                                    <a href="{{ route(
                                                'sales.edit',
                                                $sale->id
                                            ) }}"
                                        class="btn btn-warning"
                                        title="Edit Invoice">

                                        <i class="fas fa-edit"></i>

                                    </a>

                                    @endcan


                                    @can('sales.print')

                                    <a href="{{ route(
                                                'sales.print',
                                                $sale->id
                                            ) }}"
                                        target="_blank"
                                        class="btn btn-secondary"
                                        title="Print Invoice">

                                        <i class="fas fa-print"></i>

                                    </a>

                                    @endcan


                                    @can('sales.delete')

                                    <form action="{{ route(
                                                'sales.destroy',
                                                $sale->id
                                            ) }}"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm(
                                                      'Are you sure you want to delete this Sales Invoice? Stock will be restored.'
                                                  );">

                                        @csrf

                                        @method('DELETE')

                                        <button type="submit"
                                            class="btn btn-danger"
                                            title="Delete Invoice">

                                            <i class="fas fa-trash"></i>

                                        </button>

                                    </form>

                                    @endcan

                                </div>

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="11"
                                class="text-center py-5">

                                <div class="text-muted">

                                    <i class="fas fa-file-invoice-dollar fa-3x mb-3"></i>

                                    <h5>
                                        No Sales Invoice Found
                                    </h5>

                                    <p class="mb-3">
                                        There are no sales invoices to display.
                                    </p>

                                    @can('sales.create')

                                    <a href="{{ route('sales.create') }}"
                                        class="btn btn-success">

                                        <i class="fas fa-plus-circle"></i>

                                        Create Sales Invoice

                                    </a>

                                    @endcan

                                </div>

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Pagination --}}
            @if($sales->hasPages())

            <div class="d-flex justify-content-between align-items-center mt-3">

                <small class="text-muted">

                    Showing
                    {{ $sales->firstItem() }}
                    to
                    {{ $sales->lastItem() }}
                    of
                    {{ $sales->total() }}
                    invoices

                </small>

                <div>

                    {{ $sales->links() }}

                </div>

            </div>

            @endif

        </div>

    </div>

</div>

@endsection