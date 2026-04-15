<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UnitRequest extends FormRequest
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
        $unitId = $this->route('unit')?->id;
    
        return [
            'company_id' => 'required|exists:companies,id',
    
            'unit_name' => 'required|max:100|unique:units,unit_name,' . $unitId . ',id,company_id,' . $this->company_id,
    
            'short_name' => 'required|max:20|unique:units,short_name,' . $unitId . ',id,company_id,' . $this->company_id,
    
            'description' => 'nullable',
    
            'status' => 'required|boolean',
        ];
    }
}
