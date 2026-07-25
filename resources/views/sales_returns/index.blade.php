@extends('layouts.app')
@section('title','Sales Return List')
@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header bg-danger text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0"> <i class="fas fa-undo">

                </i> Sales Return List </h4> 
                <a href="{{ route('sales-returns.create') }}" class="btn btn-light"> <i class="fas fa-plus"></i> New Return </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">Return No</th>
                            <th width="12%">Return Date</th>
                            <th width="15%">Invoice No</th>
                            <th>Customer</th>
                            <th width="15%" class="text-end"> Return Amount </th>
                            <th width="10%" class="text-center"> Status </th>
                            <th width="18%" class="text-center"> Action </th>
                        </tr>
                    </thead>
                    <tbody> @forelse($returns as $return)
                         <tr>
                            <td> {{ $loop->iteration + ($returns->currentPage()-1) * $returns->perPage() }} </td>
                            <td> <strong> {{ $return->return_no }} </strong> </td>
                            <td> {{ date('d-m-Y',strtotime($return->return_date)) }} </td>
                            <td> {{ $return->sale->invoice_no ?? '-' }} </td>
                            <td> {{ $return->customer->customer_name ?? '-' }} </td>
                            <td class="text-end fw-bold text-danger"> {{ number_format($return->total_amount,2) }} </td>
                            <td class="text-center"> <span class="badge bg-success"> Completed </span> </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm"> <a href="{{ route('sales-returns.show',$return->id) }}" class="btn btn-info" title="View"> <i class="fas fa-eye"></i> </a> <a href="{{ route('sales-returns.print',$return->id) }}" target="_blank" class="btn btn-secondary" title="Print"> <i class="fas fa-print"></i> </a> <a href="{{ route('sales-returns.edit',$return->id) }}" class="btn btn-warning" title="Edit"> <i class="fas fa-edit"></i> </a>
                                    <form action="{{ route('sales-returns.destroy',$return->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this Sales Return?')"> 
                                        @csrf @method('DELETE') 
                                        <button type="submit" class="btn btn-danger btn-sm" title="Delete"> 
                                            <i class="fas fa-trash"></i> 
                                        </button> 
                                    </form>
                                </div>
                            </td>
                        </tr> @empty <tr>
                            <td colspan="8" class="text-center text-muted py-4"> <i class="fas fa-undo fa-2x mb-2"></i> <br> No Sales Return Found. </td>
                        </tr> @endforelse </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end mt-3"> {{ $returns->links() }} </div>
        </div>
    </div>
</div>
@endsection