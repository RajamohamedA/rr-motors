@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="container-fluid">

    <h2>Dashboard</h2>

    <div class="alert alert-success">
        Welcome {{ auth()->user()->name }}
    </div>

</div>

@endsection