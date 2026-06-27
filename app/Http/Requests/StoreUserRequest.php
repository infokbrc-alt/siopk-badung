<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('pengguna')?->id;

        return [
            'name' => 'required|string|max:150',
            'email' => ['required', 'email', $userId ? Rule::unique('users')->ignore($userId) : 'unique:users,email'],
            'password' => ($userId ? 'nullable' : 'required').'|string|min:8|confirmed',
            'role' => 'required|in:superadmin,admin,verifikator,petugas',
            'nip' => 'nullable|string|max:30',
            'no_hp' => 'nullable|string|max:20',
            'instansi' => 'nullable|string|max:150',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'email.unique' => 'Email sudah terdaftar.',
        ];
    }
}
