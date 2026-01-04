@extends('layouts.app', ['title' => 'Dashboard Pengawas'])

@section('content')
<div class="space-y-10">
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div class="space-y-3">
      <p class="text-xs font-medium uppercase tracking-[0.3em] text-slate-400">Koordinasi Supervisi</p>
      <div class="space-y-1">
        <h1 class="text-3xl font-semibold text-slate-900">Dashboard Pengawas</h1>
        <p class="text-sm text-slate-500">Atur jadwal, pantau evaluasi, dan kelola guru dalam satu tampilan terintegrasi.</p>
      </div>
    </div>
  </div>



  <!-- Statistics Section -->
  <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
    <!-- Upcoming Schedules -->
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-slate-500">Jadwal Mendatang</p>
          <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $upcomingSchedules }}</p>
        </div>
        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 text-indigo-500">
          @include('layouts.partials.icon', ['name' => 'calendar', 'classes' => 'h-6 w-6'])
        </div>
      </div>
      <div class="mt-4 flex items-center text-sm">
        <span class="flex items-center text-slate-500">
          <span class="ml-1">{{ $upcomingTrend }}</span>
        </span>
      </div>
    </div>

    <!-- Completed Schedules -->
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-slate-500">Jadwal Selesai</p>
          <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $completedSchedules }}</p>
        </div>
        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100 text-emerald-500">
          @include('layouts.partials.icon', ['name' => 'check-circle', 'classes' => 'h-6 w-6'])
        </div>
      </div>
      <div class="mt-4 flex items-center text-sm">
        <span class="flex items-center text-emerald-500">
          @include('layouts.partials.icon', ['name' => 'trending-up', 'classes' => 'h-4 w-4'])
          <span class="ml-1">{{ $completedTrend }}</span>
        </span>
      </div>
    </div>

    <!-- Pending Schedules -->
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-slate-500">Jadwal Tertunda</p>
          <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $pendingSchedules }}</p>
        </div>
        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 text-amber-500">
          @include('layouts.partials.icon', ['name' => 'clock', 'classes' => 'h-6 w-6'])
        </div>
      </div>
      <div class="mt-4 flex items-center text-sm">
        <span class="flex items-center text-amber-500">
          @include('layouts.partials.icon', ['name' => 'alert-circle', 'classes' => 'h-4 w-4'])
          <span class="ml-1">{{ $pendingTrend }}</span>
        </span>
      </div>
    </div>

    <!-- Teachers Supervised -->
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-slate-500">Guru yang Diawasi</p>
          <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $teachersSupervised }}</p>
        </div>
        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 text-blue-500">
          @include('layouts.partials.icon', ['name' => 'graduation-cap', 'classes' => 'h-6 w-6'])
        </div>
      </div>
      <div class="mt-4 flex items-center text-sm">
        <span class="flex items-center text-emerald-500">
          @include('layouts.partials.icon', ['name' => 'trending-up', 'classes' => 'h-4 w-4'])
          <span class="ml-1">{{ $teachersTrend }}</span>
        </span>
      </div>
    </div>
  </div>

  <div class="grid gap-6 lg:grid-cols-2">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h2 class="text-base font-semibold text-slate-900">Langkah Selanjutnya</h2>
          <p class="mt-2 text-sm text-slate-500">Pastikan setiap sesi supervisi tersusun rapi dan terpantau progresnya.</p>
        </div>
        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-500">Aktif</span>
      </div>
      <div class="mt-6 space-y-4">
        <div class="flex items-start gap-4 rounded-xl border border-slate-200 bg-white px-4 py-4 text-sm text-slate-600 shadow-sm shadow-slate-200/40 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:bg-indigo-50/60">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-indigo-500">
            @include('layouts.partials.icon', ['name' => 'calendar', 'classes' => 'h-5 w-5'])
          </div>
          <div class="flex-1 space-y-1">
            <p class="font-medium text-slate-900">Periksa agenda supervisi</p>
            <p class="text-xs text-slate-500 md:text-sm">Pastikan jadwal mendatang telah disetujui dan lengkap.</p>
          </div>
          <a href="{{ route('supervisor.schedules') }}" class="text-xs font-semibold text-indigo-500 transition-all duration-300 ease-in-out hover:text-indigo-600">Buka</a>
        </div>
        <div class="flex items-start gap-4 rounded-xl border border-slate-200 bg-white px-4 py-4 text-sm text-slate-600 shadow-sm shadow-slate-200/40 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:bg-indigo-50/60">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-500">
            @include('layouts.partials.icon', ['name' => 'graduation-cap', 'classes' => 'h-5 w-5'])
          </div>
          <div class="flex-1 space-y-1">
            <p class="font-medium text-slate-900">Kembangkan jaringan guru</p>
            <p class="text-xs text-slate-500 md:text-sm">Kirim undangan kepada guru untuk bergabung di sistem supervisi.</p>
          </div>
          <a href="{{ route('supervisor.invitations.create') }}" class="text-xs font-semibold text-indigo-500 transition-all duration-300 ease-in-out hover:text-indigo-600">Undang</a>
        </div>
      </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg">
      <h2 class="text-base font-semibold text-slate-900">Jadwal Supervisi Terbaru</h2>
      <p class="mt-2 text-sm text-slate-500">Daftar jadwal supervisi terkini.</p>
      <div class="mt-6 space-y-4">
        @foreach($recentSchedules as $schedule)
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <p class="font-medium text-slate-900">{{ $schedule['title'] }}</p>
              <p class="text-sm text-slate-500">{{ $schedule['teacher'] }}</p>
              <div class="mt-2 flex items-center gap-2 text-xs">
                <span class="rounded-full bg-indigo-100 px-2 py-1 text-indigo-800">{{ $schedule['date'] }}</span>
              </div>
            </div>
            <span class="rounded-full px-2 py-1 text-xs font-medium {{ $schedule['status_class'] }}">{{ $schedule['status'] }}</span>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endsection