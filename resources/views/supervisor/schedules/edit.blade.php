@extends('layouts.app', ['title' => 'Pengawas - Ubah Jadwal'])

@section('content')
<div class="space-y-10">
  <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div class="space-y-3">
      <p class="text-xs font-medium uppercase tracking-[0.3em] text-slate-400">Manajemen Jadwal</p>
      <div class="space-y-1">
        <h1 class="text-3xl font-semibold text-slate-900">Ubah Jadwal Supervisi</h1>
        <p class="text-sm text-slate-500">Perbarui informasi jadwal untuk menjaga sinkronisasi dengan guru terkait.</p>
      </div>
    </div>
    <a href="{{ route('supervisor.schedules') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm shadow-slate-200/70 transition-all duration-300 ease-in-out hover:border-slate-300 hover:bg-slate-50">
      @include('layouts.partials.icon', ['name' => 'chevron-left', 'classes' => 'h-4 w-4 text-slate-400'])
      Kembali ke Jadwal
    </a>
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

  <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40">
    <form action="{{ route('supervisor.schedules.update', $schedule) }}" method="post" class="space-y-6">
      @csrf

      <div class="grid gap-6 md:grid-cols-2">
        <div class="space-y-2">
          <label class="text-sm font-semibold text-slate-700">Judul Sesi</label>
          <input type="text" name="title" value="{{ old('title', $schedule->title) }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" placeholder="Sesi Supervisi" required>
        </div>
        <div class="space-y-2">
          <label class="text-sm font-semibold text-slate-700">Sekolah</label>
          <select name="school_id" id="school-select" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
            @foreach ($schools as $school)
              <option value="{{ $school->id }}" @selected(old('school_id', $schedule->school_id) == $school->id)>{{ $school->name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="grid gap-6 md:grid-cols-2">
        <div class="space-y-2">
          <label class="text-sm font-semibold text-slate-700">Guru</label>
          @php
            $selectedSchoolId = old('school_id', $schedule->school_id);
            $oldTeacherId = old('teacher_id', $schedule->teacher_id);
            $initialTeachers = $selectedSchoolId ? ($teachersBySchool[$selectedSchoolId] ?? collect()) : collect();
          @endphp
          <select
            name="teacher_id"
            id="teacher-select"
            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            required
            data-placeholder="Pilih Guru"
            data-select-school-label="Pilih sekolah terlebih dahulu"
            data-empty-label="Tidak ada guru pada sekolah ini"
            data-old-teacher="{{ $oldTeacherId }}"
          >
            <option value="" disabled {{ $oldTeacherId ? '' : 'selected' }}>Pilih Guru</option>
            @foreach ($initialTeachers as $teacher)
              <option value="{{ $teacher['id'] }}" @selected($oldTeacherId == $teacher['id'])>
                {{ $teacher['name'] }} @if(!empty($teacher['nip']))(NIP {{ $teacher['nip'] }})@endif
              </option>
            @endforeach
          </select>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
          <div class="space-y-2">
            <label class="text-sm font-semibold text-slate-700">Tanggal</label>
            <input type="date" name="date" value="{{ old('date', optional($schedule->date)->format('Y-m-d')) }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
          </div>
          <div class="space-y-2">
            <label class="text-sm font-semibold text-slate-700">Kelas</label>
            <select name="class_name" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
              @foreach (['1','2','3','4','5','6'] as $class)
                <option value="{{ $class }}" @selected(old('class_name', $schedule->class_name) == $class)>Kelas {{ $class }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-sm font-semibold text-slate-700">Catatan untuk Guru (Opsional)</label>
        <textarea name="notes" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" placeholder="Tambahkan arahan atau fokus supervisi">{{ old('notes', $schedule->remarks) }}</textarea>
        <p class="text-xs text-slate-400">Catatan akan terlihat oleh guru pada jadwal ini.</p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-200/60 transition-all duration-300 ease-in-out hover:opacity-90">
          @include('layouts.partials.icon', ['name' => 'check', 'classes' => 'h-4 w-4 text-white'])
          Simpan Perubahan
        </button>
        <button type="submit" form="delete-schedule-form" class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-600 transition-all duration-300 ease-in-out hover:bg-rose-100">
          @include('layouts.partials.icon', ['name' => 'trash', 'classes' => 'h-4 w-4 text-rose-500'])
          Hapus Jadwal
        </button>
      </div>
    </form>
  </div>

  <form id="delete-schedule-form" action="{{ route('supervisor.schedules.destroy', $schedule) }}" method="post" class="hidden js-confirm" data-message="Hapus jadwal ini? Tindakan tidak dapat dibatalkan." data-variant="danger">
    @csrf
    @method('DELETE')
  </form>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const teacherSelect = document.getElementById('teacher-select');
    if (!teacherSelect) {
      return;
    }

    const teacherData = @json($teachersBySchool);
    const schoolSelect = document.getElementById('school-select');
    const selectSchoolLabel = teacherSelect.dataset.selectSchoolLabel || 'Pilih sekolah terlebih dahulu';
    const placeholder = teacherSelect.dataset.placeholder || 'Pilih Guru';
    const emptyLabel = teacherSelect.dataset.emptyLabel || 'Tidak ada guru pada sekolah ini';

    const populateOptions = function (schoolId, preserveSelection) {
      teacherSelect.innerHTML = '';

      const defaultOption = document.createElement('option');
      defaultOption.value = '';
      defaultOption.disabled = true;
      defaultOption.textContent = schoolId ? placeholder : selectSchoolLabel;
      teacherSelect.appendChild(defaultOption);

      const teachers = schoolId && teacherData[String(schoolId)] ? teacherData[String(schoolId)] : [];

      if (teachers.length === 0) {
        if (schoolId) {
          defaultOption.textContent = emptyLabel;
        }
        defaultOption.selected = true;
        teacherSelect.disabled = true;
        return;
      }

      teachers.forEach(function (teacher) {
        const option = document.createElement('option');
        option.value = teacher.id;
        option.textContent = teacher.nip ? teacher.name + ' (NIP ' + teacher.nip + ')' : teacher.name;
        teacherSelect.appendChild(option);
      });

      teacherSelect.disabled = false;

      if (preserveSelection) {
        const previous = teacherSelect.dataset.oldTeacher;
        if (previous && teacherSelect.querySelector('option[value="' + previous + '"]')) {
          teacherSelect.value = previous;
          teacherSelect.dataset.oldTeacher = '';
          return;
        }
      }

      defaultOption.selected = true;
      teacherSelect.value = '';
    };

    if (schoolSelect) {
      const initialSchoolId = schoolSelect.value;
      if (initialSchoolId) {
        populateOptions(initialSchoolId, true);
      }

      schoolSelect.addEventListener('change', function (event) {
        teacherSelect.dataset.oldTeacher = '';
        populateOptions(event.target.value, false);
      });
    }
  });
</script>
@endpush
