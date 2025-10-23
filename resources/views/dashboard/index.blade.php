@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
<div class="space-y-10">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="space-y-3">
            <p class="text-xs font-medium uppercase tracking-[0.3em] text-slate-400">Ringkasan</p>
            <div class="space-y-1">
                <h1 class="text-3xl font-semibold text-slate-900">Dashboard</h1>
                <p class="text-sm text-slate-500">Kelola aktivitas supervisi digital Anda dengan visual yang tenang dan futuristik.</p>
            </div>
        </div>
        <a href="{{ url('/my/schedules') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-200/60 transition-all duration-300 ease-in-out hover:opacity-90">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                <path d="M5.5 5h13" />
                <path d="M16 3.5v3" />
                <path d="M8 3.5v3" />
                <path d="M5.5 9h13" />
                <path d="M12 13l3 3-3 3" />
                <path d="M9 16h6" />
            </svg>
            Jadwal Saya
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Aksi Cepat</h2>
                    <p class="mt-2 text-sm text-slate-500">Mulai hari Anda dengan navigasi langsung ke tugas prioritas.</p>
                </div>
                <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-500">Fokus</span>
            </div>
            <ul class="mt-6 space-y-4">
                <li class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 shadow-sm shadow-slate-200/40 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:bg-indigo-50/60">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                                <path d="M3.5 6.5h17" />
                                <path d="M3.5 12h17" />
                                <path d="M3.5 17.5h11" />
                            </svg>
                        </span>
                        <div class="space-y-1">
                            <p class="font-medium text-slate-900">Lihat Jadwal Aktif</p>
                            <p class="text-xs text-slate-500">Pantau supervisi mendatang secara terpusat.</p>
                        </div>
                    </div>
                    <a href="{{ url('/my/schedules') }}" class="text-xs font-semibold text-indigo-500 transition-all duration-300 ease-in-out hover:text-indigo-600">Buka</a>
                </li>
                <li class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 shadow-sm shadow-slate-200/40 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:bg-indigo-50/60">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-500">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                                <path d="M12 6.5v11" />
                                <path d="m8.5 10 3.5-3.5 3.5 3.5" />
                                <path d="M6 17.5h12" />
                            </svg>
                        </span>
                        <div class="space-y-1">
                            <p class="font-medium text-slate-900">Debug Google Config</p>
                            <p class="text-xs text-slate-500">Tersedia untuk kebutuhan pengembangan lokal.</p>
                        </div>
                    </div>
                    <a href="{{ url('/debug/google-config') }}" class="text-xs font-semibold text-indigo-500 transition-all duration-300 ease-in-out hover:text-indigo-600">Buka</a>
                </li>
            </ul>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Status Sistem</h2>
                    <p class="mt-2 text-sm text-slate-500">Lacak notifikasi penting dan aktivitas terbaru.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-500">Realtime</span>
            </div>
            <div class="mt-6 space-y-3 text-sm text-slate-600">
                @if (session('success'))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-600 shadow-sm shadow-emerald-100/60">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-600 shadow-sm shadow-rose-100/60">{{ session('error') }}</div>
                @endif
                <p class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">Selamat datang di Supervisi Digital. Pantau jadwal dan progres supervisi secara menyeluruh.</p>
            </div>
        </div>
    </div>
</div>
@endsection
