@extends('layouts.app', ['title' => 'Sekolah'])

@section('content')
<div class="space-y-10">
  <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div class="space-y-3">
      <p class="text-xs font-medium uppercase tracking-[0.3em] text-slate-400">Data Master</p>
      <div class="space-y-1">
        <h1 class="text-3xl font-semibold text-slate-900">Sekolah</h1>
        <p class="text-sm text-slate-500">Kelola daftar sekolah dan sinkronkan informasi alamat untuk kebutuhan supervisi.</p>
      </div>
    </div>
    <a href="{{ route('admin.schools.create') }}" class="inline-flex h-11 items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-200/60 transition-all duration-300 ease-in-out hover:opacity-90">
      @include('layouts.partials.icon', ['name' => 'plus', 'classes' => 'h-4 w-4 text-white'])
      Tambah Sekolah
    </a>
  </div>

  <form method="get" class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40 md:flex-row md:items-center md:justify-between">
    <div class="relative w-full md:max-w-md">
      <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
        @include('layouts.partials.icon', ['name' => 'search', 'classes' => 'h-4 w-4'])
      </span>
      <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama atau alamat" class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-600 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
    </div>
    @if (!empty($q))
      <a href="{{ route('admin.schools.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-500 transition-all duration-300 ease-in-out hover:border-slate-300 hover:text-slate-700">Reset</a>
    @endif
  </form>

  @if (session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-600 shadow-sm shadow-emerald-100/60">{{ session('success') }}</div>
  @endif

  <div class="rounded-xl border border-slate-200 bg-white shadow-md shadow-slate-200/40">
    <div class="space-y-4 px-5 py-6 md:hidden">
      @forelse ($schools as $s)
        <article class="space-y-4 rounded-2xl border border-slate-200 bg-[#F9FAFB] p-5 shadow-sm shadow-slate-200/60">
          <div class="space-y-1">
            <p class="text-base font-semibold text-slate-900">{{ $s->name }}</p>
            <p class="text-xs text-slate-400">Kode: {{ $s->npsn ?? 'Belum tersedia' }}</p>
          </div>
          <div class="space-y-1 text-xs text-slate-500">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Alamat</p>
            <p class="text-sm text-slate-600">{{ $s->address ?? 'Alamat belum diisi' }}</p>
          </div>
          <div class="flex flex-wrap justify-end gap-2">
            <a href="{{ route('admin.schools.edit', $s) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-600">
              @include('layouts.partials.icon', ['name' => 'pencil', 'classes' => 'h-3.5 w-3.5'])
              Edit
            </a>
            <form action="{{ route('admin.schools.destroy', $s) }}" method="post" class="inline js-confirm" data-message="Hapus sekolah ini? Tindakan tidak dapat dibatalkan." data-variant="danger">
              @csrf
              @method('DELETE')
              <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600 transition-all duration-300 ease-in-out hover:bg-rose-100">
                @include('layouts.partials.icon', ['name' => 'trash', 'classes' => 'h-3.5 w-3.5'])
                Hapus
              </button>
            </form>
          </div>
        </article>
      @empty
        <div class="rounded-2xl border border-slate-200 bg-[#F9FAFB] px-4 py-5 text-center text-sm text-slate-400">Belum ada data sekolah.</div>
      @endforelse
    </div>

    <div class="hidden overflow-x-auto md:block">
      <table class="min-w-full text-sm">
        <thead class="bg-[#F9FAFB] text-xs font-medium uppercase tracking-[0.18em] text-slate-400">
          <tr>
            <th class="px-5 py-3 text-left">Sekolah</th>
            <th class="px-5 py-3 text-left">Alamat</th>
            <th class="px-5 py-3 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-600">
          @forelse ($schools as $s)
            <tr class="group transition-all duration-300 ease-in-out hover:bg-slate-50">
              <td class="px-5 py-4 align-top">
                <div class="space-y-1">
                  <p class="font-semibold text-slate-900">{{ $s->name }}</p>
                  <p class="text-xs text-slate-400">Kode: {{ $s->npsn ?? 'Belum tersedia' }}</p>
                </div>
              </td>
              <td class="px-5 py-4 align-top">
                <p class="text-sm text-slate-500">{{ $s->address ?? 'Alamat belum diisi' }}</p>
              </td>
              <td class="px-5 py-4 align-top">
                <div class="flex items-center justify-end gap-2">
                  <a href="{{ route('admin.schools.edit', $s) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-600">
                    @include('layouts.partials.icon', ['name' => 'pencil', 'classes' => 'h-3.5 w-3.5'])
                    Edit
                  </a>
                  <form action="{{ route('admin.schools.destroy', $s) }}" method="post" class="inline js-confirm" data-message="Hapus sekolah ini? Tindakan tidak dapat dibatalkan." data-variant="danger">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600 transition-all duration-300 ease-in-out hover:bg-rose-100">@include('layouts.partials.icon', ['name' => 'trash', 'classes' => 'h-3.5 w-3.5']) Hapus</button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="px-5 py-8 text-center text-sm text-slate-400">Belum ada data sekolah.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($schools->hasPages())
      <div class="border-t border-slate-100 px-6 py-4">
        {{ $schools->links('vendor.pagination.tailwind') }}
      </div>
    @endif
  </div>
</div>
@endsection
