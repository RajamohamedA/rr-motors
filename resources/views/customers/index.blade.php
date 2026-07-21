@extends('layouts.app')

@section('title','Customers')

@section('content')

<div class="container-fluid">

    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show">

            {{ session('success') }}

            <button class="btn-close" data-bs-dismiss="alert"></button>

        </div>

    @endif

    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">

            <h5 class="mb-0">
                <i class="fas fa-users me-2"></i>
                Customer List
            </h5>

            <a href="{{ route('customers.create') }}"
               class="btn btn-light">

                <i class="fas fa-plus"></i>

                New Customer

            </a>

        </div>

        <div class="card-body">

            <div class="row mb-3">

                <div class="col-md-4">

                    <input type="text"
                           id="customerSearch"
                           class="form-control"
                           placeholder="Search Customer...">

                </div>

            </div>

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle"
                       id="customerTable">

                    <thead class="table-dark">

                    <tr>

                        <th width="60">#</th>

                        <th>Code</th>

                        <th>Name</th>

                        <th>Mobile</th>

                        <th>City</th>

                        <th>Credit Limit</th>

                        <th>Status</th>

                        <th width="180">Action</th>

                    </tr>

                    </thead>

                    <tbody>

                    @forelse($customers as $customer)

                        <tr>

                            <td>{{ $loop->iteration + ($customers->currentPage()-1)*$customers->perPage() }}</td>

                            <td>{{ $customer->customer_code }}</td>

                            <td>{{ $customer->customer_name }}</td>

                            <td>{{ $customer->mobile }}</td>

                            <td>{{ $customer->city }}</td>

                            <td class="text-end">
                                {{ number_format($customer->credit_limit,2) }}
                            </td>

                            <td>

                                @if($customer->status)

                                    <span class="badge bg-success">
                                        Active
                                    </span>

                                @else

                                    <span class="badge bg-danger">
                                        Inactive
                                    </span>

                                @endif

                            </td>

                            <td>

                                <a href="{{ route('customers.show',$customer->id) }}"
                                   class="btn btn-info btn-sm">

                                    <i class="fas fa-eye"></i>

                                </a>

                                <a href="{{ route('customers.edit',$customer->id) }}"
                                   class="btn btn-warning btn-sm">

                                    <i class="fas fa-edit"></i>

                                </a>

                                <form action="{{ route('customers.destroy',$customer->id) }}"
                                      method="POST"
                                      class="d-inline">

                                    @csrf

                                    @method('DELETE')

                                    <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('Delete this customer?')">

                                        <i class="fas fa-trash"></i>

                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="8"
                                class="text-center text-muted">

                                No customers found.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-3">

                {{ $customers->links() }}

            </div>

        </div>

    </div>

</div>

@endsection

@push('scripts')

<script>

$('#customerSearch').on('keyup', function () {

    let value = $(this).val().toLowerCase();

    $('#customerTable tbody tr').filter(function () {

        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);

    });

});

</script>

@endpush