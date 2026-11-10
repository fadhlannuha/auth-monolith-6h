<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Buku</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route('catalog.books.store') }}">
                    @csrf
                    @include('catalog.books._form', ['submitLabel' => 'Simpan Buku'])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
