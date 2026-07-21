@csrf

<div class="card shadow-sm mb-3">

    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="fas fa-user"></i>
            Customer Information
        </h5>
    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-3 mb-3">
                <label class="form-label">Customer Code <span class="text-danger">*</span></label>
                <input type="text"
                       name="customer_code"
                       class="form-control"
                       value="{{ old('customer_code', $customer->customer_code ?? $customerCode) }}"
                       readonly>
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label">Company <span class="text-danger">*</span></label>

                <select name="company_id" class="form-select" required>

                    <option value="">Select Company</option>

                    @foreach($companies as $company)

                        <option value="{{ $company->id }}"
                            {{ old('company_id', $customer->company_id ?? '') == $company->id ? 'selected' : '' }}>

                            {{ $company->company_name }}

                        </option>

                    @endforeach

                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Customer Name <span class="text-danger">*</span></label>

                <input type="text"
                       name="customer_name"
                       class="form-control"
                       value="{{ old('customer_name', $customer->customer_name ?? '') }}"
                       required>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Contact Person</label>

                <input type="text"
                       name="contact_person"
                       class="form-control"
                       value="{{ old('contact_person', $customer->contact_person ?? '') }}">
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Mobile <span class="text-danger">*</span></label>

                <input type="text"
                       name="mobile"
                       class="form-control"
                       value="{{ old('mobile', $customer->mobile ?? '') }}"
                       required>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Phone</label>

                <input type="text"
                       name="phone"
                       class="form-control"
                       value="{{ old('phone', $customer->phone ?? '') }}">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Email</label>

                <input type="email"
                       name="email"
                       class="form-control"
                       value="{{ old('email', $customer->email ?? '') }}">
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label">GST Number</label>

                <input type="text"
                       name="gst_number"
                       class="form-control"
                       value="{{ old('gst_number', $customer->gst_number ?? '') }}">
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label">PAN Number</label>

                <input type="text"
                       name="pan_number"
                       class="form-control"
                       value="{{ old('pan_number', $customer->pan_number ?? '') }}">
            </div>

        </div>

    </div>

</div>

<div class="card shadow-sm mb-3">

    <div class="card-header bg-secondary text-white">
        Address
    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-6 mb-3">
                <label class="form-label">Billing Address</label>

                <textarea name="billing_address"
                          class="form-control"
                          rows="3">{{ old('billing_address', $customer->billing_address ?? '') }}</textarea>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Shipping Address</label>

                <textarea name="shipping_address"
                          class="form-control"
                          rows="3">{{ old('shipping_address', $customer->shipping_address ?? '') }}</textarea>
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label">City</label>

                <input type="text"
                       name="city"
                       class="form-control"
                       value="{{ old('city', $customer->city ?? '') }}">
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label">State</label>

                <input type="text"
                       name="state"
                       class="form-control"
                       value="{{ old('state', $customer->state ?? '') }}">
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label">Country</label>

                <input type="text"
                       name="country"
                       class="form-control"
                       value="{{ old('country', $customer->country ?? 'India') }}">
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label">Pincode</label>

                <input type="text"
                       name="pincode"
                       class="form-control"
                       value="{{ old('pincode', $customer->pincode ?? '') }}">
            </div>

        </div>

    </div>

</div>

<div class="card shadow-sm mb-3">

    <div class="card-header bg-success text-white">
        Financial Details
    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-3 mb-3">
                <label class="form-label">Credit Limit</label>

                <input type="number"
                       step="0.01"
                       name="credit_limit"
                       class="form-control"
                       value="{{ old('credit_limit', $customer->credit_limit ?? 0) }}">
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label">Credit Days</label>

                <input type="number"
                       name="credit_days"
                       class="form-control"
                       value="{{ old('credit_days', $customer->credit_days ?? 0) }}">
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label">Opening Balance</label>

                <input type="number"
                       step="0.01"
                       name="opening_balance"
                       class="form-control"
                       value="{{ old('opening_balance', $customer->opening_balance ?? 0) }}">
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label">Balance Type</label>

                <select name="balance_type" class="form-select">

                    <option value="Dr"
                        {{ old('balance_type', $customer->balance_type ?? 'Dr') == 'Dr' ? 'selected' : '' }}>
                        Debit
                    </option>

                    <option value="Cr"
                        {{ old('balance_type', $customer->balance_type ?? '') == 'Cr' ? 'selected' : '' }}>
                        Credit
                    </option>

                </select>

            </div>

            <div class="col-md-12 mb-3">
                <label class="form-label">Remarks</label>

                <textarea name="remarks"
                          class="form-control"
                          rows="3">{{ old('remarks', $customer->remarks ?? '') }}</textarea>
            </div>

        </div>

    </div>

</div>

<div class="text-end">

    <button type="submit" class="btn btn-success btn-lg">

        <i class="fas fa-save"></i>

        {{ isset($customer) ? 'Update Customer' : 'Save Customer' }}

    </button>

</div>