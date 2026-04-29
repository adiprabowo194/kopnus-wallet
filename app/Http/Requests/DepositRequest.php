<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount'      => ['required', 'numeric', 'min:1000', 'max:100000000'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Amount wajib diisi.',
            'amount.numeric'  => 'Amount harus berupa angka.',
            'amount.min'      => 'Minimum deposit adalah Rp 1.000.',
            'amount.max'      => 'Maksimum deposit per transaksi Rp 100.000.000.',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'code'    => 422,
            'message' => 'Cek Kembali Parameter Anda. Validasi Gagal',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
