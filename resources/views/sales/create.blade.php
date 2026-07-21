@extends('layouts.app')

@section('title','Create Sales Invoice')

@section('content')

<form action="{{ route('sales.store') }}" method="POST">

    @include('sales.form')

</form>

@endsection 