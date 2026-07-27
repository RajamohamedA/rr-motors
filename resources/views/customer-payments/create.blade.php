@extends('layouts.app')

@section('title','Receive Customer Payment')

@section('content')

<form action="{{ route('customer-payments.store') }}"
    method="POST">

    @php
    $payment = null;
    @endphp

    @include('customer-payments.form')

</form>

@endsection

@vite([
'resources/js/customer-payment.js'
])