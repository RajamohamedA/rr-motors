<div class="row">

    <div class="col-md-6 mb-3">
        <label class="form-label">Company <span class="text-danger">*</span></label>

        <select name="company_id" class="form-select @error('company_id') is-invalid @enderror">
            <option value="">Select Company</option>

            @foreach($companies as $company)
                <option value="{{ $company->id }}"
                    {{ old('company_id', $category->company_id ?? '') == $company->id ? 'selected' : '' }}>
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
            Category Name <span class="text-danger">*</span>
        </label>

        <input
            type="text"
            name="name"
            value="{{ old('name', $category->name ?? '') }}"
            class="form-control @error('name') is-invalid @enderror">

        @error('name')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>

    <div class="col-md-6 mb-3">

        <label class="form-label">
            Status
        </label>

        <select
            name="status"
            class="form-select @error('status') is-invalid @enderror">

            <option value="1"
                {{ old('status',$category->status ?? 1)==1 ? 'selected' : '' }}>
                Active
            </option>

            <option value="0"
                {{ old('status',$category->status ?? 1)==0 ? 'selected' : '' }}>
                Inactive
            </option>

        </select>

        @error('status')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>

    <div class="col-md-12 mb-3">

        <label class="form-label">
            Description
        </label>

        <textarea
            name="description"
            rows="4"
            class="form-control @error('description') is-invalid @enderror">{{ old('description',$category->description ?? '') }}</textarea>

        @error('description')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>

</div>