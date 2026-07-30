<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreditCardUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'bank_id'     => 'nullable|exists:banks,id',
            'limit'       => 'required|numeric|min:0',
            'closing_day' => 'required|integer|min:1|max:28',
            'due_day'     => 'required|integer|min:1|max:28',
            'color'       => 'nullable|string|max:7',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'Informe o nome do cartão.',
            'limit.required'       => 'Informe o limite do cartão.',
            'closing_day.required' => 'Informe o dia de fechamento da fatura.',
            'closing_day.min'      => 'O dia de fechamento deve ser entre 1 e 28.',
            'closing_day.max'      => 'O dia de fechamento deve ser entre 1 e 28.',
            'due_day.required'     => 'Informe o dia de vencimento da fatura.',
            'due_day.min'          => 'O dia de vencimento deve ser entre 1 e 28.',
            'due_day.max'          => 'O dia de vencimento deve ser entre 1 e 28.',
        ];
    }
}
