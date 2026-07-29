@extends('layouts.app')

@section('content')

<div class="container-fluid">

    @include('roles.form', [
    'role' => null,
    'route' => route('roles.store'),
    'method' => 'POST'
    ])

</div>

@endsection