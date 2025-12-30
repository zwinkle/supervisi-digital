@extends('layouts.guest', ['title' => 'Masuk'])

@section('content')
<div class="flex min-h-[70vh] items-center justify-center bg-[#F9FAFB] px-4 py-16">
    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white/90 p-8 shadow-lg shadow-slate-200/50">
        <div class="mb-8 space-y-2 text-center">
            <span class="inline-flex items-center gap-2 rounded-xl border border-indigo-100 bg-indigo-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-indigo-500">Masuk</span>
            <h1 class="text-2xl font-semibold text-slate-900">Selamat datang kembali</h1>
            <p class="text-sm text-slate-500">Gunakan kredensial institusi atau akun Google yang telah ditautkan.</p>
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

        <form action="{{ route('login.attempt') }}" method="post" class="space-y-5">
            @csrf
            <div class="space-y-2">
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
            </div>
            <div class="space-y-2">
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Kata sandi</label>
                <div class="group relative">
                    <input type="password" name="password" required autocomplete="current-password" class="js-pass w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 pr-12 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                    <button type="button" class="js-toggle-pass absolute inset-y-0 right-0 flex items-center justify-center rounded-xl border border-transparent px-3 text-slate-400 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-500" aria-label="Tampilkan atau sembunyikan kata sandi"></button>
                </div>
            </div>
            <div class="flex items-center justify-between text-sm text-slate-500">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-indigo-500 focus:ring-indigo-300" />
                    Ingat saya
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="font-semibold text-indigo-500 transition-all duration-300 ease-in-out hover:text-indigo-600">Lupa kata sandi?</a>
                @endif
            </div>
            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-200/70 transition-all duration-300 ease-in-out hover:opacity-90">Masuk</button>
            <div class="flex items-center gap-3 text-xs text-slate-400">
                <div class="h-px flex-1 bg-slate-200"></div>
                <span>atau</span>
                <div class="h-px flex-1 bg-slate-200"></div>
            </div>
            <a href="{{ route('google.redirect') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm shadow-slate-200/70 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-600">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" class="h-4 w-4" aria-hidden="true"><path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12 s5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C33.14,6.053,28.791,4,24,4C12.955,4,4,12.955,4,24 s8.955,20,20,20s20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/><path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,16.108,18.961,14,24,14c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657 C33.14,6.053,28.791,4,24,4C16.318,4,9.706,8.337,6.306,14.691z"/><path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36 c-5.202,0-9.619-3.317-11.274-7.958l-6.5,5.02C9.595,39.556,16.227,44,24,44z"/><path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.175-3.897,5.571 c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.998,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/></svg>
                Masuk dengan Google
            </a>
            <p class="text-xs text-slate-500">Fitur Google tersedia untuk akun yang telah menautkan email Google melalui menu Profil.</p>
        </form>
    </div>
</div>

<script>
    (function () {
        const toggle = document.querySelector('.js-toggle-pass');
        const input = document.querySelector('.js-pass');
        const icons = {
            show: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5"><path d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12z"/><circle cx="12" cy="12" r="3.25"/></svg>',
            hide: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5"><path d="M3 3l18 18"/><path d="M10.58 5.27A10.9 10.9 0 0 1 12 5.25c6 0 9.75 6.75 9.75 6.75a18.6 18.6 0 0 1-3.06 3.83"/><path d="M6.27 6.27A18.94 18.94 0 0 0 2.25 12S6 18.75 12 18.75c1.2 0 2.34-.2 3.39-.55"/><circle cx="12" cy="12" r="3.25"/></svg>'
        };
        if (!toggle || !input) {
            return;
        }
        const render = () => {
            toggle.innerHTML = input.type === 'password' ? icons.show : icons.hide;
        };
        render();
        toggle.addEventListener('click', () => {
            input.type = input.type === 'password' ? 'text' : 'password';
            render();
        });
    })();
</script>
@endsection
