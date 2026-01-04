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

  <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40" id="schools-container">
    <form id="schools-search-form" class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <div class="flex w-full flex-col gap-2 md:flex-row md:gap-3">
        <div class="relative w-full md:max-w-sm">
          <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
            @include('layouts.partials.icon', ['name' => 'search', 'classes' => 'h-4 w-4'])
          </span>
          <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama sekolah atau alamat..." class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-600 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" autocomplete="off">
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">Baris:</span>
            <select name="per_page" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <option value="10" @selected(request('per_page', 10) == 10)>10</option>
                <option value="20" @selected(request('per_page') == 20)>20</option>
            </select>
        </div>
      </div>
      <div class="text-xs text-slate-400">Pencarian diperbarui otomatis saat Anda mengetik.</div>
    </form>



    <div id="schools-results" class="mt-6">
      @include('admin.schools.partials.results', ['schools' => $schools])
    </div>
  </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('schools-search-form');
    const container = document.getElementById('schools-container');
    const results = document.getElementById('schools-results');
    if (!form || !container || !results) {
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

      fetch(`{{ route('admin.schools.index') }}` + (params.toString() ? `?${params.toString()}` : ''), {
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
            results.innerHTML = data.html;
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
