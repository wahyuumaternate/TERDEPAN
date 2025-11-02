<?php

namespace Modules\TerminalData\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetFoldersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'bidang_id' => 'nullable|exists:master_bidang,id',
            'level' => 'nullable|integer|min:0|max:10',
            'search' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort_by' => 'nullable|string|in:name,created_at,updated_at,level',
            'sort_order' => 'nullable|string|in:asc,desc',
            'is_starred' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'bidang_id.exists' => 'Bidang tidak ditemukan.',
            'level.integer' => 'Level harus berupa angka.',
            'level.min' => 'Level minimal adalah 0.',
            'level.max' => 'Level maksimal adalah 10.',
            'per_page.integer' => 'Jumlah per halaman harus berupa angka.',
            'per_page.min' => 'Minimal 1 data per halaman.',
            'per_page.max' => 'Maksimal 100 data per halaman.',
            'sort_by.in' => 'Sortir hanya dapat dilakukan berdasarkan: name, created_at, updated_at, level.',
            'sort_order.in' => 'Urutan sortir hanya dapat: asc atau desc.',
        ];
    }

    /**
     * Get validated data with defaults
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Set defaults
        $validated['level'] = $validated['level'] ?? 0; // Default level 1 (bidang)
        $validated['sort_by'] = $validated['sort_by'] ?? 'name';
        $validated['sort_order'] = $validated['sort_order'] ?? 'asc';

        return $validated;
    }
}
