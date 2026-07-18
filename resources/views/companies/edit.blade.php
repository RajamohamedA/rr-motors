@extends('layouts.app')

@section('title', 'Company Information')

@section('content')

<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header bg-primary text-white d-flex justify-content-between">

            <h4 class="mb-0">
                Company Information
            </h4>

        </div>

        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('company.update') }}" method="POST" enctype="multipart/form-data">

                @csrf

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Company Name *</label>
                        <input type="text"
                               name="company_name"
                               class="form-control"
                               value="{{ old('company_name', $company->company_name ?? '') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Owner Name</label>
                        <input type="text"
                               name="owner_name"
                               class="form-control"
                               value="{{ old('owner_name', $company->owner_name ?? '') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">GST Number</label>
                        <input type="text"
                               name="gst_number"
                               class="form-control"
                               value="{{ old('gst_number', $company->gst_number ?? '') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">PAN Number</label>
                        <input type="text"
                               name="pan_number"
                               class="form-control"
                               value="{{ old('pan_number', $company->pan_number ?? '') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email"
                               name="email"
                               class="form-control"
                               value="{{ old('email', $company->email ?? '') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text"
                               name="phone"
                               class="form-control"
                               value="{{ old('phone', $company->phone ?? '') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Website</label>
                        <input type="text"
                               name="website"
                               class="form-control"
                               value="{{ old('website', $company->website ?? '') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Country</label>
                        <input type="text"
                               name="country"
                               class="form-control"
                               value="{{ old('country', $company->country ?? 'India') }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">City</label>
                        <input type="text"
                               name="city"
                               class="form-control"
                               value="{{ old('city', $company->city ?? '') }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">State</label>
                        <input type="text"
                               name="state"
                               class="form-control"
                               value="{{ old('state', $company->state ?? '') }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Pincode</label>
                        <input type="text"
                               name="pincode"
                               class="form-control"
                               value="{{ old('pincode', $company->pincode ?? '') }}">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address"
                                  rows="3"
                                  class="form-control">{{ old('address', $company->address ?? '') }}</textarea>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Company Logo</label>
                        <input type="file"
                               name="logo"
                               class="form-control">
                    </div>

                </div>

                <hr>

                <button class="btn btn-primary">
                    <i class="bi bi-check-circle"></i>
                    Save Company
                </button>

                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                    Cancel
                </a>

            </form>

        </div>

    </div>

</div>

@endsection