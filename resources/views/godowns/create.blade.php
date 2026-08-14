@extends('layouts.app')

@section('content')

<div class="container-fluid">

    @include('godowns.form', [
    'godown' => null,
    'route' => route('godowns.store'),
    'method' => 'POST'
    ])

</div>

@endsection