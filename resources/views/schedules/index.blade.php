@extends('layouts.app', ['title' => 'Jadwal Saya'])

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white/90 p-8 shadow-lg shadow-slate-200/50 md:flex-row md:items-center md:justify-between">
        <div class="space-y-2">
            <h1 class="text-3xl font-semibold text-slate-900">Jadwal Saya</h1>
            <p class="text-sm text-slate-500">Lihat rangkuman supervisi yang Anda ikuti sebagai guru maupun pengawas.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-medium uppercase tracking-[0.2em] text-slate-500 shadow-sm shadow-slate-200/70">
                @include('layouts.partials.icon', ['name' => 'calendar', 'classes' => 'h-4 w-4 text-indigo-500'])
                Sinkron</span>
            @if (app()->environment('local'))
                <form action="/debug/create-sample-schedule" method="post">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-indigo-200/70 transition-all duration-300 ease-in-out hover:opacity-90">
                        @include('layouts.partials.icon', ['name' => 'sparkles', 'classes' => 'h-4 w-4 text-white'])
                        Jadwal Contoh
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-600 shadow-sm shadow-emerald-100/60">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="space-y-4 rounded-2xl border border-slate-200 bg-white/90 p-8 shadow-md shadow-slate-200/40">
            <header class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Sebagai Guru</h2>
                    <p class="text-xs text-slate-500">Jadwal observasi dan unggahan teaching log Anda.</p>
                </div>
                <span class="rounded-lg border border-indigo-100 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-500">{{ $asTeacher->count() }} agenda</span>
            </header>
            <div class="space-y-3">
                @forelse ($asTeacher as $schedule)
                    <article class="relative overflow-hidden rounded-xl border border-slate-200 bg-white px-5 py-4 shadow-sm shadow-slate-200/60 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex items-start justify-between gap-4">
                            <div class="space-y-2 text-sm text-slate-600">
                                <p class="text-base font-semibold text-slate-900">{{ $schedule->title ?? 'Agenda Supervisi' }}</p>
                                <div class="flex items-center gap-2 text-xs uppercase tracking-wide text-slate-400">
                                    @include('layouts.partials.icon', ['name' => 'clock', 'classes' => 'h-3.5 w-3.5 text-indigo-400'])
                                    {{ \Carbon\Carbon::parse($schedule->date)->translatedFormat('d F Y') }}
                                </div>
                                @if ($schedule->school)
                                    <div class="inline-flex items-center gap-2 text-xs font-medium text-slate-500">
                                        @include('layouts.partials.icon', ['name' => 'buildings', 'classes' => 'h-3.5 w-3.5 text-indigo-400'])
                                        {{ $schedule->school->name }}
                                    </div>
                                @endif
                            </div>
                            <a href="{{ route('submissions.show', $schedule) }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-3 py-2 text-xs font-semibold text-white shadow-md shadow-indigo-200/70 transition-all duration-300 ease-in-out hover:opacity-90">
                                @include('layouts.partials.icon', ['name' => 'upload', 'classes' => 'h-4 w-4 text-white'])
                                Unggah
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl border border-slate-200 bg-[#F9FAFB] px-4 py-3 text-sm text-slate-500">
                        Belum ada jadwal sebagai guru.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="space-y-4 rounded-2xl border border-slate-200 bg-white/90 p-8 shadow-md shadow-slate-200/40">
            <header class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Sebagai Supervisor</h2>
                    <p class="text-xs text-slate-500">Pantau agenda pendampingan dan bahan evaluasi guru.</p>
                </div>
                <span class="rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-500">{{ $asSupervisor->count() }} agenda</span>
            </header>
            <div class="space-y-3">
                @forelse ($asSupervisor as $schedule)
                    <article class="relative overflow-hidden rounded-xl border border-slate-200 bg-white px-5 py-4 shadow-sm shadow-slate-200/60 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex items-start justify-between gap-4">
                            <div class="space-y-2 text-sm text-slate-600">
                                <p class="text-base font-semibold text-slate-900">{{ $schedule->title ?? 'Agenda Supervisi' }}</p>
                                <div class="flex items-center gap-2 text-xs uppercase tracking-wide text-slate-400">
                                    @include('layouts.partials.icon', ['name' => 'clock', 'classes' => 'h-3.5 w-3.5 text-indigo-400'])
                                    {{ \Carbon\Carbon::parse($schedule->date)->translatedFormat('d F Y') }}
                                </div>
                                @if ($schedule->school)
                                    <div class="inline-flex items-center gap-2 text-xs font-medium text-slate-500">
                                        @include('layouts.partials.icon', ['name' => 'buildings', 'classes' => 'h-3.5 w-3.5 text-indigo-400'])
                                        {{ $schedule->school->name }}
                                    </div>
                                @endif
                                @if ($schedule->teacher)
                                    <div class="inline-flex items-center gap-2 text-xs font-medium text-slate-500">
                                        @include('layouts.partials.icon', ['name' => 'user-circle', 'classes' => 'h-3.5 w-3.5 text-indigo-400'])
                                        {{ $schedule->teacher->name }}
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2 text-[11px] uppercase tracking-wide text-slate-400">
                                        @if($schedule->teacher->teacher_type_label)
                                            <span class="inline-flex items-center gap-1 rounded border border-slate-200 bg-white px-2 py-1">Jenis <strong class="ml-1 text-slate-600">{{ $schedule->teacher->teacher_type_label }}</strong></span>
                                        @endif
                                        @if($schedule->teacher->teacher_detail_label)
                                            <span class="inline-flex items-center gap-1 rounded border border-slate-200 bg-white px-2 py-1">Detail <strong class="ml-1 text-slate-600">{{ $schedule->teacher->teacher_detail_label }}</strong></span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            @php($hasSubmissionFiles = $schedule->submission && ((optional($schedule->submission->documents)->count() ?? 0) > 0 || optional($schedule->submission->videoFile)->id))
                            @if($hasSubmissionFiles)
                                <a href="{{ route('supervisor.submissions.show', $schedule) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm shadow-slate-200/70 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-600">
                                    @include('layouts.partials.icon', ['name' => 'document', 'classes' => 'h-4 w-4 text-indigo-500'])
                                    Lihat Berkas
                                </a>
                            @else
                                <span class="inline-flex items-center gap-2 rounded-xl border border-dashed border-slate-200 bg-[#F9FAFB] px-3 py-2 text-xs font-semibold text-slate-400">
                                    @include('layouts.partials.icon', ['name' => 'inbox', 'classes' => 'h-4 w-4 text-slate-400'])
                                    Belum ada unggahan
                                </span>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl border border-slate-200 bg-[#F9FAFB] px-4 py-3 text-sm text-slate-500">
                        Belum ada jadwal sebagai supervisor.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
