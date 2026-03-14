<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvestimentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => 'required|string|max:255',
            'dt_investment' => 'required|date_format:Y-m-d H:i:s',
            'value'         => 'required|numeric|min:0',
            'type'          => 'required|exists:categories,id',
            'profitability' => 'required|integer',
        ];
    }
}
