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

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-600 shadow-sm shadow-emerald-100/60">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40">
        <form id="supervisor-schedules-search" class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div class="flex w-full flex-col gap-2 md:flex-row md:gap-3">
                <div class="relative w-full md:max-w-sm">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                        @include('layouts.partials.icon', ['name' => 'search', 'classes' => 'h-4 w-4'])
                    </span>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama guru atau sekolah" class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-600 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" autocomplete="off">
                </div>
                <select name="filter" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200 md:w-48">
                    <option value="all" @selected(request('filter', 'all') === 'all')>Semua</option>
                    <option value="teacher" @selected(request('filter') === 'teacher')>Nama Guru</option>
                    <option value="school" @selected(request('filter') === 'school')>Sekolah</option>
                </select>
            </div>
            <div class="text-xs text-slate-400">Pencarian diperbarui otomatis saat Anda mengetik.</div>
        </form>
    </div>

    <div class="space-y-4" id="supervisor-schedules-results">
        @forelse ($schedules as $schedule)
            @php($badge = $schedule->computedBadge())
            @php($evalByType = ($schedule->evaluations ?? collect())->keyBy('type'))
            @php($hasFiles = $schedule->submission && ((optional($schedule->submission->documents)->count() ?? 0) > 0 || optional($schedule->submission->videoFile)->id))
            @php($isDue = \Carbon\Carbon::parse($schedule->date)->isToday() || \Carbon\Carbon::parse($schedule->date)->isPast())
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
                        @if ($schedule->teacher)
                            <div class="flex flex-wrap items-center gap-2 text-xs font-medium text-slate-500">
                                @include('layouts.partials.icon', ['name' => 'user-circle', 'classes' => 'h-3.5 w-3.5 text-indigo-400'])
                                {{ $schedule->teacher->name }}
                                @if (!empty($schedule->teacher->nip))
                                    <span class="rounded-lg border border-slate-200 bg-[#F9FAFB] px-2 py-0.5 text-[11px] text-slate-500">NIP {{ $schedule->teacher->nip }}</span>
                                @endif
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
                                <p class="mt-1 text-sm font-semibold text-slate-700">{{ optional($evalByType->get('rpp'))->total_score ?? '-' }}</p>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-[#F9FAFB] px-3 py-2">
                                <p class="text-[11px] uppercase tracking-wide text-slate-400">Skor Pembelajaran</p>
                                <p class="mt-1 text-sm font-semibold text-slate-700">
                                    {{ optional($evalByType->get('pembelajaran'))->total_score ?? '-' }}
                                    @if(optional($evalByType->get('pembelajaran'))->category)
                                        <span class="ml-1 text-xs text-indigo-500">({{ optional($evalByType->get('pembelajaran'))->category }})</span>
                                    @endif
                                </p>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-[#F9FAFB] px-3 py-2">
                                <p class="text-[11px] uppercase tracking-wide text-slate-400">Skor Asesmen</p>
                                <p class="mt-1 text-sm font-semibold text-slate-700">{{ optional($evalByType->get('asesmen'))->total_score ?? '-' }}</p>
                            </div>
                        </div>
                        @if (!empty($schedule->remarks))
                            <p class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs text-slate-500">
                                <span class="font-semibold text-slate-600">Catatan:</span>
                                {{ \Illuminate\Support\Str::limit($schedule->remarks, 160) }}
                            </p>
                        @endif
                    </div>
                    <div class="flex flex-col items-stretch justify-between gap-3 text-sm">
                        <div class="flex flex-col gap-3">
                            @if ($hasFiles)
                                <a href="{{ route('supervisor.submissions.show', $schedule) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm shadow-slate-200/70 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-600">
                                    @include('layouts.partials.icon', ['name' => 'document', 'classes' => 'h-4 w-4 text-indigo-500'])
                                    Berkas Guru
                                </a>
                            @else
                                <span class="inline-flex items-center gap-2 rounded-xl border border-dashed border-slate-200 bg-[#F9FAFB] px-4 py-2 text-xs font-medium text-slate-400">
                                    @include('layouts.partials.icon', ['name' => 'inbox', 'classes' => 'h-4 w-4 text-slate-400'])
                                    Belum ada unggahan
                                </span>
                            @endif

                            @if (!$schedule->conducted_at && $isDue)
                                <form action="{{ route('supervisor.schedules.conduct', $schedule) }}" method="post" onsubmit="return confirm('Tandai jadwal sudah dilaksanakan?')">
                                    @csrf
                                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-emerald-200/70 transition-all duration-300 ease-in-out hover:opacity-90">
                                        @include('layouts.partials.icon', ['name' => 'check', 'classes' => 'h-4 w-4 text-white'])
                                        Tandai Selesai
                                    </button>
                                </form>
                            @elseif($schedule->conducted_at)
                                <span class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-600">
                                    @include('layouts.partials.icon', ['name' => 'check', 'classes' => 'h-4 w-4 text-emerald-500'])
                                    Dilaksanakan {{ optional($schedule->conducted_at)->format('d-m-Y H:i') }}
                                </span>
                            @endif
                        </div>
                        <div class="flex flex-col gap-3">
                            <a href="{{ route('supervisor.schedules.assessment', $schedule) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-indigo-200/70 transition-all duration-300 ease-in-out hover:opacity-90">
                                @include('layouts.partials.icon', ['name' => 'sparkles', 'classes' => 'h-4 w-4 text-white'])
                                Penilaian
                            </a>
                            <a href="{{ route('supervisor.schedules.edit', $schedule) }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-600 shadow-sm shadow-amber-100/60 transition-all duration-300 ease-in-out hover:border-amber-300">
                                @include('layouts.partials.icon', ['name' => 'edit', 'classes' => 'h-4 w-4 text-amber-500'])
                                Edit Jadwal
                            </a>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-[#F9FAFB] px-6 py-8 text-center text-sm text-slate-500">
                Belum ada jadwal sebagai pengawas.
            </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('supervisor-schedules-search');
    const results = document.getElementById('supervisor-schedules-results');
    if (!form || !results) {
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

    form.addEventListener('input', scheduleSubmit);
    form.addEventListener('change', scheduleSubmit);
  });
</script>
@endpush
@endsection
