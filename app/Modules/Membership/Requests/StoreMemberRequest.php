<?php

namespace Modules\Membership\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:members,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'max_loans' => ['required', 'integer', 'between:1,10'],
            'membership_type' => ['required', 'in:basic,premium'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:membership_roles,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama anggota wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar sebagai anggota.',
            'max_loans.required' => 'Batas peminjaman wajib diisi.',
            'membership_type.required' => 'Jenis keanggotaan wajib dipilih.',
            'role_ids.*.exists' => 'Role anggota tidak valid.',
        ];
    }
}
