@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h4>
            <i class="bi bi-box-seam"></i>
            Product Details
        </h4>

        <a href="{{ route('products.index') }}"
            class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i>
            Back
        </a>

    </div>

    <div class="card">

        <div class="card-header">
            Product Information
        </div>

        <div class="card-body">

            <table class="table table-bordered">

                <tr>
                    <th width="25%">Product Code</th>
                    <td>{{ $product->product_code }}</td>
                </tr>

                <tr>
                    <th>Product Name</th>
                    <td>{{ $product->product_name }}</td>
                </tr>

                <tr>
                    <th>Category</th>
                    <td>{{ $product->category->category_name ?? '-' }}</td>
                </tr>

                <tr>
                    <th>Unit</th>
                    <td>{{ $product->unit->unit_name ?? '-' }}</td>
                </tr>

                <tr>
                    <th>Purchase Price</th>
                    <td>{{ number_format($product->purchase_price, 2) }}</td>
                </tr>

                <tr>
                    <th>Selling Price</th>
                    <td>{{ number_format($product->selling_price, 2) }}</td>
                </tr>

                <tr>
                    <th>Status</th>
                    <td>
                        @if($product->status)
                        <span class="badge bg-success">Active</span>
                        @else
                        <span class="badge bg-danger">Inactive</span>
                        @endif
                    </td>
                </tr>

                <tr>
                    <th>Description</th>
                    <td>{{ $product->description }}</td>
                </tr>

                <tr>
                    <th>Created At</th>
                    <td>{{ $product->created_at }}</td>
                </tr>

                <tr>
                    <th>Updated At</th>
                    <td>{{ $product->updated_at }}</td>
                </tr>

            </table>

        </div>

    </div>

</div>

@endsection