@extends('layouts.app')

@section('title','Dashboard')

@section('content')

<div class="row g-4">

    <div class="col-lg-3 col-md-6">

        <div class="card dashboard-card">

            <div class="card-body">

                <div class="icon bg-primary">

                    <i class="fas fa-box"></i>

                </div>

                <h6>Total Products</h6>

                <h2>1,250</h2>

            </div>

        </div>

    </div>

    <div class="col-lg-3 col-md-6">

        <div class="card dashboard-card">

            <div class="card-body">

                <div class="icon bg-success">

                    <i class="fas fa-file-invoice-dollar"></i>

                </div>

                <h6>Today's Sales</h6>

                <h2>₹45,200</h2>

            </div>

        </div>

    </div>

    <div class="col-lg-3 col-md-6">

        <div class="card dashboard-card">

            <div class="card-body">

                <div class="icon bg-warning">

                    <i class="fas fa-shopping-cart"></i>

                </div>

                <h6>Purchase</h6>

                <h2>₹30,450</h2>

            </div>

        </div>

    </div>

    <div class="col-lg-3 col-md-6">

        <div class="card dashboard-card">

            <div class="card-body">

                <div class="icon bg-danger">

                    <i class="fas fa-users"></i>

                </div>

                <h6>Customers</h6>

                <h2>320</h2>

            </div>

        </div>

    </div>

</div>

@endsection