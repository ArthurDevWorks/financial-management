<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReleaseStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:revenue,expense',
            'date' => 'required|date',
            'payment_method' => 'nullable|in:cash,credit_card,debit_card,pix',
            'is_installment' => 'nullable|boolean',
            'total_installments' => 'nullable|integer|min:2|max:360',
            'is_recurring' => 'nullable|boolean',
            'recurrence_frequency' => 'nullable|in:monthly,yearly,weekly,biweekly,quarterly',
            'recurrence_end_date' => 'nullable|date|after:date',
            'status' => 'nullable|in:pending,paid,canceled',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $data = $this->all();

            if (($data['payment_method'] ?? null) === 'credit_card' && !empty($data['is_installment'])) {
                if (empty($data['total_installments']) || (int) $data['total_installments'] < 2) {
                    $validator->errors()->add('total_installments', 'Informe o número de parcelas (mínimo 2).');
                }
            }

            if (!empty($data['is_recurring'])) {
                if (empty($data['recurrence_frequency'])) {
                    $validator->errors()->add('recurrence_frequency', 'Informe a frequência da recorrência.');
                }
                if (empty($data['recurrence_end_date'])) {
                    $validator->errors()->add('recurrence_end_date', 'Informe a data final da recorrência.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'recurrence_end_date.after' => 'A data final deve ser posterior à data do lançamento.',
        ];
    }
}
