@extends('layouts.app')

@section('title','View Quotation')

@section('content')

<div class="card shadow">

    <div class="card-header bg-primary text-white">

        <div class="d-flex justify-content-between">

            <h4>

                <i class="fas fa-file-alt"></i>

                Quotation Details

            </h4>

            <div>

                @can('quotation.edit')
                <a href="{{ route('quotations.edit',$quotation->id) }}"
                    class="btn btn-warning">

                    <i class="fas fa-edit"></i>

                    Edit

                </a>
                @endcan

                @can('quotation.print')
                <a href="{{ route('quotations.print',$quotation->id) }}"
                    target="_blank"
                    class="btn btn-primary">

                    <i class="fas fa-print"></i>

                    Print

                </a>
                @endcan

                @can('quotation.convert-sale')
                <a href="{{ route('quotations.convert',$quotation->id) }}"
                    class="btn btn-success"
                    onclick="return confirm('Convert this quotation to Sales Invoice?')">

                    <i class="fas fa-exchange-alt"></i>

                    Convert To Sales

                </a>
                @endcan



                <a href="{{ route('quotations.index') }}"
                    class="btn btn-light">

                    Back

                </a>



            </div>

        </div>

    </div>

    <div class="card-body">

        <div class="row mb-4">

            <div class="col-md-4">

                <strong>Quotation No</strong>

                <br>

                {{ $quotation->quotation_no }}

            </div>

            <div class="col-md-4">

                <strong>Date</strong>

                <br>

                {{ $quotation->quotation_date }}

            </div>

            <div class="col-md-4">

                <strong>Valid Until</strong>

                <br>

                {{ $quotation->valid_until }}

            </div>

        </div>

        <div class="row mb-4">

            <div class="col-md-4">

                <strong>Company</strong>

                <br>

                {{ $quotation->company->company_name }}

            </div>

            <div class="col-md-4">

                <strong>Store</strong>

                <br>

                {{ $quotation->store->store_name }}

            </div>

            <div class="col-md-4">

                <strong>Customer</strong>

                <br>

                {{ $quotation->customer->customer_name }}

            </div>

        </div>

        <table class="table table-bordered">

            <thead class="table-dark">

                <tr>

                    <th>#</th>

                    <th>Code</th>

                    <th>Product</th>

                    <th>Unit</th>

                    <th class="text-end">Qty</th>

                    <th class="text-end">Price</th>

                    <th class="text-end">GST%</th>

                    <th class="text-end">GST Amount</th>

                    <th class="text-end">Total</th>

                </tr>

            </thead>

            <tbody>

                @foreach($quotation->items as $item)

                <tr>

                    <td>{{ $loop->iteration }}</td>

                    <td>{{ $item->product->product_code }}</td>

                    <td>{{ $item->product->product_name }}</td>

                    <td>{{ $item->product->unit->short_name ?? '' }}</td>

                    <td class="text-end">{{ $item->quantity }}</td>

                    <td class="text-end">{{ number_format($item->sale_price,2) }}</td>

                    <td class="text-end">{{ number_format($item->gst_percent,2) }}</td>

                    <td class="text-end">{{ number_format($item->gst_amount,2) }}</td>

                    <td class="text-end">{{ number_format($item->line_total,2) }}</td>

                </tr>

                @endforeach

            </tbody>

            <tfoot>

                <tr>

                    <th colspan="8" class="text-end">

                        Sub Total

                    </th>

                    <th class="text-end">

                        {{ number_format($quotation->sub_total,2) }}

                    </th>

                </tr>

                <tr>

                    <th colspan="8" class="text-end">

                        GST Total

                    </th>

                    <th class="text-end">

                        {{ number_format($quotation->gst_total,2) }}

                    </th>

                </tr>

                <tr>

                    <th colspan="8" class="text-end">

                        Grand Total

                    </th>

                    <th class="text-end">

                        {{ number_format($quotation->grand_total,2) }}

                    </th>

                </tr>

            </tfoot>

        </table>

        @if($quotation->remarks)

        <div class="mt-3">

            <strong>Remarks</strong>

            <br>

            {{ $quotation->remarks }}

        </div>

        @endif

    </div>

</div>

@endsection