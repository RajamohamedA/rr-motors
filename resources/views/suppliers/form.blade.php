<div class="row">

    <div class="col-md-6 mb-3">
        <label class="form-label">Company *</label>

        <select name="company_id" class="form-select @error('company_id') is-invalid @enderror">

            <option value="">Select Company</option>

            @foreach($companies as $company)
                <option value="{{ $company->id }}"
                    {{ old('company_id',$supplier->company_id ?? '')==$company->id ? 'selected':'' }}>
                    {{ $company->company_name }}
                </option>
            @endforeach

        </select>

        @error('company_id')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Supplier Name *</label>

        <input type="text"
               name="supplier_name"
               class="form-control @error('supplier_name') is-invalid @enderror"
               value="{{ old('supplier_name',$supplier->supplier_name ?? '') }}">

        @error('supplier_name')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label>Contact Person</label>
        <input type="text" name="contact_person" class="form-control"
               value="{{ old('contact_person',$supplier->contact_person ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label>Phone</label>
        <input type="text" name="phone" class="form-control"
               value="{{ old('phone',$supplier->phone ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control"
               value="{{ old('email',$supplier->email ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label>GST Number</label>
        <input type="text" name="gst_number" class="form-control"
               value="{{ old('gst_number',$supplier->gst_number ?? '') }}">
    </div>

    <div class="col-md-12 mb-3">
        <label>Address</label>
        <textarea name="address" class="form-control">{{ old('address',$supplier->address ?? '') }}</textarea>
    </div>

    <div class="col-md-4 mb-3">
        <label>City *</label>
        <input type="text"
            name="city"
            value="{{ old('city', $supplier->city ?? '') }}"
            class="form-control @error('city') is-invalid @enderror">

        @error('city')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label>State *</label>
        <input type="text"
            name="state"
            value="{{ old('state', $supplier->state ?? '') }}"
            class="form-control @error('state') is-invalid @enderror">

        @error('state')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label>Pincode *</label>
        <input type="text"
            name="pincode"
            value="{{ old('pincode', $supplier->pincode ?? '') }}"
            class="form-control @error('pincode') is-invalid @enderror">

        @error('pincode')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label>Opening Balance</label>
        <input type="number" step="0.01"
               name="opening_balance"
               class="form-control"
               value="{{ old('opening_balance',$supplier->opening_balance ?? 0) }}">
    </div>

    <div class="col-md-4 mb-3">
        <label>Balance Type</label>

        <select name="balance_type" class="form-select">

            <option value="Dr"
                {{ old('balance_type',$supplier->balance_type ?? 'Cr')=='Dr'?'selected':'' }}>
                Debit
            </option>

            <option value="Cr"
                {{ old('balance_type',$supplier->balance_type ?? 'Cr')=='Cr'?'selected':'' }}>
                Credit
            </option>

        </select>

    </div>

    <div class="col-md-4 mb-3">

        <label>Status</label>

        <select name="status" class="form-select">

            <option value="1"
                {{ old('status',$supplier->status ?? 1)==1?'selected':'' }}>
                Active
            </option>

            <option value="0"
                {{ old('status',$supplier->status ?? 1)==0?'selected':'' }}>
                Inactive
            </option>

        </select>

    </div>

</div>