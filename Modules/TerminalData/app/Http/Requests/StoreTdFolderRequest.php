<?php

namespace Modules\TerminalData\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTdFolderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'parent_id' => 'nullable|uuid|exists:td_folders,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'bidang_id' => 'nullable|integer|exists:master_bidang,id',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'is_public' => 'nullable|boolean',
            'is_starred' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'parent_id' => 'parent folder',
            'name' => 'nama folder',
            'description' => 'deskripsi',
            'bidang_id' => 'bidang',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama folder wajib diisi',
            'name.max' => 'Nama folder maksimal 255 karakter',
            'parent_id.exists' => 'Parent folder tidak ditemukan',
            'bidang_id.exists' => 'Bidang tidak ditemukan',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
