@extends('layouts.app', ['title' => 'Unggah Berkas'])

@php($canEdit = auth()->id() === $schedule->teacher_id)
@php($submission = $schedule->submission)
@php($documents = optional($submission)->documents ?? collect())
@php($documentsByCategory = $documents->groupBy('category'))
@php($maxPerCategory = \App\Models\SubmissionDocument::MAX_PER_CATEGORY)
@php($videoFile = optional($submission)->videoFile)
@php($categoryLabels = ['rpp' => 'RPP', 'asesmen' => 'Asesmen', 'administrasi' => 'Administrasi'])
@php($formatSize = function ($bytes) {
    if (!$bytes) return '-';
    $mb = $bytes / 1024 / 1024;
    return $mb >= 1 ? number_format($mb, 2).' MB' : number_format($bytes / 1024, 0).' KB';
})

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

    <section class="space-y-6 rounded-2xl border border-slate-200 bg-white/90 p-8 shadow-md shadow-slate-200/40">
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

        <div class="grid gap-4 lg:grid-cols-2">
            @foreach($categoryLabels as $key => $label)
                @php($docs = $documentsByCategory->get($key, collect()))
                <article class="space-y-3 rounded-2xl border border-slate-200 bg-[#F9FAFB] p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ $label }}</p>
                            <p class="text-sm text-slate-500">{{ $docs->count() }} dari {{ $maxPerCategory }} berkas tersimpan</p>
                        </div>
                    </div>
                    <div data-doc-list="{{ $key }}" data-has-processing="false" class="space-y-3">
                        @foreach($docs as $doc)
                            @php($file = $doc->file)
                            @php($extra = optional($file)->extra ?? [])
                            <div class="flex flex-col gap-2 rounded-xl border border-white/60 bg-white px-4 py-3 shadow-sm shadow-slate-200">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 break-words">{{ $file?->name ?? 'Tanpa nama' }}</p>
                                    <p class="text-xs text-slate-500 flex items-center gap-1">
                                        {{ $formatSize($extra['size'] ?? null) }}
                                        @if(!empty($extra['pageCount']))
                                            <span class="mx-1 text-slate-400" aria-hidden="true">|</span>
                                            {{ $extra['pageCount'] }} halaman
                                        @endif
                                    </p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                                    @if(!empty($file?->web_view_link))
                                        <a target="_blank" href="{{ $file->web_view_link }}" class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-indigo-600 transition-all duration-300 ease-in-out hover:bg-indigo-100">
                                            @include('layouts.partials.icon', ['name' => 'external-link', 'classes' => 'h-3.5 w-3.5'])
                                            Lihat
                                        </a>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-lg border border-dashed border-slate-200 px-3 py-1.5 text-slate-400" data-doc-processing="true">
                                            @include('layouts.partials.icon', ['name' => 'refresh', 'classes' => 'h-3.5 w-3.5'])
                        Sinkronisasi...
                                        </span>
                                    @endif
                                    @if($canEdit)
                                        <form action="{{ route('guru.submissions.documents.destroy', [$schedule, $doc]) }}" method="post" class="delete-form inline-flex items-center" data-kind="{{ $label }}: {{ $file->name }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-rose-600 transition-all duration-300 ease-in-out hover:bg-rose-100 js-open-delete">
                                                @include('layouts.partials.icon', ['name' => 'trash', 'classes' => 'h-3.5 w-3.5'])
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p data-doc-empty="{{ $key }}" class="text-sm text-slate-400 {{ $docs->isNotEmpty() ? 'hidden' : '' }}">Belum ada berkas.</p>
                </article>
            @endforeach
        </div>

        <article class="space-y-3 rounded-2xl border border-slate-200 bg-white/90 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Video Pembelajaran</p>
                    <p class="text-sm text-slate-500">Tautan YouTube atau Google Drive.</p>
                </div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-[#F9FAFB] px-4 py-3">
                <p id="video-name" class="text-sm font-semibold text-slate-900">{{ $videoFile->name ?? '-' }}</p>
                @php($durationMillis = optional($videoFile)->extra['videoMediaMetadata']['durationMillis'] ?? null)
                @php($durationSeconds = $durationMillis ? (int) round($durationMillis / 1000) : null)
                @php($durationText = $durationSeconds !== null ? (intdiv($durationSeconds, 60).':'.str_pad($durationSeconds % 60, 2, '0', STR_PAD_LEFT).' menit') : null)
                <p class="text-xs text-slate-500 flex items-center gap-1">
                    <span id="video-size">{{ $videoFile ? $formatSize($videoFile->extra['size'] ?? null) : '-' }}</span>
                    <span id="video-meta-separator" class="mx-1 text-slate-300 {{ $durationText ? '' : 'hidden' }}" aria-hidden="true">|</span>
                    <span id="video-meta" class="{{ $durationText ? '' : 'text-slate-400' }}">{{ $durationText ?? 'Durasi tidak tersedia' }}</span>
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold" data-video-actions>
                @if($videoFile && $videoFile->web_view_link)
                    <a id="video-view" target="_blank" href="{{ $videoFile->web_view_link }}" class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-indigo-600 transition-all duration-300 ease-in-out hover:bg-indigo-100">
                        @include('layouts.partials.icon', ['name' => 'external-link', 'classes' => 'h-3.5 w-3.5'])
                        Lihat
                    </a>
                @elseif($videoFile)
                    <span id="video-processing" class="inline-flex items-center gap-1 rounded-lg border border-dashed border-slate-200 px-3 py-1.5 text-slate-400">
                        @include('layouts.partials.icon', ['name' => 'refresh', 'classes' => 'h-3.5 w-3.5'])
                        Sinkronisasi...
                    </span>
                @else
                    <span class="text-xs text-slate-400">Belum ada video.</span>
                @endif
                @if($canEdit && $videoFile)
                    <form action="{{ route('guru.submissions.delete', [$schedule, 'video']) }}" method="post" class="delete-form inline-flex items-center" data-kind="Video">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-rose-600 transition-all duration-300 ease-in-out hover:bg-rose-100 js-open-delete">
                            @include('layouts.partials.icon', ['name' => 'trash', 'classes' => 'h-3.5 w-3.5'])
                            Hapus
                        </button>
                    </form>
                @endif
            </div>
        </article>
    </section>

    @if($canEdit)
        <section class="space-y-6 rounded-2xl border border-slate-200 bg-white/90 p-8 shadow-md shadow-slate-200/40">
            <header class="space-y-2">
                <h2 class="text-lg font-semibold text-slate-900">Unggah berkas</h2>
                <p class="text-xs text-slate-500">Setiap jenis dokumen (RPP, Asesmen, Administrasi) dapat menyimpan maksimal {{ $maxPerCategory }} berkas. Hapus berkas lama jika ingin mengganti.</p>
            </header>
            @if(session('error'))
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm text-rose-600">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600">{{ $errors->first() }}</div>
            @endif
            <form id="upload-form" action="{{ route('guru.submissions.store', $schedule) }}" method="post" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div class="grid gap-6 md:grid-cols-2">
                    <label class="group flex cursor-pointer flex-col gap-3 rounded-2xl border-2 border-dashed border-indigo-200 bg-[#F9FAFB] p-6 text-sm text-slate-500 transition-all duration-300 ease-in-out hover:border-indigo-300 hover:bg-indigo-50/40">
                        <span class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600">
                            @include('layouts.partials.icon', ['name' => 'document', 'classes' => 'h-5 w-5 text-indigo-500'])
                            RPP (PDF/DOC/DOCX) - multi file, maksimal 20MB/berkas
                        </span>
                        <span class="text-xs text-slate-400">Klik untuk pilih file</span>
                        <input type="file" name="rpp[]" accept="application/pdf,.doc,.docx" class="hidden" data-file-input="rpp" multiple />
                        <div class="hidden mt-2 space-y-1" data-file-names="rpp"></div>
                    </label>

                    <label class="group flex cursor-pointer flex-col gap-3 rounded-2xl border-2 border-dashed border-indigo-200 bg-[#F9FAFB] p-6 text-sm text-slate-500 transition-all duration-300 ease-in-out hover:border-indigo-300 hover:bg-indigo-50/40">
                        <span class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600">
                            @include('layouts.partials.icon', ['name' => 'document', 'classes' => 'h-5 w-5 text-indigo-500'])
                            Asesmen (PDF/DOC/DOCX) - multi file, maksimal 20MB/berkas
                        </span>
                        <span class="text-xs text-slate-400">Klik untuk pilih file</span>
                        <input type="file" name="asesmen[]" accept="application/pdf,.doc,.docx" class="hidden" data-file-input="asesmen" multiple />
                        <div class="hidden mt-2 space-y-1" data-file-names="asesmen"></div>
                    </label>

                    <label class="group flex cursor-pointer flex-col gap-3 rounded-2xl border-2 border-dashed border-indigo-200 bg-[#F9FAFB] p-6 text-sm text-slate-500 transition-all duration-300 ease-in-out hover:border-indigo-300 hover:bg-indigo-50/40">
                        <span class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600">
                            @include('layouts.partials.icon', ['name' => 'document', 'classes' => 'h-5 w-5 text-indigo-500'])
                            Administrasi (PDF/DOC/DOCX) - multi file, maksimal 20MB/berkas
                        </span>
                        <span class="text-xs text-slate-400">Klik untuk pilih file</span>
                        <input type="file" name="administrasi[]" accept="application/pdf,.doc,.docx" class="hidden" data-file-input="administrasi" multiple />
                        <div class="hidden mt-2 space-y-1" data-file-names="administrasi"></div>
                    </label>

                    <div class="flex flex-col gap-3 rounded-2xl border-2 border-dashed border-indigo-200 bg-[#F9FAFB] p-6 text-sm text-slate-500 transition-all duration-300 ease-in-out hover:border-indigo-300 hover:bg-indigo-50/40">
                        <span class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600">
                            @include('layouts.partials.icon', ['name' => 'video', 'classes' => 'h-5 w-5 text-indigo-500'])
                            Video Link (YouTube/Google Drive) - durasi <= 30 menit
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
                        @include('layouts.partials.icon', ['name' => 'inbox', 'classes' => 'h-4 w-4 text-indigo-500'])
                        Setiap jenis dokumen memiliki batas {{ $maxPerCategory }} berkas.
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
        const overlay = document.getElementById('upload-overlay');
        if(form){
            form.addEventListener('submit', ()=>{
                if(overlay){
                    overlay.classList.remove('hidden');
                    overlay.classList.add('flex');
                }
                document.body.classList.add('is-uploading');
                const title = document.getElementById('overlay-title');
                if(title){ title.textContent = 'Mengunggah berkas...'; }
                setTimeout(()=>{
                    Array.from(form.elements).forEach(el=>{
                        if(!el) return;
                        const type = el.getAttribute('type');
                        const isHidden = el.getAttribute('type') === 'hidden';
                        const isFile = type === 'file';
                        const name = el.getAttribute('name') || '';
                        const isCsrf = name === '_token' || name === '_method';
                        if(isHidden || isCsrf || isFile) return;
                        el.setAttribute('disabled','disabled');
                    });
                },0);
            });
        }

        // File preview sederhana
        var fileInputs = document.querySelectorAll('[data-file-input]');
        
        fileInputs.forEach(function(input) {
            input.onchange = function() {
                var category = this.getAttribute('data-file-input');
                var div = document.querySelector('[data-file-names="' + category + '"]');
                
                if (!div) return;
                
                var html = '';
                for (var i = 0; i < this.files.length; i++) {
                    html += '<div class="flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs"><svg class="h-3.5 w-3.5 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg><span class="font-semibold text-indigo-700">' + this.files[i].name + '</span></div>';
                }
                
                if (html) {
                    div.innerHTML = html;
                    div.style.display = 'block';
                } else {
                    div.innerHTML = '';
                    div.style.display = 'none';
                }
            };
        });

        const CAN_EDIT = {{ $canEdit ? 'true' : 'false' }};
        @if($canEdit)
        const statusUrl = {!! json_encode(route('guru.submissions.status', $schedule)) !!};
        const deleteUrlTemplate = {!! json_encode(route('guru.submissions.documents.destroy', [$schedule, '__DOC__'])) !!};
        @else
        const statusUrl = {!! json_encode(route('supervisor.submissions.status', $schedule)) !!};
        const deleteUrlTemplate = '';
        @endif
        const csrfField = `@csrf`;
        const deleteMethod = `@method('DELETE')`;
        const CATEGORY_LABELS = @json($categoryLabels);

        function fmtSize(size){ if(!size) return '-'; const mb = size/1024/1024; return mb>=1? mb.toFixed(2)+' MB' : Math.round(size/1024)+' KB'; }
        function fmtDur(ms){
            if(!ms){ return null; }
            const sec = Math.round(ms/1000);
            const m = Math.floor(sec/60);
            const s = String(sec%60).padStart(2,'0');
            return m+':'+s+' menit';
        }

        function renderDocuments(category, docs){
            const container = document.querySelector('[data-doc-list="'+category+'"]');
            const emptyState = document.querySelector('[data-doc-empty="'+category+'"]');
            if(!container) return;
            if(!docs || !docs.length){
                container.innerHTML = '';
                container.dataset.hasProcessing = 'false';
                if(emptyState){ emptyState.classList.remove('hidden'); }
                return;
            }
            const rows = docs.map(doc => {
                const viewBtn = doc.webViewLink
                    ? '<a target="_blank" href="' + doc.webViewLink + '" class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-indigo-600 transition-all duration-300 ease-in-out hover:bg-indigo-100">Lihat</a>'
                    : '<span class="inline-flex items-center gap-1 rounded-lg border border-dashed border-slate-200 px-3 py-1.5 text-slate-400" data-doc-processing="true">Sinkronisasi...</span>';
                let deleteBtn = '';
                if (CAN_EDIT) {
                    const url = deleteUrlTemplate.replace('__DOC__', doc.id);
                    const docKind = (CATEGORY_LABELS[category] || 'Dokumen') + ': ' + doc.name;
                    deleteBtn = '<form action="' + url + '" method="post" class="delete-form inline-flex items-center" data-kind="' + docKind + '">' + csrfField + deleteMethod + '<button type="button" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-rose-600 transition-all duration-300 ease-in-out hover:bg-rose-100 js-open-delete">Hapus</button></form>';
                }
                const pageMeta = doc.pageCount ? '<span class="mx-1 text-slate-400" aria-hidden="true">|</span>' + doc.pageCount + ' halaman' : '';
                const docName = doc.name || 'Tanpa nama';
                return '<div class="flex flex-col gap-2 rounded-xl border border-white/60 bg-white px-4 py-3 shadow-sm shadow-slate-200"><div><p class="text-sm font-semibold text-slate-900 break-words">' + docName + '</p><p class="text-xs text-slate-500 flex items-center gap-1">' + fmtSize(doc.size) + pageMeta + '</p></div><div class="flex flex-wrap items-center gap-2 text-xs font-semibold">' + viewBtn + deleteBtn + '</div></div>';
            }).join('');
            container.innerHTML = rows;
            container.dataset.hasProcessing = docs.some(doc => !doc.webViewLink) ? 'true' : 'false';
            if(emptyState){ emptyState.classList.add('hidden'); }
        }

                function updateVideo(video){
            const nameEl = document.getElementById('video-name');
            const sizeEl = document.getElementById('video-size');
            const metaEl = document.getElementById('video-meta');
            const separator = document.getElementById('video-meta-separator');
            const viewWrap = document.getElementById('video-view');
            const processing = document.getElementById('video-processing');
            const actions = document.querySelector('[data-video-actions]');
            if(!video){
                if(nameEl) nameEl.textContent = '-';
                if(sizeEl) sizeEl.textContent = '-';
                if(metaEl){
                    metaEl.textContent = 'Durasi tidak tersedia';
                    metaEl.classList.add('text-slate-400');
                }
                if(separator){ separator.classList.add('hidden'); }
                if(viewWrap) viewWrap.remove();
                if(processing) processing.remove();
                return;
            }
            if(nameEl) nameEl.textContent = video.name || '-';
            if(sizeEl) sizeEl.textContent = fmtSize(video.size);
            const durationText = fmtDur(video.durationMillis);
            if(metaEl){
                if(durationText){
                    metaEl.textContent = durationText;
                    metaEl.classList.remove('text-slate-400');
                    if(separator){ separator.classList.remove('hidden'); }
                } else {
                    metaEl.textContent = 'Durasi tidak tersedia';
                    metaEl.classList.add('text-slate-400');
                    if(separator){ separator.classList.add('hidden'); }
                }
            }
            if(video.webViewLink){
                if(processing) processing.remove();
                if(viewWrap){
                    viewWrap.href = video.webViewLink;
                } else if(actions){
                    const link = document.createElement('a');
                    link.id = 'video-view';
                    link.target = '_blank';
                    link.className = 'inline-flex items-center gap-1 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-indigo-600 transition-all duration-300 ease-in-out hover:bg-indigo-100';
                    link.textContent = 'Lihat';
                    if(actions){ actions.prepend(link); }
                }
            } else if(!processing && actions){
                const span = document.createElement('span');
                span.id = 'video-processing';
                span.className = 'inline-flex items-center gap-1 rounded-lg border border-dashed border-slate-200 px-3 py-1.5 text-slate-400';
                span.textContent = 'Sinkronisasi...';
                actions.prepend(span);
            }
        }


        function apply(){
            if (!statusUrl) return;
            
            fetch(statusUrl, {headers:{'X-Requested-With':'XMLHttpRequest'}})
                .then(r=>r.json())
                .then(d=>{
                    if(d.documents){
                        Object.keys(d.documents).forEach(key => renderDocuments(key, d.documents[key] || []));
                    }
                    if(d.video){ updateVideo(d.video); }
                })
                .catch((err)=>{
                    console.error('Error fetching status:', err);
                })
                .finally(()=>{
                    const docLists = Array.from(document.querySelectorAll('[data-doc-list]'));
                    const hasProcessing = docLists.some(list => list.dataset.hasProcessing === 'true');
                    const videoProcessing = !!document.getElementById('video-processing');
                    if(hasProcessing || videoProcessing){ setTimeout(apply, 3000); }
                });
        }
        apply();

        // Modal delete
        const modal = document.createElement('div');
        modal.id = 'delete-modal';
        modal.className = 'fixed inset-0 z-[100] hidden items-center justify-center';
        modal.innerHTML = '<div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="document.getElementById(\'delete-modal\').classList.add(\'hidden\');document.getElementById(\'delete-modal\').classList.remove(\'flex\');"></div>'+
          '<div class="relative w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/60">'+
          '<h3 class="text-lg font-semibold text-slate-900">Konfirmasi Hapus</h3>'+ 
          '<p id="delete-modal-text" class="mt-2 text-sm text-slate-500">Apakah Anda yakin ingin menghapus berkas ini?</p>'+ 
          '<div class="mt-5 flex justify-end gap-2">'+ 
          '<button type="button" id="btn-cancel-del" class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-600 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-600">Batal</button>'+ 
          '<button type="button" id="btn-confirm-del" class="rounded-xl bg-rose-500 px-3 py-1.5 text-sm font-semibold text-white shadow-md shadow-rose-200/70 transition-all duration-300 ease-in-out hover:opacity-90">Hapus</button>'+ 
          '</div></div>';
        document.body.appendChild(modal);
        
        var pendingForm = null;
        
        // Event delegation - tangkap klik di seluruh document
        document.body.addEventListener('click', function(e) {
            // Cari apakah yang diklik adalah tombol delete atau child-nya
            var deleteBtn = e.target.closest('.js-open-delete');
            if (deleteBtn) {
                e.preventDefault();
                e.stopPropagation();
                
                var form = deleteBtn.closest('form.delete-form');
                if (!form) return;
                
                pendingForm = form;
                var kind = form.getAttribute('data-kind') || 'berkas ini';
                document.getElementById('delete-modal-text').textContent = 'Apakah Anda yakin ingin menghapus ' + kind + '?';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        });
        
        // Tombol batal
        document.getElementById('btn-cancel-del').onclick = function() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            pendingForm = null;
        };
        
        // Tombol konfirmasi hapus
        document.getElementById('btn-confirm-del').onclick = function() {
            if (!pendingForm) return;
            
            this.disabled = true;
            this.textContent = 'Menghapus...';
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            
            if (overlay) {
                document.getElementById('overlay-title').textContent = 'Menghapus berkas...';
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
            }
            
            setTimeout(function() {
                pendingForm.submit();
            }, 100);
        };
    })();
</script>
@endsection





