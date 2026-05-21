<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvestimentValuationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'current_fcf' => $this->normalizeNumericInput($this->input('current_fcf')),
            'total_shares' => $this->normalizeNumericInput($this->input('total_shares')),
            'current_price_per_share' => $this->normalizeNumericInput($this->input('current_price_per_share')),
            'payout' => $this->normalizeNumericInput($this->input('payout')),
            'roe' => $this->normalizeNumericInput($this->input('roe')),
            'discount_rate' => $this->normalizeNumericInput($this->input('discount_rate')),
            'terminal_growth_rate' => $this->normalizeNumericInput($this->input('terminal_growth_rate')),
            'projection_years' => $this->normalizeIntegerInput($this->input('projection_years')),
            'growth_rates' => collect($this->input('growth_rates', []))
                ->map(fn (mixed $value): string => $this->normalizeNumericInput($value))
                ->all(),
        ]);
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

    private function normalizeIntegerInput(mixed $value): string
    {
        $normalizedValue = $this->normalizeNumericInput($value);

        if ($normalizedValue === '') {
            return '';
        }

        return (string) (int) floor((float) $normalizedValue);
    }
}
