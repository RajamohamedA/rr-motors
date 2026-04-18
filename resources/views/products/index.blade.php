@extends('layouts.app')

@section('title','Products')

@section('content')

<div class="container-fluid mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h3 class="mb-0">Products</h3>

        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Product
        </a>

    </div>

    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show">

            {{ session('success') }}

            <button class="btn-close" data-bs-dismiss="alert"></button>

        </div>

    @endif

    <div class="card shadow-sm">

        <div class="card-body">

            <form method="GET"
                  action="{{ route('products.index') }}"
                  class="row mb-3">

                <div class="col-md-5">

                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           class="form-control"
                           placeholder="Search by Code, Name, Barcode, Part Number">

                </div>

                <div class="col-md-2">

                    <button class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>

                </div>

            </form>

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-dark">

                    <tr>

                        <th width="70">Image</th>

                        <th>Code</th>

                        <th>Product</th>

                        <th>Category</th>

                        <th>Brand</th>

                        <th>Unit</th>

                        <th>Stock</th>

                        <th>Purchase</th>

                        <th>Sale</th>

                        <th>Status</th>

                        <th width="140">Action</th>

                    </tr>

                    </thead>

                    <tbody>

                    @forelse($products as $product)

                        <tr>

                            <td>

                                @if($product->image)

                                    <img src="{{ asset('storage/'.$product->image) }}"
                                         width="50"
                                         height="50"
                                         class="rounded border">

                                @else

                                    <img src="{{ asset('images/no-image.png') }}"
                                         width="50"
                                         height="50"
                                         class="rounded border">

                                @endif

                            </td>

                            <td>{{ $product->product_code }}</td>

                            <td>

                                <strong>{{ $product->product_name }}</strong>

                                @if($product->part_number)

                                    <br>

                                    <small class="text-muted">
                                        {{ $product->part_number }}
                                    </small>

                                @endif

                            </td>

                            <td>{{ $product->category->name ?? '-' }}</td>

                            <td>{{ $product->brand->brand_name ?? '-' }}</td>

                            <td>{{ $product->unit->unit_name ?? '-' }}</td>

                            <td>{{ $product->opening_stock }}</td>

                            <td>₹ {{ number_format($product->purchase_price,2) }}</td>

                            <td>₹ {{ number_format($product->selling_price,2) }}</td>

                            <td>

                                @if($product->status)

                                    <span class="badge bg-success">
                                        Active
                                    </span>

                                @else

                                    <span class="badge bg-danger">
                                        Inactive
                                    </span>

                                @endif

                            </td>

                            <td>

                                <a href="{{ route('products.edit',$product) }}"
                                   class="btn btn-warning btn-sm">

                                    <i class="fas fa-edit"></i>

                                </a>

                                <form action="{{ route('products.destroy',$product) }}"
                                      method="POST"
                                      class="d-inline">

                                    @csrf

                                    @method('DELETE')

                                    <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('Delete this product?')">

                                        <i class="fas fa-trash"></i>

                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="11"
                                class="text-center">

                                No products found.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

            {{ $products->withQueryString()->links() }}

        </div>

    </div>

</div>

@endsection