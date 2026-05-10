<?php

namespace App\Http\Requests;

use App\Enums\CategoryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvestimentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'dt_investment' => $this->input('dt_investment', now()->toDateTimeString()),
            'profitability' => $this->input('profitability', 0),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'type' => [
                'required',
                Rule::exists('categories', 'id')->where('type', CategoryType::INVESTMENT->value),
            ],
            'dt_investment' => 'nullable|date',
            'profitability' => 'nullable|integer|min:-100',
        ];
    }
}
