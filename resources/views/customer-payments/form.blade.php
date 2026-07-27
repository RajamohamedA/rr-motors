@csrf

<div class="card shadow-sm mb-3">

    <div class="card-header bg-success text-white">

        <h5 class="mb-0">

            <i class="fas fa-money-check-alt"></i>

            {{ isset($payment) ? 'Edit Customer Payment' : 'Receive Customer Payment' }}

        </h5>

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-3 mb-3">

                <label class="form-label">Receipt No</label>

                <input type="text"
                    class="form-control"
                    readonly
                    value="{{ old('receipt_no',$payment->receipt_no ?? $receiptNo) }}">

            </div>

            <div class="col-md-3 mb-3">

                <label class="form-label">Payment Date</label>

                <input type="date"
                    class="form-control"
                    name="payment_date"
                    value="{{ old('payment_date',isset($payment)?\Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d'):date('Y-m-d')) }}">

                @error('payment_date')

                <small class="text-danger">{{ $message }}</small>

                @enderror

            </div>

            <div class="col-md-3 mb-3">

                <label class="form-label">Company</label>

                <select class="form-select" name="company_id">

                    <option value="">Select Company</option>

                    @foreach($companies as $company)

                    <option value="{{ $company->id }}"
                        {{ old('company_id',$payment->company_id ?? '')==$company->id?'selected':'' }}>

                        {{ $company->company_name }}

                    </option>

                    @endforeach

                </select>

                @error('company_id')

                <small class="text-danger">{{ $message }}</small>

                @enderror

            </div>

            <div class="col-md-3 mb-3">

                <label class="form-label">Store</label>

                <select class="form-select" name="store_id">

                    <option value="">Select Store</option>

                    @foreach($stores as $store)

                    <option value="{{ $store->id }}"
                        {{ old('store_id',$payment->store_id ?? '')==$store->id?'selected':'' }}>

                        {{ $store->store_name }}

                    </option>

                    @endforeach

                </select>

                @error('store_id')

                <small class="text-danger">{{ $message }}</small>

                @enderror

            </div>

        </div>

    </div>

</div>

@if(!isset($payment))

<div class="card shadow-sm mb-3">

    <div class="card-header bg-primary text-white">

        Search Sales Invoice

    </div>

    <div class="card-body">

        <input type="text"
            id="invoice_search"
            class="form-control form-control-lg"
            autocomplete="off"
            placeholder="Invoice Number">

        <div id="invoiceResult"
            class="list-group mt-2"
            style="display:none;max-height:300px;overflow-y:auto;">
        </div>

    </div>

</div>

@endif

<input type="hidden"
    id="searchUrl"
    value="{{ route('customer-payments.search') }}">

<input type="hidden"
    id="invoiceUrl"
    value="{{ url('customer-payments/invoice') }}">

<input type="hidden"
    id="sale_id"
    name="sale_id"
    value="{{ old('sale_id',$payment->sale_id ?? '') }}">

<input type="hidden"
    id="customer_id"
    name="customer_id"
    value="{{ old('customer_id',$payment->customer_id ?? '') }}">

<div class="card shadow-sm mb-3">

    <div class="card-header bg-info text-white">

        Customer Information

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-4">

                <label>Customer</label>

                <input type="text"
                    id="customer_name"
                    class="form-control"
                    readonly
                    value="{{ old('customer_name',$payment->customer->customer_name ?? '') }}">

            </div>

            <div class="col-md-4">

                <label>Mobile</label>

                <input type="text"
                    id="customer_mobile"
                    class="form-control"
                    readonly
                    value="{{ old('customer_mobile',$payment->customer->mobile ?? '') }}">

            </div>

            <div class="col-md-4">

                <label>Invoice Total</label>

                <input type="text"
                    id="grand_total"
                    class="form-control"
                    readonly
                    value="{{ old('grand_total',$payment->sale->grand_total ?? '') }}">

            </div>

        </div>

    </div>

</div>

<div class="card shadow-sm">

    <div class="card-header bg-warning">

        <h5 class="mb-0">

            <i class="fas fa-wallet"></i>

            Payment Details

        </h5>

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-3 mb-3">

                <label class="form-label">

                    Paid Till Now

                </label>

                <input type="text"
                    id="paid_amount"
                    class="form-control"
                    readonly
                    value="{{ old('paid_amount',$payment->sale->paid_amount ?? '') }}">

            </div>

            <div class="col-md-3 mb-3">

                <label class="form-label">

                    Balance Amount

                </label>

                <input type="text"
                    id="balance_amount"
                    class="form-control"
                    readonly
                    value="{{ old('balance_amount',$payment->sale->balance_amount ?? '') }}">

            </div>

            <div class="col-md-3 mb-3">

                <label class="form-label">

                    Receive Amount

                </label>

                <input type="number"
                    step="0.01"
                    id="receive_amount"
                    name="amount"
                    class="form-control"
                    value="{{ old('amount',$payment->amount ?? '') }}">

                @error('amount')

                <small class="text-danger">

                    {{ $message }}

                </small>

                @enderror

            </div>

            <div class="col-md-3 mb-3">

                <label class="form-label">

                    Payment Mode

                </label>

                <select class="form-select"
                    name="payment_mode">

                    <option value="Cash"
                        {{ old('payment_mode',$payment->payment_mode ?? '')=='Cash'?'selected':'' }}>

                        Cash

                    </option>

                    <option value="UPI"
                        {{ old('payment_mode',$payment->payment_mode ?? '')=='UPI'?'selected':'' }}>

                        UPI

                    </option>

                    <option value="Bank"
                        {{ old('payment_mode',$payment->payment_mode ?? '')=='Bank'?'selected':'' }}>

                        Bank

                    </option>

                    <option value="Cheque"
                        {{ old('payment_mode',$payment->payment_mode ?? '')=='Cheque'?'selected':'' }}>

                        Cheque

                    </option>

                </select>

                @error('payment_mode')

                <small class="text-danger">

                    {{ $message }}

                </small>

                @enderror

            </div>

        </div>

        <div class="row">

            <div class="col-md-6 mb-3">

                <label class="form-label">

                    Reference No

                </label>

                <input type="text"
                    name="reference_no"
                    class="form-control"
                    value="{{ old('reference_no',$payment->reference_no ?? '') }}">

                @error('reference_no')

                <small class="text-danger">

                    {{ $message }}

                </small>

                @enderror

            </div>

            <div class="col-md-6 mb-3">

                <label class="form-label">

                    Remarks

                </label>

                <textarea name="remarks"
                    rows="3"
                    class="form-control">{{ old('remarks',$payment->remarks ?? '') }}</textarea>

                @error('remarks')

                <small class="text-danger">

                    {{ $message }}

                </small>

                @enderror

            </div>

        </div>

    </div>

</div>

<hr>

<div class="row mt-4">

    <div class="col-md-12 text-end">

        <a href="{{ route('customer-payments.index') }}"
            class="btn btn-secondary">

            <i class="fas fa-arrow-left"></i>

            Cancel

        </a>

        <button type="submit"
            class="btn btn-success">

            <i class="fas fa-save"></i>

            {{ isset($payment) ? 'Update Payment' : 'Receive Payment' }}

        </button>

    </div>

</div>