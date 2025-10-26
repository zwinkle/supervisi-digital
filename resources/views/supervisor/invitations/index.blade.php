@extends('layouts.app', ['title' => 'Undangan Guru'])

@section('content')
<div class="space-y-10">
  <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div class="space-y-3">
      <p class="text-xs font-medium uppercase tracking-[0.3em] text-slate-400">Manajemen Undangan</p>
      <div class="space-y-1">
        <h1 class="text-3xl font-semibold text-slate-900">Undangan Guru</h1>
        <p class="text-sm text-slate-500">Kirim undangan kepada guru dan pantau status penerimaan secara real time.</p>
      </div>
    </div>
    <a href="{{ route('supervisor.invitations.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-200/60 transition-all duration-300 ease-in-out hover:opacity-90">
      @include('layouts.partials.icon', ['name' => 'sparkles', 'classes' => 'h-4 w-4 text-white'])
      Undang Guru
    </a>
  </div>

  @foreach ([
      'success' => 'border-emerald-200 bg-emerald-50 text-emerald-600 shadow-sm shadow-emerald-100/60',
      'warning' => 'border-amber-200 bg-amber-50 text-amber-600 shadow-sm shadow-amber-100/60',
      'error' => 'border-rose-200 bg-rose-50 text-rose-600 shadow-sm shadow-rose-100/60'
    ] as $type => $classes)
    @if (session($type))
      <div class="rounded-xl border {{ $classes }} px-5 py-4 text-sm">{{ session($type) }}</div>
    @endif
  @endforeach

  @php($invitationEntries = collect($invitations instanceof \Illuminate\Contracts\Pagination\Paginator ? $invitations->items() : $invitations)->map(function($invitation){
      return [
          'model' => $invitation,
          'schools' => \App\Models\School::whereIn('id', (array) $invitation->school_ids)->pluck('name'),
          'link' => \Illuminate\Support\Facades\URL::temporarySignedRoute('invites.accept.show', $invitation->expires_at ?? now()->addDays(7), ['token' => $invitation->token]),
      ];
  }))

  <div class="rounded-xl border border-slate-200 bg-white shadow-md shadow-slate-200/40">
    <div class="space-y-4 px-5 py-6 md:hidden">
      @forelse ($invitationEntries as $entry)
        @php($invitation = $entry['model'])
        @php($schools = $entry['schools'])
        @php($link = $entry['link'])
        <article class="space-y-4 rounded-2xl border border-slate-200 bg-[#F9FAFB] p-5 shadow-sm shadow-slate-200/60">
          <div class="space-y-1">
            <p class="text-base font-semibold text-slate-900 break-all">{{ $invitation->email }}</p>
            <p class="text-xs text-slate-400">Token: {{ Str::limit($invitation->token, 10) }}</p>
          </div>
          <div class="space-y-3 text-xs text-slate-500">
            @php($typeLabel = \App\Support\TeacherOptions::teacherTypes()[$invitation->teacher_type] ?? null)
            @php($detail = $invitation->teacher_type === 'subject' ? ($invitation->teacher_subject ?? null) : ($invitation->teacher_type === 'class' ? ($invitation->teacher_class ? 'Kelas '.$invitation->teacher_class : null) : null))
            <div class="space-y-1">
              <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Jenis Guru</p>
              <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 font-medium text-slate-600">
                  <span class="h-2 w-2 rounded-full bg-indigo-400"></span>
                  {{ $typeLabel ?? '—' }}
                </span>
                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 font-medium text-slate-600">
                  <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                  {{ $detail ?? '—' }}
                </span>
              </div>
            </div>
            <div class="space-y-1">
              <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Sekolah</p>
              <div class="flex flex-wrap gap-2">
                @forelse ($schools as $school)
                  <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 font-medium text-slate-600">
                    <span class="h-2 w-2 rounded-full bg-indigo-400"></span>
                    {{ $school }}
                  </span>
                @empty
                  <span class="text-xs text-slate-400">Belum ditentukan</span>
                @endforelse
              </div>
            </div>
            <div class="space-y-1">
              <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Kedaluwarsa</p>
              <p class="font-semibold text-slate-600">{{ $invitation->expires_at ? $invitation->expires_at->format('d M Y H:i') : '—' }}</p>
            </div>
            <div class="space-y-1">
              <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Status</p>
              @if ($invitation->used_at)
                <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 font-semibold text-emerald-500">Digunakan</span>
              @elseif ($invitation->expires_at && now()->greaterThan($invitation->expires_at))
                <span class="inline-flex items-center rounded-full bg-rose-50 px-3 py-1 font-semibold text-rose-500">Kedaluwarsa</span>
              @else
                <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 font-semibold text-indigo-500">Aktif</span>
              @endif
            </div>
          </div>
          <div class="space-y-2">
            <div class="flex flex-wrap gap-2">
              <a href="#" class="js-view-link inline-flex items-center gap-1 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-600 transition-all duration-300 ease-in-out hover:bg-indigo-100" data-link="{{ $link }}">
                @include('layouts.partials.icon', ['name' => 'eye', 'classes' => 'h-3.5 w-3.5'])
                Lihat link
              </a>
              <a href="#" class="js-copy-link inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-500 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-600" data-link="{{ $link }}">
                @include('layouts.partials.icon', ['name' => 'copy', 'classes' => 'h-3.5 w-3.5'])
                Salin
              </a>
            </div>
            <div class="flex flex-wrap gap-2">
              @if (!$invitation->used_at)
                <form action="{{ route('supervisor.invitations.resend', $invitation) }}" method="post" class="inline js-confirm" data-message="Perbarui kedaluwarsa undangan untuk {{ $invitation->email }}?" data-variant="success">
                  @csrf
                  <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-600 transition-all duration-300 ease-in-out hover:bg-emerald-100">
                    @include('layouts.partials.icon', ['name' => 'refresh', 'classes' => 'h-3.5 w-3.5'])
                    Perbarui
                  </button>
                </form>
                <form action="{{ route('supervisor.invitations.revoke', $invitation) }}" method="post" class="inline js-confirm" data-message="Cabut undangan ini?" data-variant="danger">
                  @csrf
                  <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600 transition-all duration-300 ease-in-out hover:bg-rose-100">
                    @include('layouts.partials.icon', ['name' => 'shield-alert', 'classes' => 'h-3.5 w-3.5'])
                    Cabut
                  </button>
                </form>
              @else
                <span class="inline-flex rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-400">Selesai</span>
              @endif
            </div>
          </div>
        </article>
      @empty
        <div class="rounded-2xl border border-slate-200 bg-[#F9FAFB] px-4 py-5 text-center text-sm text-slate-400">Belum ada undangan yang dibuat.</div>
      @endforelse
    </div>

    <div class="hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm shadow-slate-200/40 md:block">
      <table class="min-w-full text-sm">
        <thead class="bg-[#F9FAFB] text-xs font-medium uppercase tracking-[0.18em] text-slate-400">
          <tr>
            <th class="px-5 py-3 text-left">Email</th>
            <th class="px-5 py-3 text-left">Sekolah</th>
            <th class="px-5 py-3 text-left">Link Undangan</th>
            <th class="px-5 py-3 text-left">Kedaluwarsa</th>
            <th class="px-5 py-3 text-left">Status</th>
            <th class="px-5 py-3 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-600">
      @forelse ($invitationEntries as $entry)
        @php($invitation = $entry['model'])
        @php($schools = $entry['schools'])
        @php($link = $entry['link'])
            <tr class="group transition-all duration-300 ease-in-out hover:bg-slate-50">
              <td class="px-5 py-4 align-top">
                <div class="space-y-1">
                  <div class="font-semibold text-slate-900" title="{{ $invitation->email }}" style="display:block; max-width:240px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $invitation->email }}</div>
                  <p class="text-xs text-slate-400">Token: {{ Str::limit($invitation->token, 10) }}</p>
                </div>
                @php($typeLabel = \App\Support\TeacherOptions::teacherTypes()[$invitation->teacher_type] ?? null)
                @php($detail = $invitation->teacher_type === 'subject' ? ($invitation->teacher_subject ?? null) : ($invitation->teacher_type === 'class' ? ($invitation->teacher_class ? 'Kelas '.$invitation->teacher_class : null) : null))
                <div class="mt-2 space-y-1 text-xs text-slate-500">
                  <div><span class="font-semibold text-slate-600">Jenis:</span> {{ $typeLabel ?? '—' }}</div>
                  <div><span class="font-semibold text-slate-600">Detail:</span> {{ $detail ?? '—' }}</div>
                </div>
              </td>
              <td class="px-5 py-4 align-top">
                <div class="flex flex-wrap gap-2">
                  @forelse ($schools as $school)
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1 text-xs font-medium text-slate-600">
                      <span class="h-2 w-2 rounded-full bg-indigo-400"></span>
                      {{ $school }}
                    </span>
                  @empty
                    <span class="text-xs text-slate-400">Belum ditentukan</span>
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
                <p class="text-sm text-slate-500">{{ $invitation->expires_at ? $invitation->expires_at->format('d M Y H:i') : '—' }}</p>
              </td>
              <td class="px-5 py-4 align-top">
                @if ($invitation->used_at)
                  <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-500">Digunakan</span>
                @elseif ($invitation->expires_at && now()->greaterThan($invitation->expires_at))
                  <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-500">Kedaluwarsa</span>
                @else
                  <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-500">Aktif</span>
                @endif
              </td>
              <td class="px-5 py-4 align-top">
                <div class="flex items-center justify-end gap-2">
                  @if (!$invitation->used_at)
                    <form action="{{ route('supervisor.invitations.resend', $invitation) }}" method="post" class="inline js-confirm" data-message="Perbarui kedaluwarsa undangan untuk {{ $invitation->email }}?" data-variant="success">
                      @csrf
                      <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-600 transition-all duration-300 ease-in-out hover:bg-emerald-100">
                        @include('layouts.partials.icon', ['name' => 'refresh', 'classes' => 'h-3.5 w-3.5'])
                        Perbarui
                      </button>
                    </form>
                    <form action="{{ route('supervisor.invitations.revoke', $invitation) }}" method="post" class="inline js-confirm" data-message="Cabut undangan ini?" data-variant="danger">
                      @csrf
                      <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600 transition-all duration-300 ease-in-out hover:bg-rose-100">
                        @include('layouts.partials.icon', ['name' => 'shield-alert', 'classes' => 'h-3.5 w-3.5'])
                        Cabut
                      </button>
                    </form>
                  @else
                    <span class="rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-400">Selesai</span>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-5 py-8 text-center text-sm text-slate-400">Belum ada undangan yang dibuat.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if (method_exists($invitations, 'hasPages') && $invitations->hasPages())
      <div class="px-6 py-4">
        {{ $invitations->links('vendor.pagination.tailwind') }}
      </div>
    @endif
  </div>
</div>
@endsection
