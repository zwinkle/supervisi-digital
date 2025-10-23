@extends('layouts.app', ['title' => 'Daftar'])

@section('content')
<div class="flex min-h-[70vh] items-center justify-center bg-[#F9FAFB] px-4 py-16">
    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white/90 p-8 shadow-lg shadow-slate-200/50">
        <div class="mb-8 space-y-2 text-center">
            <span class="inline-flex items-center gap-2 rounded-xl border border-indigo-100 bg-indigo-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-indigo-500">Daftar</span>
            <h1 class="text-2xl font-semibold text-slate-900">Buat akun institusi baru</h1>
            <p class="text-sm text-slate-500">Isi informasi dasar Anda untuk mengakses dashboard Supervisi Digital.</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600 shadow-sm shadow-rose-100/60">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.attempt') }}" method="post" class="space-y-5">
            @csrf
            <div class="space-y-2">
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Nama lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
            </div>
            <div class="space-y-2">
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Email institusi</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
            </div>
            <div class="space-y-2">
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Kata sandi</label>
                <div class="group relative">
                    <input type="password" name="password" required class="js-pass w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 pr-12 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                    <button type="button" class="js-toggle-pass absolute inset-y-0 right-0 flex items-center justify-center rounded-xl border border-transparent px-3 text-slate-400 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-500" aria-label="Tampilkan atau sembunyikan kata sandi"></button>
                </div>
            </div>
            <div class="space-y-2">
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Konfirmasi kata sandi</label>
                <div class="group relative">
                    <input type="password" name="password_confirmation" required class="js-pass-confirm w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 pr-12 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                    <button type="button" class="js-toggle-pass-confirm absolute inset-y-0 right-0 flex items-center justify-center rounded-xl border border-transparent px-3 text-slate-400 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-500" aria-label="Tampilkan atau sembunyikan konfirmasi kata sandi"></button>
                </div>
            </div>
            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-200/70 transition-all duration-300 ease-in-out hover:opacity-90">Daftar</button>
            @if (Route::has('login'))
                <p class="text-center text-xs text-slate-500">Sudah memiliki akun? <a href="{{ route('login') }}" class="font-semibold text-indigo-500 transition-all duration-300 ease-in-out hover:text-indigo-600">Masuk di sini</a></p>
            @endif
        </form>
    </div>
</div>

<script>
    (function () {
        const setups = [
            { toggle: '.js-toggle-pass', input: '.js-pass' },
            { toggle: '.js-toggle-pass-confirm', input: '.js-pass-confirm' }
        ];
        const icons = {
            show: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5"><path d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12z"/><circle cx="12" cy="12" r="3.25"/></svg>',
            hide: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5"><path d="M3 3l18 18"/><path d="M10.58 5.27A10.9 10.9 0 0 1 12 5.25C18 5.25 21.75 12 21.75 12a18.6 18.6 0 0 1-3.06 3.83"/><path d="M6.27 6.27A18.94 18.94 0 0 0 2.25 12S6 18.75 12 18.75c1.2 0 2.34-.2 3.39-.55"/><circle cx="12" cy="12" r="3.25"/></svg>'
        };
        setups.forEach(({ toggle, input }) => {
            const toggleEl = document.querySelector(toggle);
            const inputEl = document.querySelector(input);
            if (!toggleEl || !inputEl) {
                return;
            }
            const render = () => {
                toggleEl.innerHTML = inputEl.type === 'password' ? icons.show : icons.hide;
            };
            render();
            toggleEl.addEventListener('click', () => {
                inputEl.type = inputEl.type === 'password' ? 'text' : 'password';
                render();
            });
        });
    })();
</script>
@endsection
