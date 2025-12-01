<div class="space-y-4 md:hidden" id="schools-mobile-list">
  @forelse ($schools as $school)
    <article class="space-y-4 rounded-2xl border border-slate-200 bg-[#F9FAFB] p-5 shadow-sm shadow-slate-200/60">
      <div class="space-y-1">
        <p class="text-base font-semibold text-slate-900">{{ $school->name }}</p>
        <p class="text-xs text-slate-400">Kode: {{ $school->npsn ?? 'Belum tersedia' }}</p>
      </div>
      <div class="space-y-1 text-xs text-slate-500">
        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Alamat</p>
        <p class="text-sm text-slate-600">{{ $school->address ?? 'Alamat belum diisi' }}</p>
      </div>
      <div class="flex flex-wrap justify-end gap-2">
        <a href="{{ route('admin.schools.edit', $school) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-600">
          @include('layouts.partials.icon', ['name' => 'pencil', 'classes' => 'h-3.5 w-3.5'])
          Edit
        </a>
        <form action="{{ route('admin.schools.destroy', $school) }}" method="post" class="inline js-confirm" data-message="Hapus sekolah ini? Tindakan tidak dapat dibatalkan." data-variant="danger">
          @csrf
          @method('DELETE')
          <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600 transition-all duration-300 ease-in-out hover:bg-rose-100">
            @include('layouts.partials.icon', ['name' => 'trash', 'classes' => 'h-3.5 w-3.5'])
            Hapus
          </button>
        </form>
      </div>
    </article>
  @empty
    <div class="rounded-2xl border border-slate-200 bg-[#F9FAFB] px-4 py-5 text-center text-sm text-slate-400">Belum ada data sekolah.</div>
  @endforelse
</div>

<div class="hidden mt-6 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm shadow-slate-200/40 md:block" id="schools-table">
  <table class="min-w-full text-sm">
    <thead class="bg-[#F9FAFB] text-xs font-medium uppercase tracking-[0.18em] text-slate-400">
      <tr>
        <th class="px-5 py-3 text-left">Sekolah</th>
        <th class="px-5 py-3 text-left">Alamat</th>
        <th class="px-5 py-3 text-right">Aksi</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100 text-slate-600">
      @forelse ($schools as $school)
        <tr class="group transition-all duration-300 ease-in-out hover:bg-slate-50">
          <td class="px-5 py-4 align-top">
            <div class="space-y-1">
              <p class="font-semibold text-slate-900">{{ $school->name }}</p>
              <p class="text-xs text-slate-400">Kode: {{ $school->npsn ?? 'Belum tersedia' }}</p>
            </div>
          </td>
          <td class="px-5 py-4 align-top">
            <p class="text-sm text-slate-500">{{ $school->address ?? 'Alamat belum diisi' }}</p>
          </td>
          <td class="px-5 py-4 align-top">
            <div class="flex items-center justify-end gap-2 pr-1">
              <a href="{{ route('admin.schools.edit', $school) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-600">
                @include('layouts.partials.icon', ['name' => 'pencil', 'classes' => 'h-3.5 w-3.5'])
                Edit
              </a>
              <form action="{{ route('admin.schools.destroy', $school) }}" method="post" class="inline js-confirm" data-message="Hapus sekolah ini? Tindakan tidak dapat dibatalkan." data-variant="danger">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600 transition-all duration-300 ease-in-out hover:bg-rose-100">
                  @include('layouts.partials.icon', ['name' => 'trash', 'classes' => 'h-3.5 w-3.5'])
                  Hapus
                </button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="3" class="px-5 py-8 text-center text-sm text-slate-400">Belum ada data sekolah.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
