<div class="space-y-4 md:hidden" id="users-mobile-list">
  @forelse ($users as $u)
    @php
      $roles = [];
      if ($u->is_admin) {
        $roles[] = 'Admin';
      }
      $supervisorSchools = $u->schools->where('pivot.role', 'supervisor');
      if ($supervisorSchools->isNotEmpty()) {
        $roles[] = 'Supervisor';
      }
      $teacherSchools = $u->schools->where('pivot.role', 'teacher');
      if ($teacherSchools->isNotEmpty()) {
        $roles[] = 'Guru';
      }
      $role = $roles ? implode(', ', $roles) : 'Belum ada peran';
    @endphp
    <article class="space-y-4 rounded-2xl border border-slate-200 bg-[#F9FAFB] p-5 shadow-sm shadow-slate-200/60">
      <div class="flex items-start gap-3">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-base font-semibold text-indigo-500">{{ Str::upper(Str::substr($u->name, 0, 2)) }}</div>
        <div class="space-y-1 text-sm text-slate-500">
          <p class="text-base font-semibold text-slate-900">{{ $u->name }}</p>
          <p class="text-xs text-slate-400">{{ $u->email }}</p>
        </div>
      </div>
      <div class="space-y-2 text-xs text-slate-500">
        <div class="flex flex-wrap items-center gap-2">
          <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 font-semibold text-slate-600">{{ $role }}</span>
          <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 font-semibold text-slate-500">
            @include('layouts.partials.icon', ['name' => 'shield', 'classes' => 'h-3.5 w-3.5 text-slate-400'])
            {{ $u->is_admin ? 'Admin Sistem' : 'Pengguna' }}
          </span>
          <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 font-semibold text-slate-500">
            @include('layouts.partials.icon', ['name' => 'at-sign', 'classes' => 'h-3.5 w-3.5 text-slate-400'])
            {{ $u->google_email ? 'Google Terhubung' : 'Google belum terhubung' }}
          </span>
          @if($u->teacher_type_label || $u->teacher_detail_label)
            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 font-semibold text-slate-500">
              @include('layouts.partials.icon', ['name' => 'sparkles', 'classes' => 'h-3.5 w-3.5 text-slate-400'])
              {{ $u->teacher_type_label }}
            </span>
            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 font-semibold text-slate-500">
              @include('layouts.partials.icon', ['name' => 'bookmark', 'classes' => 'h-3.5 w-3.5 text-slate-400'])
              {{ $u->teacher_detail_label ?? '—' }}
            </span>
          @endif
        </div>
        <div class="space-y-1">
          <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Sekolah</p>
          <div class="flex flex-wrap gap-2">
            @forelse ($u->schools as $sch)
              <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-600">
                <span class="h-2 w-2 rounded-full bg-indigo-400"></span>
                {{ $sch->name }}
              </span>
            @empty
              <span class="text-xs text-slate-400">Belum terhubung ke sekolah</span>
            @endforelse
          </div>
        </div>
      </div>
      <div class="flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500">
        <div class="space-y-1">
          <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Google</p>
          <p>{{ $u->google_email ?? 'Belum terhubung' }}</p>
        </div>
        <div class="flex w-full flex-wrap justify-end gap-2 sm:w-auto">
          <a href="{{ route('admin.users.edit', $u) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 font-semibold text-slate-600 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-600">
            @include('layouts.partials.icon', ['name' => 'pencil', 'classes' => 'h-3.5 w-3.5'])
            Edit
          </a>
          <form action="{{ route('admin.users.deactivate', $u) }}" method="post" class="inline js-confirm" data-message="Nonaktifkan pengguna ini?" data-variant="warning">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 font-semibold text-amber-600 transition-all duration-300 ease-in-out hover:bg-amber-100">
              @include('layouts.partials.icon', ['name' => 'shield-alert', 'classes' => 'h-3.5 w-3.5'])
              Nonaktif
            </button>
          </form>
          <form action="{{ route('admin.users.destroy', $u) }}" method="post" class="inline js-confirm" data-message="Hapus pengguna ini secara permanen? Tindakan ini tidak dapat dibatalkan." data-variant="danger">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 font-semibold text-rose-600 transition-all duration-300 ease-in-out hover:bg-rose-100">
              @include('layouts.partials.icon', ['name' => 'trash', 'classes' => 'h-3.5 w-3.5'])
              Hapus
            </button>
          </form>
        </div>
      </div>
    </article>
  @empty
    <div class="rounded-2xl border border-slate-200 bg-[#F9FAFB] px-4 py-5 text-center text-sm text-slate-400">Tidak ada data pengguna ditemukan.</div>
  @endforelse
