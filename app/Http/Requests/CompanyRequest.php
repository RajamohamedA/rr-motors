<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => 'required|string|max:255',
            'owner_name'   => 'nullable|string|max:255',
            'gst_number'   => 'nullable|string|max:50',
            'pan_number'   => 'nullable|string|max:50',
            'email'        => 'nullable|email|max:255',
            'phone'        => 'nullable|string|max:20',
            'website'      => 'nullable|url|max:255',
            'address'      => 'nullable|string',
            'city'         => 'required|string|max:100',
            'state'        => 'required|string|max:100',
            'country'      => 'required|string|max:100',
            'pincode'      => 'required|string|max:10',
            'logo'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status'       => 'required|boolean',
        ];
    }
}