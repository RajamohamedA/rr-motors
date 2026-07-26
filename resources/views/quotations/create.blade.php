@extends('layouts.app')

@section('title','Create Quotation')

@section('content')

<form action="{{ route('quotations.store') }}" method="POST">

    @include('quotations.form')

</form>

@endsection