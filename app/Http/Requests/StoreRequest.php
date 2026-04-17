<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id'      => 'required|exists:companies,id',
            'store_name'      => 'required|max:255',
            'contact_person'  => 'nullable|max:255',
            'phone'           => 'nullable|max:20',
            'email'           => 'nullable|email',
            'address'         => 'nullable',
            'city'            => 'nullable|max:100',
            'state'           => 'nullable|max:100',
            'country'         => 'nullable|max:100',
            'pincode'         => 'nullable|max:10',
            'status'          => 'required|boolean',
        ];
    }
}
