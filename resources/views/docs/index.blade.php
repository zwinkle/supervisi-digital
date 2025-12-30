@extends('layouts.guest', ['title' => 'Dokumentasi'])

@section('content')
<div class="bg-gradient-to-br from-[#667eea] to-[#764ba2] text-white">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">Supervisi Digital</h1>
        <p class="mt-4 text-xl text-indigo-100">Platform Digital untuk Supervisi Pendidikan Guru</p>
    </div>
</div>

<div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="rounded-2xl bg-white p-8 shadow-xl shadow-slate-200/50">
        <h2 class="text-2xl font-bold text-slate-900 border-b-2 border-slate-100 pb-2 mb-6">Tentang Supervisi Digital</h2>
        <p class="text-lg text-slate-600 mb-8 leading-relaxed">
            Supervisi Digital adalah platform inovatif yang dirancang untuk memfasilitasi proses supervisi pendidikan di lingkungan sekolah dasar. Aplikasi ini membantu supervisor dan guru dalam merencanakan, melaksanakan, dan mengevaluasi sesi supervisi secara efektif dan efisien.
        </p>
        
        <div class="grid gap-8 md:grid-cols-3 mb-12">
            <div class="rounded-xl bg-slate-50 p-6 border-l-4 border-indigo-500">
                <h3 class="font-semibold text-lg text-slate-900 mb-2">Penjadwalan Supervisi</h3>
                <p class="text-slate-600">Sistem penjadwalan yang terintegrasi memungkinkan supervisor dan guru untuk merencanakan sesi supervisi dengan mudah dan terorganisir.</p>
            </div>
            
            <div class="rounded-xl bg-slate-50 p-6 border-l-4 border-indigo-500">
                <h3 class="font-semibold text-lg text-slate-900 mb-2">Pengelolaan Dokumen</h3>
                <p class="text-slate-600">Unggah dan kelola dokumen pembelajaran seperti RPP, video pembelajaran, dan dokumen asesmen dengan integrasi Google Drive.</p>
            </div>
            
            <div class="rounded-xl bg-slate-50 p-6 border-l-4 border-indigo-500">
                <h3 class="font-semibold text-lg text-slate-900 mb-2">Evaluasi dan Penilaian</h3>
                <p class="text-slate-600">Formulir evaluasi terstruktur untuk menilai kinerja guru selama sesi supervisi dengan rubrik yang jelas.</p>
            </div>
        </div>
        
        <h2 class="text-2xl font-bold text-slate-900 border-b-2 border-slate-100 pb-2 mb-6">Fitur Utama</h2>
        <ul class="space-y-3 mb-12">
            <li class="flex items-start gap-3">
                <svg class="h-6 w-6 text-indigo-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="text-slate-700"><strong>Dashboard Personalisasi</strong> - Tampilan khusus untuk administrator, supervisor, dan guru</span>
            </li>
            <li class="flex items-start gap-3">
                <svg class="h-6 w-6 text-indigo-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="text-slate-700"><strong>Pelaporan Real-time</strong> - Laporan kemajuan dan statistik supervisi yang selalu diperbarui</span>
            </li>
            <li class="flex items-start gap-3">
                <svg class="h-6 w-6 text-indigo-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="text-slate-700"><strong>Notifikasi Otomatis</strong> - Pengingat jadwal dan notifikasi status supervisi</span>
            </li>
            <li class="flex items-start gap-3">
                <svg class="h-6 w-6 text-indigo-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="text-slate-700"><strong>Integrasi Google</strong> - Otentikasi Google dan penyimpanan dokumen di Google Drive</span>
            </li>
            <li class="flex items-start gap-3">
                <svg class="h-6 w-6 text-indigo-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="text-slate-700"><strong>Keamanan Data</strong> - Enkripsi data dan kontrol akses berbasis peran</span>
            </li>
        </ul>
        
        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-6 py-3 text-base font-medium text-white shadow-lg shadow-indigo-200 transition-all hover:bg-indigo-700 hover:shadow-indigo-300">
                Masuk ke Aplikasi
            </a>
            <a href="{{ route('docs.privacy') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-6 py-3 text-base font-medium text-slate-600 shadow-sm transition-all hover:bg-slate-50 hover:text-slate-900">
                Kebijakan Privasi
            </a>
            <a href="{{ route('docs.terms') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-6 py-3 text-base font-medium text-slate-600 shadow-sm transition-all hover:bg-slate-50 hover:text-slate-900">
                Syarat dan Ketentuan
            </a>
        </div>
    </div>
</div>

<footer class="bg-white py-12 border-t border-slate-200 mt-auto">
    <div class="mx-auto max-w-7xl px-4 text-center text-slate-500">
        <p>&copy; {{ date('Y') }} Supervisi Digital. Hak Cipta Dilindungi.</p>
        <p class="mt-2 text-sm">Email: admin@supervisi.rendratriana.my.id</p>
    </div>
</footer>
@endsection
