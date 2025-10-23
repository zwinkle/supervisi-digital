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
    <a href="{{ route('admin.invitations.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-200/60 transition-all duration-300 ease-in-out hover:opacity-90">
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

  <div class="rounded-xl border border-slate-200 bg-white shadow-md shadow-slate-200/40">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-[#F9FAFB] text-xs font-medium uppercase tracking-[0.18em] text-slate-400">
          <tr>
            <th class="px-5 py-3 text-left">Email</th>
            <th class="px-5 py-3 text-left">Role</th>
            <th class="px-5 py-3 text-left">Sekolah</th>
            <th class="px-5 py-3 text-left">Link</th>
            <th class="px-5 py-3 text-left">Kedaluwarsa</th>
            <th class="px-5 py-3 text-left">Status</th>
            <th class="px-5 py-3 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-600">
          @forelse ($invitations as $inv)
            @php($link = \Illuminate\Support\Facades\URL::temporarySignedRoute('invites.accept.show', $inv->expires_at ?? now()->addDays(7), ['token' => $inv->token]))
            <tr class="group transition-all duration-300 ease-in-out hover:bg-slate-50">
              <td class="px-5 py-4 align-top">
                <div class="space-y-1">
                  <p class="font-semibold text-slate-900">{{ $inv->email }}</p>
                  <p class="text-xs text-slate-400">Token: {{ \Illuminate\Support\Str::limit($inv->token, 12) }}</p>
                </div>
              </td>
              <td class="px-5 py-4 align-top">
                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $inv->role === 'teacher' ? 'Guru' : \Illuminate\Support\Str::title($inv->role) }}</span>
              </td>
              <td class="px-5 py-4 align-top">
                <div class="flex flex-wrap gap-2">
                  @php($names = \App\Models\School::whereIn('id', (array) $inv->school_ids)->pluck('name'))
                  @forelse ($names as $name)
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1 text-xs font-medium text-slate-600">
                      <span class="h-2 w-2 rounded-full bg-indigo-400"></span>
                      {{ $name }}
                    </span>
                  @empty
                    <span class="text-xs text-slate-400">Tidak ditentukan</span>
                  @endforelse
                </div>
              </td>
              <td class="px-5 py-4 align-top">
                <div class="flex items-center gap-2 text-xs font-semibold">
                  <a href="#" class="js-view-link inline-flex items-center gap-1 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-indigo-600 transition-all duration-300 ease-in-out hover:bg-indigo-100" data-link="{{ $link }}">
                    @include('layouts.partials.icon', ['name' => 'eye', 'classes' => 'h-3.5 w-3.5'])
                    Lihat
                  </a>
                  <a href="#" class="js-copy-link inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-slate-500 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-600" data-link="{{ $link }}">
                    @include('layouts.partials.icon', ['name' => 'copy', 'classes' => 'h-3.5 w-3.5'])
                    Salin
                  </a>
                </div>
              </td>
              <td class="px-5 py-4 align-top">
                <p class="text-sm text-slate-500">{{ $inv->expires_at ? $inv->expires_at->format('d M Y H:i') : '—' }}</p>
              </td>
              <td class="px-5 py-4 align-top">
                @if ($inv->used_at)
                  <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-500">Digunakan</span>
                @elseif ($inv->expires_at && now()->greaterThan($inv->expires_at))
                  <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-500">Kedaluwarsa</span>
                @else
                  <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-500">Aktif</span>
                @endif
              </td>
              <td class="px-5 py-4 align-top">
                <div class="flex items-center justify-end gap-2">
                  @if (!$inv->used_at)
                    <form action="{{ route('admin.invitations.resend', $inv) }}" method="post" class="inline js-confirm" data-message="Perbarui kedaluwarsa undangan untuk {{ $inv->email }}?" data-variant="success">
                      @csrf
                      <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-600 transition-all duration-300 ease-in-out hover:bg-emerald-100">@include('layouts.partials.icon', ['name' => 'refresh', 'classes' => 'h-3.5 w-3.5']) Perbarui</button>
                    </form>
                    <form action="{{ route('admin.invitations.revoke', $inv) }}" method="post" class="inline js-confirm" data-message="Cabut undangan ini?" data-variant="danger">
                      @csrf
                      <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600 transition-all duration-300 ease-in-out hover:bg-rose-100">@include('layouts.partials.icon', ['name' => 'shield-alert', 'classes' => 'h-3.5 w-3.5']) Cabut</button>
                    </form>
                  @else
                    <span class="rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-400">Selesai</span>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-5 py-8 text-center text-sm text-slate-400">Belum ada undangan yang dibuat.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($invitations->hasPages())
      <div class="border-t border-slate-100 px-6 py-4">
        {{ $invitations->links('vendor.pagination.tailwind') }}
      </div>
    @endif
  </div>
</div>
@endsection
