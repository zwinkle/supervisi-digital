@extends('layouts.app', ['title' => 'Penilaian - '.$type])

@section('content')
<div class="space-y-10">
  <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div class="space-y-3">
      <p class="text-xs font-medium uppercase tracking-[0.3em] text-slate-400">Form Penilaian</p>
      <div class="space-y-1">
        <h1 class="text-3xl font-semibold text-slate-900">Penilaian ({{ strtoupper($type) }})</h1>
        <p class="text-sm text-slate-500">Isi lembar evaluasi sesuai indikator yang telah ditetapkan.</p>
      </div>
    </div>
    <a href="{{ route('supervisor.schedules') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm shadow-slate-200/70 transition-all duration-300 ease-in-out hover:border-slate-300 hover:bg-slate-50">
      @include('layouts.partials.icon', ['name' => 'chevron-left', 'classes' => 'h-4 w-4 text-slate-400'])
      Kembali ke Jadwal
    </a>
  </div>

  <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40">
    <div class="grid gap-4 md:grid-cols-2 text-sm text-slate-600">
      <div class="rounded-xl bg-slate-50 px-4 py-3">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Guru</p>
        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $schedule->teacher->name ?? '-' }}</p>
        <p class="text-xs text-slate-500">NIP: {{ $schedule->teacher->nip ?? '-' }}</p>
      </div>
      <div class="rounded-xl bg-slate-50 px-4 py-3">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Sekolah</p>
        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $schedule->school->name ?? '-' }}</p>
        <p class="text-xs text-slate-500">Tanggal: {{ optional($schedule->date)->translatedFormat('d F Y') ?? '-' }}</p>
      </div>
      <div class="rounded-xl bg-slate-50 px-4 py-3">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Jenis Guru</p>
        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $schedule->teacher->teacher_type_label ?? '—' }}</p>
        <p class="text-xs text-slate-500">Supervisor: {{ auth()->user()->name }}</p>
      </div>
      <div class="rounded-xl bg-slate-50 px-4 py-3">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Detail Penugasan</p>
        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $schedule->teacher->teacher_detail_label ?? '—' }}</p>
      </div>
    </div>
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

  <form action="{{ route('supervisor.evaluations.store', [$schedule, $type]) }}" method="post" class="space-y-8">
    @csrf

    @foreach ($structure as $section)
      <div class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40">
        <div class="flex items-start justify-between gap-4">
          <div>
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-400">Bagian {{ $section['key'] }}</p>
            <h2 class="text-lg font-semibold text-slate-900">{{ $section['title'] }}</h2>
          </div>
          @if ($kind !== 'ya_tidak')
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">Skala 1-4</span>
          @else
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">Ya / Tidak</span>
          @endif
        </div>

        <div class="space-y-4">
          @foreach ($section['items'] as $itemKey => $label)
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
              <p class="text-sm font-medium text-slate-800">{{ $label }}</p>
              <div class="mt-3">
                @if ($kind === 'ya_tidak')
                  @php($value = old($section['key'].'.'.$itemKey, optional($existing)->breakdown[$section['key'].'.'.$itemKey] ?? ''))
                  <div class="flex flex-wrap gap-4 text-sm">
                    <label class="inline-flex items-center gap-2 text-slate-600">
                      <input type="radio" name="{{ $section['key'] }}[{{ $itemKey }}]" value="1" @checked((string)$value === '1') class="border-slate-300 text-indigo-500 focus:ring-indigo-300">
                      Ya
                    </label>
                    <label class="inline-flex items-center gap-2 text-slate-600">
                      <input type="radio" name="{{ $section['key'] }}[{{ $itemKey }}]" value="0" @checked((string)$value === '0') class="border-slate-300 text-indigo-500 focus:ring-indigo-300">
                      Tidak
                    </label>
                    <label class="inline-flex items-center gap-2 text-xs text-slate-400">
                      <input type="radio" name="{{ $section['key'] }}[{{ $itemKey }}]" value="" @checked($value === '' || $value === null) class="border-slate-300 text-indigo-500 focus:ring-indigo-300">
                      Kosong
                    </label>
                  </div>
                @else
                  @php($value = old($section['key'].'.'.$itemKey, optional($existing)->breakdown[$section['key'].'.'.$itemKey] ?? ''))
                  <div class="flex flex-wrap items-center gap-3 text-sm">
                    @foreach ([1, 2, 3, 4] as $score)
                      <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1 text-slate-600 transition-all duration-200 ease-in-out hover:border-indigo-200">
                        <input type="radio" name="{{ $section['key'] }}[{{ $itemKey }}]" value="{{ $score }}" @checked((string)$value === (string)$score) class="border-slate-300 text-indigo-500 focus:ring-indigo-300">
                        {{ $score }}
                      </label>
                    @endforeach
                    <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs text-slate-400 transition-all duration-200 ease-in-out hover:border-indigo-200">
                      <input type="radio" name="{{ $section['key'] }}[{{ $itemKey }}]" value="" @checked($value === '') class="border-slate-300 text-indigo-500 focus:ring-indigo-300">
                      Kosong
                    </label>
                  </div>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endforeach

    @php($score = optional($existing)->total_score)
    @php($category = optional($existing)->category)
    @if ($existing)
      <div class="rounded-xl border border-indigo-100 bg-indigo-50 px-5 py-4 text-sm text-indigo-600">
        Skor tersimpan sebelumnya: <span class="font-semibold">{{ $score ?? '—' }}</span>
        @if ($category)
          <span class="ml-2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-indigo-500">Kategori: {{ $category }}</span>
        @endif
      </div>
    @endif

    <div class="flex flex-wrap items-center gap-3">
      <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-200/60 transition-all duration-300 ease-in-out hover:opacity-90">
        @include('layouts.partials.icon', ['name' => 'check', 'classes' => 'h-4 w-4 text-white'])
        Simpan Penilaian
      </button>
      <a href="{{ route('supervisor.schedules.assessment', $schedule) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition-all duration-300 ease-in-out hover:border-slate-300 hover:bg-slate-50">
        @include('layouts.partials.icon', ['name' => 'chevron-left', 'classes' => 'h-4 w-4 text-slate-400'])
        Kembali ke Ringkasan
      </a>
    </div>
  </form>
</div>
@endsection
