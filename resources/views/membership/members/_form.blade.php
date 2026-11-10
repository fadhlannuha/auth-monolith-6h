@php
    $selectedRoles = old('role_ids', isset($member) ? $member->roles->pluck('id')->all() : []);
    $currentMembership = isset($member) ? $member->memberships->firstWhere('status', 'active') : null;
@endphp

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <x-input-label for="name" value="Nama Lengkap" />
        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $member->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $member->email ?? '')" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="phone" value="Telepon" />
        <x-text-input id="phone" name="phone" class="mt-1 block w-full" :value="old('phone', $member->phone ?? '')" />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="max_loans" value="Batas Pinjaman Aktif" />
        <x-text-input id="max_loans" name="max_loans" type="number" min="1" max="10" class="mt-1 block w-full" :value="old('max_loans', $member->max_loans ?? 3)" required />
        <x-input-error :messages="$errors->get('max_loans')" class="mt-2" />
    </div>

    @isset($member)
        <div>
            <x-input-label for="status" value="Status Anggota" />
            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach (['active' => 'Aktif', 'suspended' => 'Ditangguhkan', 'inactive' => 'Nonaktif'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $member->status) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>
    @endisset

    <div>
        <x-input-label for="membership_type" value="Jenis Keanggotaan" />
        <select id="membership_type" name="membership_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach (['basic' => 'Basic', 'premium' => 'Premium'] as $value => $label)
                <option value="{{ $value }}" @selected(old('membership_type', $currentMembership?->type ?? 'basic') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('membership_type')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="address" value="Alamat" />
        <textarea id="address" name="address" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('address', $member->address ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('address')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label value="Role Anggota" />
        <div class="mt-2 grid gap-2 rounded-md border border-gray-200 p-4 sm:grid-cols-3">
            @forelse ($roles as $role)
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" @checked(in_array($role->id, $selectedRoles)) class="rounded border-gray-300 text-indigo-600">
                    {{ $role->name }}
                </label>
            @empty
                <p class="text-sm text-gray-500">Belum ada role. Tambahkan role anggota terlebih dahulu.</p>
            @endforelse
        </div>
        <x-input-error :messages="$errors->get('role_ids')" class="mt-2" />
    </div>

    @isset($member)
        <div class="rounded-md border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600 md:col-span-2">
            Nomor anggota: <span class="font-semibold text-gray-900">{{ $member->member_code }}</span>
            &middot; Pinjaman aktif: <span class="font-semibold text-gray-900">{{ $member->active_loans }}</span>
        </div>
    @endisset
</div>

<div class="mt-6 flex items-center gap-3">
    <x-primary-button>{{ $submitLabel }}</x-primary-button>
    <a href="{{ route('membership.members.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
</div>
