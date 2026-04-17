@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Validation Errors:</strong>

        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">

    <div class="col-md-6 mb-3">
        <label class="form-label">Company <span class="text-danger">*</span></label>

        <select name="company_id"
                class="form-select @error('company_id') is-invalid @enderror">

            <option value="">Select Company</option>

            @foreach($companies as $company)

                <option value="{{ $company->id }}"
                    {{ old('company_id',$store->company_id ?? '')==$company->id?'selected':'' }}>

                    {{ $company->company_name }}

                </option>

            @endforeach

        </select>

        @error('company_id')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Store Name <span class="text-danger">*</span></label>

        <input type="text"
               name="store_name"
               class="form-control @error('store_name') is-invalid @enderror"
               value="{{ old('store_name',$store->store_name ?? '') }}">

        @error('store_name')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Contact Person</label>

        <input type="text"
               name="contact_person"
               class="form-control"
               value="{{ old('contact_person',$store->contact_person ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Phone</label>

        <input type="text"
               name="phone"
               class="form-control"
               value="{{ old('phone',$store->phone ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Email</label>

        <input type="email"
               name="email"
               class="form-control"
               value="{{ old('email',$store->email ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">City <span class="text-danger">*</span></label>

        <input type="text"
               name="city"
               class="form-control @error('city') is-invalid @enderror"
               value="{{ old('city',$store->city ?? '') }}">

        @error('city')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">State <span class="text-danger">*</span></label>

        <input type="text"
               name="state"
               class="form-control @error('state') is-invalid @enderror"
               value="{{ old('state',$store->state ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Country</label>

        <input type="text"
               name="country"
               class="form-control"
               value="{{ old('country',$store->country ?? 'India') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Pincode <span class="text-danger">*</span></label>

        <input type="text"
               name="pincode"
               class="form-control @error('pincode') is-invalid @enderror"
               value="{{ old('pincode',$store->pincode ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Address</label>

        <textarea name="address"
                  rows="3"
                  class="form-control">{{ old('address',$store->address ?? '') }}</textarea>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Status</label>

        <select name="status" class="form-select">

            <option value="1"
                {{ old('status',$store->status ?? 1)==1?'selected':'' }}>
                Active
            </option>

            <option value="0"
                {{ old('status',$store->status ?? 1)==0?'selected':'' }}>
                Inactive
            </option>

        </select>

    </div>

</div>

