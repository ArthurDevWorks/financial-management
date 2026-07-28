<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrecoTetoValuationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'desired_yield' => $this->normalizeNumericInput($this->input('desired_yield')),
            'projected_payout' => $this->normalizeNumericInput($this->input('projected_payout')),
            'projected_net_income' => $this->normalizeNumericInput($this->input('projected_net_income')),
            'total_shares' => $this->normalizeIntegerInput($this->input('total_shares')),
            'projected_growth_rate' => $this->normalizeNumericInput($this->input('projected_growth_rate')),
            'current_price_per_share' => $this->normalizeNumericInput($this->input('current_price_per_share')),
        ]);
    }

    public function rules(): array
    {
        return [
            'asset_id' => 'required|integer|exists:assets,id',
            'desired_yield' => 'required|numeric|min:0.01|max:100',
            'projected_payout' => 'required|numeric|min:0.01|max:100',
            'projected_net_income' => 'required|numeric|gt:0',
            'total_shares' => 'required|integer|gt:0',
            'projected_growth_rate' => 'required|numeric|gt:-100|max:1000',
            'current_price_per_share' => 'required|numeric|gt:0',
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
        if ($value === null || $value === '') {
            return '';
        }

        return preg_replace('/\D/u', '', (string) $value) ?: '';
    }
}
