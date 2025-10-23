@extends('layouts.app', ['title' => 'Buat Undangan'])

@section('content')
<div class="space-y-10">
  <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div class="space-y-3">
      <p class="text-xs font-medium uppercase tracking-[0.3em] text-slate-400">Manajemen Akses</p>
      <div class="space-y-1">
        <h1 class="text-3xl font-semibold text-slate-900">Buat Undangan</h1>
        <p class="text-sm text-slate-500">Kirim undangan dengan role dan informasi sekolah yang tepat.</p>
      </div>
    </div>
    <x-back-button :href="route('admin.invitations.index')" label="Kembali" />
  </div>

  @if ($errors->any())
    <div class="rounded-xl border border-rose-200 bg-rose-50 p-5 text-sm text-rose-600 shadow-sm shadow-rose-100/60">
      <p class="font-semibold text-rose-500">Periksa kembali formulir:</p>
      <ul class="mt-3 list-inside list-disc space-y-1">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40">
    <form action="{{ route('admin.invitations.store') }}" method="post" class="space-y-6">
      @csrf
      <div class="grid gap-5 md:grid-cols-2">
        <div class="space-y-2">
          <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Email</label>
          <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
        </div>
        <div class="space-y-2">
          <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Nama Pengguna</label>
          <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" placeholder="Nama sesuai undangan" required>
          <p class="text-xs text-slate-400">Nama akan digunakan saat penerima menyelesaikan proses registrasi.</p>
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Role</label>
        <select name="role" id="role" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
          <option value="">Pilih role undangan</option>
          <option value="admin" @selected(old('role') === 'admin')>Admin</option>
          <option value="supervisor" @selected(old('role') === 'supervisor')>Supervisor</option>
          <option value="teacher" @selected(old('role') === 'teacher')>Guru</option>
        </select>
      </div>

      <div id="supervisor-wrapper" class="space-y-2">
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Sekolah untuk Supervisor</label>
        <select name="supervisor_school_id" id="supervisor_school" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200">
          <option value="">Pilih sekolah</option>
          @foreach ($schools as $s)
            <option value="{{ $s->id }}" @selected(old('supervisor_school_id') == $s->id)>{{ $s->name }}</option>
          @endforeach
        </select>
        <p class="text-xs text-slate-400">Supervisor hanya dapat diundang untuk satu sekolah dalam satu undangan.</p>
      </div>

      <div id="teacher-wrapper" class="space-y-2">
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Sekolah untuk Guru</label>
        <select name="teacher_school_id" id="teacher_school" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200">
          <option value="">Tidak sebagai guru</option>
          @foreach ($schools as $s)
            <option value="{{ $s->id }}" @selected(old('teacher_school_id') == $s->id)>{{ $s->name }}</option>
          @endforeach
        </select>
        <p class="text-xs text-slate-400">Guru hanya dapat terhubung dengan satu sekolah.</p>
      </div>

      <div class="grid gap-5 md:grid-cols-2">
        <div class="space-y-2">
          <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Masa Berlaku (hari)</label>
          <input type="number" name="expires_in_days" min="1" max="30" value="{{ old('expires_in_days', 7) }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200">
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-500">
          Pastikan email penerima valid dan aktif. Undangan otomatis kedaluwarsa sesuai durasi yang ditetapkan.
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-200/60 transition-all duration-300 ease-in-out hover:opacity-90">
          @include('layouts.partials.icon', ['name' => 'sparkles', 'classes' => 'h-4 w-4 text-white'])
          Kirim Undangan
        </button>
        <a href="{{ route('admin.invitations.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm shadow-slate-200/70 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-600">Batal</a>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const roleSelect = document.getElementById('role');
    const supervisorWrapper = document.getElementById('supervisor-wrapper');
    const teacherWrapper = document.getElementById('teacher-wrapper');
    const syncVisibility = () => {
      const value = roleSelect.value;
      supervisorWrapper.style.display = value === 'supervisor' ? 'block' : 'none';
      teacherWrapper.style.display = value === 'teacher' ? 'block' : 'none';
    };
    roleSelect.addEventListener('change', syncVisibility);
    syncVisibility();
  });
</script>
@endpush
@endsection
