@extends('layouts.app')

@section('content')

<div class="container-fluid">

    @include('users.form', [
    'user' => null,
    'route' => route('users.store'),
    'method' => 'POST'
    ])

</div>

@endsection