@extends('layouts.app', ['title' => 'Undang Guru'])

@section('content')
<div class="space-y-10">
  <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div class="space-y-3">
      <p class="text-xs font-medium uppercase tracking-[0.3em] text-slate-400">Manajemen Undangan</p>
      <div class="space-y-1">
        <h1 class="text-3xl font-semibold text-slate-900">Undang Guru</h1>
        <p class="text-sm text-slate-500">Kirim kredensial awal bagi guru untuk bergabung dalam ekosistem supervisi digital.</p>
      </div>
    </div>
    <a href="{{ route('supervisor.invitations.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm shadow-slate-200/70 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-600">
      @include('layouts.partials.icon', ['name' => 'inbox', 'classes' => 'h-4 w-4 text-indigo-500'])
      Daftar Undangan
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
    <form action="{{ route('supervisor.invitations.store') }}" method="post" class="space-y-6">
      @csrf
      <div class="grid gap-6 md:grid-cols-2">
        <div class="space-y-2">
          <label class="text-sm font-semibold text-slate-700">Email Guru</label>
          <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" placeholder="guru@example.com" required>
          <p class="text-xs text-slate-400">Pastikan alamat email aktif untuk menerima tautan undangan.</p>
        </div>
        <div class="space-y-2">
          <label class="text-sm font-semibold text-slate-700">Nama Guru</label>
          <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" placeholder="Nama sesuai undangan" required>
          <p class="text-xs text-slate-400">Nama ini akan tertera pada undangan dan profil awal guru.</p>
        </div>
      </div>

      <div class="grid gap-6 md:grid-cols-2">
        <div class="space-y-2">
          <label class="text-sm font-semibold text-slate-700">Sekolah Tujuan</label>
          <select name="school_id" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
            <option value="" disabled {{ old('school_id') ? '' : 'selected' }}>Pilih Sekolah</option>
            @foreach($schools as $school)
              <option value="{{ $school->id }}" @selected(old('school_id') == $school->id)>{{ $school->name }}</option>
            @endforeach
          </select>
          <p class="text-xs text-slate-400">Daftar sekolah dikurasi berdasarkan institusi yang Anda awasi.</p>
        </div>
        <div class="space-y-2">
          <label class="text-sm font-semibold text-slate-700">Kedaluwarsa Undangan</label>
          <div class="flex items-center gap-3">
            <input type="number" name="expires_in_days" min="1" max="30" value="{{ old('expires_in_days', 7) }}" class="h-11 w-24 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            <span class="text-sm text-slate-500">hari setelah dibuat</span>
          </div>
          <p class="text-xs text-slate-400">Sistem akan otomatis menonaktifkan tautan jika melewati batas waktu ini.</p>
        </div>
      </div>

      <div class="flex flex-col gap-3 rounded-xl border border-indigo-100 bg-indigo-50/70 px-4 py-3 text-sm text-indigo-600">
        <div class="flex items-center gap-2 font-semibold">
          @include('layouts.partials.icon', ['name' => 'sparkles', 'classes' => 'h-4 w-4'])
          Tips Undangan Efektif
        </div>
        <p class="text-xs text-indigo-500 md:text-sm">Sampaikan kepada guru bahwa undangan hanya berlaku untuk satu kali penggunaan dan berikan instruksi untuk segera menyelesaikan profil setelah masuk.</p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-200/60 transition-all duration-300 ease-in-out hover:opacity-90">
          @include('layouts.partials.icon', ['name' => 'send', 'classes' => 'h-4 w-4 text-white'])
          Buat Undangan
        </button>
        <a href="{{ route('supervisor.invitations.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-600">
          @include('layouts.partials.icon', ['name' => 'chevron-left', 'classes' => 'h-4 w-4 text-slate-400'])
          Batal
        </a>
      </div>
    </form>
  </div>
</div>
@endsection
