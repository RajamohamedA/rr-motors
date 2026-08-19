@extends('layouts.app')

@section('title','Edit Sales Invoice')

@section('content')

<form action="{{ route('sales.update', $sale->id) }}" method="POST">

    @method('PUT')

    @include('sales.form')

</form>

@endsection