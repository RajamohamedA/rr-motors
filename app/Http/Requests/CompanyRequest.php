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
            'company_name' => 'required|max:255',
            'owner_name'   => 'nullable|max:255',
            'gst_number'   => 'nullable|max:50',
            'pan_number'   => 'nullable|max:50',
            'email'        => 'nullable|email',
            'phone'        => 'nullable|max:20',
            'website'      => 'nullable|max:255',
            'address'      => 'nullable',
            'city'         => 'nullable|max:100',
            'state'        => 'nullable|max:100',
            'country'      => 'nullable|max:100',
            'pincode'      => 'nullable|max:10',
            'logo'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status'       => 'required|boolean',
        ];
    }
}