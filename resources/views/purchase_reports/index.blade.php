@extends('layouts.app')

@section('title', 'Purchase Report')

@section('content')

<div class="container-fluid">

    {{-- =========================================================
         Flash Messages
    ========================================================== --}}

    @if(session('error'))

    <div class="alert alert-danger alert-dismissible fade show">

        <i class="fas fa-exclamation-circle"></i>

        {{ session('error') }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>

    </div>

    @endif


    {{-- =========================================================
         Purchase Report Card
    ========================================================== --}}

    <div class="card shadow-sm">

        {{-- =====================================================
             Header
        ====================================================== --}}

        <div class="card-header bg-primary text-white">

            <div class="d-flex justify-content-between align-items-center">

                <h4 class="mb-0">

                    <i class="fas fa-chart-bar"></i>

                    Purchase Report

                </h4>


                {{-- =================================================
                     Export Buttons
                ================================================== --}}

                <div
                    id="purchaseReportButtons"
                    class="d-flex gap-2">

                    @can('purchase-report.print')

                    <a
                        href="{{ route('purchase-reports.report.print') }}"
                        class="btn btn-light btn-sm"
                        target="_blank">

                        <i class="fas fa-print"></i>

                        Print

                    </a>

                    @endcan


                    @can('purchase-report.excel')

                    <a
                        href="{{ route('purchase-reports.report.csv') }}"
                        class="btn btn-success btn-sm"
                        id="purchaseReportCsvButton">

                        <i class="fas fa-file-csv"></i>

                        CSV

                    </a>

                    @endcan


                    @can('purchase-report.pdf')

                    <a
                        href="{{ route('purchase-reports.report.pdf') }}"
                        class="btn btn-danger btn-sm"
                        target="_blank">

                        <i class="fas fa-file-pdf"></i>

                        PDF

                    </a>

                    @endcan

                </div>

            </div>

        </div>


        {{-- =========================================================
             Filters
        ========================================================== --}}

        <div class="card-body">

            <form
                id="purchaseReportForm"
                method="POST">

                @csrf


                {{-- =================================================
                     Row 1
                ================================================== --}}

                <div class="row g-3">


                    {{-- =================================================
                         Company
                    ================================================== --}}

                    <div class="col-md-3">

                        <label
                            for="purchaseReportCompany"
                            class="form-label">

                            Company

                        </label>


                        <select
                            name="company_id"
                            id="purchaseReportCompany"
                            class="form-select">

                            @if(auth()->user()->is_super_user)

                            <option value="">

                                All Companies

                            </option>

                            @endif


                            @foreach($companies as $company)

                            <option
                                value="{{ $company->id }}"
                                {{ session(
                                        'purchase_report_filters.company_id'
                                    ) == $company->id
                                        ? 'selected'
                                        : ''
                                    }}>

                                {{ $company->company_name }}

                            </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- =================================================
                         From Date
                    ================================================== --}}

                    <div class="col-md-2">

                        <label
                            for="purchaseReportFromDate"
                            class="form-label">

                            From Date

                        </label>


                        <input
                            type="date"
                            name="from_date"
                            id="purchaseReportFromDate"
                            class="form-control"
                            value="{{ session(
                                'purchase_report_filters.from_date'
                            ) }}">

                    </div>


                    {{-- =================================================
                         To Date
                    ================================================== --}}

                    <div class="col-md-2">

                        <label
                            for="purchaseReportToDate"
                            class="form-label">

                            To Date

                        </label>


                        <input
                            type="date"
                            name="to_date"
                            id="purchaseReportToDate"
                            class="form-control"
                            value="{{ session(
                                'purchase_report_filters.to_date'
                            ) }}">

                    </div>


                    {{-- =================================================
                         Supplier
                    ================================================== --}}

                    <div class="col-md-3">

                        <label
                            for="purchaseReportSupplier"
                            class="form-label">

                            Supplier

                        </label>


                        <select
                            name="supplier_id"
                            id="purchaseReportSupplier"
                            class="form-select">

                            <option value="">

                                All Suppliers

                            </option>


                            @foreach($suppliers as $supplier)

                            <option
                                value="{{ $supplier->id }}"
                                {{ session(
                                        'purchase_report_filters.supplier_id'
                                    ) == $supplier->id
                                        ? 'selected'
                                        : ''
                                    }}>

                                {{ $supplier->supplier_name }}

                            </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- =================================================
                         Store
                    ================================================== --}}

                    <div class="col-md-2">

                        <label
                            for="purchaseReportStore"
                            class="form-label">

                            Store

                        </label>


                        <select
                            name="store_id"
                            id="purchaseReportStore"
                            class="form-select">

                            <option value="">

                                All Stores

                            </option>


                            @foreach($stores as $store)

                            <option
                                value="{{ $store->id }}"
                                {{ session(
                                        'purchase_report_filters.store_id'
                                    ) == $store->id
                                        ? 'selected'
                                        : ''
                                    }}>

                                {{ $store->store_name }}

                            </option>

                            @endforeach

                        </select>

                    </div>

                </div>


                {{-- =================================================
                     Row 2
                ================================================== --}}

                <div class="row g-3 mt-1">


                    {{-- =================================================
                         Purchase No
                    ================================================== --}}

                    <div class="col-md-3">

                        <label
                            for="purchaseReportPurchaseNo"
                            class="form-label">

                            Purchase No

                        </label>


                        <input
                            type="text"
                            name="purchase_no"
                            id="purchaseReportPurchaseNo"
                            class="form-control"
                            placeholder="Purchase Number"
                            value="{{ session(
                                'purchase_report_filters.purchase_no'
                            ) }}">

                    </div>


                    {{-- =================================================
                         Product
                    ================================================== --}}

                    <div class="col-md-3">

                        <label
                            for="purchaseReportProduct"
                            class="form-label">

                            Product

                        </label>


                        <select
                            name="product_id"
                            id="purchaseReportProduct"
                            class="form-select">

                            <option value="">

                                All Products

                            </option>


                            @foreach($products as $product)

                            <option
                                value="{{ $product->id }}"
                                {{ session(
                                        'purchase_report_filters.product_id'
                                    ) == $product->id
                                        ? 'selected'
                                        : ''
                                    }}>

                                {{ $product->product_name }}

                            </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- =================================================
                         Category
                    ================================================== --}}

                    <div class="col-md-3">

                        <label
                            for="purchaseReportCategory"
                            class="form-label">

                            Category

                        </label>


                        <select
                            name="category_id"
                            id="purchaseReportCategory"
                            class="form-select">

                            <option value="">

                                All Categories

                            </option>


                            @foreach($categories as $category)

                            <option
                                value="{{ $category->id }}"
                                {{ session(
                                        'purchase_report_filters.category_id'
                                    ) == $category->id
                                        ? 'selected'
                                        : ''
                                    }}>

                                {{ $category->name }}

                            </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- =================================================
                         Brand
                    ================================================== --}}

                    <div class="col-md-3">

                        <label
                            for="purchaseReportBrand"
                            class="form-label">

                            Brand

                        </label>


                        <select
                            name="brand_id"
                            id="purchaseReportBrand"
                            class="form-select">

                            <option value="">

                                All Brands

                            </option>


                            @foreach($brands as $brand)

                            <option
                                value="{{ $brand->id }}"
                                {{ session(
                                        'purchase_report_filters.brand_id'
                                    ) == $brand->id
                                        ? 'selected'
                                        : ''
                                    }}>

                                {{ $brand->brand_name }}

                            </option>

                            @endforeach

                        </select>

                    </div>

                </div>


                {{-- =================================================
                     Buttons
                ================================================== --}}

                <div class="row mt-4">

                    <div class="col-12 text-end">


                        <button
                            type="button"
                            id="purchaseReportSearchButton"
                            class="btn btn-primary">

                            <i class="fas fa-search"></i>

                            Search

                        </button>


                        <button
                            type="button"
                            id="purchaseReportResetButton"
                            class="btn btn-secondary">

                            <i class="fas fa-sync-alt"></i>

                            Reset

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- =========================================================
         Report Result
    ========================================================== --}}

    <div
        class="card mt-3 shadow-sm"
        id="purchaseReportResultCard">

        <div class="card-body">

            <div
                id="purchaseReportResult">

                <div class="text-center text-muted py-5">

                    <i class="fas fa-search fa-2x mb-3"></i>

                    <div>

                        Select filters and click
                        <strong>Search</strong>
                        to generate the Purchase Report.

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection


