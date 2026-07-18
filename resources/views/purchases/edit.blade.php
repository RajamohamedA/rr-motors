@extends('layouts.app')

@section('title', 'Edit Purchase')

@section('content')

<div class="container-fluid mt-4">

    <form action="{{ route('purchases.update',$purchase->id) }}" method="POST">

        @csrf
        @method('PUT')

        @include('purchases.form')

    </form>

</div>

@endsection