@extends('layouts.app', ['title' => 'Penilaian Jadwal'])

@section('content')
<div class="space-y-10">
  <div class="flex flex-col gap-6 rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40 md:flex-row md:items-center md:justify-between">
    <div class="space-y-2">
      <p class="text-xs font-medium uppercase tracking-[0.3em] text-slate-400">Ringkasan Jadwal</p>
      <h1 class="text-3xl font-semibold text-slate-900">{{ $schedule->title ?? 'Sesi Supervisi' }}</h1>
      <div class="flex flex-wrap gap-3 text-sm text-slate-500">
        <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1">
          @include('layouts.partials.icon', ['name' => 'calendar', 'classes' => 'h-4 w-4 text-slate-400'])
          {{ optional($schedule->date)->translatedFormat('d F Y') ?? 'Tanggal belum ditentukan' }}
        </span>
        @if ($schedule->school)
          <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1">
            @include('layouts.partials.icon', ['name' => 'buildings', 'classes' => 'h-4 w-4 text-slate-400'])
            {{ $schedule->school->name }}
          </span>
        @endif
        @if ($schedule->teacher)
          <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1">
            @include('layouts.partials.icon', ['name' => 'graduation-cap', 'classes' => 'h-4 w-4 text-slate-400'])
            {{ $schedule->teacher->name }}
          </span>
          @if($schedule->teacher->teacher_type_label)
            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1">
              @include('layouts.partials.icon', ['name' => 'sparkles', 'classes' => 'h-4 w-4 text-slate-400'])
              {{ $schedule->teacher->teacher_type_label }}
            </span>
          @endif
          @if($schedule->teacher->teacher_detail_label)
            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1">
              @include('layouts.partials.icon', ['name' => 'bookmark', 'classes' => 'h-4 w-4 text-slate-400'])
              {{ $schedule->teacher->teacher_detail_label }}
            </span>
          @endif
        @endif
      </div>
    </div>
    <div class="flex flex-wrap items-center gap-3">
      <a href="{{ route('supervisor.schedules.export', $schedule) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-600">
        @include('layouts.partials.icon', ['name' => 'download', 'classes' => 'h-4 w-4 text-slate-400'])
        Ekspor
      </a>
      <a href="{{ route('supervisor.schedules') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-200/60 transition-all duration-300 ease-in-out hover:opacity-90">
        @include('layouts.partials.icon', ['name' => 'chevron-left', 'classes' => 'h-4 w-4 text-white'])
        Kembali ke Jadwal
      </a>
    </div>
  </div>

  <div class="grid gap-6 md:grid-cols-3">
    @foreach ($cards as $type => $meta)
      @php($evaluation = $evalByType->get($type))
      <div class="flex flex-col justify-between rounded-xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/40 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg">
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div class="space-y-1">
              <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ $meta['label'] }}</p>
              <div class="flex items-end gap-2">
                <span class="text-3xl font-semibold text-slate-900">{{ $evaluation->total_score ?? '—' }}</span>
                @if ($type === 'pembelajaran' && optional($evaluation)->category)
                  <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-500">{{ $evaluation->category }}</span>
                @endif
              </div>
            </div>
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500">
              @include('layouts.partials.icon', ['name' => $meta['icon'], 'classes' => 'h-5 w-5'])
            </span>
          </div>
          <p class="text-sm text-slate-500">{{ $meta['description'] }}</p>
        </div>
        <div class="mt-6">
          <a href="{{ route('supervisor.evaluations.show', [$schedule, $type]) }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-indigo-200/60 transition-all duration-300 ease-in-out hover:opacity-90">
            @include('layouts.partials.icon', ['name' => 'eye', 'classes' => 'h-4 w-4 text-white'])
            Lihat / Nilai
          </a>
        </div>
      </div>
    @endforeach
  </div>

  @if ($evalByType->count() > 0)
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40">
      <h2 class="text-lg font-semibold text-slate-900">Ringkasan Penilaian</h2>
      <p class="mt-1 text-sm text-slate-500">Gunakan ringkasan ini sebagai bahan diskusi tindak lanjut dengan guru terkait.</p>
      <div class="mt-5 grid gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">RPP</p>
          <p class="mt-2 text-lg font-semibold text-slate-900">{{ optional($evalByType->get('rpp'))->total_score ?? '—' }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Pembelajaran</p>
          <div class="mt-2 flex items-center justify-between">
            <span class="text-lg font-semibold text-slate-900">{{ optional($evalByType->get('pembelajaran'))->total_score ?? '—' }}</span>
            @if (optional($evalByType->get('pembelajaran'))->category)
              <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-500">{{ optional($evalByType->get('pembelajaran'))->category }}</span>
            @endif
          </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Asesmen</p>
          <p class="mt-2 text-lg font-semibold text-slate-900">{{ optional($evalByType->get('asesmen'))->total_score ?? '—' }}</p>
        </div>
      </div>
    </div>
  @endif
</div>
@endsection
