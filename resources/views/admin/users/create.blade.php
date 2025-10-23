@extends('layouts.app', ['title' => 'Admin - Tambah Pengguna'])

@section('content')
<div class="mx-auto max-w-3xl space-y-10">
  <div class="flex items-start justify-between gap-4">
    <div class="space-y-3">
      <p class="text-xs font-medium uppercase tracking-[0.3em] text-slate-400">Manajemen</p>
      <div class="space-y-1">
        <h1 class="text-3xl font-semibold text-slate-900">Tambah Pengguna</h1>
        <p class="text-sm text-slate-500">Buat akun baru dengan role yang sesuai dan hubungkan ke sekolah terkait.</p>
      </div>
    </div>
    <x-back-button :href="route('admin.users.index')" label="Kembali" />
  </div>

  @if ($errors->any())
    <div class="rounded-xl border border-rose-200 bg-rose-50 p-5 text-sm text-rose-600 shadow-sm shadow-rose-100/60">
      <p class="font-semibold text-rose-500">Silakan perbaiki kesalahan berikut:</p>
      <ul class="mt-3 list-inside list-disc space-y-1">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('admin.users.store') }}" method="post" class="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40">
    @csrf
    <div class="grid gap-5 md:grid-cols-2">
      <div class="space-y-2">
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Nama Lengkap</label>
        <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
      </div>
      <div class="space-y-2">
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
      </div>
    </div>

    <div class="space-y-2">
      <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Kata Sandi</label>
      <div class="relative">
        <input type="password" name="password" class="js-pass w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 pr-12 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
        <button type="button" class="js-toggle-pass absolute inset-y-0 right-3 flex items-center rounded-lg border border-slate-200 bg-white px-2 text-slate-400 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-500" aria-label="Tampilkan atau sembunyikan kata sandi">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M2.5 12s3.5-6.5 9.5-6.5 9.5 6.5 9.5 6.5-3.5 6.5-9.5 6.5S2.5 12 2.5 12z" /><circle cx="12" cy="12" r="3" /></svg>
        </button>
      </div>
      <p class="text-xs text-slate-400">Password minimal 8 karakter dan kombinasi angka serta huruf.</p>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
      <div class="space-y-2">
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Role</label>
        <select name="role" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
          <option value="admin" @selected(old('role') === 'admin')>Admin</option>
          <option value="supervisor" @selected(old('role') === 'supervisor')>Supervisor</option>
          <option value="teacher" @selected(old('role') === 'teacher')>Guru</option>
        </select>
      </div>
      <div class="space-y-2">
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Sekolah</label>
        <select name="school_id" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200">
          <option value="">Pilih sekolah</option>
          @foreach(\App\Models\School::orderBy('name')->get() as $school)
            <option value="{{ $school->id }}" @selected(old('school_id') == $school->id)>{{ $school->name }}</option>
          @endforeach
        </select>
        <p class="text-xs text-slate-400">Pilih sekolah untuk role supervisor atau guru.</p>
      </div>
    </div>

    <div class="flex flex-wrap items-center gap-3">
      <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-200/60 transition-all duration-300 ease-in-out hover:opacity-90">
        @include('layouts.partials.icon', ['name' => 'check-circle', 'classes' => 'h-4 w-4 text-white'])
        Simpan Pengguna
      </button>
      <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm shadow-slate-200/70 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-600">Batal</a>
    </div>
  </form>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const toggleButton = document.querySelector('.js-toggle-pass');
    const passwordInput = document.querySelector('.js-pass');
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
