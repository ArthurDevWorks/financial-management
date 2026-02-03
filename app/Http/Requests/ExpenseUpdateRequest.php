<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseUpdateRequest extends FormRequest
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
            'name' => 'required|string|min:3|max:255',
            'value' => 'required|numeric|min:0.01',
            'dt_expense' => 'required|date',
            'description' => 'nullable|string|max:500',
        ];
    }
}
