@extends('layouts.app', ['title' => 'Dashboard Guru'])

@section('content')
<div class="space-y-10">
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div class="space-y-3">
      <p class="text-xs font-medium uppercase tracking-[0.3em] text-slate-400">Aktivitas Guru</p>
      <div class="space-y-1">
        <h1 class="text-3xl font-semibold text-slate-900">Dashboard Guru</h1>
        <p class="text-sm text-slate-500">Kelola jadwal supervisi dan unggah dokumen pembelajaran dengan mudah.</p>
      </div>
    </div>
  </div>

  @if (session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-600 shadow-sm shadow-emerald-100/60">{{ session('success') }}</div>
  @endif
  @if (session('error'))
    <div class="rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-600 shadow-sm shadow-rose-100/60">{{ session('error') }}</div>
  @endif

  <div class="grid gap-6 lg:grid-cols-2">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg">
      <h2 class="text-base font-semibold text-slate-900">Langkah Utama</h2>
      <p class="mt-2 text-sm text-slate-500">Selesaikan tugas prioritas agar proses supervisi berjalan mulus.</p>
      <div class="mt-6 space-y-4">
        <div class="flex items-start gap-4 rounded-xl border border-slate-200 bg-white px-4 py-4 text-sm text-slate-600 shadow-sm shadow-slate-200/40 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:bg-indigo-50/60">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-indigo-500">
            @include('layouts.partials.icon', ['name' => 'calendar', 'classes' => 'h-5 w-5'])
          </div>
          <div class="flex-1 space-y-1">
            <p class="font-medium text-slate-900">Tinjau jadwal supervisi</p>
            <p class="text-xs text-slate-500 md:text-sm">Pastikan seluruh sesi siap dijalankan sesuai rencana.</p>
          </div>
          <a href="{{ route('guru.schedules') }}" class="text-xs font-semibold text-indigo-500 transition-all duration-300 ease-in-out hover:text-indigo-600">Buka</a>
        </div>
        <div class="flex items-start gap-4 rounded-xl border border-slate-200 bg-white px-4 py-4 text-sm text-slate-600 shadow-sm shadow-slate-200/40 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:bg-indigo-50/60">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-500">
            @include('layouts.partials.icon', ['name' => 'cloud-upload', 'classes' => 'h-5 w-5'])
          </div>
          <div class="flex-1 space-y-1">
            <p class="font-medium text-slate-900">Unggah berkas pendukung</p>
            <p class="text-xs text-slate-500 md:text-sm">Siapkan RPP, rekaman pembelajaran, dan materi evaluasi.</p>
          </div>
          <a href="{{ route('guru.schedules') }}" class="text-xs font-semibold text-indigo-500 transition-all duration-300 ease-in-out hover:text-indigo-600">Mulai</a>
        </div>
      </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg">
      <h2 class="text-base font-semibold text-slate-900">Tips Sukses Supervisi</h2>
      <div class="mt-4 space-y-3 text-sm text-slate-600">
        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">Catat refleksi pembelajaran setelah setiap sesi untuk meningkatkan kualitas pertemuan berikutnya.</div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">Pantau status unggahan dokumen secara berkala agar seluruh kebutuhan evaluasi terpenuhi.</div>
      </div>
    </div>
  </div>
</div>
@endsection
