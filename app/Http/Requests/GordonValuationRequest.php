<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GordonValuationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_id' => 'required|integer|exists:assets,id',
            'dps' => 'required|numeric|min:0',
            'discount_rate' => 'required|numeric|min:0|max:100',
            'risk_premium' => 'nullable|numeric|min:0|max:50',
            'growth_perpetuity' => 'required|numeric|min:0|max:100',
            'current_price' => 'nullable|numeric|min:0',
            'projection_years' => 'nullable|integer|min:1|max:50',
            'growth_rates' => 'nullable|array|max:50',
            'growth_rates.*' => 'numeric|min:0|max:100',
        ];
    }
}
