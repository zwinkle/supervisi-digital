@extends('layouts.app', ['title' => 'Daftar Pengguna'])

@section('content')
<div class="space-y-10">
  <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div class="space-y-3">
      <p class="text-xs font-medium uppercase tracking-[0.3em] text-slate-400">Manajemen</p>
      <div class="space-y-1">
        <h1 class="text-3xl font-semibold text-slate-900">Pengguna</h1>
        <p class="text-sm text-slate-500">Kelola akses, undangan, dan peran setiap akun di dalam platform Supervisi Digital.</p>
      </div>
    </div>
    <div class="flex flex-wrap gap-3">
      <a href="{{ route('admin.invitations.index') }}" class="inline-flex h-11 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-md shadow-slate-200/60 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-600">
        @include('layouts.partials.icon', ['name' => 'sparkles', 'classes' => 'h-4 w-4 text-indigo-500'])
        Daftar Undangan
      </a>
      <a href="{{ route('admin.invitations.create') }}" class="inline-flex h-11 items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-200/60 transition-all duration-300 ease-in-out hover:opacity-90">
        @include('layouts.partials.icon', ['name' => 'plus', 'classes' => 'h-4 w-4 text-white'])
        Undang Pengguna
      </a>
    </div>
  </div>

  @if (session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-600 shadow-sm shadow-emerald-100/60">{{ session('success') }}</div>
  @endif

  <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40" id="users-container">
    <form id="users-search-form" class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <div class="flex w-full flex-col gap-2 md:flex-row md:gap-3">
        <div class="relative w-full md:max-w-sm">
          <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
            @include('layouts.partials.icon', ['name' => 'search', 'classes' => 'h-4 w-4'])
          </span>
          <input type="text" name="q" value="{{ $q }}" placeholder="Cari guru" class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-600 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" autocomplete="off" />
        </div>
        <select name="filter" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200 md:w-48">
          @foreach (['name' => 'Nama', 'email' => 'Email', 'teacher_type' => 'Jenis Guru', 'school' => 'Sekolah'] as $key => $label)
            <option value="{{ $key }}" @selected($filter === $key)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="text-xs text-slate-400">Pencarian diperbarui otomatis saat Anda mengetik.</div>
    </form>

    <div id="users-results" class="mt-6 w-full min-w-0">
      @include('admin.users.partials.results', ['users' => $users])
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('users-search-form');
    const container = document.getElementById('users-container');
    const resultsWrapper = document.getElementById('users-results');
    if (!form || !container || !resultsWrapper) {
      return;
    }

    let controller = null;
    let debounceTimer = null;

    const submitAjax = function () {
      const formData = new FormData(form);
      const params = new URLSearchParams();
      for (const [key, value] of formData.entries()) {
        if (value) {
          params.append(key, value);
        }
      }

      if (controller) {
        controller.abort();
      }
      controller = new AbortController();

      container.classList.add('opacity-60');

      fetch(`{{ route('admin.users.index') }}` + (params.toString() ? `?${params.toString()}` : ''), {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        },
        signal: controller.signal,
      })
        .then(function (response) {
          if (!response.ok) {
            throw new Error('Gagal memuat data');
          }
          return response.json();
        })
        .then(function (data) {
          if (data.html) {
            resultsWrapper.innerHTML = data.html;
          }
        })
        .catch(function (error) {
          if (error.name === 'AbortError') {
            return;
          }
          console.error(error);
        })
        .finally(function () {
          container.classList.remove('opacity-60');
        });
    };

    const scheduleSubmit = function () {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(submitAjax, 600);
    };

    form.addEventListener('input', scheduleSubmit);
    form.addEventListener('change', scheduleSubmit);
  });
</script>
@endpush
