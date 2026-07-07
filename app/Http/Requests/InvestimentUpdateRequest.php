<?php

namespace App\Http\Requests;

use App\Enums\InvestmentAssetType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class InvestimentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => strtoupper(trim($this->input('name', ''))),
        ]);

        $currentBalance = $this->normalizeDecimal($this->input('current_balance')) ?? '0';

        $investiment = $this->route('investiment');

        $this->merge([
            'value' => $currentBalance,
            'dt_investment' => $this->input('dt_investment', $investiment?->dt_investment?->format('Y-m-d H:i:s') ?? now()->toDateTimeString()),
        ]);

        $typeInput = $this->input('type');

        if ($typeInput !== null) {
            $isValidEnum = collect(InvestmentAssetType::cases())
                ->contains(fn ($c) => $c->value === $typeInput);

            if ($isValidEnum) {
                return;
            }

            $enum = null;

            if (is_numeric($typeInput)) {
                $category = DB::table('categories')->find((int) $typeInput);
                if ($category) {
                    $enum = InvestmentAssetType::fromLegacyCategoryName($category->name);
                }
            } else {
                $category = DB::table('categories')
                    ->get()
                    ->first(function (object $cat) use ($typeInput) {
                        if (InvestmentAssetType::fromLegacyCategoryName($cat->name)->value === $typeInput) {
                            return true;
                        }

                        $labels = array_map(fn ($c) => $c->label(), InvestmentAssetType::cases());
                        if (in_array($typeInput, $labels, true)) {
                            return strcasecmp($cat->name, $typeInput) === 0;
                        }

                        return strcasecmp($cat->name, $typeInput) === 0;
                    });

                if ($category) {
                    $enum = InvestmentAssetType::fromLegacyCategoryName($category->name);
                } else {
                    $enum = collect(InvestmentAssetType::cases())->first(fn ($c) => $c->value === $typeInput);
                }
            }

            if ($enum) {
                $this->merge(['type' => $enum->value]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => ['required', function ($attribute, $value, $fail) {
                $valid = collect(InvestmentAssetType::cases())->contains(fn ($c) => $c->value === $value);

                if (! $valid) {
                    $fail('Tipo inválido.');
                }
            }],
            'current_balance' => 'sometimes',
            'value' => 'required|numeric|min:0',
            'dt_investment' => 'required|date',
        ];
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
