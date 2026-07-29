@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between mb-3">

        <h4>
            Stock Adjustments
        </h4>

        <a href="{{ route('stock-adjustments.create') }}"
            class="btn btn-primary">

            <i class="bi bi-plus"></i>
            New Adjustment

        </a>

    </div>


    @if(session('success'))

    <div class="alert alert-success">
        {{ session('success') }}
    </div>

    @endif


    <div class="card">

        <div class="card-body">


            <table class="table table-bordered">

                <thead>

                    <tr>

                        <th>No</th>
                        <th>Date</th>
                        <th>Store</th>
                        <th>Type</th>
                        <th>Remarks</th>

                    </tr>

                </thead>


                <tbody>


                    @foreach($adjustments as $adjustment)

                    <tr>

                        <td>
                            {{ $adjustment->adjustment_no }}
                        </td>


                        <td>
                            {{ $adjustment->adjustment_date }}
                        </td>


                        <td>
                            {{ $adjustment->store->store_name ?? '' }}
                        </td>


                        <td>

                            @if($adjustment->type=='increase')

                            <span class="badge bg-success">
                                Increase
                            </span>

                            @else

                            <span class="badge bg-danger">
                                Decrease
                            </span>

                            @endif

                        </td>


                        <td>
                            {{ $adjustment->remarks }}
                        </td>


                    </tr>


                    @endforeach


                </tbody>


            </table>


        </div>

    </div>


</div>


@endsection