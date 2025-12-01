@php($teacherItems = $teachers->map(function ($teacher) {
    return [
        'teacher' => $teacher,
        'schools' => $teacher->schools->filter(function ($school) {
            return optional($school->pivot)->role === 'teacher';
        }),
    ];
}))

<div class="space-y-4 md:hidden" id="supervisor-users-mobile">
  @forelse ($teacherItems as $item)
    @php($teacher = $item['teacher'])
    @php($teacherSchools = $item['schools'])
    <article class="space-y-4 rounded-2xl border border-slate-200 bg-[#F9FAFB] p-5 shadow-sm shadow-slate-200/60">
      <div class="flex items-start gap-3">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-base font-semibold text-indigo-500">{{ Str::upper(Str::substr($teacher->name, 0, 2)) }}</div>
        <div class="space-y-1 text-sm text-slate-500">
          <p class="text-base font-semibold text-slate-900">{{ $teacher->name }}</p>
          <p class="text-xs text-slate-400">{{ $teacher->email }}</p>
        </div>
      </div>
      <div class="space-y-1">
        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Sekolah</p>
        <div class="flex flex-wrap gap-2">
          @forelse ($teacherSchools as $school)
            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-600">
              <span class="h-2 w-2 rounded-full bg-indigo-400"></span>
              {{ $school->name }}
            </span>
          @empty
            <span class="text-xs text-slate-400">Belum terhubung ke sekolah</span>
          @endforelse
        </div>
      </div>
      <div class="space-y-1 text-xs text-slate-500">
        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Jenis Guru</p>
        <div class="flex flex-wrap gap-2">
          <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 font-medium text-slate-600">
            <span class="h-2 w-2 rounded-full bg-indigo-400"></span>
            {{ $teacher->teacher_type_label ?? '—' }}
          </span>
          <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 font-medium text-slate-600">
            <span class="h-2 w-2 rounded-full bg-slate-300"></span>
            {{ $teacher->teacher_detail_label ?? '—' }}
          </span>
        </div>
      </div>
    </article>
  @empty
    <div class="rounded-2xl border border-slate-200 bg-[#F9FAFB] px-4 py-5 text-center text-sm text-slate-400">Belum ada guru pada sekolah di bawah pengawasan Anda.</div>
  @endforelse
</div>

<div class="hidden mt-6 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm shadow-slate-200/40 md:block" id="supervisor-users-table">
  <table class="min-w-full text-sm">
    <thead class="bg-[#F9FAFB] text-xs font-medium uppercase tracking-[0.18em] text-slate-400">
      <tr>
        <th class="px-5 py-3 text-left">Guru</th>
        <th class="px-5 py-3 text-left">Email</th>
        <th class="px-5 py-3 text-left">Sekolah</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100 text-slate-600">
      @forelse ($teacherItems as $item)
        @php($teacher = $item['teacher'])
        @php($teacherSchools = $item['schools'])
        <tr class="group transition-all duration-300 ease-in-out hover:bg-slate-50">
          <td class="px-5 py-4 align-top">
            <div class="flex items-start gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-sm font-semibold text-indigo-500">{{ Str::upper(Str::substr($teacher->name, 0, 2)) }}</div>
              <div class="space-y-1">
                <p class="font-semibold text-slate-900">{{ $teacher->name }}</p>
                <p class="text-xs text-slate-500"><span class="font-semibold text-slate-600">Jenis:</span> {{ $teacher->teacher_type_label ?? '—' }}</p>
                <p class="text-xs text-slate-500"><span class="font-semibold text-slate-600">Detail:</span> {{ $teacher->teacher_detail_label ?? '—' }}</p>
              </div>
            </div>
          </td>
          <td class="px-5 py-4 align-top">
            <span class="text-sm text-slate-600">{{ $teacher->email }}</span>
          </td>
          <td class="px-5 py-4 align-top">
            <div class="flex flex-wrap gap-2">
              @forelse ($teacherSchools as $school)
                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1 text-xs font-medium text-slate-600">
                  <span class="h-2 w-2 rounded-full bg-indigo-400"></span>
                  {{ $school->name }}
                </span>
              @empty
                <span class="text-xs text-slate-400">Belum terhubung ke sekolah</span>
              @endforelse
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="3" class="px-5 py-8 text-center text-sm text-slate-400">Belum ada guru pada sekolah di bawah pengawasan Anda.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
