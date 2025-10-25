@extends('layouts.app', ['title' => 'Lengkapi Profil'])

@section('content')
<div class="flex min-h-[70vh] items-center justify-center bg-[#F9FAFB] px-4 py-16">
    <div class="w-full max-w-3xl space-y-8">
        <div class="rounded-2xl border border-slate-200 bg-white/90 p-8 shadow-lg shadow-slate-200/50">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div class="space-y-2">
                    <span class="inline-flex items-center gap-2 rounded-xl border border-indigo-100 bg-indigo-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-indigo-500">Lengkapi Profil</span>
                    <h1 class="text-3xl font-semibold text-slate-900">Finalisasi identitas Anda</h1>
                    <p class="text-sm text-slate-500">Isi data berikut untuk mengaktifkan akses penuh ke Dashboard Supervisi Digital.</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-medium text-slate-500 shadow-sm shadow-slate-200/70">
                    @include('layouts.partials.icon', ['name' => 'user-circle', 'classes' => 'mr-2 inline h-4 w-4 text-indigo-500'])
                    {{ $user->email }}
                </div>
            </div>

            <div class="mt-6 space-y-3">
                @if ($errors->any())
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600 shadow-sm shadow-rose-100/60">
                        <ul class="list-disc space-y-1 pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('info'))
                    <div class="rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-600 shadow-sm shadow-indigo-100/60">
                        {{ session('info') }}
                    </div>
                @endif
            </div>
        </div>

        <form id="profileForm" action="{{ route('profile.complete.store') }}" method="post" class="rounded-2xl border border-slate-200 bg-white/90 p-8 shadow-lg shadow-slate-200/50">
            @csrf
            <div class="grid gap-5 md:grid-cols-2">
                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Nama</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" placeholder="Nama lengkap" />
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">NIP</label>
                    <input type="text" name="nip" value="{{ old('nip', $user->nip) }}" minlength="8" maxlength="18" inputmode="numeric" pattern="^[0-9]{8,18}$" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" placeholder="Hanya angka" />
                    <p class="text-xs text-slate-400">Masukkan 8-18 digit tanpa simbol.</p>
                </div>
            </div>

            @php($requiresTeacherMeta = !$hasSupervisor || $hasTeacher)
            @if($requiresTeacherMeta)
                @php($currentTeacherType = old('teacher_type', $resolvedTeacherType))
                @php($currentTeacherSubject = old('subject', $resolvedTeacherSubject))
                @php($currentTeacherClass = old('class_name', $resolvedTeacherClass))
                <div class="mt-6 grid gap-5 md:grid-cols-2" x-data>
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Jenis Guru</label>
                        <select id="teacher-type" name="teacher_type" data-teacher-type class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" {{ $requiresTeacherMeta ? 'required' : '' }}>
                            <option value="" disabled {{ $currentTeacherType ? '' : 'selected hidden' }}>Pilih jenis guru</option>
                            @foreach(($teacherTypes ?? []) as $value => $label)
                                <option value="{{ $value }}" @selected($currentTeacherType === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2" data-teacher-field="subject" style="{{ $currentTeacherType === 'subject' ? 'display:block;' : 'display:none;' }}">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Mata Pelajaran</label>
                        <select name="subject" data-required-for="subject" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            <option value="" disabled {{ $currentTeacherSubject ? '' : 'selected hidden' }}>Pilih mata pelajaran</option>
                            @foreach(($subjects ?? []) as $opt)
                                <option value="{{ $opt }}" @selected($currentTeacherSubject === $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2" data-teacher-field="class" style="{{ $currentTeacherType === 'class' ? 'display:block;' : 'display:none;' }}">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Kelas</label>
                        <select name="class_name" data-required-for="class" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            <option value="" disabled {{ $currentTeacherClass ? '' : 'selected hidden' }}>Pilih kelas</option>
                            @foreach(($classes ?? []) as $c)
                                <option value="{{ $c }}" @selected($currentTeacherClass == $c)>Kelas {{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif

            <div class="mt-6 space-y-2">
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Sekolah</label>
                @if(!$hasTeacher && !$hasSupervisor)
                    <select id="schoolSelect" name="school_id" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" required {{ $schools->isEmpty() ? 'disabled' : '' }}>
                        <option value="" disabled selected hidden>Pilih sekolah</option>
                        @forelse($schools as $school)
                            <option value="{{ $school->id }}" @selected(old('school_id') == $school->id)>{{ $school->name }}</option>
                        @empty
                            <option value="" disabled>Belum ada data sekolah</option>
                        @endforelse
                    </select>
                    @if($schools->isEmpty())
                        <p class="text-xs text-slate-400">Admin atau supervisor perlu menambahkan sekolah terlebih dahulu.</p>
                    @endif
                @else
                    @php($selectedId = $teacherSchoolId ?? $supervisorSchoolId)
                    <select class="w-full rounded-xl border border-slate-200 bg-[#F9FAFB] px-4 py-2.5 text-sm text-slate-600" disabled>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" @selected($selectedId == $school->id)>{{ $school->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-400">Sekolah ditetapkan dari undangan yang Anda terima.</p>
                @endif
            </div>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-400">Pastikan data sesuai dokumen resmi untuk memudahkan validasi supervisi.</p>
                <button id="submitBtn" type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-200/70 transition-all duration-300 ease-in-out hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60" {{ $schools->isEmpty() ? 'disabled' : '' }}>
                    @include('layouts.partials.icon', ['name' => 'sparkles', 'classes' => 'h-4 w-4 text-white'])
                    Simpan Profil
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        const nip = document.querySelector('input[name="nip"]');
        if (!nip) {
            return;
        }
        const blocked = ['e', 'E', '+', '-', '.'];
        nip.addEventListener('keydown', (event) => {
            if (blocked.includes(event.key)) {
                event.preventDefault();
            }
        });
        nip.addEventListener('input', function () {
            this.value = this.value.replace(/\D+/g, '');
        });
    })();

    (function () {
        const form = document.getElementById('profileForm');
        const submitBtn = document.getElementById('submitBtn');
        if (!form || !submitBtn) {
            return;
        }

        const teacherType = form.querySelector('[data-teacher-type]');
        const fieldMap = Array.from(form.querySelectorAll('[data-teacher-field]'));

        const syncTeacherFields = () => {
            if (!teacherType) return;
            const current = teacherType.value;
            fieldMap.forEach(wrapper => {
                const type = wrapper.getAttribute('data-teacher-field');
                const input = wrapper.querySelector('[data-required-for]');
                const isActive = current === type;
                wrapper.style.display = isActive ? 'block' : 'none';
                if (input) {
                    if (isActive) {
                        input.setAttribute('required', 'required');
                    } else {
                        input.removeAttribute('required');
                    }
                }
            });
        };

        const evaluate = () => {
            syncTeacherFields();
            const requiredFilled = Array.from(form.querySelectorAll('[required]')).every((element) => {
                if (element.tagName === 'SELECT') {
                    return Boolean(element.value);
                }
                return Boolean(element.value && element.value.trim());
            });
            const valid = requiredFilled && (typeof form.checkValidity === 'function' ? form.checkValidity() : true);
            submitBtn.disabled = !valid;
        };

        form.addEventListener('input', evaluate);
        form.addEventListener('change', evaluate);
        document.addEventListener('DOMContentLoaded', () => {
            syncTeacherFields();
            evaluate();
        });
        setTimeout(() => {
            syncTeacherFields();
            evaluate();
        }, 0);
    })();
</script>
@endsection
