<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Anggota</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route('membership.members.update', $member) }}">
                    @csrf
                    @method('PUT')
                    @include('membership.members._form', ['submitLabel' => 'Perbarui Anggota'])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
