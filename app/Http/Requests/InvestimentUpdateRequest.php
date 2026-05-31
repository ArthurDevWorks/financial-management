<?php

namespace App\Http\Requests;

use App\Enums\FixedIncomeIndexer;
use App\Enums\FixedIncomeProfitabilityType;
use App\Enums\InvestmentAssetType;
use App\Models\Investiment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvestimentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        /** @var Investiment|null $investiment */
        $investiment = $this->route('investiment');
        $type = $this->input('type', $investiment?->type?->value);
        $quantity = $this->normalizeDecimal($this->input('quantity', $investiment?->quantity ?? 1)) ?? '1';
        $averagePrice = $this->normalizeDecimal($this->input('average_price', $investiment?->average_price ?? $investiment?->value ?? 0)) ?? '0';
        $currentBalance = $this->normalizeDecimal($this->input('current_balance', $investiment?->current_balance));
        $investedAmount = (float) $quantity * (float) $averagePrice;
        $currentBalance ??= number_format($investedAmount, 2, '.', '');
        $assetType = InvestmentAssetType::tryFrom((string) $type);
        $isFixedIncome = $assetType?->isFixedIncome() ?? false;
        $profitability = $investedAmount > 0
            ? (((float) $currentBalance - $investedAmount) / $investedAmount) * 100
            : 0;

        $this->merge([
            'type' => $type,
            'dt_investment' => $this->input(
                'dt_investment',
                $investiment?->dt_investment?->format('Y-m-d H:i:s') ?? now()->toDateTimeString(),
            ),
            'quantity' => $quantity,
            'average_price' => $averagePrice,
            'current_balance' => $currentBalance,
            'value' => $averagePrice,
            'profitability' => round($profitability, 2),
            'profitability_type' => $isFixedIncome
                ? $this->input('profitability_type', $investiment?->profitability_type?->value)
                : null,
            'indexer' => $isFixedIncome
                ? $this->input('indexer', $investiment?->indexer?->value)
                : null,
            'contracted_rate' => $isFixedIncome
                ? $this->normalizeDecimal($this->input('contracted_rate', $investiment?->contracted_rate))
                : null,
            'maturity_date' => $isFixedIncome
                ? $this->input('maturity_date', $investiment?->maturity_date?->format('Y-m-d'))
                : null,
            'liquidity' => $isFixedIncome
                ? $this->input('liquidity', $investiment?->liquidity)
                : null,
        ]);
    }

    public function rules(): array
    {
        $isFixedIncome = $this->assetTypeIsFixedIncome();

        return [
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::enum(InvestmentAssetType::class)],
            'quantity' => 'required|numeric|min:0.00000001',
            'average_price' => 'required|numeric|min:0',
            'current_balance' => 'nullable|numeric|min:0',
            'value' => 'nullable|numeric|min:0',
            'dt_investment' => 'nullable|date',
            'profitability' => 'nullable|numeric|min:-100',
            'profitability_type' => [Rule::requiredIf($isFixedIncome), 'nullable', Rule::enum(FixedIncomeProfitabilityType::class)],
            'indexer' => [Rule::requiredIf($isFixedIncome), 'nullable', Rule::enum(FixedIncomeIndexer::class)],
            'contracted_rate' => [Rule::requiredIf($isFixedIncome), 'nullable', 'numeric', 'min:0'],
            'maturity_date' => 'nullable|date',
            'liquidity' => 'nullable|string|max:255',
        ];
    }

    private function assetTypeIsFixedIncome(): bool
    {
        return InvestmentAssetType::tryFrom((string) $this->input('type'))?->isFixedIncome() ?? false;
    }

    private function normalizeDecimal(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        $decimalValue = preg_replace('/[^\d,.-]/', '', (string) $value);

        if ($decimalValue === null || $decimalValue === '') {
            return null;
        }

        $lastComma = strrpos($decimalValue, ',');
        $lastDot = strrpos($decimalValue, '.');

        if ($lastComma !== false && $lastDot !== false) {
            return $lastComma > $lastDot
                ? str_replace(',', '.', str_replace('.', '', $decimalValue))
                : str_replace(',', '', $decimalValue);
        }

        if ($lastComma !== false) {
            return str_replace(',', '.', $decimalValue);
        }

        return $decimalValue;
    }
}
