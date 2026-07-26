@extends('layouts.app')

@section('title','Edit Quotation')

@section('content')

<form action="{{ route('quotations.update',$quotation->id) }}" method="POST">

    @csrf
    @method('PUT')

    @include('quotations.form')

</form>

@endsection