@extends('layouts.app')

@section('title','Create Customer')

@section('content')

<form action="{{ route('customers.store') }}" method="POST">

    @include('customers.form')

</form>

@endsection