@extends('layouts.app', ['title' => 'Ubah Pengguna'])

@section('content')
<div class="space-y-10">
  <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div class="space-y-3">
      <p class="text-xs font-medium uppercase tracking-[0.3em] text-slate-400">Pengaturan</p>
      <div class="space-y-1">
        <h1 class="text-3xl font-semibold text-slate-900">Ubah Pengguna</h1>
        <p class="text-sm text-slate-500">Perbarui informasi akun, peran, dan akses sekolah untuk pengguna ini.</p>
      </div>
    </div>
    <x-back-button :href="route('admin.users.index')" label="Kembali ke Pengguna" />
  </div>

  @if ($errors->any())
    <div class="rounded-xl border border-rose-200 bg-rose-50 p-5 text-sm text-rose-600 shadow-sm shadow-rose-100/60">
      <p class="font-semibold text-rose-500">Periksa kembali input Anda:</p>
      <ul class="mt-3 list-inside list-disc space-y-1">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @php
    $currentRole = $user->is_admin
        ? 'admin'
        : ($user->schools()->wherePivot('role', 'supervisor')->exists()
            ? 'supervisor'
            : ($user->schools()->wherePivot('role', 'teacher')->exists() ? 'teacher' : ''));
  @endphp

  <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40">
    <form action="{{ route('admin.users.update', $user) }}" method="post" class="space-y-6">
      @csrf
      <div class="grid gap-5 md:grid-cols-2">
        <div class="space-y-2">
          <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Nama Lengkap</label>
          <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
        </div>
        <div class="space-y-2">
          <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Email</label>
          <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Kata Sandi</label>
        <div class="relative">
          <input type="password" name="password" class="js-pass w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 pr-12 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" placeholder="Biarkan kosong jika tidak diubah" autocomplete="new-password">
          <button type="button" class="js-toggle-pass absolute inset-y-0 right-3 flex items-center rounded-lg border border-slate-200 bg-white px-2 text-slate-400 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-500" aria-label="Tampilkan atau sembunyikan kata sandi">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M2.5 12s3.5-6.5 9.5-6.5 9.5 6.5 9.5 6.5-3.5 6.5-9.5 6.5S2.5 12 2.5 12z" /><circle cx="12" cy="12" r="3" /></svg>
          </button>
        </div>
        <p class="text-xs text-slate-400">Kata sandi minimal 8 karakter. Kosongkan jika tidak ingin mengubah.</p>
      </div>

      <div class="grid gap-5 md:grid-cols-2">
        <div class="space-y-2">
          <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Role Pengguna</label>
          <select name="role" id="role" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
            <option value="">Pilih role pengguna</option>
            <option value="admin" @selected(old('role', $currentRole) === 'admin')>Admin</option>
            <option value="supervisor" @selected(old('role', $currentRole) === 'supervisor')>Supervisor</option>
            <option value="teacher" @selected(old('role', $currentRole) === 'teacher')>Guru</option>
          </select>
        </div>
      </div>

      <div id="supervisor-wrapper" class="space-y-3">
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Sekolah yang diawasi</label>
        <select name="supervisor_school_ids[]" id="supervisor_schools" multiple class="h-40 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200">
          @foreach ($schools as $s)
            <option value="{{ $s->id }}" @selected(collect(old('supervisor_school_ids', $user->schools()->wherePivot('role', 'supervisor')->pluck('schools.id')->toArray()))->contains($s->id))>{{ $s->name }}</option>
          @endforeach
        </select>
        <p class="text-xs text-slate-400">Supervisor dapat bertanggung jawab pada beberapa sekolah sekaligus.</p>
      </div>

      <div id="teacher-wrapper" class="space-y-3">
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Sekolah guru</label>
        <select name="teacher_school_id" id="teacher_school" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200">
          <option value="">Tidak sebagai guru</option>
          @foreach ($schools as $s)
            <option value="{{ $s->id }}" @selected(old('teacher_school_id', $user->schools()->wherePivot('role', 'teacher')->value('schools.id')) == $s->id)>{{ $s->name }}</option>
          @endforeach
        </select>
        <p class="text-xs text-slate-400">Guru hanya dapat terdaftar di satu sekolah.</p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-200/60 transition-all duration-300 ease-in-out hover:opacity-90">
          @include('layouts.partials.icon', ['name' => 'check-circle', 'classes' => 'h-4 w-4 text-white'])
          Simpan Perubahan
        </button>
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm shadow-slate-200/70 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-600">Batal</a>
      </div>
    </form>
  </div>

  <form action="{{ route('admin.users.deactivate', $user) }}" method="post" class="flex items-center justify-between gap-4 rounded-xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-600 shadow-sm shadow-amber-100/60 js-confirm" data-message="Nonaktifkan pengguna ini?" data-variant="warning">
    @csrf
    <div>
      <p class="text-sm font-semibold text-amber-600">Nonaktifkan akun pengguna ini</p>
      <p class="mt-1 text-xs text-amber-500">Pengguna tetap tercatat namun tidak dapat masuk ke sistem.</p>
    </div>
    <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-amber-300 bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-600 transition-all duration-300 ease-in-out hover:bg-amber-200">
      @include('layouts.partials.icon', ['name' => 'shield-alert', 'classes' => 'h-4 w-4'])
      Nonaktifkan
    </button>
  </form>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const roleSelect = document.getElementById('role');
    const supervisorWrapper = document.getElementById('supervisor-wrapper');
    const teacherWrapper = document.getElementById('teacher-wrapper');
    const passwordInput = document.querySelector('.js-pass');
    const toggleButton = document.querySelector('.js-toggle-pass');

    const syncRoleVisibility = () => {
      const value = roleSelect.value;
      supervisorWrapper.style.display = value === 'supervisor' ? 'block' : 'none';
      teacherWrapper.style.display = value === 'teacher' ? 'block' : 'none';
    };

    roleSelect.addEventListener('change', syncRoleVisibility);
    syncRoleVisibility();

    if (toggleButton && passwordInput) {
      const icons = {
        show: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M3 3l18 18" /><path d="M10.6 5.3a11 11 0 0 1 1.4-.05c6 0 9.5 6.5 9.5 6.5a18.3 18.3 0 0 1-3 3.8" /><path d="M6.3 6.3A18.7 18.7 0 0 0 2.5 12s3.5 6.5 9.5 6.5c1.2 0 2.3-.2 3.3-.5" /><circle cx="12" cy="12" r="3" /></svg>',
        hide: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M2.5 12s3.5-6.5 9.5-6.5 9.5 6.5 9.5 6.5-3.5 6.5-9.5 6.5S2.5 12 2.5 12z" /><circle cx="12" cy="12" r="3" /></svg>'
      };
      const renderIcon = () => {
        toggleButton.innerHTML = passwordInput.type === 'password' ? icons.hide : icons.show;
      };
      renderIcon();
      toggleButton.addEventListener('click', () => {
        passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
        renderIcon();
      });
    }
  });
</script>
@endpush
@endsection
