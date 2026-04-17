<div class="row">

    <!-- Company Name -->
    <div class="col-md-6 mb-3">
        <label class="form-label">Company Name <span class="text-danger">*</span></label>
        <input type="text"
               name="company_name"
               class="form-control @error('company_name') is-invalid @enderror"
               value="{{ old('company_name', $company->company_name ?? '') }}">

        @error('company_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Owner -->
    <div class="col-md-6 mb-3">
        <label class="form-label">Owner Name</label>
        <input type="text"
               name="owner_name"
               class="form-control"
               value="{{ old('owner_name', $company->owner_name ?? '') }}">
    </div>

    <!-- GST -->
    <div class="col-md-4 mb-3">
        <label class="form-label">GST Number</label>
        <input type="text"
               name="gst_number"
               class="form-control"
               value="{{ old('gst_number', $company->gst_number ?? '') }}">
    </div>

    <!-- PAN -->
    <div class="col-md-4 mb-3">
        <label class="form-label">PAN Number</label>
        <input type="text"
               name="pan_number"
               class="form-control"
               value="{{ old('pan_number', $company->pan_number ?? '') }}">
    </div>

    <!-- Phone -->
    <div class="col-md-4 mb-3">
        <label class="form-label">Phone</label>
        <input type="text"
               name="phone"
               class="form-control"
               value="{{ old('phone', $company->phone ?? '') }}">
    </div>

    <!-- Email -->
    <div class="col-md-6 mb-3">
        <label class="form-label">Email</label>
        <input type="email"
               name="email"
               class="form-control"
               value="{{ old('email', $company->email ?? '') }}">
    </div>

    <!-- Website -->
    <div class="col-md-6 mb-3">
        <label class="form-label">Website</label>
        <input type="text"
               name="website"
               class="form-control"
               value="{{ old('website', $company->website ?? '') }}">
    </div>

    <!-- Address -->
    <div class="col-md-12 mb-3">
        <label class="form-label">Address</label>
        <textarea name="address"
                  rows="3"
                  class="form-control">{{ old('address', $company->address ?? '') }}</textarea>
    </div>

    <!-- City -->
    <div class="col-md-3 mb-3">
        <label class="form-label">City <span class="text-danger">*</span></label>

        <input type="text"
            name="city"
            value="{{ old('city', $company->city ?? '') }}"
            class="form-control @error('city') is-invalid @enderror">

        @error('city')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>

    <!-- State -->
    <div class="col-md-3 mb-3">
        <label class="form-label">State<span class="text-danger">*</span></label>
        <input type="text"
               name="state"
               class="form-control @error('city') is-invalid @enderror"
               value="{{ old('state', $company->state ?? '') }}">

        @error('state')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>

    <!-- Country -->
    <div class="col-md-3 mb-3">
        <label class="form-label">Country<span class="text-danger">*</span></label>
       
            <input type="text"
            name="country"
            class="form-control @error('country') is-invalid @enderror"
            value="{{ old('country', $company->country ?? 'India') }}">
            @error('country')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
    </div>

    <!-- Pincode -->
    <div class="col-md-3 mb-3">
        <label class="form-label">Pincode</label>
        <input type="text"
               name="pincode"
               class="form-control"
               value="{{ old('pincode', $company->pincode ?? '') }}">
    </div>

    <!-- Logo -->
    <div class="col-md-6 mb-3">
        <label class="form-label">Company Logo</label>
        <input type="file"
               name="logo"
               class="form-control">
    </div>

    <!-- Status -->
    <div class="col-md-6 mb-3">
        <label class="form-label">Status<span class="text-danger">*</span></label>

        <select name="status" class="form-select @error('country') is-invalid @enderror">
            <option value="1" {{ old('status', $company->status ?? 1) ? 'selected' : '' }}>
                Active
            </option>

            <option value="0" {{ old('status', $company->status ?? 1) == 0 ? 'selected' : '' }}>
                Inactive
            </option>
        </select>
        @error('status')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>

</div>