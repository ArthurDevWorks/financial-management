<?php

namespace App\Http\Requests;

use App\Enums\AccountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bank_id' => 'required|exists:banks,id',
            'type' => ['required', Rule::enum(AccountType::class)],
            'agency' => 'required|string|max:20',
            'account' => 'required|string|max:20',
            'total' => 'required|numeric|min:0|max:999999999.99',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'bank_id.required' => 'O banco é obrigatório',
            'bank_id.exists' => 'O banco selecionado não existe',
            'type.required' => 'O tipo de conta é obrigatório',
            'type.enum' => 'O tipo de conta selecionado é inválido',
            'agency.required' => 'A agência é obrigatória',
            'account.required' => 'O número da conta é obrigatório',
            'total.required' => 'O saldo é obrigatório',
            'total.numeric' => 'O saldo deve ser um valor numérico',
            'total.min' => 'O saldo não pode ser negativo',
        ];
    }

    protected function prepareForValidation(): void
    {
        $total = $this->input('total');

        if (is_string($total) && $total !== '') {
            $normalized = str_contains($total, ',')
                ? str_replace(['.', ','], ['', '.'], $total)
                : $total;

            $this->merge([
                'total' => (float) $normalized,
            ]);
        }
    }
}
