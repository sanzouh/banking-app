<?php

namespace App\Http\Requests\Withdrawal;

use Illuminate\Foundation\Http\FormRequest;

class StoreWithdrawalRequest extends FormRequest
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
            'withdraw_num' => 'required|integer|unique:withdrawals',
            'check_num'    => 'required|integer|unique:withdrawals',
            'account_num'  => 'required|integer|exists:clients,account_num',
            'amount'       => 'required|numeric|min:0',
        ];
    }
}
