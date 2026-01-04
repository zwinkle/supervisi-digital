@extends('layouts.app', ['title' => 'Jadwal Pengawas'])

@section('content')
<div class="space-y-10">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div class="space-y-3">
            <p class="text-xs font-medium uppercase tracking-[0.3em] text-slate-400">Koordinasi Supervisi</p>
            <div class="space-y-1">
                <h1 class="text-3xl font-semibold text-slate-900">Jadwal Pengawas</h1>
                <p class="text-sm text-slate-500">Kelola agenda pendampingan, penilaian, dan unggahan guru dalam satu panel.</p>
            </div>
        </div>
        <a href="{{ route('supervisor.schedules.create') }}" class="inline-flex h-11 items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-200/70 transition-all duration-300 ease-in-out hover:opacity-90">
            @include('layouts.partials.icon', ['name' => 'plus', 'classes' => 'h-4 w-4 text-white'])
            Tambah Jadwal
        </a>
    </div>



    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40">
        <form id="supervisor-schedules-search" class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div class="flex w-full flex-col gap-2 md:flex-row md:gap-3">
                <div class="relative w-full md:max-w-xs">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                        @include('layouts.partials.icon', ['name' => 'calendar', 'classes' => 'h-4 w-4'])
                    </span>
                    <select name="month" class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-600 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="">Semua Bulan</option>
                        @foreach(['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'] as $m => $name)
                            <option value="{{ $m }}" @selected(request('month') === $m)>{{ $name }} {{ request('year', date('Y')) }}</option>
                        @endforeach
                    </select>
                </div>
                <select name="year" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200 md:w-32">
                    @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                        <option value="{{ $y }}" @selected(request('year', date('Y')) == $y)>{{ $y }}</option>
                    @endfor
                </select>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-500">Baris:</span>
                    <select name="per_page" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="10" @selected(request('per_page', 10) == 10)>10</option>
                        <option value="20" @selected(request('per_page') == 20)>20</option>
                    </select>
                </div>
            </div>
            <div class="text-xs text-slate-400">Filter otomatis diperbarui saat pilihan berubah.</div>
        </form>
    </div>

    <div class="space-y-4" id="supervisor-schedules-results">
        @include('schedules.partials.supervisor_list')
    </div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('supervisor-schedules-search');
    const results = document.getElementById('supervisor-schedules-results');
    const monthSelect = form.querySelector('[name="month"]');
    const yearSelect = form.querySelector('[name="year"]');

    if (!form || !results) {
      return;
    }

    const monthNames = {
      '01': 'Januari', '02': 'Februari', '03': 'Maret', '04': 'April',
      '05': 'Mei', '06': 'Juni', '07': 'Juli', '08': 'Agustus',
      '09': 'September', '10': 'Oktober', '11': 'November', '12': 'Desember'
    };

    // Update teks bulan ketika tahun berubah
    if (yearSelect && monthSelect) {
      yearSelect.addEventListener('change', function() {
        const selectedYear = this.value;
        const currentMonth = monthSelect.value;
        
        // Update semua option bulan dengan tahun yang baru
        Array.from(monthSelect.options).forEach(function(option) {
          if (option.value !== '') {
            option.textContent = monthNames[option.value] + ' ' + selectedYear;
          }
        });
        
        // Restore selected month
        monthSelect.value = currentMonth;
      });
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

      results.style.opacity = '0.6';

      fetch(`{{ route('supervisor.schedules') }}` + (params.toString() ? `?${params.toString()}` : ''), {
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
          results.style.opacity = '1';
        });
    };

    const scheduleSubmit = function () {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(submitAjax, 600);
    };

    form.addEventListener('change', scheduleSubmit);
  });
</script>
@endpush
@endsection
