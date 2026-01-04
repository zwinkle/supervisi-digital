@props([
    'icon' => 'folder-open',
    'title' => 'Belum ada data',
    'message' => 'Data yang Anda cari tidak ditemukan atau belum tersedia.',
    'action' => null,
    'actionRoute' => null,
    'actionLabel' => null,
])

<div class="flex flex-col items-center justify-center rounded-xl bg-white p-12 text-center" {{ $attributes }}>
    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-50 text-slate-400">
        @include('layouts.partials.icon', ['name' => $icon, 'classes' => 'h-8 w-8'])
    </div>
    <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ $title }}</h3>
    <p class="mt-2 max-w-sm text-sm text-slate-500">{{ $message }}</p>
    
    @if($action && $actionRoute && $actionLabel)
        <a href="{{ $actionRoute }}" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-indigo-200/50 transition-all duration-300 ease-in-out hover:bg-indigo-700">
            @include('layouts.partials.icon', ['name' => 'plus', 'classes' => 'h-4 w-4'])
            {{ $actionLabel }}
        </a>
    @elseif($action)
        <div class="mt-6">
            {{ $action }}
        </div>
    @endif
</div>
