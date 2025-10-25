@extends('layouts.app', ['title' => 'Profil'])

@section('content')
@php
    $isAdmin = method_exists($user, 'is_admin') ? $user->is_admin : ($user->hasRole('admin') ?? false);
    $supervisorSchools = $user->schools()->wherePivot('role', 'supervisor')->orderBy('name')->get(['schools.id', 'schools.name']);
    $teacherSchool = $user->schools()->wherePivot('role', 'teacher')->orderBy('name')->first(['schools.id', 'schools.name']);
    $roles = collect([
        $isAdmin ? 'Admin' : null,
        $supervisorSchools->isNotEmpty() ? 'Supervisor' : null,
        $teacherSchool ? 'Guru' : null,
    ])->filter()->values()->all();
@endphp

<div class="space-y-10">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div class="space-y-3">
            <p class="text-xs font-medium uppercase tracking-[0.3em] text-slate-400">Akun Anda</p>
            <div class="space-y-1">
                <h1 class="text-3xl font-semibold text-slate-900">Profil</h1>
                <p class="text-sm text-slate-500">Pantau identitas dan koneksi akun Anda di ekosistem Supervisi Digital.</p>
            </div>
        </div>
        <div class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 shadow-sm shadow-slate-200/70">
            @include('layouts.partials.icon', ['name' => 'user-circle', 'classes' => 'h-4 w-4 text-indigo-500'])
            {{ $user->email }}
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-600 shadow-sm shadow-emerald-100/60">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600 shadow-sm shadow-rose-100/60">{{ session('error') }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white/90 p-8 shadow-md shadow-slate-200/40">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">Informasi Akun</h2>
                <span class="rounded-lg border border-indigo-100 bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-500">Terverifikasi</span>
            </div>
            <dl class="mt-6 space-y-4 text-sm text-slate-600">
                <div class="flex flex-col gap-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Nama</dt>
                    <dd class="text-slate-900">{{ $user->name }}</dd>
                </div>
                <div class="flex flex-col gap-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Email</dt>
                    <dd class="text-slate-900">{{ $user->email }}</dd>
                </div>
                <div class="flex flex-col gap-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">NIP</dt>
                    <dd>{{ $user->nip ?? '-' }}</dd>
                </div>
                <div class="flex flex-col gap-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Jenis Guru</dt>
                    <dd>{{ $user->teacher_type_label ?? '—' }}</dd>
                </div>
                <div class="flex flex-col gap-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Detail Penugasan</dt>
                    <dd>{{ $user->teacher_detail_label ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white/90 p-8 shadow-md shadow-slate-200/40">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">Peran & Sekolah</h2>
                <span class="rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-500">Role Matrix</span>
            </div>
            <div class="mt-6 space-y-4 text-sm text-slate-600">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Peran aktif</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @forelse($roles as $role)
                            <span class="inline-flex items-center gap-2 rounded-lg border border-indigo-100 bg-white px-3 py-1 text-xs font-semibold text-indigo-500">
                                @include('layouts.partials.icon', ['name' => 'sparkles', 'classes' => 'h-3.5 w-3.5 text-indigo-500'])
                                {{ $role }}
                            </span>
                        @empty
                            <span class="text-xs text-slate-400">Belum ada peran</span>
                        @endforelse
                    </div>
                </div>
                @if($supervisorSchools->isNotEmpty())
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Sekolah sebagai Supervisor</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($supervisorSchools as $school)
                                <span class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-[#F9FAFB] px-3 py-1 text-xs font-medium text-slate-600">
                                    @include('layouts.partials.icon', ['name' => 'buildings', 'classes' => 'h-3.5 w-3.5 text-indigo-400'])
                                    {{ $school->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if($teacherSchool)
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Sekolah sebagai Guru</p>
                        <div class="mt-2 inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-[#F9FAFB] px-3 py-1 text-xs font-medium text-slate-600">
                            @include('layouts.partials.icon', ['name' => 'buildings', 'classes' => 'h-3.5 w-3.5 text-indigo-400'])
                            {{ $teacherSchool->name }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white/90 p-8 shadow-md shadow-slate-200/40">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Koneksi Google Workspace</h2>
                <p class="mt-1 text-sm text-slate-500">Sinkronkan dokumen dan video supervisi secara otomatis melalui Google Drive.</p>
            </div>
            <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-medium text-slate-500">
                @include('layouts.partials.icon', ['name' => 'cloud', 'classes' => 'h-4 w-4 text-indigo-500'])
                Workspace Sync
            </span>
        </div>

        @if ($user->google_access_token)
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/70 px-4 py-3 text-sm text-emerald-600">
                    <span class="font-semibold">Status: </span>Tertaut
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#F9FAFB] px-4 py-3 text-sm text-slate-600">
                    <span class="font-semibold">Email Google: </span>{{ $user->google_email ?? '-' }}
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#F9FAFB] px-4 py-3 text-sm text-slate-600">
                    <span class="font-semibold">Kedaluwarsa Token: </span>{{ optional($user->google_token_expires_at)->format('d-m-Y H:i') ?? '-' }}
                </div>
            </div>
            <div class="mt-6 flex flex-wrap gap-3">
                <form action="{{ route('profile.google.disconnect') }}" method="post">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-rose-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-rose-200/60 transition-all duration-300 ease-in-out hover:opacity-90">
                        @include('layouts.partials.icon', ['name' => 'shield-x', 'classes' => 'h-4 w-4 text-white'])
                        Putuskan Tautan
                    </button>
                </form>
                <a href="{{ route('google.redirect') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm shadow-slate-200/70 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-600">
                    @include('layouts.partials.icon', ['name' => 'refresh', 'classes' => 'h-4 w-4 text-indigo-500'])
                    Perbarui Izin
                </a>
            </div>
        @else
            <div class="mt-6 space-y-4">
                <div class="rounded-xl border border-amber-200 bg-amber-50/70 px-4 py-3 text-sm text-amber-600">
                    <span class="font-semibold">Status: </span>Belum tertaut
                </div>
                <p class="text-xs text-slate-500">Gunakan alamat Google yang sama dengan email institusi Anda (<span class="font-mono text-slate-600">{{ $user->email }}</span>). Akses dengan email berbeda akan ditolak demi keamanan.</p>
                <a href="{{ route('google.redirect') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-200/70 transition-all duration-300 ease-in-out hover:opacity-90 md:w-auto">
                    @include('layouts.partials.icon', ['name' => 'cloud-upload', 'classes' => 'h-4 w-4 text-white'])
                    Tautkan Akun Google
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
