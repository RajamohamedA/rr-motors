@extends('layouts.app')

@section('title','Current Stock')

@section('content')

@push('styles')

<style>
    @media print {

        .btn,
        form,
        nav,
        .sidebar,
        .card-header {

            display: none !important;

        }

        body {

            font-size: 12px;

        }

        table {

            font-size: 11px;

        }

    }

    .table-danger {

        background: #ffd8d8 !important;

    }

    .table-warning {

        background: #fff3cd !important;

    }

    .table-success {

        background: #d1e7dd !important;

    }
</style>

@endpush

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h3>
            <i class="fas fa-boxes text-primary"></i>
            Current Stock
        </h3>

        <div>

            <a href="{{ route('inventory.current-stock.print',request()->query()) }}"
                target="_blank"
                class="btn btn-primary">

                <i class="fas fa-print"></i>
                Print

            </a>

            <a href="{{ route('inventory.current-stock.pdf',request()->query()) }}"
                target="_blank"
                class="btn btn-danger">

                <i class="fas fa-file-pdf"></i>
                PDF

            </a>

            <a href="{{ route('inventory.current-stock.csv',request()->query()) }}"
                class="btn btn-success">

                <i class="fas fa-file-csv"></i>
                CSV

            </a>

        </div>

    </div>

    <form method="GET"
        action="{{ route('inventory.current-stock') }}">

        <div class="card shadow-sm mb-3">

            <div class="card-header bg-primary text-white">

                <strong>

                    <i class="fas fa-filter"></i>

                    Search Filters

                </strong>

            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-2">

                        <label class="form-label">

                            Company

                        </label>

                        <select
                            name="company_id"
                            class="form-select">

                            <option value="">All Companies</option>

                            @foreach($companies as $company)

                            <option value="{{ $company->id }}"
                                {{ request('company_id')==$company->id ? 'selected':'' }}>

                                {{ $company->company_name }}

                            </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-2">

                        <label class="form-label">

                            Store

                        </label>

                        <select
                            name="store_id"
                            class="form-select">

                            <option value="">All Stores</option>

                            @foreach($stores as $store)

                            <option value="{{ $store->id }}"
                                {{ request('store_id')==$store->id ? 'selected':'' }}>

                                {{ $store->store_name }}

                            </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-2">

                        <label class="form-label">

                            Category

                        </label>

                        <select
                            name="category_id"
                            class="form-select">

                            <option value="">All Categories</option>

                            @foreach($categories ?? [] as $category)

                            <option value="{{ $category->id }}"
                                {{ request('category_id')==$category->id ? 'selected':'' }}>

                                {{ $category->category_name }}

                            </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-2">

                        <label class="form-label">

                            Brand

                        </label>

                        <select
                            name="brand_id"
                            class="form-select">

                            <option value="">All Brands</option>

                            @foreach($brands ?? [] as $brand)

                            <option value="{{ $brand->id }}"
                                {{ request('brand_id')==$brand->id ? 'selected':'' }}>

                                {{ $brand->brand_name }}

                            </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-2">

                        <label class="form-label">

                            Product

                        </label>

                        <input
                            type="text"
                            name="product"
                            value="{{ request('product') }}"
                            class="form-control"
                            placeholder="Product Name">

                    </div>

                    <div class="col-md-2">

                        <label class="form-label">

                            &nbsp;

                        </label>

                        <div>

                            <button class="btn btn-success">

                                <i class="fas fa-search"></i>

                                Search

                            </button>

                            <a href="{{ route('inventory.current-stock') }}"
                                class="btn btn-secondary">

                                Reset

                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </form>

    @php

    $totalProducts = $stocks->count();

    $availableQty = $stocks->sum('current_stock');

    $stockValue = $stocks->sum(function($row){

    return $row->current_stock * ($row->product->purchase_price ?? 0);

    });

    $lowStock = $stocks->filter(function($row){

    return $row->current_stock > 0 &&
    $row->current_stock <= ($row->product->minimum_stock ?? 10);

        })->count();

        $outStock = $stocks->filter(function($row){

        return $row->current_stock <= 0;

            })->count();

            @endphp

            <div class="row mb-3">

                <div class="col-lg-2 col-md-4">

                    <div class="card bg-primary text-white">

                        <div class="card-body text-center">

                            <h6>Total Products</h6>

                            <h3>{{ $totalProducts }}</h3>

                        </div>

                    </div>

                </div>

                <div class="col-lg-2 col-md-4">

                    <div class="card bg-success text-white">

                        <div class="card-body text-center">

                            <h6>Available Qty</h6>

                            <h3>{{ number_format($availableQty,2) }}</h3>

                        </div>

                    </div>

                </div>

                <div class="col-lg-2 col-md-4">

                    <div class="card bg-info text-white">

                        <div class="card-body text-center">

                            <h6>Stock Value</h6>

                            <h4>{{ number_format($stockValue,2) }}</h4>

                        </div>

                    </div>

                </div>

                <div class="col-lg-2 col-md-4">

                    <div class="card bg-warning">

                        <div class="card-body text-center">

                            <h6>Low Stock</h6>

                            <h3>{{ $lowStock }}</h3>

                        </div>

                    </div>

                </div>

                <div class="col-lg-2 col-md-4">

                    <div class="card bg-danger text-white">

                        <div class="card-body text-center">

                            <h6>Out of Stock</h6>

                            <h3>{{ $outStock }}</h3>

                        </div>

                    </div>

                </div>

            </div>

            <div class="card shadow-sm">

                <div class="card-header bg-dark text-white">

                    <strong>

                        <i class="fas fa-boxes"></i>

                        Current Stock Details

                    </strong>

                </div>

                <div class="row mb-3">

                    <div class="col-md-4">

                        <input
                            type="text"
                            id="stockSearch"
                            class="form-control"
                            placeholder="Search Product / Category / Brand">

                    </div>

                </div>

                <div class="card-body table-responsive">

                    <table
                        id="stockTable"
                        class="table table-bordered table-hover table-striped">

                        <thead class="table-dark">

                            <tr>

                                <th width="60">#</th>

                                <th>Product Code</th>

                                <th>Product Name</th>

                                <th>Category</th>

                                <th>Brand</th>

                                <th>Company</th>

                                <th>Store</th>

                                <th>Unit</th>

                                <th class="text-end">Purchase Price</th>

                                <th class="text-end">Selling Price</th>

                                <th class="text-end">Current Stock</th>

                                <th class="text-end">Stock Value</th>

                                <th width="120">Status</th>

                                <th width="130">Action</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($stocks as $index=>$stock)

                            @php

                            $minimum = $stock->product->minimum_stock ?? 10;

                            $value = $stock->current_stock *
                            ($stock->product->purchase_price ?? 0);

                            @endphp

                            @if($stock->current_stock<=0)

                                <tr class="table-danger">

                                @elseif($stock->current_stock<=$minimum)

                                    <tr class="table-warning">

                                    @else

                                    <tr>

                                        @endif

                                        <td>

                                            {{ $index+1 }}

                                        </td>

                                        <td>

                                            {{ $stock->product->product_code }}

                                        </td>

                                        <td>

                                            {{ $stock->product->product_name }}

                                        </td>

                                        <td>

                                            {{ $stock->product->category->category_name ?? '-' }}

                                        </td>

                                        <td>

                                            {{ $stock->product->brand->brand_name ?? '-' }}

                                        </td>

                                        <td>

                                            {{ $stock->company->company_name }}

                                        </td>

                                        <td>

                                            {{ $stock->store->store_name }}

                                        </td>

                                        <td>

                                            {{ $stock->product->unit->short_name ?? '' }}

                                        </td>

                                        <td class="text-end">

                                            {{ number_format($stock->product->purchase_price,2) }}

                                        </td>

                                        <td class="text-end">

                                            {{ number_format($stock->product->selling_price,2) }}

                                        </td>

                                        <td class="text-end fw-bold">

                                            {{ number_format($stock->current_stock,2) }}

                                        </td>

                                        <td class="text-end fw-bold">

                                            {{ number_format($value,2) }}

                                        </td>

                                        <td class="text-center">

                                            @if($stock->current_stock<=0)

                                                <span class="badge bg-danger">

                                                Out of Stock

                                                </span>

                                                @elseif($stock->current_stock<=$minimum)

                                                    <span class="badge bg-warning text-dark">

                                                    Low Stock

                                                    </span>

                                                    @else

                                                    <span class="badge bg-success">

                                                        Available

                                                    </span>

                                                    @endif

                                        </td>

                                        <td>

                                            <div class="btn-group btn-group-sm">

                                                <a href="{{ route('stock-ledgers.show',$stock->product_id) }}"
                                                    class="btn btn-info"
                                                    title="Ledger">

                                                    <i class="fas fa-history"></i>

                                                </a>

                                                <a href="{{ route('products.show',$stock->product_id) }}"
                                                    class="btn btn-primary"
                                                    title="View">

                                                    <i class="fas fa-eye"></i>

                                                </a>

                                                <a href="{{ route('stock-adjustments.create',
                                                    [
                                                        'store'=>$stock->store_id,
                                                        'product'=>$stock->product_id
                                                    ]
                                                ) }}"
                                                    class="btn btn-warning"
                                                    title="Adjust">

                                                    <i class="fas fa-edit"></i>

                                                </a>

                                            </div>

                                        </td>

                                    </tr>

                                    @empty

                                    <tr>

                                        <td colspan="14" class="text-center">

                                            <strong>No Stock Found</strong>

                                        </td>

                                    </tr>

                                    @endforelse

                        </tbody>

                        <tfoot class="table-secondary">

                            <tr>

                                <th colspan="10" class="text-end">

                                    TOTAL

                                </th>

                                <th class="text-end">

                                    {{ number_format($stocks->sum('current_stock'),2) }}

                                </th>

                                <th class="text-end">

                                    {{ number_format(
                $stocks->sum(function($stock){
                    return $stock->current_stock *
                           ($stock->product->purchase_price ?? 0);
                }),2)
            }}

                                </th>

                                <th colspan="2"></th>

                            </tr>

                        </tfoot>

                    </table>

                </div>

                <div class="card-footer">

                    <div class="row">

                        <div class="col-md-8">

                            <span class="badge bg-success">

                                Available

                            </span>

                            <span class="me-3">

                                Stock is above minimum level

                            </span>

                            <span class="badge bg-warning text-dark">

                                Low Stock

                            </span>

                            <span class="me-3">

                                Stock is below minimum level

                            </span>

                            <span class="badge bg-danger">

                                Out of Stock

                            </span>

                            <span>

                                No stock available

                            </span>

                        </div>

                        <div class="col-md-4 text-end">

                            @if(method_exists($stocks,'links'))

                            {{ $stocks->links() }}

                            @endif

                        </div>

                    </div>

                </div>

            </div>

            <div class="row mt-3">

                <div class="col-md-3">

                    <div class="card border-primary">

                        <div class="card-body text-center">

                            <h6>Total Inventory Value</h6>

                            <h3 class="text-primary">

                                {{ number_format(
                        $stocks->sum(function($stock){
                            return $stock->current_stock *
                                   ($stock->product->purchase_price ?? 0);
                        }),2)
                    }}

                            </h3>

                        </div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card border-success">

                        <div class="card-body text-center">

                            <h6>Total Quantity</h6>

                            <h3 class="text-success">

                                {{ number_format(
                        $stocks->sum('current_stock'),
                        2
                    ) }}

                            </h3>

                        </div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card border-warning">

                        <div class="card-body text-center">

                            <h6>Low Stock Products</h6>

                            <h3 class="text-warning">

                                {{ $lowStock }}

                            </h3>

                        </div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card border-danger">

                        <div class="card-body text-center">

                            <h6>Out of Stock</h6>

                            <h3 class="text-danger">

                                {{ $outStock }}

                            </h3>

                        </div>

                    </div>

                </div>

            </div>

            @endsection

            @vite([
            'resources/js/current-stock.js'
            ])