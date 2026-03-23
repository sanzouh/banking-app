<?php

namespace App\Http\Requests\Withdrawal;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWithdrawalRequest extends FormRequest
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
            'check_num'   => 'sometimes|integer|unique:withdrawals,check_num,' . $this->route('withdrawal') . ',withdraw_num',
            'account_num' => 'sometimes|integer|exists:clients,account_num',
            'amount'      => 'sometimes|numeric|min:0',
        ];
    }
}
