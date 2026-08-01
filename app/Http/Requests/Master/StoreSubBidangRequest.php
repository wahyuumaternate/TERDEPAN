<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubBidangRequest extends FormRequest
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
            'bidang_id' => 'required|exists:master_bidang,id',
            'nama' => 'required|string|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'bidang_id.required' => 'Bidang wajib dipilih',
            'bidang_id.exists' => 'Bidang tidak ditemukan',
            'nama.required' => 'Nama sub bidang wajib diisi',
        ];
    }
}