@push('scripts')

<script>
    document.addEventListener(
        'DOMContentLoaded',
        function() {

            /*
            |--------------------------------------------------------------------------
            | Purchase Report - Dedicated Variables
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            |
            | These names are unique to this page.
            |
            | Do not use generic variables such as:
            |
            | form
            | rows
            | result
            | searchButton
            |
            | This prevents conflicts with other Blade pages.
            |
            |--------------------------------------------------------------------------
            */


            const purchaseReportForm =
                document.getElementById(
                    'purchaseReportForm'
                );


            const purchaseReportSearchButton =
                document.getElementById(
                    'purchaseReportSearchButton'
                );


            const purchaseReportResetButton =
                document.getElementById(
                    'purchaseReportResetButton'
                );


            const purchaseReportResult =
                document.getElementById(
                    'purchaseReportResult'
                );


            /*
            |--------------------------------------------------------------------------
            | Search URL
            |--------------------------------------------------------------------------
            */

            const purchaseReportSearchUrl =
                @json(
                    route('purchase-reports.search')
                );


            /*
            |--------------------------------------------------------------------------
            | Search Report
            |--------------------------------------------------------------------------
            */

            purchaseReportSearchButton.addEventListener(
                'click',
                function() {

                    /*
                    |--------------------------------------------------------------------------
                    | Disable Button
                    |--------------------------------------------------------------------------
                    */

                    purchaseReportSearchButton.disabled =
                        true;


                    purchaseReportSearchButton.innerHTML =
                        '<i class="fas fa-spinner fa-spin"></i> Searching...';


                    /*
                    |--------------------------------------------------------------------------
                    | Show Loading
                    |--------------------------------------------------------------------------
                    */

                    purchaseReportResult.innerHTML = `

                    <div class="text-center py-5">

                        <div
                            class="spinner-border text-primary"
                            role="status">
                        </div>

                        <div class="mt-2 text-muted">

                            Loading Purchase Report...

                        </div>

                    </div>

                `;


                    /*
                    |--------------------------------------------------------------------------
                    | Form Data
                    |--------------------------------------------------------------------------
                    */

                    const purchaseReportFormData =
                        new FormData(
                            purchaseReportForm
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | AJAX Request
                    |--------------------------------------------------------------------------
                    */

                    fetch(
                            purchaseReportSearchUrl, {

                                method: 'POST',

                                headers: {

                                    'X-CSRF-TOKEN': document
                                        .querySelector(
                                            '#purchaseReportForm input[name="_token"]'
                                        )
                                        .value,

                                    'X-Requested-With': 'XMLHttpRequest',

                                },

                                body: purchaseReportFormData,

                            }
                        )
                        .then(
                            function(
                                purchaseReportResponse
                            ) {

                                if (
                                    !purchaseReportResponse.ok
                                ) {

                                    throw new Error(
                                        'Unable to load Purchase Report.'
                                    );

                                }

                                return purchaseReportResponse.text();

                            }
                        )
                        .then(
                            function(
                                purchaseReportHtml
                            ) {

                                /*
                                |--------------------------------------------------------------------------
                                | IMPORTANT
                                |--------------------------------------------------------------------------
                                |
                                | Insert ONLY the returned result
                                | into the result container.
                                |
                                | This prevents the entire page from
                                | being replaced with the result Blade.
                                |
                                |--------------------------------------------------------------------------
                                */

                                purchaseReportResult.innerHTML =
                                    purchaseReportHtml;


                                /*
                                |--------------------------------------------------------------------------
                                | Restore Button
                                |--------------------------------------------------------------------------
                                */

                                purchaseReportSearchButton.disabled =
                                    false;


                                purchaseReportSearchButton.innerHTML =
                                    '<i class="fas fa-search"></i> Search';


                                /*
                                |--------------------------------------------------------------------------
                                | Update Export URLs
                                |--------------------------------------------------------------------------
                                |
                                | Export buttons use session filters on
                                | the server, so no query string is required.
                                |
                                |--------------------------------------------------------------------------
                                */

                            }
                        )
                        .catch(
                            function(
                                purchaseReportError
                            ) {

                                console.error(
                                    purchaseReportError
                                );


                                purchaseReportResult.innerHTML = `

                            <div class="alert alert-danger">

                                <i class="fas fa-exclamation-circle"></i>

                                Unable to load Purchase Report.

                                Please try again.

                            </div>

                        `;


                                purchaseReportSearchButton.disabled =
                                    false;


                                purchaseReportSearchButton.innerHTML =
                                    '<i class="fas fa-search"></i> Search';

                            }
                        );

                }
            );


            /*
            |--------------------------------------------------------------------------
            | Reset
            |--------------------------------------------------------------------------
            */

            purchaseReportResetButton.addEventListener(
                'click',
                function() {

                    /*
                    |--------------------------------------------------------------------------
                    | Reset Form
                    |--------------------------------------------------------------------------
                    */

                    purchaseReportForm.reset();


                    /*
                    |--------------------------------------------------------------------------
                    | Reset Result
                    |--------------------------------------------------------------------------
                    */

                    purchaseReportResult.innerHTML = `

                    <div class="text-center text-muted py-5">

                        <i class="fas fa-search fa-2x mb-3"></i>

                        <div>

                            Select filters and click
                            <strong>Search</strong>
                            to generate the Purchase Report.

                        </div>

                    </div>

                `;


                    /*
                    |--------------------------------------------------------------------------
                    | Clear Session Filters
                    |--------------------------------------------------------------------------
                    |
                    | The controller needs a small endpoint for this
                    | if you want Reset to clear the server session too.
                    |
                    |--------------------------------------------------------------------------
                    */

                    fetch(
                        @json(
                            route('purchase-reports.index')
                        ), {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        }
                    );

                }
            );


        }
    );
</script>

@endpush