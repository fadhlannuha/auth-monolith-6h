<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="mb-6 rounded-lg bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Selamat datang, {{ auth()->user()->name }}</h3>
                <p class="mt-1 text-sm text-gray-600">
                    Sistem Perpustakaan Modular dengan pendekatan Domain-Driven Design (DDD).
                    Setiap bounded context memiliki model dan tabelnya sendiri.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <a href="{{ route('catalog.books.index') }}" class="rounded-lg bg-white p-6 shadow-sm transition hover:shadow-md">
                    <div class="text-xs font-semibold uppercase tracking-wide text-indigo-600">Catalog Context</div>
                    <div class="mt-2 text-lg font-bold text-gray-900">Buku &amp; Kategori</div>
                    <p class="mt-1 text-sm text-gray-600">Kelola buku, penulis, penerbit, serta kategori bertingkat.</p>
                </a>

                <a href="{{ route('membership.members.index') }}" class="rounded-lg bg-white p-6 shadow-sm transition hover:shadow-md">
                    <div class="text-xs font-semibold uppercase tracking-wide text-emerald-600">Membership Context</div>
                    <div class="mt-2 text-lg font-bold text-gray-900">Anggota &amp; Keanggotaan</div>
                    <p class="mt-1 text-sm text-gray-600">Kelola anggota, role, dan masa aktif keanggotaan.</p>
                </a>

                <a href="{{ route('circulation.loans.index') }}" class="rounded-lg bg-white p-6 shadow-sm transition hover:shadow-md">
                    <div class="text-xs font-semibold uppercase tracking-wide text-amber-600">Circulation Context</div>
                    <div class="mt-2 text-lg font-bold text-gray-900">Peminjaman &amp; Denda</div>
                    <p class="mt-1 text-sm text-gray-600">Transaksi peminjaman, pengembalian, dan perhitungan denda otomatis.</p>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
