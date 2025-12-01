@props([
  'href' => '#',
  'label' => 'Kembali'
])
<a href="{{ $href }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 shadow-sm shadow-slate-200/60 transition-all duration-300 ease-in-out hover:border-slate-300 hover:bg-slate-50">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-slate-400 transition-all duration-300 ease-in-out">
    <path d="M15 6.5 9 12l6 5.5" />
  </svg>
  <span>{{ $label }}</span>
</a>
