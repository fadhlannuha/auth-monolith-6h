<?php

namespace Modules\Membership\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $memberId = $this->route('member')->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('members', 'email')->ignore($memberId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,suspended,inactive'],
            'max_loans' => ['required', 'integer', 'between:1,10'],
            'membership_type' => ['required', 'in:basic,premium'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:membership_roles,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Email sudah digunakan anggota lain.',
            'status.required' => 'Status anggota wajib dipilih.',
            'membership_type.required' => 'Jenis keanggotaan wajib dipilih.',
        ];
    }
}
