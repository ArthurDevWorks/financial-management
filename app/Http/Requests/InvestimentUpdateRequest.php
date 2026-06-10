<?php

namespace App\Http\Requests;

use App\Enums\InvestmentAssetType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Enums\CategoryType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InvestimentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $currentBalance = $this->normalizeDecimal($this->input('current_balance')) ?? '0';

        $investiment = $this->route('investiment');

        $this->merge([
            'value' => $currentBalance,
            'dt_investment' => $this->input('dt_investment', $investiment?->dt_investment?->format('Y-m-d H:i:s') ?? now()->toDateTimeString()),
        ]);

        $typeInput = $this->input('type');

        if ($typeInput !== null && !is_numeric($typeInput)) {
            try {
                $columnType = Schema::getColumnType('investiments', 'type');
            } catch (\Throwable $e) {
                $columnType = null;
            }

            if ($columnType !== 'string') {
                $category = DB::table('categories')
                    ->get()
                    ->first(function (object $cat) use ($typeInput) {
                        if (InvestmentAssetType::fromLegacyCategoryName($cat->name)->value === $typeInput) {
                            return true;
                        }

                        $labels = array_map(fn($c) => $c->label(), InvestmentAssetType::cases());
                        if (in_array($typeInput, $labels, true)) {
                            return strcasecmp($cat->name, $typeInput) === 0;
                        }

                        return strcasecmp($cat->name, $typeInput) === 0;
                    });

                if (! $category) {
                    $enum = collect(InvestmentAssetType::cases())->first(fn($c) => $c->value === $typeInput);
                    $name = $enum?->label() ?? ucfirst($typeInput);

                    $newId = DB::table('categories')->insertGetId([
                        'type' => CategoryType::INVESTMENT->value,
                        'name' => $name,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->merge(['type' => $newId]);
                } else {
                    $this->merge(['type' => $category->id]);
                }
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => ['required', function ($attribute, $value, $fail) {
                if (is_numeric($value)) {
                    if (! DB::table('categories')->where('id', $value)->exists()) {
                        $fail('Tipo inválido.');
                    }
                    return;
                }

                $valid = collect(InvestmentAssetType::cases())->contains(fn($c) => $c->value === $value);

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
