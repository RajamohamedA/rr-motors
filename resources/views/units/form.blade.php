<div class="row">

    <div class="col-md-6 mb-3">

        <label class="form-label">Unit Name</label>

        <input
            type="text"
            name="name"
            value="{{ old('name',$unit->name ?? '') }}"
            class="form-control">

    </div>

    <div class="col-md-6 mb-3">

        <label class="form-label">

            Short Name

        </label>

        <input
            type="text"
            name="short_name"
            value="{{ old('short_name',$unit->short_name ?? '') }}"
            class="form-control">

    </div>

    <div class="col-md-12 mb-3">

        <label class="form-label">

            Description

        </label>

        <textarea
            name="description"
            class="form-control">{{ old('description',$unit->description ?? '') }}</textarea>

    </div>

    <div class="col-md-3">

        <label>Status</label>

        <select
            name="status"
            class="form-select">

            <option value="1">Active</option>

            <option value="0">Inactive</option>

        </select>

    </div>

</div>