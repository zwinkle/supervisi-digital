@extends('layouts.app', ['title' => 'Penilaian Jadwal'])

@section('content')
<div class="space-y-10">
  @if (session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-600 shadow-sm shadow-emerald-100/60">
      {{ session('success') }}
    </div>
  @endif

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
      @if($schedule->evaluation_method === 'upload' && $schedule->uploaded_evaluation_file)
        <a href="{{ route('supervisor.schedules.download-evaluation', $schedule) }}" class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 shadow-sm shadow-emerald-100/70 transition-all duration-300 ease-in-out hover:border-emerald-300 hover:bg-emerald-100">
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
          <a href="{{ route('supervisor.schedules.export', $schedule) }}" class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 shadow-sm shadow-emerald-100/70 transition-all duration-300 ease-in-out hover:border-emerald-300 hover:bg-emerald-100">
            @include('layouts.partials.icon', ['name' => 'download', 'classes' => 'h-4 w-4 text-emerald-600'])
            Ekspor
          </a>
        @else
          <button disabled class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-400 cursor-not-allowed">
            @include('layouts.partials.icon', ['name' => 'download', 'classes' => 'h-4 w-4 text-slate-400'])
            Ekspor
          </button>
        @endif
      @endif
      <a href="{{ route('supervisor.schedules') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm shadow-slate-200/70 transition-all duration-300 ease-in-out hover:border-slate-300 hover:bg-slate-50">
        @include('layouts.partials.icon', ['name' => 'chevron-left', 'classes' => 'h-4 w-4 text-slate-400'])
        Kembali ke Jadwal
      </a>
    </div>
  </div>

  <!-- Opsi Metode Evaluasi -->
  <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40">
    <h2 class="text-lg font-semibold text-slate-900">Metode Evaluasi</h2>
    <p class="mt-1 text-sm text-slate-500">Pilih metode untuk melakukan penilaian supervisi</p>
    
    <div class="mt-6 grid gap-6 md:grid-cols-2">
      <!-- Opsi 1: Penilaian Manual -->
      <div id="manual-option" class="rounded-xl border-2 transition-all" data-method="manual">
        <div class="flex items-start gap-4 p-5">
          <input type="radio" name="evaluation_method" value="manual" id="method_manual" 
            class="mt-1 h-5 w-5 text-indigo-600 focus:ring-2 focus:ring-indigo-500" 
            {{ $schedule->evaluation_method === 'manual' ? 'checked' : '' }}
            onchange="handleMethodChange('manual')">
          <div class="flex-1">
            <label for="method_manual" class="cursor-pointer">
              <h3 class="text-base font-semibold text-slate-900">Penilaian Manual</h3>
              <p class="mt-1 text-sm text-slate-600">Nilai menggunakan form penilaian dengan radio button dan checkbox untuk setiap aspek (RPP, Pembelajaran, Asesmen)</p>
            </label>
          </div>
        </div>
      </div>

      <!-- Opsi 2: Upload File -->
      <div id="upload-option" class="rounded-xl border-2 transition-all" data-method="upload">
        <div class="flex items-start gap-4 p-5">
          <input type="radio" name="evaluation_method" value="upload" id="method_upload"
            class="mt-1 h-5 w-5 text-indigo-600 focus:ring-2 focus:ring-indigo-500"
            {{ $schedule->evaluation_method === 'upload' ? 'checked' : '' }}
            onchange="handleMethodChange('upload')">
          <div class="flex-1">
            <label for="method_upload" class="cursor-pointer">
              <h3 class="text-base font-semibold text-slate-900">Upload Hasil Supervisi</h3>
              <p class="mt-1 text-sm text-slate-600">Upload file hasil supervisi yang sudah jadi (PDF, DOC, DOCX) yang mencakup seluruh penilaian</p>
            </label>
          </div>
        </div>
      </div>
    </div>

    <!-- Form Upload -->
    <div id="upload-form-section" class="mt-6 {{ $schedule->evaluation_method === 'upload' ? '' : 'hidden' }}">
      <!-- Input Skor untuk Metode Upload Manual -->
      <div class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40">
        <h3 class="text-base font-semibold text-slate-900 mb-4">Input Skor Evaluasi</h3>
        <p class="text-sm text-slate-600 mb-4">Wajib diisi untuk melengkapi data evaluasi saat metode upload file</p>
        
        <div class="grid gap-4 md:grid-cols-3">
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Skor RPP</label>
            <input type="number" name="scores[rpp]" min="0" max="100" step="0.5" 
              class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200"
              placeholder="0-100"
              {{ $schedule->uploaded_evaluation_file ? '' : 'required' }}
              {{ ($schedule->hasSubmissionFor('rpp') && $schedule->hasSubmissionFor('pembelajaran') && $schedule->hasSubmissionFor('asesmen')) ? '' : 'disabled' }}
              @if($schedule->manual_rpp_score) value="{{ $schedule->manual_rpp_score }}" @endif>
            <p class="mt-1 text-xs text-slate-500">Skor 0-100</p>
          </div>
          
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Skor Pembelajaran</label>
            <input type="number" name="scores[pembelajaran]" min="0" max="100" step="0.5"
              class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200"
              placeholder="0-100"
              {{ $schedule->uploaded_evaluation_file ? '' : 'required' }}
              {{ ($schedule->hasSubmissionFor('rpp') && $schedule->hasSubmissionFor('pembelajaran') && $schedule->hasSubmissionFor('asesmen')) ? '' : 'disabled' }}
              @if($schedule->manual_pembelajaran_score) value="{{ $schedule->manual_pembelajaran_score }}" @endif>
            <p class="mt-1 text-xs text-slate-500">Skor 0-100</p>
          </div>
          
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Skor Asesmen</label>
            <input type="number" name="scores[asesmen]" min="0" max="100" step="0.5"
              class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200"
              placeholder="0-100"
              {{ $schedule->uploaded_evaluation_file ? '' : 'required' }}
              {{ ($schedule->hasSubmissionFor('rpp') && $schedule->hasSubmissionFor('pembelajaran') && $schedule->hasSubmissionFor('asesmen')) ? '' : 'disabled' }}
              @if($schedule->manual_asesmen_score) value="{{ $schedule->manual_asesmen_score }}" @endif>
            <p class="mt-1 text-xs text-slate-500">Skor 0-100</p>
          </div>
        </div>
        
        @if(!$schedule->hasSubmissionFor('rpp') || !$schedule->hasSubmissionFor('pembelajaran') || !$schedule->hasSubmissionFor('asesmen'))
          <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3">
            <div class="flex items-start gap-2">
              @include('layouts.partials.icon', ['name' => 'alert-triangle', 'classes' => 'h-5 w-5 text-amber-600 mt-0.5'])
              <div>
                <p class="text-sm font-medium text-amber-800">Input skor evaluasi tidak dapat dilakukan</p>
                <p class="text-xs text-amber-700 mt-1">Supervisor dapat melakukan input skor evaluasi setelah guru mengupload semua berkas yang diperlukan (RPP, Pembelajaran, dan Asesmen).</p>
              </div>
            </div>
          </div>
        @endif
      </div>

      <!-- Card Status Berkas -->
      <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40">
        <h3 class="text-base font-semibold text-slate-900 mb-4">Status Berkas Guru</h3>
        <div class="space-y-3">
          <div class="flex items-center justify-between p-3 rounded-lg border border-slate-200 bg-slate-50">
            <div class="flex items-center gap-3">
              @include('layouts.partials.icon', ['name' => 'document', 'classes' => 'h-5 w-5 text-slate-500'])
              <span class="text-sm font-medium text-slate-700">RPP (Rencana Pelaksanaan Pembelajaran)</span>
            </div>
            <div class="flex items-center gap-2">
              @if($schedule->hasSubmissionFor('rpp'))
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">
                  @include('layouts.partials.icon', ['name' => 'check-circle', 'classes' => 'h-3 w-3'])
                  Sudah diupload
                </span>
              @else
                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700">
                  @include('layouts.partials.icon', ['name' => 'x-circle', 'classes' => 'h-3 w-3'])
                  Belum diupload
                </span>
              @endif
            </div>
          </div>
          
          <div class="flex items-center justify-between p-3 rounded-lg border border-slate-200 bg-slate-50">
            <div class="flex items-center gap-3">
              @include('layouts.partials.icon', ['name' => 'layout-dashboard', 'classes' => 'h-5 w-5 text-slate-500'])
              <span class="text-sm font-medium text-slate-700">Dokumen Pembelajaran</span>
            </div>
            <div class="flex items-center gap-2">
              @if($schedule->hasSubmissionFor('pembelajaran'))
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">
                  @include('layouts.partials.icon', ['name' => 'check-circle', 'classes' => 'h-3 w-3'])
                  Sudah diupload
                </span>
              @else
                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700">
                  @include('layouts.partials.icon', ['name' => 'x-circle', 'classes' => 'h-3 w-3'])
                  Belum diupload
                </span>
              @endif
            </div>
          </div>
          
          <div class="flex items-center justify-between p-3 rounded-lg border border-slate-200 bg-slate-50">
            <div class="flex items-center gap-3">
              @include('layouts.partials.icon', ['name' => 'badge-check', 'classes' => 'h-5 w-5 text-slate-500'])
              <span class="text-sm font-medium text-slate-700">Dokumen Asesmen</span>
            </div>
            <div class="flex items-center gap-2">
              @if($schedule->hasSubmissionFor('asesmen'))
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">
                  @include('layouts.partials.icon', ['name' => 'check-circle', 'classes' => 'h-3 w-3'])
                  Sudah diupload
                </span>
              @else
                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700">
                  @include('layouts.partials.icon', ['name' => 'x-circle', 'classes' => 'h-3 w-3'])
                  Belum diupload
                </span>
              @endif
            </div>
          </div>
        </div>
        
        @if(!$schedule->hasSubmissionFor('rpp') || !$schedule->hasSubmissionFor('pembelajaran') || !$schedule->hasSubmissionFor('asesmen'))
          <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3">
            <div class="flex items-start gap-2">
              @include('layouts.partials.icon', ['name' => 'alert-triangle', 'classes' => 'h-5 w-5 text-amber-600 mt-0.5'])
              <div>
                <p class="text-sm font-medium text-amber-800">Upload hasil supervisi tidak dapat dilakukan</p>
                <p class="text-xs text-amber-700 mt-1">Supervisor dapat melakukan upload hasil supervisi setelah guru mengupload semua berkas yang diperlukan (RPP, Pembelajaran, dan Asesmen).</p>
              </div>
            </div>
          </div>
        @endif
      </div>

      <!-- Card Upload File -->
      <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40">
        <form action="{{ route('supervisor.schedules.upload-evaluation', $schedule) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
          @csrf
          <div>
            <h3 class="text-base font-semibold text-slate-900 mb-1">Upload File Hasil Supervisi</h3>
            <p class="text-sm text-slate-600 mb-4">Upload file hasil supervisi yang sudah jadi (PDF, DOC, DOCX) yang mencakup seluruh penilaian</p>
            
            @if($schedule->uploaded_evaluation_file)
              <div class="mb-3 rounded-lg border border-emerald-200 bg-emerald-50 p-3">
                <div class="flex items-center gap-2 text-sm text-emerald-700">
                  @include('layouts.partials.icon', ['name' => 'check-circle', 'classes' => 'h-5 w-5'])
                  <span class="font-medium">File sudah diupload:</span>
                  <span class="font-semibold">{{ basename($schedule->uploaded_evaluation_file) }}</span>
                </div>
              </div>
            @endif
            <input type="file" name="evaluation_file" accept=".pdf,.doc,.docx" 
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200"
              {{ $schedule->uploaded_evaluation_file ? '' : 'required' }}
              {{ ($schedule->hasSubmissionFor('rpp') && $schedule->hasSubmissionFor('pembelajaran') && $schedule->hasSubmissionFor('asesmen')) ? '' : 'disabled' }}>
            <p class="mt-1 text-xs text-slate-500">Format: PDF, DOC, DOCX. Maksimal 10MB</p>
          </div>

          <button type="submit" 
            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-200/60 transition-all duration-300 ease-in-out hover:opacity-90
            {{ ($schedule->hasSubmissionFor('rpp') && $schedule->hasSubmissionFor('pembelajaran') && $schedule->hasSubmissionFor('asesmen')) ? '' : 'opacity-50 cursor-not-allowed' }}"
            {{ ($schedule->hasSubmissionFor('rpp') && $schedule->hasSubmissionFor('pembelajaran') && $schedule->hasSubmissionFor('asesmen')) ? '' : 'disabled' }}>
            @include('layouts.partials.icon', ['name' => 'upload', 'classes' => 'h-4 w-4 text-white'])
            {{ $schedule->uploaded_evaluation_file ? 'Ganti File' : 'Upload File' }}
          </button>
        </form>
      </div>
    </div>
  </div>

  <div id="manual-evaluation-section" class="{{ $schedule->evaluation_method === 'manual' ? '' : 'hidden' }}">
    <div class="grid gap-6 md:grid-cols-3">
    @foreach ($cards as $type => $meta)
      @php($evaluation = $evalByType->get($type))
      @php($isEnabled = $availability[$type] ?? false)
      @php($buttonClasses = $isEnabled
          ? 'inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-indigo-200/60 transition-all duration-300 ease-in-out hover:opacity-90'
          : 'inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-400 cursor-not-allowed select-none')
      @php($iconClasses = $isEnabled ? 'h-4 w-4 text-white' : 'h-4 w-4 text-slate-400')
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
          @if ($isEnabled)
            <a href="{{ route('supervisor.evaluations.show', [$schedule, $type]) }}" class="{{ $buttonClasses }}">
              @include('layouts.partials.icon', ['name' => 'eye', 'classes' => $iconClasses])
              Lihat / Nilai
            </a>
          @else
            <span class="{{ $buttonClasses }}" aria-disabled="true">
              @include('layouts.partials.icon', ['name' => 'eye', 'classes' => $iconClasses])
              Lihat / Nilai
            </span>
          @endif
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
          <p class="mt-2 text-lg font-semibold text-slate-900">
            @if($schedule->evaluation_method === 'upload' && $schedule->manual_rpp_score)
              {{ $schedule->manual_rpp_score }}
            @else
              {{ optional($evalByType->get('rpp'))->total_score ?? '—' }}
            @endif
          </p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Pembelajaran</p>
          <div class="mt-2 flex items-center justify-between">
            <span class="text-lg font-semibold text-slate-900">
              @if($schedule->evaluation_method === 'upload' && $schedule->manual_pembelajaran_score)
                {{ $schedule->manual_pembelajaran_score }}
              @else
                {{ optional($evalByType->get('pembelajaran'))->total_score ?? '—' }}
              @endif
            </span>
            @if ($schedule->evaluation_method === 'manual' && optional($evalByType->get('pembelajaran'))->category)
              <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-500">{{ optional($evalByType->get('pembelajaran'))->category }}</span>
            @endif
          </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Asesmen</p>
          <p class="mt-2 text-lg font-semibold text-slate-900">
            @if($schedule->evaluation_method === 'upload' && $schedule->manual_asesmen_score)
              {{ $schedule->manual_asesmen_score }}
            @else
              {{ optional($evalByType->get('asesmen'))->total_score ?? '—' }}
            @endif
          </p>
        </div>
      </div>
    </div>
  @endif
  </div>
</div>
@endsection

@push('scripts')
<script>
// Initialize styling on page load
document.addEventListener('DOMContentLoaded', function() {
  const currentMethod = '{{ $schedule->evaluation_method }}';
  updateRadioStyling(currentMethod);
});

function handleMethodChange(method) {
  const uploadSection = document.getElementById('upload-form-section');
  const manualSection = document.getElementById('manual-evaluation-section');
  
  if (method === 'upload') {
    uploadSection.classList.remove('hidden');
    manualSection.classList.add('hidden');
  } else {
    uploadSection.classList.add('hidden');
    manualSection.classList.remove('hidden');
  }
  
  // Update radio button styling
  updateRadioStyling(method);
  
  // Update evaluation method via AJAX
  fetch('{{ route("supervisor.schedules.update-method", $schedule) }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    },
    body: JSON.stringify({ evaluation_method: method })
  });
}

function updateRadioStyling(selectedMethod) {
  const manualOption = document.getElementById('manual-option');
  const uploadOption = document.getElementById('upload-option');
  
  // Reset both options to default
  manualOption.classList.remove('border-indigo-500', 'bg-indigo-50');
  manualOption.classList.add('border-slate-200', 'bg-white');
  
  uploadOption.classList.remove('border-indigo-500', 'bg-indigo-50');
  uploadOption.classList.add('border-slate-200', 'bg-white');
  
  // Apply selected styling
  if (selectedMethod === 'manual') {
    manualOption.classList.remove('border-slate-200', 'bg-white');
    manualOption.classList.add('border-indigo-500', 'bg-indigo-50');
  } else if (selectedMethod === 'upload') {
    uploadOption.classList.remove('border-slate-200', 'bg-white');
    uploadOption.classList.add('border-indigo-500', 'bg-indigo-50');
  }
}
</script>
@endpush
