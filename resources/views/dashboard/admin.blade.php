@extends('layouts.app', ['title' => 'Dashboard Admin'])

@section('content')
<div class="space-y-10">
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div class="space-y-3">
      <p class="text-xs font-medium uppercase tracking-[0.3em] text-slate-400">Gambaran Umum</p>
      <div class="space-y-1">
        <h1 class="text-3xl font-semibold text-slate-900">Dashboard Admin</h1>
        <p class="text-sm text-slate-500">Monitor performa platform dan kelola entitas utama dari satu tempat.</p>
      </div>
    </div>
  </div>

  @if (session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-600 shadow-sm shadow-emerald-100/60">{{ session('success') }}</div>
  @endif
  @if (session('error'))
    <div class="rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-600 shadow-sm shadow-rose-100/60">{{ session('error') }}</div>
  @endif

  <div class="grid gap-6 lg:grid-cols-3">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg lg:col-span-2">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h2 class="text-base font-semibold text-slate-900">Saran Aktivitas</h2>
          <p class="mt-2 text-sm text-slate-500">Optimalkan data dengan pembaruan rutin pada entitas penting.</p>
        </div>
        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-500">Prioritas</span>
      </div>
      <div class="mt-6 space-y-4">
        <div class="flex items-start gap-4 rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm shadow-slate-200/40 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:bg-indigo-50/60">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-indigo-500">
            @include('layouts.partials.icon', ['name' => 'users', 'classes' => 'h-5 w-5'])
          </div>
          <div class="flex-1 space-y-1">
            <p class="font-medium text-slate-900">Periksa daftar pengguna</p>
            <p class="text-sm text-slate-500">Tinjau akun baru dan atur izin akses sesuai kebutuhan.</p>
          </div>
          <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-indigo-500 transition-all duration-300 ease-in-out hover:text-indigo-600">Buka</a>
        </div>
        <div class="flex items-start gap-4 rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm shadow-slate-200/40 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:bg-indigo-50/60">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-500">
            @include('layouts.partials.icon', ['name' => 'buildings', 'classes' => 'h-5 w-5'])
          </div>
          <div class="flex-1 space-y-1">
            <p class="font-medium text-slate-900">Lengkapi data sekolah</p>
            <p class="text-sm text-slate-500">Pastikan profil sekolah mutakhir untuk mendukung laporan.</p>
          </div>
          <a href="{{ route('admin.schools.index') }}" class="text-xs font-semibold text-indigo-500 transition-all duration-300 ease-in-out hover:text-indigo-600">Buka</a>
        </div>
        <div class="flex items-start gap-4 rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm shadow-slate-200/40 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:bg-indigo-50/60">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
            @include('layouts.partials.icon', ['name' => 'sparkles', 'classes' => 'h-5 w-5'])
          </div>
          <div class="flex-1 space-y-1">
            <p class="font-medium text-slate-900">Kelola undangan</p>
            <p class="text-sm text-slate-500">Kirim ulang undangan dan awasi status penerimaan.</p>
          </div>
          <a href="{{ route('admin.invitations.index') }}" class="text-xs font-semibold text-indigo-500 transition-all duration-300 ease-in-out hover:text-indigo-600">Buka</a>
        </div>
      </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg">
      <h2 class="text-base font-semibold text-slate-900">Tidak ada konten khusus</h2>
      <p class="mt-2 text-sm text-slate-500">Gunakan menu untuk mengelola sekolah, pengguna, dan undangan.</p>
      <div class="mt-6 space-y-3 text-sm text-slate-600">
        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">Aktivitas terbaru akan muncul setelah Anda melakukan tindakan administratif.</div>
      </div>
    </div>
  </div>
</div>
@endsection
