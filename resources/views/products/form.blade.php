<div class="row">

    {{-- Company --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Company <span class="text-danger">*</span></label>

        <select name="company_id"  id="company_id" class="form-select @error('company_id') is-invalid @enderror">
            <option value="">Select Company</option>

            @foreach($companies as $company)
                <option value="{{ $company->id }}"
                    {{ old('company_id', $product->company_id ?? '') == $company->id ? 'selected' : '' }}>
                    {{ $company->company_name }}
                </option>
            @endforeach
        </select>

        @error('company_id')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Store --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Store <span class="text-danger">*</span></label>

        <select name="store_id" id="store_id" class="form-select @error('store_id') is-invalid @enderror">
            <option value="">Select Store</option>

            @foreach($stores as $store)
                <option value="{{ $store->id }}"
                    {{ old('store_id', $product->store_id ?? '') == $store->id ? 'selected' : '' }}>
                    {{ $store->store_name }}
                </option>
            @endforeach
        </select>

        @error('store_id')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Product Code --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Product Code <span class="text-danger">*</span></label>

        <input type="text"
               name="product_code"
               value="{{ old('product_code', $product->product_code ?? '') }}"
               class="form-control @error('product_code') is-invalid @enderror">

        @error('product_code')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Product Name --}}
    <div class="col-md-6 mb-3">
        <label class="form-label">Product Name <span class="text-danger">*</span></label>

        <input type="text"
               name="product_name"
               value="{{ old('product_name', $product->product_name ?? '') }}"
               class="form-control @error('product_name') is-invalid @enderror">

        @error('product_name')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Barcode --}}
    <div class="col-md-3 mb-3">
        <label class="form-label">Barcode</label>

        <input type="text"
               name="barcode"
               value="{{ old('barcode', $product->barcode ?? '') }}"
               class="form-control">
    </div>

    {{-- Part Number --}}
    <div class="col-md-3 mb-3">
        <label class="form-label">Part Number</label>

        <input type="text"
               name="part_number"
               value="{{ old('part_number', $product->part_number ?? '') }}"
               class="form-control">
    </div>

    {{-- Category --}}
    <div class="col-md-3 mb-3">
        <label class="form-label">Category</label>

        <select name="category_id" class="form-select">
            <option value="">Select Category</option>

            @foreach($categories as $category)
                <option value="{{ $category->id }}"
                    {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Brand --}}
    <div class="col-md-3 mb-3">
        <label class="form-label">Brand</label>

        <select name="brand_id" class="form-select">
            <option value="">Select Brand</option>

            @foreach($brands as $brand)
                <option value="{{ $brand->id }}"
                    {{ old('brand_id', $product->brand_id ?? '') == $brand->id ? 'selected' : '' }}>
                    {{ $brand->brand_name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Unit --}}
    <div class="col-md-3 mb-3">
        <label class="form-label">Unit</label>

        <select name="unit_id" class="form-select">
            <option value="">Select Unit</option>

            @foreach($units as $unit)
                <option value="{{ $unit->id }}"
                    {{ old('unit_id', $product->unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                    {{ $unit->unit_name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Supplier --}}
    <div class="col-md-3 mb-3">
        <label class="form-label">Supplier</label>

        <select name="supplier_id" class="form-select">
            <option value="">Select Supplier</option>

            @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}"
                    {{ old('supplier_id', $product->supplier_id ?? '') == $supplier->id ? 'selected' : '' }}>
                    {{ $supplier->supplier_name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- HSN --}}
    <div class="col-md-3 mb-3">
        <label class="form-label">HSN Code</label>
        <input type="text" name="hsn_code"
               value="{{ old('hsn_code', $product->hsn_code ?? '') }}"
               class="form-control">
    </div>

    {{-- GST --}}
    <div class="col-md-3 mb-3">
        <label class="form-label">GST %</label>
        <input type="number" step="0.01"
               name="gst_percent"
               value="{{ old('gst_percent', $product->gst_percent ?? 18) }}"
               class="form-control">
    </div>

    {{-- Purchase --}}
    <div class="col-md-3 mb-3">
        <label class="form-label">Purchase Price</label>
        <input type="number" step="0.01"
               name="purchase_price"
               value="{{ old('purchase_price', $product->purchase_price ?? 0) }}"
               class="form-control">
    </div>

    {{-- Selling --}}
    <div class="col-md-3 mb-3">
        <label class="form-label">Selling Price</label>
        <input type="number" step="0.01"
               name="selling_price"
               value="{{ old('selling_price', $product->selling_price ?? 0) }}"
               class="form-control">
    </div>

    <div class="col-md-4 mb-3">
    <label class="form-label">
        Status <span class="text-danger">*</span>
    </label>

    {{-- Status --}}
    <select name="status"
            class="form-select @error('status') is-invalid @enderror">

        <option value="1"
            {{ old('status', $product?->status ?? 1) == 1 ? 'selected' : '' }}>
            Active
        </option>

        <option value="0"
            {{ old('status', $product?->status ?? 1) == 0 ? 'selected' : '' }}>
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