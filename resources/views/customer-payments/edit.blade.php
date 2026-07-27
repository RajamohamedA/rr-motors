@extends('layouts.app')

@section('title','Edit Customer Payment')

@section('content')

<form action="{{ route('customer-payments.update',$payment->id) }}"
    method="POST">

    @csrf
    @method('PUT')

    @include('customer-payments.form')

</form>

@endsection

@vite([
'resources/js/customer-payment.js'
])