</div>

<div class="hidden mt-6 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm shadow-slate-200/40 md:block" id="users-table">
  <table class="min-w-full text-sm">
    <thead class="bg-[#F9FAFB] text-xs font-medium uppercase tracking-[0.18em] text-slate-400">
      <tr>
        <th class="px-5 py-3 text-left">Pengguna</th>
        <th class="px-5 py-3 text-left">Peran</th>
        <th class="px-5 py-3 text-left">Sekolah</th>
        <th class="px-5 py-3 text-left">Admin</th>
        <th class="px-5 py-3 text-left">Google</th>
        <th class="px-5 py-3 text-right">Aksi</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100 text-slate-600">
      @forelse ($users as $u)
        @php
          $roles = [];
          if ($u->is_admin) {
            $roles[] = 'Admin';
          }
          $supervisorSchools = $u->schools->where('pivot.role', 'supervisor');
          if ($supervisorSchools->isNotEmpty()) {
            $roles[] = 'Supervisor';
          }
          $teacherSchools = $u->schools->where('pivot.role', 'teacher');
          if ($teacherSchools->isNotEmpty()) {
            $roles[] = 'Guru';
          }
          $role = $roles ? implode(', ', $roles) : 'Belum ada peran';
        @endphp
        <tr class="group transition-all duration-300 ease-in-out hover:bg-slate-50">
          <td class="px-5 py-4 align-top">
            <div class="flex items-start gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-sm font-semibold text-indigo-500">{{ Str::upper(Str::substr($u->name, 0, 2)) }}</div>
              <div class="space-y-1">
                <p class="font-semibold text-slate-900">{{ $u->name }}</p>
                <p class="text-xs text-slate-400">{{ $u->email }}</p>
              </div>
            </div>
          </td>
          <td class="px-5 py-4 align-top">
            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $role }}</span>
            @if($u->teacher_type_label || $u->teacher_detail_label)
              <div class="mt-2 space-y-1 text-xs text-slate-500">
                <div><span class="font-semibold text-slate-600">Jenis:</span> {{ $u->teacher_type_label }}</div>
                <div><span class="font-semibold text-slate-600">Detail:</span> {{ $u->teacher_detail_label ?? '—' }}</div>
              </div>
            @endif
          </td>
          <td class="px-5 py-4 align-top">
            <div class="space-y-1 text-xs text-slate-500">
              @forelse ($u->schools as $sch)
                <p>{{ $sch->name }}</p>
              @empty
                <span class="text-xs text-slate-400">Belum terhubung</span>
              @endforelse
            </div>
          </td>
          <td class="px-5 py-4 align-top">
            <span class="text-xs font-semibold {{ $u->is_admin ? 'text-emerald-500' : 'text-slate-400' }}">{{ $u->is_admin ? 'Ya' : 'Tidak' }}</span>
          </td>
          <td class="px-5 py-4 align-top">
            <span class="text-xs text-slate-500">{{ $u->google_email ?? 'Belum terhubung' }}</span>
          </td>
          <td class="px-5 py-4 align-top">
            <div class="flex items-center justify-end gap-2 pr-1">
              <a href="{{ route('admin.users.edit', $u) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-600">
                @include('layouts.partials.icon', ['name' => 'pencil', 'classes' => 'h-3.5 w-3.5'])
                Edit
              </a>
              <form action="{{ route('admin.users.deactivate', $u) }}" method="post" class="inline js-confirm" data-message="Nonaktifkan pengguna ini?" data-variant="warning">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-600 transition-all duration-300 ease-in-out hover:bg-amber-100">@include('layouts.partials.icon', ['name' => 'shield-alert', 'classes' => 'h-3.5 w-3.5']) Nonaktif</button>
              </form>
              <form action="{{ route('admin.users.destroy', $u) }}" method="post" class="inline js-confirm" data-message="Hapus pengguna ini secara permanen? Tindakan ini tidak dapat dibatalkan." data-variant="danger">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600 transition-all duration-300 ease-in-out hover:bg-rose-100">@include('layouts.partials.icon', ['name' => 'trash', 'classes' => 'h-3.5 w-3.5']) Hapus</button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="px-5 py-8 text-center text-sm text-slate-400">Tidak ada data pengguna ditemukan.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
