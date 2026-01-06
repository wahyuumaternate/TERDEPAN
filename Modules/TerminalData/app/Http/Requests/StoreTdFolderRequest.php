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

    /**
     * Prepare data for validation
     */
    protected function prepareForValidation()
    {
        /** @var \App\Models\MasterPegawai $user */
        $user = $this->user();
        $kodeJabatan = $user->jabatan?->kode;

        // Jika bukan ADMIN, KABAN, atau SEKBAN, force bidang_id ke bidang user
        if (!in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
            // Jika parent_id ada, ambil bidang_id dari parent
            if ($this->has('parent_id') && $this->parent_id) {
                $parent = \Modules\TerminalData\Models\TdFolder::find($this->parent_id);
                if ($parent) {
                    // Validasi bahwa parent folder bidangnya sesuai dengan bidang user
                    if ($parent->bidang_id !== $user->bidang_id) {
                        abort(403, 'Anda hanya dapat membuat folder di bidang Anda sendiri.');
                    }
                    $this->merge([
                        'bidang_id' => $parent->bidang_id,
                    ]);
                }
            } else {
                // Jika tidak ada parent, set ke bidang user
                $this->merge([
                    'bidang_id' => $user->bidang_id,
                ]);
            }
        }
    }
}
