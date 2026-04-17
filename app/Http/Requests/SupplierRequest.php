<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => 'required|exists:companies,id',
            'supplier_name' => 'required|max:150',
            'contact_person' => 'nullable|max:150',
            'phone' => 'nullable|max:20',
            'alternate_phone' => 'nullable|max:20',
            'email' => 'nullable|email',
            'gst_number' => 'nullable|max:20',
            'pan_number' => 'nullable|max:20',
            'address' => 'nullable',
            'city' => 'required|max:100',
            'state' => 'required|max:100',
            'country' => 'nullable|max:100',
            'pincode' => 'required|max:10',
            'opening_balance' => 'nullable|numeric',
            'balance_type' => 'required|in:Dr,Cr',
            'status' => 'required|boolean',
        ];
    }
}
