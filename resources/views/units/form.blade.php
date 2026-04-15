@if($errors->any())

<div class="alert alert-danger">

    <ul class="mb-0">

        @foreach($errors->all() as $error)

            <li>{{ $error }}</li>

        @endforeach

    </ul>

</div>

@endif

<div class="row">

    <div class="col-md-6 mb-3">
        <label class="form-label">Unit Name</label>

        <input
            type="text"
            name="unit_name"
            class="form-control @error('unit_name') is-invalid @enderror"
            value="{{ old('unit_name', $unit->unit_name ?? '') }}">

        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Short Name</label>

        <input
            type="text"
            name="short_name"
            class="form-control @error('short_name') is-invalid @enderror"
            value="{{ old('short_name', $unit->short_name ?? '') }}">

        @error('short_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12 mb-3">
        <label class="form-label">Description</label>

        <textarea
            name="description"
            class="form-control">{{ old('description', $unit->description ?? '') }}</textarea>
    </div>

    <div class="col-md-6 mb-3">

        <label class="form-label">Company</label>

        <select name="company_id" class="form-select">

            @foreach($companies as $company)

                <option value="{{ $company->id }}">

                    {{ $company->company_name }}

                </option>

            @endforeach

        </select>

    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Status</label>

        <select name="status" class="form-select">

            <option value="1"
                {{ old('status',$unit->status ?? 1)==1 ? 'selected':'' }}>
                Active
            </option>

            <option value="0"
                {{ old('status',$unit->status ?? 1)==0 ? 'selected':'' }}>
                Inactive
            </option>

        </select>
    </div>

</div>