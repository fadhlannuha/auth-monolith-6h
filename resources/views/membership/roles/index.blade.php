<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Role Anggota</h2>
                <p class="mt-1 text-sm text-gray-500">Role dikelola di dalam Membership Context.</p>
            </div>
            <a href="{{ route('membership.members.index') }}" class="rounded-md bg-gray-100 px-3 py-2 text-sm text-gray-700 hover:bg-gray-200">Kembali ke Anggota</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="grid gap-6 md:grid-cols-3">
                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Tambah Role</h3>
                    <form method="POST" action="{{ route('membership.roles.store') }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="name" value="Nama Role" />
                            <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name')" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="description" value="Deskripsi" />
                            <textarea id="description" name="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                        </div>
                        <x-primary-button>Simpan Role</x-primary-button>
                    </form>
                </div>

                <div class="rounded-lg bg-white shadow-sm md:col-span-2">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-5 py-3">Role</th>
                                <th class="px-5 py-3">Jumlah Anggota</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($roles as $role)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-3">
                                        <span class="font-medium text-gray-900">{{ $role->name }}</span>
                                        <div class="text-xs text-gray-500">{{ $role->slug }} &middot; {{ $role->description }}</div>
                                    </td>
                                    <td class="px-5 py-3 text-gray-700">{{ $role->members_count }}</td>
                                    <td class="px-5 py-3 text-right">
                                        <form method="POST" action="{{ route('membership.roles.destroy', $role) }}" onsubmit="return confirm('Hapus role ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-5 py-10 text-center text-gray-500">Belum ada role.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
