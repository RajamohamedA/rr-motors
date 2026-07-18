@extends('layouts.app')

@section('title', 'My Profile')

@section('content')

<div class="container-fluid">

    <h3 class="mb-4">My Profile</h3>

    <div class="row">

        <div class="col-lg-6 mb-4">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="col-lg-6 mb-4">
            @include('profile.partials.update-password-form')
        </div>

    </div>

    <div class="row">

        <div class="col-lg-6">
            @include('profile.partials.delete-user-form')
        </div>

    </div>

</div>

@endsection