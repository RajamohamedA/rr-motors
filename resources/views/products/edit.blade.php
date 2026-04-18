@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')

<div class="container-fluid mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h3>Edit Product</h3>

        <a href="{{ route('products.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>

    </div>

    @if ($errors->any())

        <div class="alert alert-danger">

            <strong>Please fix the following errors:</strong>

            <ul class="mb-0">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    <form action="{{ route('products.update', $product->id) }}"
          method="POST"
          enctype="multipart/form-data">

        @csrf
        @method('PUT')

        @include('products.form')

        <div class="card mt-3">

            <div class="card-body text-end">

                <button type="submit" class="btn btn-success">

                    <i class="fas fa-save"></i>

                    Update Product

                </button>

            </div>

        </div>

    </form>

</div>

@endsection

@push('scripts')

<script>

$(document).ready(function () {

    $('#company_id').change(function () {

        let companyId = $(this).val();

        if(companyId == ''){

            $('#store_id').html('<option value="">Select Store</option>');
            return;

        }

        $.get('/get-stores/' + companyId, function (stores) {

            let options = '<option value="">Select Store</option>';

            $.each(stores, function (index, store) {

                let selected = (store.id == "{{ old('store_id', $product->store_id) }}")
                    ? 'selected'
                    : '';

                options += '<option value="' + store.id + '" ' + selected + '>' +
                    store.store_name +
                    '</option>';

            });

            $('#store_id').html(options);

        });

    });

    $('#company_id').trigger('change');

});

</script>

@endpush