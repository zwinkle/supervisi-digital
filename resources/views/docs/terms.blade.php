@extends('layouts.guest', ['title' => 'Syarat dan Ketentuan'])

@section('content')
<div class="bg-white py-12 md:py-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Syarat dan Ketentuan</h1>
            <p class="mt-4 text-lg text-slate-600">Supervisi Digital</p>
            <p class="mt-2 text-sm text-slate-500">Berlaku sejak: 20 November 2025</p>
        </div>

        <div class="prose prose-slate max-w-none text-slate-600">
            <h2 class="text-xl font-bold text-slate-900 mt-8 mb-4">1. Penerimaan Syarat</h2>
            <p class="mb-4">Dengan mengakses atau menggunakan aplikasi Supervisi Digital, Anda menyetujui untuk terikat oleh Syarat dan Ketentuan ini serta semua hukum dan peraturan yang berlaku.</p>

            <h2 class="text-xl font-bold text-slate-900 mt-8 mb-4">2. Deskripsi Layanan</h2>
            <p class="mb-2">Supervisi Digital adalah platform digital yang memfasilitasi proses supervisi pendidikan bagi guru dan supervisor di lingkungan sekolah. Layanan mencakup:</p>
            <ul class="list-disc pl-5 mb-4 space-y-1">
                <li>Penjadwalan sesi supervisi</li>
                <li>Pengunggahan dokumen pembelajaran (RPP, asesmen, video pembelajaran)</li>
                <li>Evaluasi dan penilaian supervisi</li>
                <li>Pencatatan catatan supervisi</li>
                <li>Pelaporan dan analisis data supervisi</li>
            </ul>

            <h2 class="text-xl font-bold text-slate-900 mt-8 mb-4">3. Kelayakan Pengguna</h2>
            <p class="mb-2">Layanan ini ditujukan untuk:</p>
            <ul class="list-disc pl-5 mb-4 space-y-1">
                <li>Guru sekolah dasar yang terdaftar</li>
                <li>Supervisor pendidikan yang terdaftar</li>
                <li>Administrator sistem yang berwenang</li>
            </ul>

            <h2 class="text-xl font-bold text-slate-900 mt-8 mb-4">4. Akun Pengguna</h2>
            <h3 class="text-lg font-semibold text-slate-900 mt-6 mb-3">4.1 Pendaftaran</h3>
            <p class="mb-4">Pengguna harus didaftarkan oleh administrator sistem melalui sistem undangan. Pendaftaran terbuka tidak diperbolehkan.</p>

            <h3 class="text-lg font-semibold text-slate-900 mt-6 mb-3">4.2 Keamanan Akun</h3>
            <p class="mb-2">Pengguna bertanggung jawab atas:</p>
            <ul class="list-disc pl-5 mb-4 space-y-1">
                <li>Menjaga kerahasiaan kredensial login</li>
                <li>Semua aktivitas yang terjadi di bawah akun mereka</li>
                <li>Segera melaporkan akses tidak sah ke akun</li>
            </ul>

            <h2 class="text-xl font-bold text-slate-900 mt-8 mb-4">5. Penggunaan yang Dilarang</h2>
            <p class="mb-2">Pengguna dilarang untuk:</p>
            <ul class="list-disc pl-5 mb-4 space-y-1">
                <li>Mengunggah konten yang melanggar hak cipta</li>
                <li>Menyebarkan konten yang tidak pantas, ilegal, atau menyinggung</li>
                <li>Menggunakan aplikasi untuk tujuan komersial tanpa izin</li>
                <li>Mencoba meretas atau mengganggu sistem</li>
                <li>Menggunakan akun orang lain tanpa izin</li>
                <li>Menjual, membagikan, atau mentransfer akun kepada pihak lain</li>
            </ul>

            <h2 class="text-xl font-bold text-slate-900 mt-8 mb-4">6. Hak Kekayaan Intelektual</h2>
            <p class="mb-4">Seluruh hak cipta, merek dagang, dan hak kekayaan intelektual lainnya dalam aplikasi ini adalah milik pengembang aplikasi. Pengguna diberikan lisensi terbatas untuk menggunakan aplikasi sesuai dengan Syarat dan Ketentuan ini.</p>

            <h2 class="text-xl font-bold text-slate-900 mt-8 mb-4">7. Integrasi Google Drive</h2>
            <p class="mb-2">Aplikasi menggunakan Google Drive API untuk penyimpanan dokumen. Dengan menggunakan layanan ini, Anda setuju dengan:</p>
            <ul class="list-disc pl-5 mb-4 space-y-1">
                <li>Kebijakan Privasi Google</li>
                <li>Persyaratan Layanan Google APIs</li>
                <li>Memahami bahwa file disimpan di akun Google Anda</li>
            </ul>

            <h2 class="text-xl font-bold text-slate-900 mt-8 mb-4">8. Ketersediaan Layanan</h2>
            <p class="mb-2">Kami berusaha menyediakan layanan yang andal, namun tidak menjamin:</p>
            <ul class="list-disc pl-5 mb-4 space-y-1">
                <li>Akses yang tidak terganggu atau bebas kesalahan</li>
                <li>Ketersediaan layanan 24/7</li>
                <li>Keamanan mutlak dari data pengguna</li>
            </ul>

            <h2 class="text-xl font-bold text-slate-900 mt-8 mb-4">9. Pembatasan Tanggung Jawab</h2>
            <p class="mb-2">Sejauh yang diizinkan oleh hukum, kami tidak bertanggung jawab atas:</p>
            <ul class="list-disc pl-5 mb-4 space-y-1">
                <li>Kerugian langsung, tidak langsung, insidental, khusus, atau konsekuensial</li>
                <li>Kehilangan data atau keuntungan</li>
                <li>Gangguan layanan atau kinerja yang terdegradasi</li>
            </ul>

            <h2 class="text-xl font-bold text-slate-900 mt-8 mb-4">10. Perubahan Syarat</h2>
            <p class="mb-4">Kami berhak mengubah Syarat dan Ketentuan ini kapan saja. Perubahan akan diberitahukan melalui aplikasi atau email kepada pengguna.</p>

            <h2 class="text-xl font-bold text-slate-900 mt-8 mb-4">11. Pengakhiran Akun</h2>
            <p class="mb-4">Kami berhak menangguhkan atau mengakhiri akun pengguna yang melanggar Syarat dan Ketentuan ini tanpa pemberitahuan sebelumnya.</p>

            <h2 class="text-xl font-bold text-slate-900 mt-8 mb-4">12. Hukum yang Berlaku</h2>
            <p class="mb-4">Syarat dan Ketentuan ini diatur dan ditafsirkan sesuai dengan hukum Republik Indonesia.</p>

            <h2 class="text-xl font-bold text-slate-900 mt-8 mb-4">13. Kontak</h2>
            <p class="mb-2">Jika Anda memiliki pertanyaan tentang Syarat dan Ketentuan ini, silakan hubungi kami di:</p>
            <p class="font-medium">Email: admin@supervisi.yourdomain.my.id</p>
        </div>

        <div class="mt-12 pt-8 border-t border-slate-200 text-center">
            <a href="{{ route('docs.index') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">&larr; Kembali ke Dokumentasi</a>
        </div>
    </div>
</div>
@endsection
