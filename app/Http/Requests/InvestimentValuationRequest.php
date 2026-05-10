<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvestimentValuationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $projectionYears = max(1, (int) $this->input('projection_years', 5));

        return [
            'current_fcf' => 'required|numeric|min:0',
            'total_shares' => 'required|numeric|gt:0',
            'current_price_per_share' => 'nullable|numeric|gt:0',
            'payout' => 'required|numeric|min:0|max:100',
            'roe' => 'required|numeric|min:0|max:100',
            'discount_rate' => 'required|numeric|min:0.01|max:100',
            'terminal_growth_rate' => 'required|numeric|min:0|lt:discount_rate',
            'projection_years' => 'required|integer|min:3|max:15',
            'growth_rates' => 'required|array|size:' . $projectionYears,
            'growth_rates.*' => 'required|numeric|min:0|max:100',
        ];
    }
}
