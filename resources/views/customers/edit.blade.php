@extends('layouts.app')

@section('title','Edit Customer')

@section('content')

<form action="{{ route('customers.update',$customer->id) }}" method="POST">

    @csrf

    @method('PUT')

    @include('customers.form')

</form>

@endsection