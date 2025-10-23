@extends('layouts.app', ['title' => 'Unggah Berkas'])

@php($canEdit = auth()->id() === $schedule->teacher_id)
@php($submission = $schedule->submission)

@section('content')
<div id="upload-overlay" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm">
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
            <p class="text-sm text-slate-500">Sinkronkan dokumen RPP dan video pembelajaran ke Google Drive institusi.</p>
            <div class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-[#F9FAFB] px-4 py-2 text-xs font-semibold text-slate-500">
                @include('layouts.partials.icon', ['name' => 'calendar', 'classes' => 'h-4 w-4 text-indigo-500'])
                {{ $schedule->date->translatedFormat('d F Y') }}
            </div>
        </div>
        <x-back-button :href="$canEdit ? route('guru.schedules') : route('supervisor.schedules')" />
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600 shadow-sm shadow-rose-100/60">
            <ul class="list-disc space-y-1 pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-600 shadow-sm shadow-emerald-100/60">
            {{ session('success') }}
        </div>
    @endif

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

        <div class="overflow-hidden rounded-xl border border-slate-200">
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
                        <td id="rpp-name" class="px-4 py-3 break-words">{{ optional(optional($submission)->rppFile)->name ?? '-' }}</td>
                        <td id="rpp-size" class="px-4 py-3 whitespace-nowrap">
                            @php($rppSize = optional(optional($submission)->rppFile)->extra['size'] ?? null)
                            {{ $rppSize ? number_format($rppSize/1024, 0) . ' KB' : '-' }}
                        </td>
                        <td id="rpp-meta" class="px-4 py-3 whitespace-nowrap">
                            @php($rppPages = optional(optional($submission)->rppFile)->extra['pageCount'] ?? null)
                            {{ $rppPages ? ($rppPages . ' halaman') : '-' }}
                        </td>
                        <td id="rpp-action" class="px-4 py-3 whitespace-nowrap">
                            @if(optional($submission)->rppFile)
                                @if(optional($submission->rppFile)->web_view_link)
                                    <a id="rpp-view" target="_blank" href="{{ optional($submission->rppFile)->web_view_link }}" class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-500 transition-all duration-300 ease-in-out hover:text-indigo-600">
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
                                        <button id="rpp-processing" type="button" class="cursor-not-allowed text-sm font-semibold text-slate-400" disabled>Proses unggah…</button>
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
                        <td id="video-name" class="px-4 py-3 break-words">{{ optional(optional($submission)->videoFile)->name ?? '-' }}</td>
                        <td id="video-size" class="px-4 py-3 whitespace-nowrap">
                            @php($vidSize = optional(optional($submission)->videoFile)->extra['size'] ?? null)
                            {{ $vidSize ? number_format($vidSize/1024/1024, 2) . ' MB' : '-' }}
                        </td>
                        <td id="video-meta" class="px-4 py-3 whitespace-nowrap">
                            @php($durMs = optional(optional($submission)->videoFile)->extra['videoMediaMetadata']['durationMillis'] ?? null)
                            @if($durMs)
                                @php($sec = (int) round($durMs / 1000))
                                {{ floor($sec/60) }}:{{ str_pad($sec % 60, 2, '0', STR_PAD_LEFT) }}
                            @else
                                -
                            @endif
                        </td>
                        <td id="video-action" class="px-4 py-3 whitespace-nowrap">
                            @if(optional($submission)->videoFile)
                                @if(optional($submission->videoFile)->web_view_link)
                                    <a id="video-view" target="_blank" href="{{ optional($submission->videoFile)->web_view_link }}" class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-500 transition-all duration-300 ease-in-out hover:text-indigo-600">
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
                                        <button id="video-processing" type="button" class="cursor-not-allowed text-sm font-semibold text-slate-400" disabled>Proses unggah…</button>
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
                <p class="text-xs text-slate-500">Mengunggah RPP atau Video akan mengganti berkas sebelumnya untuk jenis tersebut.</p>
            </header>
            <form id="upload-form" action="{{ route('guru.submissions.store', $schedule) }}" method="post" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <input type="hidden" name="video_duration_ms" id="video-duration-ms" value="">

                <div class="grid gap-6 md:grid-cols-2">
                    <label class="group flex cursor-pointer flex-col gap-3 rounded-2xl border-2 border-dashed border-indigo-200 bg-[#F9FAFB] p-6 text-sm text-slate-500 transition-all duration-300 ease-in-out hover:border-indigo-300 hover:bg-indigo-50/40">
                        <span class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600">
                            @include('layouts.partials.icon', ['name' => 'document', 'classes' => 'h-5 w-5 text-indigo-500'])
                            RPP (PDF/DOC/DOCX) • maksimal 20MB
                        </span>
                        <span class="text-xs text-slate-400">Seret dan lepas atau pilih berkas dari perangkat Anda.</span>
                        <input type="file" name="rpp" accept="application/pdf,.doc,.docx" class="hidden" />
                    </label>

                    <label class="group flex cursor-pointer flex-col gap-3 rounded-2xl border-2 border-dashed border-indigo-200 bg-[#F9FAFB] p-6 text-sm text-slate-500 transition-all duration-300 ease-in-out hover:border-indigo-300 hover:bg-indigo-50/40">
                        <span class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600">
                            @include('layouts.partials.icon', ['name' => 'video', 'classes' => 'h-5 w-5 text-indigo-500'])
                            Video (MP4) • durasi ± 30 menit
                        </span>
                        <span class="text-xs text-slate-400">Durasi diverifikasi otomatis. Metadata Drive akan diperbarui setelah unggah.</span>
                        <input id="input-video" type="file" name="video" accept="video/mp4" class="hidden" />
                    </label>
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
                // Capture client-side duration to hidden input
                (function(){
                  const inputVideo = document.getElementById('input-video');
                  const hiddenDur = document.getElementById('video-duration-ms');
                  if(!inputVideo || !hiddenDur) return;
                  inputVideo.addEventListener('change', ()=>{
                    const file = inputVideo.files && inputVideo.files[0];
                    if(!file){ hiddenDur.value=''; return; }
                    try{
                      const url = URL.createObjectURL(file);
                      const v = document.createElement('video');
                      v.preload = 'metadata';
                      v.src = url;
                      v.onloadedmetadata = function(){
                        URL.revokeObjectURL(url);
                        const seconds = Math.round(v.duration || 0);
                        hiddenDur.value = String(seconds*1000);
                      };
                      setTimeout(()=>{ try{ URL.revokeObjectURL(url);}catch(_){} }, 10000);
                    }catch(_){ hiddenDur.value=''; }
                  });
                })();
                function apply(){
                    fetch(statusUrl, {headers:{'X-Requested-With':'XMLHttpRequest'}})
                      .then(r=>r.json())
                      .then(d=>{
                        if(d.rpp){
                          document.getElementById('rpp-name').textContent = d.rpp.name || '-';
                          document.getElementById('rpp-size').textContent = fmtSize(d.rpp.size);
                          document.getElementById('rpp-meta').textContent = d.rpp.pageCount ? (d.rpp.pageCount+' halaman') : '-';
                          if(d.rpp.webViewLink){
                            const act = document.getElementById('rpp-action');
                            let html = '<a id="rpp-view" target="_blank" href="'+d.rpp.webViewLink+'" class="text-indigo-600 hover:underline">Lihat</a>';
                            if (CAN_EDIT) {
                              html += '<form id="rpp-del" action="{{ route('guru.submissions.delete', [$schedule, 'rpp']) }}" method="post" class="inline ml-2 delete-form" data-kind="RPP">@csrf<button type="button" class="text-red-600 hover:underline js-open-delete">Hapus</button></form>';
                            }
                            act.innerHTML = html;
                          }
                        }
                        if(d.video){
                          document.getElementById('video-name').textContent = d.video.name || '-';
                          document.getElementById('video-size').textContent = fmtSize(d.video.size);
                          document.getElementById('video-meta').textContent = fmtDur(d.video.durationMillis);
                          if(d.video.webViewLink){
                            const act = document.getElementById('video-action');
                            let html = '<a id="video-view" target="_blank" href="'+d.video.webViewLink+'" class="text-indigo-600 hover:underline">Lihat</a>';
                            if (CAN_EDIT) {
                              html += '<form id="video-del" action="{{ route('guru.submissions.delete', [$schedule, 'video']) }}" method="post" class="inline ml-2 delete-form" data-kind="Video">@csrf<button type="button" class="text-red-600 hover:underline js-open-delete">Hapus</button></form>';
                            }
                            act.innerHTML = html;
                          }
                        }
                      })
                      .catch(()=>{})
                      .finally(()=>{
                        // keep polling while processing buttons visible or duration not yet available
                        const rppProcessing = !!document.getElementById('rpp-processing');
                        const videoProcessing = !!document.getElementById('video-processing');
                        const videoHasFile = (document.getElementById('video-name').textContent.trim() !== '-');
                        const videoHasDuration = (document.getElementById('video-meta').textContent.trim() !== '-');
                        const needPoll = rppProcessing || videoProcessing || (videoHasFile && !videoHasDuration);
                        if(needPoll){ setTimeout(apply, 3000); }
                      });
                }
                // Delete confirmation modal
                const modal = document.createElement('div');
                modal.id = 'delete-modal';
                modal.className = 'fixed inset-0 z-50 hidden items-center justify-center';
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
                });
                document.getElementById('btn-cancel-del').addEventListener('click', ()=>{
                  modal.classList.add('hidden');
                  modal.classList.remove('flex');
                  pendingForm = null;
                });
                const btnConfirm = document.getElementById('btn-confirm-del');
                btnConfirm.addEventListener('click', ()=>{
                  if(!pendingForm) return;
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
                  if(overlay){ overlay.classList.remove('hidden'); overlay.classList.add('flex'); }
                  // prefer requestSubmit when available
                  if (typeof pendingForm.requestSubmit === 'function') {
                    pendingForm.requestSubmit();
                  } else {
                    pendingForm.submit();
                  }
                });
                apply();
            })();
        </script>
@endsection
