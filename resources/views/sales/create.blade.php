@extends('layouts.app')

@section('title', 'Create Sales Invoice')

@section('content')

<form action="{{ route('sales.store') }}"
      method="POST"
      id="salesForm">

    @csrf

    @include('sales.form')

</form>

@endsection