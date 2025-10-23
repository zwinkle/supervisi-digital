@extends('layouts.app', ['title' => 'Data Guru'])

@section('content')
<div class="space-y-10">
  <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div class="space-y-3">
      <p class="text-xs font-medium uppercase tracking-[0.3em] text-slate-400">Koordinasi Guru</p>
      <div class="space-y-1">
        <h1 class="text-3xl font-semibold text-slate-900">Data Guru</h1>
        <p class="text-sm text-slate-500">Pantau guru yang berada di bawah pembinaan Anda dan pastikan data sekolah selalu mutakhir.</p>
      </div>
    </div>
    <div class="flex flex-wrap gap-3">
      <a href="{{ route('supervisor.invitations.index') }}" class="inline-flex h-11 items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 shadow-md shadow-slate-200/60 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-600">
        @include('layouts.partials.icon', ['name' => 'sparkles', 'classes' => 'h-4 w-4 text-indigo-500'])
        Kelola Undangan
      </a>
      <a href="{{ route('supervisor.invitations.create') }}" class="inline-flex h-11 items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-200/70 transition-all duration-300 ease-in-out hover:opacity-90">
        @include('layouts.partials.icon', ['name' => 'plus', 'classes' => 'h-4 w-4 text-white'])
        Undang Guru Baru
      </a>
    </div>
  </div>

  @foreach (['success' => 'border-emerald-200 bg-emerald-50 text-emerald-600 shadow-sm shadow-emerald-100/60', 'error' => 'border-rose-200 bg-rose-50 text-rose-600 shadow-sm shadow-rose-100/60'] as $type => $classes)
    @if (session($type))
      <div class="rounded-xl border {{ $classes }} px-5 py-4 text-sm">{{ session($type) }}</div>
    @endif
  @endforeach

  <div class="rounded-xl border border-slate-200 bg-white shadow-md shadow-slate-200/40">
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm shadow-slate-200/40">
      <table class="min-w-full text-sm">
        <thead class="bg-[#F9FAFB] text-xs font-medium uppercase tracking-[0.18em] text-slate-400">
          <tr>
            <th class="px-5 py-3 text-left">Guru</th>
            <th class="px-5 py-3 text-left">Email</th>
            <th class="px-5 py-3 text-left">Sekolah</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-600">
          @forelse ($teachers as $teacher)
            @php($teacherSchools = $teacher->schools()->where('school_user.role', 'teacher')->get())
            <tr class="group transition-all duration-300 ease-in-out hover:bg-slate-50">
              <td class="px-5 py-4 align-top">
                <div class="flex items-start gap-3">
                  <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-sm font-semibold text-indigo-500">{{ Str::upper(Str::substr($teacher->name, 0, 2)) }}</div>
                  <div class="space-y-1">
                    <p class="font-semibold text-slate-900">{{ $teacher->name }}</p>
                    <p class="text-xs text-slate-400">ID: {{ $teacher->id }}</p>
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
  </div>
</div>
@endsection
