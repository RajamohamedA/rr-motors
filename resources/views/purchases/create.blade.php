@extends('layouts.app')

@section('title', 'Create Purchase')

@section('content')

<div class="container-fluid mt-4">

    <form action="{{ route('purchases.store') }}" method="POST">

        @csrf

        @include('purchases.form')

    </form>

</div>

@endsection