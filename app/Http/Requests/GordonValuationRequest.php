<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GordonValuationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $growthRates = collect($this->input('growth_rates', []))
            ->map(fn (mixed $value): string => $this->normalizeNumericInput($value))
            ->all();

        $this->merge([
            'dps' => $this->normalizeNumericInput($this->input('dps')),
            'discount_rate' => $this->normalizeNumericInput($this->input('discount_rate')),
            'risk_premium' => $this->normalizeNumericInput($this->input('risk_premium')),
            'growth_perpetuity' => $this->normalizeNumericInput($this->input('growth_perpetuity')),
            'current_price' => $this->normalizeNumericInput($this->input('current_price')),
            'projection_years' => $this->normalizeNumericInput($this->input('projection_years')),
            'growth_rates' => $growthRates,
        ]);
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

    private function normalizeNumericInput(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $textValue = preg_replace('/[^\d,.\-]/u', '', (string) $value);

        if ($textValue === '') {
            return '';
        }

        $lastCommaIndex = strrpos($textValue, ',');
        $lastDotIndex = strrpos($textValue, '.');
        $commaCount = substr_count($textValue, ',');
        $dotCount = substr_count($textValue, '.');

        if ($lastCommaIndex !== false && $lastDotIndex !== false) {
            return $lastCommaIndex > $lastDotIndex
                ? str_replace(',', '.', str_replace('.', '', $textValue))
                : str_replace(',', '', $textValue);
        }

        if ($lastCommaIndex !== false) {
            return $commaCount > 1
                ? str_replace(',', '', $textValue)
                : str_replace(',', '.', $textValue);
        }

        if ($lastDotIndex !== false) {
            return $dotCount > 1
                ? str_replace('.', '', $textValue)
                : $textValue;
        }

        return $textValue;
    }
}
