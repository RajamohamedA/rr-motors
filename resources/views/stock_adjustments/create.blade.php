@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between mb-3">

        <h4>
            <i class="bi bi-sliders"></i>
            Stock Adjustment
        </h4>

        <a href="{{ route('stock-adjustments.index') }}"
            class="btn btn-secondary">

            <i class="bi bi-arrow-left"></i>
            Back

        </a>

    </div>



    <div class="card">

        <div class="card-header bg-primary text-white">

            New Stock Adjustment

        </div>


        <div class="card-body">


            <form action="{{ route('stock-adjustments.store') }}"
                method="POST">

                @csrf


                <div class="row">


                    <!-- Store -->

                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            Store
                        </label>


                        <select name="store_id"
                            class="form-select"
                            required>


                            <option value="">
                                Select Store
                            </option>


                            @foreach($stores as $item)

                            <option value="{{ $item->id }}"
                                @if($selectedStore && $selectedStore->id == $item->id)
                                selected
                                @endif
                                >

                                {{ $item->store_name }}

                            </option>

                            @endforeach


                        </select>


                    </div>



                    <!-- Product -->


                    <div class="col-md-4 mb-3">


                        <label class="form-label">
                            Product
                        </label>


                        <select name="product_id"
                            id="product_id"
                            class="form-select"
                            required>

                            <option value="">Select Product</option>

                            @foreach($products as $item)

                            <option value="{{ $item->id }}"
                                data-cost="{{ $item->purchase_price }}"
                                data-stock="{{ $item->stock ?? 0 }}"
                                {{ ($selectedProduct && $selectedProduct->id == $item->id) ? 'selected' : '' }}>

                                {{ $item->product_name }}

                            </option>

                            @endforeach

                        </select>


                    </div>



                    <!-- Current Stock -->


                    <div class="col-md-4 mb-3">


                        <label class="form-label">
                            Current Stock
                        </label>


                        <input type="text"
                            id="current_stock"
                            class="form-control"
                            value="{{ $currentStock }}"
                            readonly>


                    </div>



                </div>





                <div class="row">


                    <!-- Adjustment Type -->

                    <div class="col-md-4 mb-3">


                        <label class="form-label d-block">

                            Adjustment Type

                        </label>


                        <div class="form-check form-check-inline">


                            <input class="form-check-input"
                                type="radio"
                                name="type"
                                value="increase"
                                checked>


                            <label class="form-check-label text-success">

                                Increase

                            </label>


                        </div>



                        <div class="form-check form-check-inline">


                            <input class="form-check-input"
                                type="radio"
                                name="type"
                                value="decrease">


                            <label class="form-check-label text-danger">

                                Decrease

                            </label>


                        </div>


                    </div>




                    <!-- Quantity -->

                    <div class="col-md-4 mb-3">


                        <label class="form-label">

                            Quantity

                        </label>


                        <input type="number"
                            step="0.01"
                            name="qty"
                            class="form-control"
                            required>


                    </div>




                    <!-- Unit Cost -->


                    <div class="col-md-4 mb-3">


                        <label class="form-label">

                            Unit Cost

                        </label>


                        <input type="number"
                            step="0.01"
                            name="unit_cost"
                            id="unit_cost"
                            class="form-control"
                            value="{{ $unitCost ?? 0 }}">


                    </div>


                </div>





                <!-- Remarks -->

                <div class="mb-3">


                    <label class="form-label">

                        Remarks

                    </label>


                    <textarea name="remarks"
                        class="form-control"
                        rows="3"></textarea>


                </div>





                <button type="submit"
                    class="btn btn-success">


                    <i class="bi bi-check-circle"></i>

                    Save Adjustment


                </button>



            </form>


        </div>

    </div>


</div>


@endsection

@vite([
'resources/js/stock-adjustment.js'
])