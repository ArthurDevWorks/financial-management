<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReleaseUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'account_id' => [
                'required',
                Rule::exists('accounts', 'id')->where('user_id', $userId),
            ],
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01|max:999999999.99',
            'type' => 'required|in:revenue,expense',
            'date' => 'required|date',
            'payment_method' => 'nullable|in:cash,credit_card,debit_card,pix',
            'credit_card_id' => [
                'nullable',
                Rule::exists('credit_cards', 'id')->where('user_id', $userId),
            ],
            'status' => 'nullable|in:pending,paid,canceled',
        ];
    }
}
