<div class="row">

    <div class="col-md-6 mb-3">

        <label class="form-label">
            Company <span class="text-danger">*</span>
        </label>

        <select
            name="company_id"
            class="form-select @error('company_id') is-invalid @enderror">

            <option value="">Select Company</option>

            @foreach($companies as $company)

                <option
                    value="{{ $company->id }}"
                    {{ old('company_id',$brand->company_id ?? '')==$company->id?'selected':'' }}>

                    {{ $company->company_name }}

                </option>

            @endforeach

        </select>

        @error('company_id')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror

    </div>

    <div class="col-md-6 mb-3">

        <label class="form-label">
            Brand Name <span class="text-danger">*</span>
        </label>

        <input
            type="text"
            name="brand_name"
            class="form-control @error('brand_name') is-invalid @enderror"
            value="{{ old('brand_name',$brand->brand_name ?? '') }}">

        @error('brand_name')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror

    </div>

    <div class="col-md-12 mb-3">

        <label class="form-label">Description</label>

        <textarea
            name="description"
            rows="4"
            class="form-control">{{ old('description',$brand->description ?? '') }}</textarea>

    </div>

    <div class="col-md-4 mb-3">

        <label class="form-label">Status</label>

        <select name="status" class="form-select">

            <option
                value="1"
                {{ old('status',$brand->status ?? 1)==1?'selected':'' }}>

                Active

            </option>

            <option
                value="0"
                {{ old('status',$brand->status ?? 1)==0?'selected':'' }}>

                Inactive

            </option>

        </select>

    </div>

</div>