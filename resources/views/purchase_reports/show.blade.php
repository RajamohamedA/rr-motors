@extends('layouts.app')

@section('title','Purchase Details')

@section('content')

<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header bg-primary text-white">

            <div class="d-flex justify-content-between">

                <h4>

                    <i class="fas fa-file-invoice"></i>

                    Purchase Details

                </h4>

                <div>

                    <a href="{{ route('purchase-reports.index') }}"
                        class="btn btn-light btn-sm">

                        <i class="fas fa-arrow-left"></i>

                        Back

                    </a>

                    <a href="{{ route('purchase-reports.print',$purchase->id) }}"
                        target="_blank"
                        class="btn btn-success btn-sm">

                        <i class="fas fa-print"></i>

                        Print

                    </a>

                </div>

            </div>

        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-4">

                    <table class="table table-bordered">

                        <tr>
                            <th>Purchase No</th>
                            <td>{{ $purchase->purchase_no }}</td>
                        </tr>

                        <tr>
                            <th>Date</th>
                            <td>{{ date('d-m-Y',strtotime($purchase->purchase_date)) }}</td>
                        </tr>

                        <tr>
                            <th>Store</th>
                            <td>{{ $purchase->store->store_name ?? '-' }}</td>
                        </tr>

                    </table>

                </div>

                <div class="col-md-4">

                    <table class="table table-bordered">

                        <tr>
                            <th>Supplier</th>
                            <td>{{ $purchase->supplier->supplier_name }}</td>
                        </tr>

                        <tr>
                            <th>Mobile</th>
                            <td>{{ $purchase->supplier->mobile }}</td>
                        </tr>

                        <tr>
                            <th>GST</th>
                            <td>{{ $purchase->supplier->gst_no }}</td>
                        </tr>

                    </table>

                </div>

                <div class="col-md-4">

                    <table class="table table-bordered">

                        <tr>
                            <th>Remarks</th>
                            <td>{{ $purchase->remarks }}</td>
                        </tr>

                    </table>

                </div>

            </div>