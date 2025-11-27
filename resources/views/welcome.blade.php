@extends('layouts.app', ['title' => config('app.name', 'Supervisi Digital')])

@php
    $user = Auth::user();
    $isAdmin = $user?->is_admin;
    $isSupervisor = $user ? $user->schools()->wherePivot('role', 'supervisor')->exists() : false;
    $isTeacher = $user ? $user->schools()->wherePivot('role', 'teacher')->exists() : false;

    $dashboardRoute = $isAdmin
        ? route('admin.dashboard')
        : ($isSupervisor
            ? route('supervisor.dashboard')
            : ($isTeacher
                ? route('guru.dashboard')
                : route('dashboard.index')));
@endphp

@section('content')
<div class="space-y-20">
    <section class="grid items-center gap-12 lg:grid-cols-[1.15fr_0.85fr]">
        <div class="space-y-8">
            <span class="inline-flex items-center gap-2 rounded-xl border border-indigo-100 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-indigo-500 shadow-sm shadow-indigo-100 transition-all duration-300 ease-in-out">Supervisi Digital</span>
            <div class="space-y-4">
                <h1 class="text-4xl font-semibold text-slate-900 sm:text-5xl">Dashboard premium untuk supervisi pendidikan yang tenang dan terukur</h1>
                <p class="text-base text-slate-500 sm:text-lg">Kelola jadwal, undangan, evaluasi, dan pelaporan dalam satu antarmuka putih futuristik yang ringan, responsif, dan terintegrasi dengan ekosistem Google.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                @guest
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-6 py-3 text-sm font-semibold text-white shadow-md shadow-indigo-200/60 transition-all duration-300 ease-in-out hover:opacity-90">
                        @include('layouts.partials.icon', ['name' => 'sparkles', 'classes' => 'h-4 w-4 text-white'])
                        Masuk untuk Memulai
                    </a>
                @else
                    @if ($dashboardRoute)
                        <a href="{{ $dashboardRoute }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-6 py-3 text-sm font-semibold text-white shadow-md shadow-indigo-200/60 transition-all duration-300 ease-in-out hover:opacity-90">
                            @include('layouts.partials.icon', ['name' => 'layout-dashboard', 'classes' => 'h-4 w-4 text-white'])
                            Lanjut ke Dashboard
                        </a>
                    @endif
                @endguest
            </div>
            <div class="grid gap-6 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/50">
                    <p class="text-3xl font-semibold text-slate-900">24+</p>
                    <p class="mt-1 text-sm text-slate-500">Sekolah aktif dalam ekosistem Supervisi Digital</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/50">
                    <p class="text-3xl font-semibold text-slate-900">1.2K</p>
                    <p class="mt-1 text-sm text-slate-500">Sesi supervisi tersinkronisasi lintas perangkat</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/50">
                    <p class="text-3xl font-semibold text-slate-900">98%</p>
                    <p class="mt-1 text-sm text-slate-500">Tingkat kepuasan terhadap antarmuka modern</p>
                </div>
            </div>
        </div>
        <div class="relative">
            <div class="absolute inset-0 -z-10 overflow-hidden rounded-3xl">
                <div class="absolute -bottom-40 -right-40 h-80 w-80 rounded-full bg-indigo-500 opacity-10 blur-3xl"></div>
                <div class="absolute -left-40 -top-40 h-80 w-80 rounded-full bg-blue-500 opacity-10 blur-3xl"></div>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white/80 shadow-xl shadow-slate-200/40 backdrop-blur-lg">
                <div class="border-b border-slate-200 p-4">
                    <div class="flex items-center gap-2">
                        <div class="flex h-3 w-3 rounded-full bg-rose-500"></div>
                        <div class="flex h-3 w-3 rounded-full bg-amber-500"></div>
                        <div class="flex h-3 w-3 rounded-full bg-emerald-500"></div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500 text-white shadow-md shadow-indigo-200/60">SD</div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Supervisi Digital</p>
                                <p class="text-xs text-slate-500">Platform Edukasi</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm shadow-slate-200/40">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-500">
                                    @include('layouts.partials.icon', ['name' => 'calendar', 'classes' => 'h-4 w-4'])
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-900">Jadwal Supervisi</p>
                                    <p class="text-xs text-slate-500">12 November, 10:00 AM</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm shadow-slate-200/40">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-500">
                                    @include('layouts.partials.icon', ['name' => 'graduation-cap', 'classes' => 'h-4 w-4'])
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-900">Evaluasi Pembelajaran</p>
                                    <p class="text-xs text-slate-500">85% Kualitas Tinggi</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="space-y-12">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-3xl font-semibold text-slate-900">Solusi supervisi digital terintegrasi untuk pendidikan modern</h2>
            <p class="mt-4 text-slate-500">Platform inovatif yang menghubungkan guru, pengawas, dan administrator dalam satu ekosistem kolaboratif yang aman dan skalabel.</p>
        </div>
        <div class="grid gap-6 md:grid-cols-3">
            <article class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/40">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100 text-indigo-500">
                    @include('layouts.partials.icon', ['name' => 'calendar', 'classes' => 'h-6 w-6'])
                </div>
                <h3 class="text-lg font-semibold text-slate-900">Penjadwalan Otomatis</h3>
                <p class="text-sm text-slate-500">Sistem penjadwalan cerdas yang menyesuaikan dengan ketersediaan guru dan pengawas secara real-time.</p>
            </article>
            <article class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/40">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-500">
                    @include('layouts.partials.icon', ['name' => 'shield', 'classes' => 'h-6 w-6'])
                </div>
                <h3 class="text-lg font-semibold text-slate-900">Keamanan Data</h3>
                <p class="text-sm text-slate-500">Arsitektur keamanan tingkat tinggi dengan enkripsi end-to-end dan kontrol akses berbasis peran.</p>
            </article>
            <article class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/40">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-500">
                    @include('layouts.partials.icon', ['name' => 'cloud', 'classes' => 'h-6 w-6'])
                </div>
                <h3 class="text-lg font-semibold text-slate-900">Sinkronisasi Cloud</h3>
                <p class="text-sm text-slate-500">Akses jadwal, dokumen, dan evaluasi dari mana saja dengan sinkronisasi real-time ke Google Drive.</p>
            </article>
        </div>
    </section>

    <section class="space-y-8 rounded-3xl bg-gradient-to-r from-indigo-500 to-blue-500 px-6 py-16 text-white">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-3xl font-semibold">Siap meningkatkan kualitas supervisi pendidikan?</h2>
            <p class="mt-4 opacity-90">Bergabunglah dengan ribuan institusi pendidikan yang telah beralih ke Supervisi Digital.</p>
        </div>
        <div class="flex flex-wrap justify-center gap-3">
            @guest
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-semibold text-indigo-500 shadow-md shadow-indigo-900/20 transition-all duration-300 ease-in-out hover:opacity-90">
                    @include('layouts.partials.icon', ['name' => 'sparkles', 'classes' => 'h-4 w-4'])
                    Masuk Sekarang
                </a>
            @else
                @if ($dashboardRoute)
                    <a href="{{ $dashboardRoute }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-semibold text-indigo-500 shadow-md shadow-indigo-900/20 transition-all duration-300 ease-in-out hover:opacity-90">
                        @include('layouts.partials.icon', ['name' => 'layout-dashboard', 'classes' => 'h-4 w-4'])
                        Buka Dashboard
                    </a>
                @endif
            @endguest
            <a href="mailto:{{ config('mail.from.address') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/30 bg-transparent px-6 py-3 text-sm font-semibold text-white backdrop-blur-sm transition-all duration-300 ease-in-out hover:border-white/50 hover:bg-white/10">
                @include('layouts.partials.icon', ['name' => 'mail', 'classes' => 'h-4 w-4'])
                Ajukan Akses Institusi
            </a>
        </div>
    </section>
</div>
@endsection