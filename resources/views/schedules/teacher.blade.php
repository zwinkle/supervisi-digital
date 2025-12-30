@extends('layouts.app', ['title' => 'Jadwal Guru'])

@section('content')
<div class="space-y-10">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div class="space-y-3">
            <p class="text-xs font-medium uppercase tracking-[0.3em] text-slate-400">Kesiapan Supervisi</p>
            <div class="space-y-1">
                <h1 class="text-3xl font-semibold text-slate-900">Jadwal Guru</h1>
                <p class="text-sm text-slate-500">Pantau agenda supervisi Anda dan unggah dokumen pendukung tepat waktu.</p>
            </div>
        </div>
        <!-- <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-medium uppercase tracking-[0.2em] text-slate-500 shadow-sm shadow-slate-200/70">
            @include('layouts.partials.icon', ['name' => 'timeline', 'classes' => 'h-4 w-4 text-indigo-500'])
            Mode Guru
        </span> -->
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-600 shadow-sm shadow-emerald-100/60">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40">
        <form id="teacher-schedules-filter" class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
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
            </div>
            <div class="text-xs text-slate-400">Filter otomatis diperbarui saat pilihan berubah.</div>
        </form>
    </div>

    <div class="space-y-4" id="teacher-schedules-results">
        @forelse ($schedules as $schedule)
            @php
                $badge = $schedule->computedBadge();
                $evalByType = ($schedule->evaluations ?? collect())->keyBy('type');
            @endphp
            <article class="rounded-2xl border border-slate-200 bg-white px-6 py-5 shadow-md shadow-slate-200/50 transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:shadow-lg">
                <div class="flex flex-col gap-6 lg:flex-row lg:justify-between">
                    <div class="space-y-3 text-sm text-slate-600">
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="text-xl font-semibold text-slate-900">{{ $schedule->title ?? 'Agenda Supervisi' }}</h2>
                            <span class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-[#F9FAFB] px-3 py-1 text-xs font-semibold text-slate-500">
                                @include('layouts.partials.icon', ['name' => 'calendar', 'classes' => 'h-3.5 w-3.5 text-indigo-500'])
                                {{ \Carbon\Carbon::parse($schedule->date)->translatedFormat('d F Y') }}
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-lg border border-transparent px-3 py-1 text-xs font-semibold text-white {{ $badge['class'] ?? '' }}" style="{{ $badge['inline_css'] ?? '' }}">{{ $badge['text'] }}</span>
                        </div>
                        @if ($schedule->school)
                            <div class="inline-flex items-center gap-2 text-xs font-medium text-slate-500">
                                @include('layouts.partials.icon', ['name' => 'buildings', 'classes' => 'h-3.5 w-3.5 text-indigo-400'])
                                {{ $schedule->school->name }}
                            </div>
                        @endif
                        <div class="flex flex-wrap items-center gap-3 text-[11px] uppercase tracking-wide text-slate-400">
                            @if(!empty($schedule->class_name))
                                <span class="inline-flex items-center gap-1 rounded border border-slate-200 bg-white px-2 py-1">Kelas <strong class="ml-1 text-slate-600">{{ $schedule->class_name }}</strong></span>
                            @endif
                            @if(optional($schedule->teacher)->teacher_type_label)
                                <span class="inline-flex items-center gap-1 rounded border border-slate-200 bg-white px-2 py-1">Jenis <strong class="ml-1 text-slate-600">{{ $schedule->teacher->teacher_type_label }}</strong></span>
                            @endif
                            @if(optional($schedule->teacher)->teacher_detail_label)
                                <span class="inline-flex items-center gap-1 rounded border border-slate-200 bg-white px-2 py-1">Detail <strong class="ml-1 text-slate-600">{{ $schedule->teacher->teacher_detail_label }}</strong></span>
                            @endif
                            @if($schedule->conducted_at)
                                <span class="inline-flex items-center gap-1 rounded border border-emerald-100 bg-emerald-50 px-2 py-1 text-emerald-600">Dilaksanakan {{ optional($schedule->conducted_at)->format('d-m-Y H:i') }}</span>
                            @endif
                            @if($schedule->evaluated_at)
                                <span class="inline-flex items-center gap-1 rounded border border-indigo-100 bg-indigo-50 px-2 py-1 text-indigo-500">Dinilai {{ optional($schedule->evaluated_at)->format('d-m-Y H:i') }}</span>
                            @endif
                        </div>

                        <div class="grid gap-2 text-xs text-slate-500 sm:grid-cols-3">
                            <div class="rounded-lg border border-slate-200 bg-[#F9FAFB] px-3 py-2">
                                <p class="text-[11px] uppercase tracking-wide text-slate-400">Skor RPP</p>
                                <p class="mt-1 text-sm font-semibold text-slate-700">
                                    @if($schedule->evaluation_method === 'upload' && $schedule->manual_rpp_score)
                                      {{ $schedule->manual_rpp_score }}
                                    @else
                                      {{ optional($evalByType->get('rpp'))->total_score ?? '-' }}
                                    @endif
                                </p>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-[#F9FAFB] px-3 py-2">
                                <p class="text-[11px] uppercase tracking-wide text-slate-400">Skor Pembelajaran</p>
                                <p class="mt-1 text-sm font-semibold text-slate-700">
                                    @if($schedule->evaluation_method === 'upload' && $schedule->manual_pembelajaran_score)
                                      {{ $schedule->manual_pembelajaran_score }}
                                    @else
                                      {{ optional($evalByType->get('pembelajaran'))->total_score ?? '-' }}
                                    @endif
                                    @if($schedule->evaluation_method === 'manual' && optional($evalByType->get('pembelajaran'))->category)
                                        <span class="ml-1 text-xs text-indigo-500">({{ optional($evalByType->get('pembelajaran'))->category }})</span>
                                    @endif
                                </p>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-[#F9FAFB] px-3 py-2">
                                <p class="text-[11px] uppercase tracking-wide text-slate-400">Skor Asesmen</p>
                                <p class="mt-1 text-sm font-semibold text-slate-700">
                                    @if($schedule->evaluation_method === 'upload' && $schedule->manual_asesmen_score)
                                      {{ $schedule->manual_asesmen_score }}
                                    @else
                                      {{ optional($evalByType->get('asesmen'))->total_score ?? '-' }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if (!empty($schedule->remarks))
                            <p class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs text-slate-500">
                                <span class="font-semibold text-slate-600">Catatan:</span>
                                {{ $schedule->remarks }}
                            </p>
                        @endif
                    </div>
                    <div class="flex flex-col gap-3 text-sm">
                        <a href="{{ route('guru.submissions.show', $schedule) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-indigo-200/70 transition-all duration-300 ease-in-out hover:opacity-90">
                            @include('layouts.partials.icon', ['name' => 'upload', 'classes' => 'h-4 w-4 text-white'])
                            Kelola Berkas
                        </a>
                        @if($schedule->evaluation_method === 'upload' && $schedule->uploaded_evaluation_file)
                            <a href="{{ route('guru.schedules.download-evaluation', $schedule) }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 shadow-sm shadow-emerald-100/70 transition-all duration-300 ease-in-out hover:border-emerald-300 hover:bg-emerald-100">
                                @include('layouts.partials.icon', ['name' => 'download', 'classes' => 'h-4 w-4 text-emerald-600'])
                                Unduh Hasil Supervisi
                            </a>
                        @else
                            @php
                                $isEvaluationComplete = false;
                                if ($schedule->evaluation_method === 'manual') {
                                    $isEvaluationComplete = $schedule->hasSubmissionFor('rpp') && $schedule->hasSubmissionFor('pembelajaran') && $schedule->hasSubmissionFor('asesmen') && 
                                                          $evalByType->has('rpp') && $evalByType->has('pembelajaran') && $evalByType->has('asesmen');
                                } elseif ($schedule->evaluation_method === 'upload') {
                                    $isEvaluationComplete = $schedule->uploaded_evaluation_file !== null;
                                }
                            @endphp
                            @if($isEvaluationComplete)
                                <a href="{{ route('guru.schedules.export', $schedule) }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 shadow-sm shadow-emerald-100/70 transition-all duration-300 ease-in-out hover:border-emerald-300 hover:bg-emerald-100">
                                    @include('layouts.partials.icon', ['name' => 'download', 'classes' => 'h-4 w-4 text-emerald-600'])
                                    Ekspor Laporan
                                </a>
                            @else
                                <button disabled class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-400 cursor-not-allowed shadow-sm shadow-slate-200/70">
                                    @include('layouts.partials.icon', ['name' => 'download', 'classes' => 'h-4 w-4 text-slate-400'])
                                    Ekspor Laporan
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-[#F9FAFB] px-6 py-8 text-center text-sm text-slate-500">
                Belum ada jadwal sebagai guru.
            </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('teacher-schedules-filter');
    const results = document.getElementById('teacher-schedules-results');
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

      fetch('{{ route('guru.schedules') }}' + (params.toString() ? '?' + params.toString() : ''), {
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
