@extends('layouts.app')

@section('content')

<div class="container-fluid">

    @include('users.form',[
    'user' => $user,
    'route' => route('users.update',$user),
    'method' => 'PUT'
    ])

</div>

@endsection