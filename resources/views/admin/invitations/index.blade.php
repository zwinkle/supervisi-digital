@extends('layouts.app', ['title' => 'Daftar Undangan'])

@section('content')
<div class="space-y-10">
  <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div class="space-y-3">
      <p class="text-xs font-medium uppercase tracking-[0.3em] text-slate-400">Manajemen Akses</p>
      <div class="space-y-1">
        <h1 class="text-3xl font-semibold text-slate-900">Undangan</h1>
        <p class="text-sm text-slate-500">Kelola undangan masuk untuk memastikan onboarding pengguna berjalan cepat.</p>
      </div>
    </div>
    <a href="{{ route('admin.invitations.create') }}" class="inline-flex h-11 items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-200/60 transition-all duration-300 ease-in-out hover:opacity-90">
      @include('layouts.partials.icon', ['name' => 'sparkles', 'classes' => 'h-4 w-4 text-white'])
      Buat Undangan
    </a>
  </div>

  @foreach ([
      'success' => 'border-emerald-200 bg-emerald-50 text-emerald-600 shadow-sm shadow-emerald-100/60',
      'warning' => 'border-amber-200 bg-amber-50 text-amber-600 shadow-sm shadow-amber-100/60',
      'error' => 'border-rose-200 bg-rose-50 text-rose-600 shadow-sm shadow-rose-100/60'
    ] as $type => $classes)
    @if (session($type))
      <div class="rounded-xl border {{ $classes }} p-5 text-sm">{{ session($type) }}</div>
    @endif
  @endforeach

  @php($invitationEntries = collect($invitations instanceof \Illuminate\Contracts\Pagination\Paginator ? $invitations->items() : $invitations)->map(function($inv){
      return [
          'model' => $inv,
          'schools' => \App\Models\School::whereIn('id', (array) $inv->school_ids)->pluck('name'),
          'link' => \Illuminate\Support\Facades\URL::temporarySignedRoute('invites.accept.show', $inv->expires_at ?? now()->addDays(7), ['token' => $inv->token]),
      ];
  }))

  <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-200/40" id="invitations-container">
    <form id="invitations-search-form" class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <div class="flex w-full flex-col gap-2 md:flex-row md:gap-3">
        <div class="relative w-full md:max-w-sm">
          <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
            @include('layouts.partials.icon', ['name' => 'search', 'classes' => 'h-4 w-4'])
          </span>
          <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari email undangan" class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-600 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" autocomplete="off">
        </div>
        <select name="status" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200 md:w-48">
          <option value="all" @selected(request('status', 'all') === 'all')>Semua Status</option>
          <option value="active" @selected(request('status') === 'active')>Aktif</option>
          <option value="used" @selected(request('status') === 'used')>Digunakan</option>
          <option value="expired" @selected(request('status') === 'expired')>Kedaluwarsa</option>
        </select>
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">Baris:</span>
            <select name="per_page" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <option value="10" @selected(request('per_page') == 10)>10</option>
                <option value="20" @selected(request('per_page', 20) == 20)>20</option>
            </select>
        </div>
      </div>
      <div class="text-xs text-slate-400">Pencarian diperbarui otomatis saat Anda mengetik.</div>
    </form>

    <div id="invitations-results" class="mt-6 w-full min-w-0">
      @include('admin.invitations.partials.results')
    </div>
  </div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('invitations-search-form');
    const container = document.getElementById('invitations-container');
    const results = document.getElementById('invitations-results');
    if (!form || !container || !results) {
      return;
    }

    let controller = null;
    let debounceTimer = null;

    const submitAjax = function () {
      const formData = new FormData(form);
      const params = new URLSearchParams();
      for (const [key, value] of formData.entries()) {
        if (value) {
          params.append(key, value);
        }
      }

      if (controller) {
        controller.abort();
      }
      controller = new AbortController();

      container.classList.add('opacity-60');

      fetch(`{{ route('admin.invitations.index') }}` + (params.toString() ? `?${params.toString()}` : ''), {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        },
        signal: controller.signal,
      })
        .then(function (response) {
          if (!response.ok) {
            throw new Error('Gagal memuat data');
          }
          return response.json();
        })
        .then(function (data) {
          if (data.html) {
            results.innerHTML = data.html;
          }
        })
        .catch(function (error) {
          if (error.name === 'AbortError') {
            return;
          }
          console.error(error);
        })
        .finally(function () {
          container.classList.remove('opacity-60');
        });
    };

    const scheduleSubmit = function () {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(submitAjax, 600);
    };

    form.addEventListener('input', scheduleSubmit);
    form.addEventListener('change', scheduleSubmit);
  });
</script>
@endpush
@endsection
