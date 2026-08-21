<?php

namespace App\Http\Requests\Panduan;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePanduanRequest extends FormRequest
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
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf|max:102400',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'judul.required' => 'Judul panduan wajib diisi',
            'file.mimes' => 'File harus berformat PDF',
            'file.max' => 'Ukuran file maksimal 100MB',
        ];
    }
}
