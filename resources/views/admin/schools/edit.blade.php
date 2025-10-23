@extends('layouts.app', ['title' => 'Ubah Sekolah'])

@section('content')
<div class="space-y-10">
  <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div class="space-y-3">
      <p class="text-xs font-medium uppercase tracking-[0.3em] text-slate-400">Data Master</p>
      <div class="space-y-1">
        <h1 class="text-3xl font-semibold text-slate-900">Ubah Sekolah</h1>
        <p class="text-sm text-slate-500">Perbarui informasi sekolah untuk memastikan data supervisi tetap akurat.</p>
      </div>
    </div>
    <x-back-button :href="route('admin.schools.index')" label="Kembali" />
  </div>

  @if ($errors->any())
    <div class="rounded-xl border border-rose-200 bg-rose-50 p-5 text-sm text-rose-600 shadow-sm shadow-rose-100/60">
      <p class="font-semibold text-rose-500">Periksa kembali isian berikut:</p>
      <ul class="mt-3 list-inside list-disc space-y-1">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40">
    <form action="{{ route('admin.schools.update', $school) }}" method="post" class="space-y-6">
      @csrf
      <div class="space-y-2">
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Nama Sekolah</label>
        <input type="text" name="name" value="{{ old('name', $school->name) }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
      </div>
      <div class="space-y-2">
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Alamat</label>
        <textarea name="address" rows="4" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200">{{ old('address', $school->address) }}</textarea>
      </div>
      <div class="flex flex-wrap items-center gap-3">
        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-200/60 transition-all duration-300 ease-in-out hover:opacity-90">
          @include('layouts.partials.icon', ['name' => 'check-circle', 'classes' => 'h-4 w-4 text-white'])
          Simpan Perubahan
        </button>
        <a href="{{ route('admin.schools.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm shadow-slate-200/70 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-600">Batal</a>
      </div>
    </form>
  </div>

  <form action="{{ route('admin.schools.destroy', $school) }}" method="post" class="flex items-center justify-between gap-4 rounded-xl border border-rose-200 bg-rose-50 p-6 text-sm text-rose-600 shadow-sm shadow-rose-100/60 js-confirm" data-message="Hapus sekolah ini? Tindakan tidak dapat dibatalkan." data-variant="danger">
    @csrf
    @method('DELETE')
    <div>
      <p class="text-sm font-semibold text-rose-600">Hapus sekolah</p>
      <p class="mt-1 text-xs text-rose-500">Sekolah akan dihapus permanen dari daftar dan tidak dapat dipulihkan.</p>
    </div>
    <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-rose-300 bg-rose-100 px-4 py-2 text-sm font-semibold text-rose-600 transition-all duration-300 ease-in-out hover:bg-rose-200">
      @include('layouts.partials.icon', ['name' => 'trash', 'classes' => 'h-4 w-4'])
      Hapus Sekolah
    </button>
  </form>
</div>
@endsection
