@extends('layouts.app', ['title' => 'Unggah Berkas'])

@php($canEdit = auth()->id() === $schedule->teacher_id)
@php($submission = $schedule->submission)

@section('content')
<div id="upload-overlay" class="fixed inset-0 z-[80] hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm">
    <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/60">
        <div class="flex items-center gap-3">
            <div class="relative h-10 w-10">
                <span class="absolute inset-0 rounded-full border-2 border-indigo-100"></span>
                <span class="absolute inset-0 animate-spin rounded-full border-2 border-indigo-500 border-t-transparent"></span>
            </div>
            <div>
                <p id="overlay-title" class="text-sm font-semibold text-slate-900">Mengunggah berkas...</p>
                <p class="text-xs text-slate-500">Jangan tutup atau refresh halaman sampai selesai.</p>
            </div>
        </div>
        <div class="mt-4 h-1 w-full overflow-hidden rounded-full bg-slate-100">
            <div class="h-full w-1/2 animate-pulse rounded-full bg-gradient-to-r from-indigo-400 to-blue-500"></div>
        </div>
    </div>
</div>

<div class="space-y-8">
    <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white/90 p-8 shadow-lg shadow-slate-200/50 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-3xl font-semibold text-slate-900">Unggah Berkas Supervisi</h1>
            <p class="text-sm text-slate-500">Sinkronkan dokumen RPP, Asesmen, Administrasi, dan video pembelajaran ke Google Drive institusi.</p>
            <div class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-[#F9FAFB] px-4 py-2 text-xs font-semibold text-slate-500">
                @include('layouts.partials.icon', ['name' => 'calendar', 'classes' => 'h-4 w-4 text-indigo-500'])
                {{ $schedule->date->translatedFormat('d F Y') }}
            </div>
        </div>
        <x-back-button :href="$canEdit ? route('guru.schedules') : route('supervisor.schedules')" />
    </div>

    <section class="space-y-4 rounded-2xl border border-slate-200 bg-white/90 p-8 shadow-md shadow-slate-200/40">
        <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Berkas saat ini</h2>
                <p class="text-xs text-slate-500">Tautan otomatis ke Google Drive yang terhubung.</p>
            </div>
            <span class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-[#F9FAFB] px-3 py-1 text-xs font-medium text-slate-500">
                @include('layouts.partials.icon', ['name' => 'cloud', 'classes' => 'h-3.5 w-3.5 text-indigo-400'])
                Status Sinkron
            </span>
        </header>

        @php($rppFile = optional($submission)->rppFile)
        @php($asesmenFile = optional($submission)->asesmenFile)
        @php($administrasiFile = optional($submission)->administrasiFile)
        @php($videoFile = optional($submission)->videoFile)

        <div class="space-y-4 md:hidden">
            <article class="space-y-3 rounded-2xl border border-slate-200 bg-[#F9FAFB] p-4 text-sm text-slate-600">
                <header class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">RPP</p>
                        <p id="rpp-name-mobile" class="text-base font-semibold text-slate-900">{{ $rppFile->name ?? '-' }}</p>
                    </div>
                    <span id="rpp-status-mobile" class="rounded-lg bg-white px-2.5 py-1 text-xs font-semibold text-slate-500">{{ $rppFile? 'Aktif' : 'Kosong' }}</span>
                </header>
                <dl class="space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <dt class="text-slate-400">Ukuran</dt>
                        @php($rppSize = $rppFile->extra['size'] ?? null)
                        <dd id="rpp-size-mobile" class="font-semibold">{{ $rppSize ? number_format($rppSize/1024, 0).' KB' : '-' }}</dd>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <dt class="text-slate-400">Halaman</dt>
                        @php($rppPages = $rppFile->extra['pageCount'] ?? null)
                        <dd id="rpp-meta-mobile" class="font-semibold">{{ $rppPages ? ($rppPages.' halaman') : '-' }}</dd>
                    </div>
                </dl>
                <div id="rpp-action-mobile" class="flex flex-wrap gap-2">
                    @if($rppFile)
                        @if($rppFile->web_view_link)
                            <a id="rpp-view-mobile" target="_blank" href="{{ $rppFile->web_view_link }}" class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-600 transition-all duration-300 ease-in-out hover:bg-indigo-100">
                                @include('layouts.partials.icon', ['name' => 'external-link', 'classes' => 'h-3.5 w-3.5'])
                                Lihat
                            </a>
                            @if($canEdit)
                                <form id="rpp-del-mobile" action="{{ route('guru.submissions.delete', [$schedule, 'rpp']) }}" method="post" class="inline js-open-delete-wrapper delete-form" data-kind="RPP">
                                    @csrf
                                    <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600 transition-all duration-300 ease-in-out hover:bg-rose-100 js-open-delete">Hapus</button>
                                </form>
                            @endif
                        @else
                            @if($canEdit)
                                <button id="rpp-processing" type="button" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-400" disabled>Proses unggah…</button>
                            @else
                                <span class="text-xs text-slate-400">-</span>
                            @endif
                        @endif
                    @else
                        <span class="text-xs text-slate-400">Tidak ada</span>
                    @endif
                </div>
            </article>

            <article class="space-y-3 rounded-2xl border border-slate-200 bg-[#F9FAFB] p-4 text-sm text-slate-600">
                <header class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Asesmen</p>
                        <p id="asesmen-name-mobile" class="text-base font-semibold text-slate-900">{{ $asesmenFile->name ?? '-' }}</p>
                    </div>
                    <span id="asesmen-status-mobile" class="rounded-lg bg-white px-2.5 py-1 text-xs font-semibold text-slate-500">{{ $asesmenFile? 'Aktif' : 'Kosong' }}</span>
                </header>
                <dl class="space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <dt class="text-slate-400">Ukuran</dt>
                        @php($asesmenSize = $asesmenFile->extra['size'] ?? null)
                        <dd id="asesmen-size-mobile" class="font-semibold">{{ $asesmenSize ? number_format($asesmenSize/1024, 0).' KB' : '-' }}</dd>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <dt class="text-slate-400">Halaman</dt>
                        @php($asesmenPages = $asesmenFile->extra['pageCount'] ?? null)
                        <dd id="asesmen-meta-mobile" class="font-semibold">{{ $asesmenPages ? ($asesmenPages.' halaman') : '-' }}</dd>
                    </div>
                </dl>
                <div id="asesmen-action-mobile" class="flex flex-wrap gap-2">
                    @if($asesmenFile)
                        @if($asesmenFile->web_view_link)
                            <a id="asesmen-view-mobile" target="_blank" href="{{ $asesmenFile->web_view_link }}" class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-600 transition-all duration-300 ease-in-out hover:bg-indigo-100">
                                @include('layouts.partials.icon', ['name' => 'external-link', 'classes' => 'h-3.5 w-3.5'])
                                Lihat
                            </a>
                            @if($canEdit)
                                <form id="asesmen-del-mobile" action="{{ route('guru.submissions.delete', [$schedule, 'asesmen']) }}" method="post" class="inline js-open-delete-wrapper delete-form" data-kind="Asesmen">
                                    @csrf
                                    <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600 transition-all duration-300 ease-in-out hover:bg-rose-100 js-open-delete">Hapus</button>
                                </form>
                            @endif
                        @else
                            @if($canEdit)
                                <button id="asesmen-processing" type="button" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-400" disabled>Proses unggah…</button>
                            @else
                                <span class="text-xs text-slate-400">-</span>
                            @endif
                        @endif
                    @else
                        <span class="text-xs text-slate-400">Tidak ada</span>
                    @endif
                </div>
            </article>

            <article class="space-y-3 rounded-2xl border border-slate-200 bg-[#F9FAFB] p-4 text-sm text-slate-600">
                <header class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Administrasi</p>
                        <p id="administrasi-name-mobile" class="text-base font-semibold text-slate-900">{{ $administrasiFile->name ?? '-' }}</p>
                    </div>
                    <span id="administrasi-status-mobile" class="rounded-lg bg-white px-2.5 py-1 text-xs font-semibold text-slate-500">{{ $administrasiFile? 'Aktif' : 'Kosong' }}</span>
                </header>
                <dl class="space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <dt class="text-slate-400">Ukuran</dt>
                        @php($administrasiSize = $administrasiFile->extra['size'] ?? null)
                        <dd id="administrasi-size-mobile" class="font-semibold">{{ $administrasiSize ? number_format($administrasiSize/1024, 0).' KB' : '-' }}</dd>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <dt class="text-slate-400">Halaman</dt>
                        @php($administrasiPages = $administrasiFile->extra['pageCount'] ?? null)
                        <dd id="administrasi-meta-mobile" class="font-semibold">{{ $administrasiPages ? ($administrasiPages.' halaman') : '-' }}</dd>
                    </div>
                </dl>
                <div id="administrasi-action-mobile" class="flex flex-wrap gap-2">
                    @if($administrasiFile)
                        @if($administrasiFile->web_view_link)
                            <a id="administrasi-view-mobile" target="_blank" href="{{ $administrasiFile->web_view_link }}" class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-600 transition-all duration-300 ease-in-out hover:bg-indigo-100">
                                @include('layouts.partials.icon', ['name' => 'external-link', 'classes' => 'h-3.5 w-3.5'])
                                Lihat
                            </a>
                            @if($canEdit)
                                <form id="administrasi-del-mobile" action="{{ route('guru.submissions.delete', [$schedule, 'administrasi']) }}" method="post" class="inline js-open-delete-wrapper delete-form" data-kind="Administrasi">
                                    @csrf
                                    <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600 transition-all duration-300 ease-in-out hover:bg-rose-100 js-open-delete">Hapus</button>
                                </form>
                            @endif
                        @else
                            @if($canEdit)
                                <button id="administrasi-processing" type="button" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-400" disabled>Proses unggah…</button>
                            @else
                                <span class="text-xs text-slate-400">-</span>
                            @endif
                        @endif
                    @else
                        <span class="text-xs text-slate-400">Tidak ada</span>
                    @endif
                </div>
            </article>

            <article class="space-y-3 rounded-2xl border border-slate-200 bg-[#F9FAFB] p-4 text-sm text-slate-600">
                <header class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Video</p>
                        <p id="video-name-mobile" class="text-base font-semibold text-slate-900">{{ $videoFile->name ?? '-' }}</p>
                    </div>
                    <span id="video-status-mobile" class="rounded-lg bg-white px-2.5 py-1 text-xs font-semibold text-slate-500">{{ $videoFile? 'Aktif' : 'Kosong' }}</span>
                </header>
                <dl class="space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <dt class="text-slate-400">Ukuran</dt>
                        @php($vidSize = $videoFile->extra['size'] ?? null)
                        <dd id="video-size-mobile" class="font-semibold">{{ $vidSize ? number_format($vidSize/1024/1024, 2).' MB' : '-' }}</dd>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <dt class="text-slate-400">Durasi</dt>
                        @php($durMs = $videoFile->extra['videoMediaMetadata']['durationMillis'] ?? null)
                        <dd id="video-meta-mobile" class="font-semibold">
                            @if($durMs)
                                @php($sec = (int) round($durMs / 1000))
                                {{ floor($sec/60) }}:{{ str_pad($sec % 60, 2, '0', STR_PAD_LEFT) }}
                            @else
                                -
                            @endif
                        </dd>
                    </div>
                </dl>
                <div id="video-action-mobile" class="flex flex-wrap gap-2">
                    @if($videoFile)
                        @if($videoFile->web_view_link)
                            <a id="video-view-mobile" target="_blank" href="{{ $videoFile->web_view_link }}" class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-600 transition-all duration-300 ease-in-out hover:bg-indigo-100">
                                @include('layouts.partials.icon', ['name' => 'external-link', 'classes' => 'h-3.5 w-3.5'])
                                Lihat
                            </a>
                            @if($canEdit)
                                <form id="video-del-mobile" action="{{ route('guru.submissions.delete', [$schedule, 'video']) }}" method="post" class="inline js-open-delete-wrapper delete-form" data-kind="Video">
                                    @csrf
                                    <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600 transition-all duration-300 ease-in-out hover:bg-rose-100 js-open-delete">Hapus</button>
                                </form>
                            @endif
                        @else
                            @if($canEdit)
                                <button id="video-processing" type="button" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-400" disabled>Proses unggah…</button>
                            @else
                                <span class="text-xs text-slate-400">-</span>
                            @endif
                        @endif
                    @else
                        <span class="text-xs text-slate-400">Tidak ada</span>
                    @endif
                </div>
            </article>
        </div>

        <div class="hidden overflow-hidden rounded-xl border border-slate-200 md:block">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-[#F9FAFB] text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Jenis</th>
                        <th class="px-4 py-3 text-left font-medium">Nama</th>
                        <th class="px-4 py-3 text-left font-medium">Ukuran</th>
                        <th class="px-4 py-3 text-left font-medium">Durasi / Halaman</th>
                        <th class="px-4 py-3 text-left font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-600">
                    <tr>
                        <td class="px-4 py-3 text-sm font-semibold text-slate-900">RPP</td>
                        <td id="rpp-name" class="px-4 py-3 break-words">{{ $rppFile->name ?? '-' }}</td>
                        <td id="rpp-size" class="px-4 py-3 whitespace-nowrap">{{ $rppSize ? number_format($rppSize/1024, 0) . ' KB' : '-' }}</td>
                        <td id="rpp-meta" class="px-4 py-3 whitespace-nowrap">{{ $rppPages ? ($rppPages . ' halaman') : '-' }}</td>
                        <td id="rpp-action" class="px-4 py-3 whitespace-nowrap">
                            @if($rppFile)
                                @if($rppFile->web_view_link)
                                    <a id="rpp-view" target="_blank" href="{{ $rppFile->web_view_link }}" class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-500 transition-all duration-300 ease-in-out hover:text-indigo-600">
                                        @include('layouts.partials.icon', ['name' => 'external-link', 'classes' => 'h-4 w-4'])
                                        Lihat
                                    </a>
                                    @if($canEdit)
                                        <form id="rpp-del" action="{{ route('guru.submissions.delete', [$schedule, 'rpp']) }}" method="post" class="inline-flex items-center gap-1 pl-3 text-sm font-semibold text-rose-500 delete-form" data-kind="RPP">
                                            @csrf
                                            <button type="button" class="js-open-delete transition-all duration-300 ease-in-out hover:text-rose-600">Hapus</button>
                                        </form>
                                    @endif
                                @else
                                    @if($canEdit)
                                        <button id="rpp-processing-desktop" type="button" class="cursor-not-allowed text-sm font-semibold text-slate-400" disabled>Proses unggah…</button>
                                    @else
                                        <span class="text-sm text-slate-400">-</span>
                                    @endif
                                @endif
                            @else
                                <span class="text-sm text-slate-400">Tidak ada</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 text-sm font-semibold text-slate-900">Asesmen</td>
                        <td id="asesmen-name" class="px-4 py-3 break-words">{{ $asesmenFile->name ?? '-' }}</td>
                        <td id="asesmen-size" class="px-4 py-3 whitespace-nowrap">{{ $asesmenSize ? number_format($asesmenSize/1024, 0) . ' KB' : '-' }}</td>
                        <td id="asesmen-meta" class="px-4 py-3 whitespace-nowrap">{{ $asesmenPages ? ($asesmenPages . ' halaman') : '-' }}</td>
                        <td id="asesmen-action" class="px-4 py-3 whitespace-nowrap">
                            @if($asesmenFile)
                                @if($asesmenFile->web_view_link)
                                    <a id="asesmen-view" target="_blank" href="{{ $asesmenFile->web_view_link }}" class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-500 transition-all duration-300 ease-in-out hover:text-indigo-600">
                                        @include('layouts.partials.icon', ['name' => 'external-link', 'classes' => 'h-4 w-4'])
                                        Lihat
                                    </a>
                                    @if($canEdit)
                                        <form id="asesmen-del" action="{{ route('guru.submissions.delete', [$schedule, 'asesmen']) }}" method="post" class="inline-flex items-center gap-1 pl-3 text-sm font-semibold text-rose-500 delete-form" data-kind="Asesmen">
                                            @csrf
                                            <button type="button" class="js-open-delete transition-all duration-300 ease-in-out hover:text-rose-600">Hapus</button>
                                        </form>
                                    @endif
                                @else
                                    @if($canEdit)
                                        <button id="asesmen-processing-desktop" type="button" class="cursor-not-allowed text-sm font-semibold text-slate-400" disabled>Proses unggah…</button>
                                    @else
                                        <span class="text-sm text-slate-400">-</span>
                                    @endif
                                @endif
                            @else
                                <span class="text-sm text-slate-400">Tidak ada</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 text-sm font-semibold text-slate-900">Administrasi</td>
                        <td id="administrasi-name" class="px-4 py-3 break-words">{{ $administrasiFile->name ?? '-' }}</td>
                        <td id="administrasi-size" class="px-4 py-3 whitespace-nowrap">{{ $administrasiSize ? number_format($administrasiSize/1024, 0) . ' KB' : '-' }}</td>
                        <td id="administrasi-meta" class="px-4 py-3 whitespace-nowrap">{{ $administrasiPages ? ($administrasiPages . ' halaman') : '-' }}</td>
                        <td id="administrasi-action" class="px-4 py-3 whitespace-nowrap">
                            @if($administrasiFile)
                                @if($administrasiFile->web_view_link)
                                    <a id="administrasi-view" target="_blank" href="{{ $administrasiFile->web_view_link }}" class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-500 transition-all duration-300 ease-in-out hover:text-indigo-600">
                                        @include('layouts.partials.icon', ['name' => 'external-link', 'classes' => 'h-4 w-4'])
                                        Lihat
                                    </a>
                                    @if($canEdit)
                                        <form id="administrasi-del" action="{{ route('guru.submissions.delete', [$schedule, 'administrasi']) }}" method="post" class="inline-flex items-center gap-1 pl-3 text-sm font-semibold text-rose-500 delete-form" data-kind="Administrasi">
                                            @csrf
                                            <button type="button" class="js-open-delete transition-all duration-300 ease-in-out hover:text-rose-600">Hapus</button>
                                        </form>
                                    @endif
                                @else
                                    @if($canEdit)
                                        <button id="administrasi-processing-desktop" type="button" class="cursor-not-allowed text-sm font-semibold text-slate-400" disabled>Proses unggah…</button>
                                    @else
                                        <span class="text-sm text-slate-400">-</span>
                                    @endif
                                @endif
                            @else
                                <span class="text-sm text-slate-400">Tidak ada</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 text-sm font-semibold text-slate-900">Video</td>
                        <td id="video-name" class="px-4 py-3 break-words">{{ $videoFile->name ?? '-' }}</td>
                        <td id="video-size" class="px-4 py-3 whitespace-nowrap">{{ $vidSize ? number_format($vidSize/1024/1024, 2) . ' MB' : '-' }}</td>
                        <td id="video-meta" class="px-4 py-3 whitespace-nowrap">
                            @if($durMs)
                                @php($sec = (int) round($durMs / 1000))
                                {{ floor($sec/60) }}:{{ str_pad($sec % 60, 2, '0', STR_PAD_LEFT) }}
                            @else
                                -
                            @endif
                        </td>
                        <td id="video-action" class="px-4 py-3 whitespace-nowrap">
                            @if($videoFile)
                                @if($videoFile->web_view_link)
                                    <a id="video-view" target="_blank" href="{{ $videoFile->web_view_link }}" class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-500 transition-all duration-300 ease-in-out hover:text-indigo-600">
                                        @include('layouts.partials.icon', ['name' => 'external-link', 'classes' => 'h-4 w-4'])
                                        Lihat
                                    </a>
                                    @if($canEdit)
                                        <form id="video-del" action="{{ route('guru.submissions.delete', [$schedule, 'video']) }}" method="post" class="inline-flex items-center gap-1 pl-3 text-sm font-semibold text-rose-500 delete-form" data-kind="Video">
                                            @csrf
                                            <button type="button" class="js-open-delete transition-all duration-300 ease-in-out hover:text-rose-600">Hapus</button>
                                        </form>
                                    @endif
                                @else
                                    @if($canEdit)
                                        <button id="video-processing-desktop" type="button" class="cursor-not-allowed text-sm font-semibold text-slate-400" disabled>Proses unggah…</button>
                                    @else
                                        <span class="text-sm text-slate-400">-</span>
                                    @endif
                                @endif
                            @else
                                <span class="text-sm text-slate-400">Tidak ada</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    @if($canEdit)
        <section class="space-y-6 rounded-2xl border border-slate-200 bg-white/90 p-8 shadow-md shadow-slate-200/40">
            <header class="space-y-2">
                <h2 class="text-lg font-semibold text-slate-900">Unggah berkas</h2>
                <p class="text-xs text-slate-500">Mengunggah RPP, Asesmen, Administrasi, atau Video akan mengganti berkas sebelumnya untuk jenis tersebut.</p>
            </header>
            <form id="upload-form" action="{{ route('guru.submissions.store', $schedule) }}" method="post" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="grid gap-6 md:grid-cols-2">
                    <label class="group flex cursor-pointer flex-col gap-3 rounded-2xl border-2 border-dashed border-indigo-200 bg-[#F9FAFB] p-6 text-sm text-slate-500 transition-all duration-300 ease-in-out hover:border-indigo-300 hover:bg-indigo-50/40">
                        <span class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600">
                            @include('layouts.partials.icon', ['name' => 'document', 'classes' => 'h-5 w-5 text-indigo-500'])
                            RPP (PDF/DOC/DOCX) • maksimal 20MB
                        </span>
                        <span class="text-xs text-slate-400">Seret dan lepas atau pilih berkas dari perangkat Anda.</span>
                        <span class="hidden text-xs font-medium text-indigo-500" data-file-label="rpp"></span>
                        <input type="file" name="rpp" accept="application/pdf,.doc,.docx" class="hidden" data-file-input="rpp" />
                    </label>

                    <label class="group flex cursor-pointer flex-col gap-3 rounded-2xl border-2 border-dashed border-indigo-200 bg-[#F9FAFB] p-6 text-sm text-slate-500 transition-all duration-300 ease-in-out hover:border-indigo-300 hover:bg-indigo-50/40">
                        <span class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600">
                            @include('layouts.partials.icon', ['name' => 'document', 'classes' => 'h-5 w-5 text-indigo-500'])
                            Asesmen (PDF/DOC/DOCX) • maksimal 20MB
                        </span>
                        <span class="text-xs text-slate-400">Unggah berkas asesmen yang relevan dengan observasi.</span>
                        <span class="hidden text-xs font-medium text-indigo-500" data-file-label="asesmen"></span>
                        <input type="file" name="asesmen" accept="application/pdf,.doc,.docx" class="hidden" data-file-input="asesmen" />
                    </label>

                    <label class="group flex cursor-pointer flex-col gap-3 rounded-2xl border-2 border-dashed border-indigo-200 bg-[#F9FAFB] p-6 text-sm text-slate-500 transition-all duration-300 ease-in-out hover:border-indigo-300 hover:bg-indigo-50/40">
                        <span class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600">
                            @include('layouts.partials.icon', ['name' => 'document', 'classes' => 'h-5 w-5 text-indigo-500'])
                            Administrasi (PDF/DOC/DOCX) • maksimal 20MB
                        </span>
                        <span class="text-xs text-slate-400">Lampirkan dokumen administrasi pendukung kegiatan.</span>
                        <span class="hidden text-xs font-medium text-indigo-500" data-file-label="administrasi"></span>
                        <input type="file" name="administrasi" accept="application/pdf,.doc,.docx" class="hidden" data-file-input="administrasi" />
                    </label>

                    <div class="flex flex-col gap-3 rounded-2xl border-2 border-dashed border-indigo-200 bg-[#F9FAFB] p-6 text-sm text-slate-500 transition-all duration-300 ease-in-out hover:border-indigo-300 hover:bg-indigo-50/40">
                        <span class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600">
                            @include('layouts.partials.icon', ['name' => 'video', 'classes' => 'h-5 w-5 text-indigo-500'])
                            Video Link (YouTube/Google Drive) • durasi ± 30 menit
                        </span>
                        <span class="text-xs text-slate-400">Paste link video dari YouTube atau Google Drive. Pastikan video dapat diakses oleh supervisor.</span>
                        <input id="input-video-link" type="url" name="video_link" placeholder="https://youtube.com/watch?v=... atau https://drive.google.com/file/d/..." class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100" />
                    </div>
                </div>

                <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-[#F9FAFB] px-4 py-3 text-xs text-slate-500">
                    <div class="flex items-center gap-2 text-slate-600">
                        @include('layouts.partials.icon', ['name' => 'shield-check', 'classes' => 'h-4 w-4 text-indigo-500'])
                        Berkas akan otomatis tersimpan di folder Drive institusi Anda.
                    </div>
                    <div class="flex items-center gap-2">
                        @include('layouts.partials.icon', ['name' => 'refresh', 'classes' => 'h-4 w-4 text-indigo-500'])
                        Jika tautan belum muncul, panel akan memuat ulang setiap beberapa detik.
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button id="btn-submit" type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-200/70 transition-all duration-300 ease-in-out hover:opacity-90">
                        @include('layouts.partials.icon', ['name' => 'upload', 'classes' => 'h-4 w-4 text-white'])
                        Unggah Berkas
                    </button>
                </div>
            </form>
        </section>
    @endif
</div>

<script>
            (function(){
                const form = document.getElementById('upload-form');
                document.body.classList.remove('is-uploading');
                function showToast(message, variant = 'success'){
                  if(!message) return;
                  let root = document.getElementById('toast-root');
                  if(!root){
                    root = document.createElement('div');
                    root.id = 'toast-root';
                    root.className = 'fixed top-4 right-4 z-[60] flex flex-col gap-3';
                    document.body.appendChild(root);
                  }
                  const toast = document.createElement('div');
                  const base = 'min-w-[220px] rounded-xl border px-4 py-3 shadow-lg transition-opacity duration-300 text-sm flex items-start gap-3';
                  if(variant === 'error'){
                    toast.className = base + ' border-rose-200 bg-rose-50 text-rose-600';
                  } else {
                    toast.className = base + ' border-emerald-200 bg-emerald-50 text-emerald-600';
                  }
                  toast.innerHTML = '<span class="mt-0.5 inline-flex h-6 w-6 items-center justify-center rounded-full '+(variant === 'error' ? 'bg-rose-500/10 text-rose-500' : 'bg-emerald-500/10 text-emerald-500')+'">'+(variant === 'error' ? '&#9888;' : '&#10003;')+'</span><span>'+message+'</span>';
                  root.appendChild(toast);
                  setTimeout(()=>{
                    toast.style.opacity = '0';
                    setTimeout(()=>toast.remove(), 300);
                  }, 4000);
                }
                @if (session('success'))
                    showToast(@json(session('success')));
                @endif
                @if ($errors->any())
                    showToast(@json($errors->first()), 'error');
                @endif
                const overlay = document.getElementById('upload-overlay');
                const btn = document.getElementById('btn-submit');
                if(form){
                    form.addEventListener('submit', function(){
                        // Show overlay and then (after tick) disable non-file controls to prevent double submit
                        // Keep hidden + CSRF + FILE inputs enabled so form data is sent correctly
                        // show overlay
                        // set overlay text for upload
                        const title = document.getElementById('overlay-title');
                        if(title){ title.textContent = 'Mengunggah berkas...'; }
                        overlay.classList.remove('hidden');
                        overlay.classList.add('flex');
                        document.body.classList.add('is-uploading');
                        // change button state
                        if(btn){ btn.textContent = 'Mengunggah...'; }
                        // defer disabling controls to avoid dropping file inputs from payload
                        setTimeout(()=>{
                          Array.from(form.elements).forEach(el => {
                            if(!el || !el.tagName) return;
                            const tag = el.tagName.toLowerCase();
                            const type = (el.getAttribute('type')||'').toLowerCase();
                            const name = el.getAttribute('name')||'';
                            const isHidden = type === 'hidden';
                            const isCsrf = name === '_token';
                            const isFile = type === 'file';
                            if(isHidden || isCsrf || isFile) return; // keep critical inputs enabled
                            el.setAttribute('disabled','disabled');
                          });
                        }, 0);
                    });
                }
                // Poll status to refresh metadata and links
                const CAN_EDIT = {{ $canEdit ? 'true' : 'false' }};
                const statusUrl = CAN_EDIT ? "{{ route('guru.submissions.status', $schedule) }}" : "{{ route('supervisor.submissions.status', $schedule) }}";
                function fmtSize(size){ if(!size) return '-'; const mb = size/1024/1024; return mb>=1? mb.toFixed(2)+' MB' : Math.round(size/1024)+' KB'; }
                function fmtDur(ms){ if(!ms) return '-'; const sec = Math.round(ms/1000); const m = Math.floor(sec/60); const s = String(sec%60).padStart(2,'0'); return m+':'+s; }
                function fmtPages(p){ if(!p) return '-'; return p+' halaman'; }
                (function(){
                  const inputs = document.querySelectorAll('[data-file-input]');
                  inputs.forEach(input => {
                    input.addEventListener('change', ()=>{
                      const target = document.querySelector('[data-file-label="'+input.dataset.fileInput+'"]');
                      if(!target) return;
                      const file = input.files && input.files[0];
                      if(file){
                        target.textContent = file.name;
                        target.classList.remove('hidden');
                      } else {
                        target.textContent = '';
                        target.classList.add('hidden');
                      }
                    });
                  });
                })();
                function apply(){
                    fetch(statusUrl, {headers:{'X-Requested-With':'XMLHttpRequest'}})
                      .then(r=>r.json())
                      .then(d=>{
                        if(d.rpp){
                          const name = d.rpp.name || '-';
                          document.getElementById('rpp-name').textContent = name;
                          const nameMobile = document.getElementById('rpp-name-mobile');
                          if(nameMobile){ nameMobile.textContent = name; }
                          const sizeText = fmtSize(d.rpp.size);
                          document.getElementById('rpp-size').textContent = sizeText;
                          const rppSizeMobile = document.getElementById('rpp-size-mobile');
                          if(rppSizeMobile){ rppSizeMobile.textContent = sizeText; }
                          const metaText = fmtPages(d.rpp.pageCount);
                          document.getElementById('rpp-meta').textContent = metaText;
                          const rppMetaMobile = document.getElementById('rpp-meta-mobile');
                          if(rppMetaMobile){ rppMetaMobile.textContent = metaText; }
                          const statusMobile = document.getElementById('rpp-status-mobile');
                          if(statusMobile){ statusMobile.textContent = 'Aktif'; }
                          if(d.rpp.webViewLink){
                            let htmlDesktop = `<a id="rpp-view" target="_blank" href="${d.rpp.webViewLink}" class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-500 transition-all duration-300 ease-in-out hover:text-indigo-600">@include('layouts.partials.icon', ['name' => 'external-link', 'classes' => 'h-4 w-4'])Lihat</a>`;
                            if (CAN_EDIT) {
                              htmlDesktop += `<form id="rpp-del" action="{{ route('guru.submissions.delete', [$schedule, 'rpp']) }}" method="post" class="inline-flex items-center gap-1 pl-3 text-sm font-semibold text-rose-500 delete-form" data-kind="RPP">@csrf<button type="button" class="js-open-delete transition-all duration-300 ease-in-out hover:text-rose-600">Hapus</button></form>`;
                            }
                            document.getElementById('rpp-action').innerHTML = htmlDesktop;
                            const actMobile = document.getElementById('rpp-action-mobile');
                            if(actMobile){
                              let htmlMobile = `<a id="rpp-view-mobile" target="_blank" href="${d.rpp.webViewLink}" class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-600 transition-all duration-300 ease-in-out hover:bg-indigo-100">@include('layouts.partials.icon', ['name' => 'external-link', 'classes' => 'h-3.5 w-3.5'])Lihat</a>`;
                              if (CAN_EDIT) {
                                htmlMobile += `<form id="rpp-del-mobile" action="{{ route('guru.submissions.delete', [$schedule, 'rpp']) }}" method="post" class="inline delete-form" data-kind="RPP">@csrf<button type="button" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600 transition-all duration-300 ease-in-out hover:bg-rose-100 js-open-delete">Hapus</button></form>`;
                              }
                              actMobile.innerHTML = htmlMobile;
                            }
                          }
                        }
                        if(d.asesmen){
                          const name = d.asesmen.name || '-';
                          document.getElementById('asesmen-name').textContent = name;
                          const nameMobile = document.getElementById('asesmen-name-mobile');
                          if(nameMobile){ nameMobile.textContent = name; }
                          const sizeText = fmtSize(d.asesmen.size);
                          document.getElementById('asesmen-size').textContent = sizeText;
                          const sizeMobile = document.getElementById('asesmen-size-mobile');
                          if(sizeMobile){ sizeMobile.textContent = sizeText; }
                          const metaText = fmtPages(d.asesmen.pageCount);
                          document.getElementById('asesmen-meta').textContent = metaText;
                          const metaMobile = document.getElementById('asesmen-meta-mobile');
                          if(metaMobile){ metaMobile.textContent = metaText; }
                          const statusMobile = document.getElementById('asesmen-status-mobile');
                          if(statusMobile){ statusMobile.textContent = 'Aktif'; }
                          if(d.asesmen.webViewLink){
                            let htmlDesktop = `<a id="asesmen-view" target="_blank" href="${d.asesmen.webViewLink}" class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-500 transition-all duration-300 ease-in-out hover:text-indigo-600">@include('layouts.partials.icon', ['name' => 'external-link', 'classes' => 'h-4 w-4'])Lihat</a>`;
                            if (CAN_EDIT) {
                              htmlDesktop += `<form id="asesmen-del" action="{{ route('guru.submissions.delete', [$schedule, 'asesmen']) }}" method="post" class="inline-flex items-center gap-1 pl-3 text-sm font-semibold text-rose-500 delete-form" data-kind="Asesmen">@csrf<button type="button" class="js-open-delete transition-all duration-300 ease-in-out hover:text-rose-600">Hapus</button></form>`;
                            }
                            document.getElementById('asesmen-action').innerHTML = htmlDesktop;
                            const actMobile = document.getElementById('asesmen-action-mobile');
                            if(actMobile){
                              let htmlMobile = `<a id="asesmen-view-mobile" target="_blank" href="${d.asesmen.webViewLink}" class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-600 transition-all duration-300 ease-in-out hover:bg-indigo-100">@include('layouts.partials.icon', ['name' => 'external-link', 'classes' => 'h-3.5 w-3.5'])Lihat</a>`;
                              if (CAN_EDIT) {
                                htmlMobile += `<form id="asesmen-del-mobile" action="{{ route('guru.submissions.delete', [$schedule, 'asesmen']) }}" method="post" class="inline delete-form" data-kind="Asesmen">@csrf<button type="button" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600 transition-all duration-300 ease-in-out hover:bg-rose-100 js-open-delete">Hapus</button></form>`;
                              }
                              actMobile.innerHTML = htmlMobile;
                            }
                          }
                        }
                        if(d.administrasi){
                          const name = d.administrasi.name || '-';
                          document.getElementById('administrasi-name').textContent = name;
                          const nameMobile = document.getElementById('administrasi-name-mobile');
                          if(nameMobile){ nameMobile.textContent = name; }
                          const sizeText = fmtSize(d.administrasi.size);
                          document.getElementById('administrasi-size').textContent = sizeText;
                          const sizeMobile = document.getElementById('administrasi-size-mobile');
                          if(sizeMobile){ sizeMobile.textContent = sizeText; }
                          const metaText = fmtPages(d.administrasi.pageCount);
                          document.getElementById('administrasi-meta').textContent = metaText;
                          const metaMobile = document.getElementById('administrasi-meta-mobile');
                          if(metaMobile){ metaMobile.textContent = metaText; }
                          const statusMobile = document.getElementById('administrasi-status-mobile');
                          if(statusMobile){ statusMobile.textContent = 'Aktif'; }
                          if(d.administrasi.webViewLink){
                            let htmlDesktop = `<a id="administrasi-view" target="_blank" href="${d.administrasi.webViewLink}" class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-500 transition-all duration-300 ease-in-out hover:text-indigo-600">@include('layouts.partials.icon', ['name' => 'external-link', 'classes' => 'h-4 w-4'])Lihat</a>`;
                            if (CAN_EDIT) {
                              htmlDesktop += `<form id="administrasi-del" action="{{ route('guru.submissions.delete', [$schedule, 'administrasi']) }}" method="post" class="inline-flex items-center gap-1 pl-3 text-sm font-semibold text-rose-500 delete-form" data-kind="Administrasi">@csrf<button type="button" class="js-open-delete transition-all duration-300 ease-in-out hover:text-rose-600">Hapus</button></form>`;
                            }
                            document.getElementById('administrasi-action').innerHTML = htmlDesktop;
                            const actMobile = document.getElementById('administrasi-action-mobile');
                            if(actMobile){
                              let htmlMobile = `<a id="administrasi-view-mobile" target="_blank" href="${d.administrasi.webViewLink}" class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-600 transition-all duration-300 ease-in-out hover:bg-indigo-100">@include('layouts.partials.icon', ['name' => 'external-link', 'classes' => 'h-3.5 w-3.5'])Lihat</a>`;
                              if (CAN_EDIT) {
                                htmlMobile += `<form id="administrasi-del-mobile" action="{{ route('guru.submissions.delete', [$schedule, 'administrasi']) }}" method="post" class="inline delete-form" data-kind="Administrasi">@csrf<button type="button" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600 transition-all duration-300 ease-in-out hover:bg-rose-100 js-open-delete">Hapus</button></form>`;
                              }
                              actMobile.innerHTML = htmlMobile;
                            }
                          }
                        }
                        if(d.video){
                          const name = d.video.name || '-';
                          document.getElementById('video-name').textContent = name;
                          const nameMobile = document.getElementById('video-name-mobile');
                          if(nameMobile){ nameMobile.textContent = name; }
                          const sizeText = fmtSize(d.video.size);
                          document.getElementById('video-size').textContent = sizeText;
                          const sizeMobile = document.getElementById('video-size-mobile');
                          if(sizeMobile){ sizeMobile.textContent = sizeText; }
                          const metaText = fmtDur(d.video.durationMillis);
                          document.getElementById('video-meta').textContent = metaText;
                          const metaMobile = document.getElementById('video-meta-mobile');
                          if(metaMobile){ metaMobile.textContent = metaText; }
                          const statusMobile = document.getElementById('video-status-mobile');
                          if(statusMobile){ statusMobile.textContent = 'Aktif'; }
                          if(d.video.webViewLink){
                            let htmlDesktop = `<a id="video-view" target="_blank" href="${d.video.webViewLink}" class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-500 transition-all duration-300 ease-in-out hover:text-indigo-600">@include('layouts.partials.icon', ['name' => 'external-link', 'classes' => 'h-4 w-4'])Lihat</a>`;
                            if (CAN_EDIT) {
                              htmlDesktop += `<form id="video-del" action="{{ route('guru.submissions.delete', [$schedule, 'video']) }}" method="post" class="inline-flex items-center gap-1 pl-3 text-sm font-semibold text-rose-500 delete-form" data-kind="Video">@csrf<button type="button" class="js-open-delete transition-all duration-300 ease-in-out hover:text-rose-600">Hapus</button></form>`;
                            }
                            document.getElementById('video-action').innerHTML = htmlDesktop;
                            const actMobile = document.getElementById('video-action-mobile');
                            if(actMobile){
                              let htmlMobile = `<a id="video-view-mobile" target="_blank" href="${d.video.webViewLink}" class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-600 transition-all duration-300 ease-in-out hover:bg-indigo-100">@include('layouts.partials.icon', ['name' => 'external-link', 'classes' => 'h-3.5 w-3.5'])Lihat</a>`;
                              if (CAN_EDIT) {
                                htmlMobile += `<form id="video-del-mobile" action="{{ route('guru.submissions.delete', [$schedule, 'video']) }}" method="post" class="inline delete-form" data-kind="Video">@csrf<button type="button" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600 transition-all duration-300 ease-in-out hover:bg-rose-100 js-open-delete">Hapus</button></form>`;
                              }
                              actMobile.innerHTML = htmlMobile;
                            }
                          }
                        }
                      })
                      .catch(()=>{})
                      .finally(()=>{
                        // keep polling while processing buttons visible or duration not yet available
                        const rppProcessing = !!document.getElementById('rpp-processing') || !!document.getElementById('rpp-processing-desktop');
                        const asesmenProcessing = !!document.getElementById('asesmen-processing') || !!document.getElementById('asesmen-processing-desktop');
                        const administrasiProcessing = !!document.getElementById('administrasi-processing') || !!document.getElementById('administrasi-processing-desktop');
                        const videoProcessing = !!document.getElementById('video-processing') || !!document.getElementById('video-processing-desktop');
                        const videoHasFile = (document.getElementById('video-name').textContent.trim() !== '-');
                        const videoHasDuration = (document.getElementById('video-meta').textContent.trim() !== '-');
                        const needPoll = rppProcessing || asesmenProcessing || administrasiProcessing || videoProcessing || (videoHasFile && !videoHasDuration);
                        if(needPoll){ setTimeout(apply, 3000); }
                      });
                }
                // Delete confirmation modal
                const modal = document.createElement('div');
                modal.id = 'delete-modal';
                modal.className = 'fixed inset-0 z-[100] hidden items-center justify-center';
                modal.innerHTML = '<div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>'+
                  '<div class="relative w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/60">'+
                  '<h3 class="text-lg font-semibold text-slate-900">Konfirmasi Hapus</h3>'+ 
                  '<p id="delete-modal-text" class="mt-2 text-sm text-slate-500">Apakah Anda yakin ingin menghapus berkas ini?</p>'+ 
                  '<div class="mt-5 flex justify-end gap-2">'+ 
                  '<button type="button" id="btn-cancel-del" class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-600 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-600">Batal</button>'+ 
                  '<button type="button" id="btn-confirm-del" class="rounded-xl bg-rose-500 px-3 py-1.5 text-sm font-semibold text-white shadow-md shadow-rose-200/70 transition-all duration-300 ease-in-out hover:opacity-90">Hapus</button>'+ 
                  '</div></div>';
                document.body.appendChild(modal);
                let pendingForm = null;
                // Delegated listener so it works for dynamic content as well
                  document.addEventListener('click', (e)=>{
                  const btn = e.target.closest('.js-open-delete');
                  if(!btn) return;
                  const form = btn.closest('form.delete-form');
                  pendingForm = form;
                  const kind = form?.dataset?.kind || '';
                  document.getElementById('delete-modal-text').textContent = 'Apakah Anda yakin ingin menghapus '+(kind?kind+' ':'')+'?';
                  modal.classList.remove('hidden');
                  modal.classList.add('flex');
                  document.body.classList.add('is-uploading'); // Hide sidebar toggle
                });
                document.getElementById('btn-cancel-del').addEventListener('click', ()=>{
                  modal.classList.add('hidden');
                  modal.classList.remove('flex');
                  document.body.classList.remove('is-uploading'); // Show sidebar toggle
                  pendingForm = null;
                });
                const btnConfirm = document.getElementById('btn-confirm-del');
                let isDeleting = false; // Prevent double submission
                btnConfirm.addEventListener('click', ()=>{
                  if(!pendingForm || isDeleting) return;
                  
                  // Set deleting flag
                  isDeleting = true;
                  
                  // prevent double click and give small feedback
                  btnConfirm.setAttribute('disabled','disabled');
                  btnConfirm.textContent = 'Menghapus...';
                  
                  // hide modal before submitting to avoid overlay glitch
                  modal.classList.add('hidden');
                  modal.classList.remove('flex');
                  
                  // show overlay with delete message
                  const overlay = document.getElementById('upload-overlay');
                  const title = document.getElementById('overlay-title');
                  if(title){ title.textContent = 'Menghapus berkas...'; }
                  if(overlay){ 
                    overlay.classList.remove('hidden'); 
                    overlay.classList.add('flex'); 
                  }
                  document.body.classList.add('is-uploading');
                  
                  // Small delay to ensure overlay is visible
                  setTimeout(() => {
                    // prefer requestSubmit when available
                    if (typeof pendingForm.requestSubmit === 'function') {
                      pendingForm.requestSubmit();
                    } else {
                      pendingForm.submit();
                    }
                    pendingForm = null;
                  }, 100);
                });
                apply();
            })();
        </script>
@endsection
