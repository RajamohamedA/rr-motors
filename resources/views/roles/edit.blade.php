@extends('layouts.app')

@section('content')

<div class="container-fluid">

    @include('roles.form', [
    'role' => $role,
    'route' => route('roles.update', $role),
    'method' => 'PUT'
    ])

</div>

@endsection