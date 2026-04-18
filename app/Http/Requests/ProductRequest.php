<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Authorize the request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation Rules
     */
    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [

            // Company
            'company_id' => 'required|exists:companies,id',

            // Store
            'store_id' => 'required|exists:stores,id',

            // Masters
            'category_id' => 'required|exists:categories,id',

            'brand_id' => 'nullable|exists:brands,id',

            'unit_id' => 'required|exists:units,id',

            'supplier_id' => 'nullable|exists:suppliers,id',

            // Product

            'product_code' => [
                'required',
                'max:50',
                Rule::unique('products', 'product_code')->ignore($productId),
            ],

            'barcode' => [
                'nullable',
                'max:100',
                Rule::unique('products', 'barcode')->ignore($productId),
            ],

            'product_name' => 'required|max:255',

            'part_number' => 'nullable|max:100',

            'manufacturer' => 'nullable|max:150',

            'rack_location' => 'nullable|max:100',

            // Tax

            'hsn_code' => 'nullable|max:20',

            'gst_percent' => 'required|numeric|min:0|max:100',

            // Price

            'purchase_price' => 'required|numeric|min:0',

            'selling_price' => 'required|numeric|min:0',

            'mrp' => 'nullable|numeric|min:0',

            // Stock

            'opening_stock' => 'nullable|numeric|min:0',

            'minimum_stock' => 'nullable|numeric|min:0',

            'maximum_stock' => 'nullable|numeric|min:0',

            // Image

            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            // Others

            'description' => 'nullable',

            'status' => 'required|boolean',

        ];
    }

    /**
     * Custom Field Names
     */
    public function attributes(): array
    {
        return [
            'company_id' => 'company',
            'store_id' => 'store',
            'category_id' => 'category',
            'brand_id' => 'brand',
            'unit_id' => 'unit',
            'supplier_id' => 'supplier',
            'product_code' => 'product code',
            'product_name' => 'product name',
            'gst_percent' => 'GST',
        ];
    }
}