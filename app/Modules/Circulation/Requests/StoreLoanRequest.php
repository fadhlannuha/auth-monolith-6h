<?php

namespace Modules\Circulation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', 'integer', 'exists:members,id'],
            'loan_days' => ['required', 'integer', 'between:1,30'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.book_id' => ['required', 'integer', 'exists:books,id'],
            'items.*.quantity' => ['required', 'integer', 'between:1,10'],
        ];
    }

    public function messages(): array
    {
        return [
            'member_id.required' => 'Anggota wajib dipilih.',
            'member_id.exists' => 'Anggota tidak valid.',
            'loan_days.required' => 'Durasi peminjaman wajib diisi.',
            'items.required' => 'Minimal satu buku harus dipinjam.',
            'items.min' => 'Minimal satu buku harus dipinjam.',
            'items.*.book_id.required' => 'Buku wajib dipilih pada setiap baris.',
            'items.*.book_id.exists' => 'Buku tidak valid.',
            'items.*.quantity.required' => 'Jumlah wajib diisi.',
            'items.*.quantity.between' => 'Jumlah harus antara 1 sampai 10.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $items = collect($this->input('items', []))
            ->filter(fn ($item) => ! empty($item['book_id']))
            ->values()
            ->all();

        $this->merge(['items' => $items]);
    }
}
