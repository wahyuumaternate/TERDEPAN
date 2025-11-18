<?php

namespace App\Http\Requests;

use App\Models\MasterPegawai;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nomor_identitas' => ['required', 'string', 'max:18', Rule::unique(MasterPegawai::class)->ignore($this->user()->id)],
            'tipe_identitas' => ['required', 'in:NIP,NIK'],
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(MasterPegawai::class)->ignore($this->user()->id)],
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'gelar_depan' => ['nullable', 'string', 'max:20'],
            'gelar_belakang' => ['nullable', 'string', 'max:20'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string'],
            'pangkat' => ['nullable', 'string', 'max:50'],
            'golongan' => ['nullable', 'string', 'max:10'],
            'foto_profile' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nomor_identitas' => 'nomor identitas',
            'tipe_identitas' => 'tipe identitas',
            'nama' => 'nama',
            'email' => 'email',
            'no_telepon' => 'nomor telepon',
            'gelar_depan' => 'gelar depan',
            'gelar_belakang' => 'gelar belakang',
            'jenis_kelamin' => 'jenis kelamin',
            'tanggal_lahir' => 'tanggal lahir',
            'alamat' => 'alamat',
            'pangkat' => 'pangkat',
            'golongan' => 'golongan',
            'foto_profile' => 'foto profil',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nomor_identitas.required' => 'Nomor identitas wajib diisi.',
            'nomor_identitas.max' => 'Nomor identitas maksimal 18 karakter.',
            'nomor_identitas.unique' => 'Nomor identitas sudah terdaftar.',

            'tipe_identitas.required' => 'Tipe identitas wajib dipilih.',
            'tipe_identitas.in' => 'Tipe identitas harus NIP atau NIK.',

            'nama.required' => 'Nama wajib diisi.',
            'nama.max' => 'Nama maksimal 100 karakter.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah terdaftar.',
            'email.lowercase' => 'Email harus huruf kecil.',

            'no_telepon.max' => 'Nomor telepon maksimal 20 karakter.',

            'gelar_depan.max' => 'Gelar depan maksimal 20 karakter.',

            'gelar_belakang.max' => 'Gelar belakang maksimal 20 karakter.',

            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin tidak valid.',

            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',

            'pangkat.max' => 'Pangkat maksimal 50 karakter.',

            'golongan.max' => 'Golongan maksimal 10 karakter.',

            'foto_profile.image' => 'File harus berupa gambar.',
            'foto_profile.mimes' => 'Foto profil harus berformat JPG, JPEG, atau PNG.',
            'foto_profile.max' => 'Ukuran foto profil maksimal 2MB.',
        ];
    }
}
