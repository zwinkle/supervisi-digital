@extends('layouts.app', ['title' => 'Pengawas - Buat Jadwal'])

@section('content')
<div class="space-y-10">
  <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div class="space-y-3">
      <p class="text-xs font-medium uppercase tracking-[0.3em] text-slate-400">Manajemen Jadwal</p>
      <div class="space-y-1">
        <h1 class="text-3xl font-semibold text-slate-900">Buat Jadwal Supervisi</h1>
        <p class="text-sm text-slate-500">Atur sesi supervisi baru dengan memilih guru, sekolah, dan catatan pendukung.</p>
      </div>
    </div>
    <a href="{{ route('supervisor.schedules') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm shadow-slate-200/70 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-600">
      @include('layouts.partials.icon', ['name' => 'chevron-left', 'classes' => 'h-4 w-4 text-slate-400'])
      Kembali ke Jadwal
    </a>
  </div>

  @if ($errors->any())
    <div class="rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-600 shadow-sm shadow-rose-100/60">
      <ul class="list-disc space-y-1 pl-5">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40">
    <form action="{{ route('supervisor.schedules.store') }}" method="post" class="space-y-6">
      @csrf

      <div class="grid gap-6 md:grid-cols-2">
        <div class="space-y-2">
          <label class="text-sm font-semibold text-slate-700">Judul Sesi</label>
          <input type="text" name="title" value="{{ old('title') }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" placeholder="Sesi Supervisi" required>
        </div>
        <div class="space-y-2">
          <label class="text-sm font-semibold text-slate-700">Sekolah</label>
          @if ($schools->count() === 1)
            @php($only = $schools->first())
            <input type="hidden" name="school_id" value="{{ $only->id }}">
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-600">{{ $only->name }}</div>
            <p class="text-xs text-slate-400">Sekolah otomatis dipilih dari penugasan Anda.</p>
          @else
            <select name="school_id" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
              <option value="" disabled {{ old('school_id') ? '' : 'selected' }}>Pilih Sekolah</option>
              @foreach ($schools as $school)
                <option value="{{ $school->id }}" @selected(old('school_id') == $school->id)>{{ $school->name }}</option>
              @endforeach
            </select>
          @endif
        </div>
      </div>

      <div class="grid gap-6 md:grid-cols-2">
        <div class="space-y-2">
          <label class="text-sm font-semibold text-slate-700">Guru</label>
          <select name="teacher_id" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
            <option value="" disabled {{ old('teacher_id') ? '' : 'selected' }}>Pilih Guru</option>
            @foreach ($teachers as $teacher)
              <option value="{{ $teacher->id }}" @selected(old('teacher_id') == $teacher->id)>
                {{ $teacher->name }} @if(!empty($teacher->nip))(NIP {{ $teacher->nip }})@endif
              </option>
            @endforeach
          </select>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
          <div class="space-y-2">
            <label class="text-sm font-semibold text-slate-700">Tanggal</label>
            <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
          </div>
          <div class="space-y-2">
            <label class="text-sm font-semibold text-slate-700">Kelas</label>
            <select name="class_name" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
              <option value="" disabled {{ old('class_name') ? '' : 'selected' }}>Pilih</option>
              @foreach (['1','2','3','4','5','6'] as $class)
                <option value="{{ $class }}" @selected(old('class_name') == $class)>Kelas {{ $class }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-sm font-semibold text-slate-700">Catatan untuk Guru (Opsional)</label>
        <textarea name="notes" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" placeholder="Tambahkan arahan atau fokus supervisi">{{ old('notes') }}</textarea>
        <p class="text-xs text-slate-400">Catatan akan terlihat oleh guru terkait jadwal ini.</p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-200/60 transition-all duration-300 ease-in-out hover:opacity-90">
          @include('layouts.partials.icon', ['name' => 'check', 'classes' => 'h-4 w-4 text-white'])
          Simpan Jadwal
        </button>
        <a href="{{ route('supervisor.schedules') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-600">
          @include('layouts.partials.icon', ['name' => 'chevron-left', 'classes' => 'h-4 w-4 text-slate-400'])
          Batal
        </a>
      </div>
    </form>
  </div>
</div>
@endsection
