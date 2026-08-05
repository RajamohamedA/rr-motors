@extends('layouts.app')

@section('title','Purchase Report')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">

            <div class="d-flex justify-content-between align-items-center">

                <h4 class="mb-0">

                    <i class="fas fa-chart-bar"></i>

                    Purchase Report

                </h4>

                <div id="purchase-report-buttons">

                    @can('purchase-report.print')
                    <a href="{{ route('purchase-reports.report.print') }}"
                        class="btn btn-light btn-sm" target="_blank">
                        <i class="fas fa-print"></i> Print
                    </a>
                    @endcan

                    @can('purchase-report.excel')
                    <a href="{{ route('purchase-reports.report.csv') }}"
                        class="btn btn-success btn-sm" id="btnCsv" target="_blank">
                        <i class="fas fa-file-excel"></i> CSV
                    </a>
                    @endcan

                    @can('purchase-report.pdf')
                    <a href="{{ route('purchase-reports.report.pdf') }}"
                        class="btn btn-danger btn-sm" target="_blank">
                        <i class="fas fa-file-pdf"></i> PDF
                    </a>
                    @endcan

                </div>

            </div>

        </div>

        <div class="card-body">

            <form id="reportForm">

                @csrf

                <div class="row">

                    <div class="col-md-2">

                        <label>

                            From Date

                        </label>

                        <input type="date"
                            name="from_date"
                            class="form-control">

                    </div>

                    <div class="col-md-2">

                        <label>

                            To Date

                        </label>

                        <input type="date"
                            name="to_date"
                            class="form-control">

                    </div>

                    <div class="col-md-3">

                        <label>

                            Supplier

                        </label>

                        <select name="supplier_id"
                            class="form-select">

                            <option value="">

                                All Suppliers

                            </option>

                            @foreach($suppliers as $supplier)

                            <option value="{{ $supplier->id }}">

                                {{ $supplier->supplier_name }}

                            </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-2">

                        <label>

                            Store

                        </label>

                        <select name="store_id"
                            class="form-select">

                            <option value="">

                                All Stores

                            </option>

                            @foreach($stores as $store)

                            <option value="{{ $store->id }}">

                                {{ $store->store_name }}

                            </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-3">

                        <label>

                            Purchase No

                        </label>

                        <input type="text"
                            name="purchase_no"
                            class="form-control"
                            placeholder="Purchase Number">

                    </div>

                </div>

                <div class="row mt-3">

                    <div class="col-md-3">

                        <label>

                            Product

                        </label>

                        <select name="product_id"
                            class="form-select">

                            <option value="">

                                All Products

                            </option>

                            @foreach($products as $product)

                            <option value="{{ $product->id }}">

                                {{ $product->product_name }}

                            </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-3">

                        <label>

                            Category

                        </label>

                        <select name="category_id"
                            class="form-select">

                            <option value="">

                                All Categories

                            </option>

                            @foreach($categories as $category)

                            <option value="{{ $category->id }}">

                                {{ $category->name }}

                            </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-3">

                        <label>

                            Brand

                        </label>

                        <select name="brand_id"
                            class="form-select">

                            <option value="">

                                All Brands

                            </option>

                            @foreach($brands as $brand)

                            <option value="{{ $brand->id }}">

                                {{ $brand->brand_name }}

                            </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-3 d-flex align-items-end">

                        <button type="button"
                            id="btnSearch"
                            class="btn btn-primary me-2">

                            <i class="fas fa-search"></i>

                            Search

                        </button>

                        <button type="reset"
                            class="btn btn-secondary">

                            <i class="fas fa-sync"></i>

                            Reset

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

    <div class="card mt-3">

        <div class="card-body">

            <div id="reportResult">

                <div class="text-center text-muted p-5">

                    Search Purchase Report

                </div>

            </div>

        </div>

    </div>

</div>

@endsection