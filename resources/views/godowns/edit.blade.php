@extends('layouts.app')

@section('content')

<div class="container-fluid">

    @include('godowns.form', [
    'godown' => $godown,
    'route' => route('godowns.update', $godown),
    'method' => 'PUT'
    ])

</div>

@endsection