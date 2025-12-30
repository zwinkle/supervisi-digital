@extends('layouts.guest', ['title' => 'Kebijakan Privasi'])

@section('content')
<div class="bg-white py-12 md:py-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Kebijakan Privasi</h1>
            <p class="mt-4 text-lg text-slate-600">Supervisi Digital</p>
            <p class="mt-2 text-sm text-slate-500">Berlaku sejak: 20 November 2025</p>
        </div>

        <div class="prose prose-slate max-w-none text-slate-600">
            <h2 class="text-xl font-bold text-slate-900 mt-8 mb-4">1. Pendahuluan</h2>
            <p class="mb-4">Kebijakan Privasi ini menjelaskan bagaimana Supervisi Digital ("kami", "aplikasi") mengumpulkan, menggunakan, membagikan, dan melindungi informasi pribadi pengguna ("Anda"). Dengan menggunakan aplikasi ini, Anda menyetujui praktik yang dijelaskan dalam kebijakan ini.</p>

            <h2 class="text-xl font-bold text-slate-900 mt-8 mb-4">2. Informasi yang Kami Kumpulkan</h2>
            
            <h3 class="text-lg font-semibold text-slate-900 mt-6 mb-3">2.1 Informasi yang Anda Berikan</h3>
            <ul class="list-disc pl-5 mb-4 space-y-1">
                <li>Nama lengkap</li>
                <li>Alamat email</li>
                <li>Nomor Induk Pegawai (NIP)</li>
                <li>Mata pelajaran yang diajarkan</li>
                <li>Kelas yang diajar</li>
                <li>Foto profil</li>
            </ul>

            <h3 class="text-lg font-semibold text-slate-900 mt-6 mb-3">2.2 Informasi yang Dikumpulkan Secara Otomatis</h3>
            <ul class="list-disc pl-5 mb-4 space-y-1">
                <li>Alamat IP</li>
                <li>Informasi perangkat (jenis browser, sistem operasi)</li>
                <li>Waktu dan tanggal aktivitas</li>
                <li>Halaman yang dikunjungi dalam aplikasi</li>
            </ul>

            <h3 class="text-lg font-semibold text-slate-900 mt-6 mb-3">2.3 Informasi dari Google OAuth</h3>
            <p class="mb-2">Jika Anda masuk menggunakan akun Google, kami mengumpulkan:</p>
            <ul class="list-disc pl-5 mb-4 space-y-1">
                <li>Nama lengkap</li>
                <li>Alamat email</li>
                <li>Foto profil</li>
                <li>ID pengguna Google</li>
            </ul>

            <h2 class="text-xl font-bold text-slate-900 mt-8 mb-4">3. Penggunaan Informasi</h2>
            <p class="mb-2">Kami menggunakan informasi yang dikumpulkan untuk:</p>
            <ul class="list-disc pl-5 mb-4 space-y-1">
                <li>Menyediakan dan mempersonalisasi layanan aplikasi</li>
                <li>Memverifikasi identitas pengguna</li>
                <li>Memfasilitasi proses supervisi digital</li>
                <li>Meningkatkan kualitas layanan</li>
                <li>Mengirim pemberitahuan terkait aplikasi</li>
                <li>Memenuhi kewajiban hukum</li>
            </ul>

            <h2 class="text-xl font-bold text-slate-900 mt-8 mb-4">4. Integrasi Google Drive</h2>
            <p class="mb-2">Aplikasi ini menggunakan Google Drive API untuk:</p>
            <ul class="list-disc pl-5 mb-4 space-y-1">
                <li>Menyimpan dokumen RPP (Rencana Pelaksanaan Pembelajaran)</li>
                <li>Menyimpan video pembelajaran</li>
                <li>Menyimpan dokumen asesmen</li>
                <li>Menyimpan dokumen administrasi lainnya</li>
            </ul>
            <p class="mb-4">Semua file yang diunggah disimpan dalam folder pribadi pengguna di Google Drive dan hanya dapat diakses oleh pengguna yang berwenang dan supervisor yang ditunjuk.</p>

            <h2 class="text-xl font-bold text-slate-900 mt-8 mb-4">5. Keamanan Data</h2>
            <p class="mb-2">Kami menerapkan langkah-langkah keamanan yang sesuai untuk melindungi informasi pribadi dari akses tidak sah, pengubahan, pengungkapan, atau penghancuran, termasuk:</p>
            <ul class="list-disc pl-5 mb-4 space-y-1">
                <li>Enkripsi data saat transit dan saat disimpan</li>
                <li>Autentikasi dua faktor</li>
                <li>Akses berbasis peran</li>
                <li>Audit log aktivitas pengguna</li>
            </ul>

            <h2 class="text-xl font-bold text-slate-900 mt-8 mb-4">6. Hak Pengguna</h2>
            <p class="mb-2">Anda memiliki hak untuk:</p>
            <ul class="list-disc pl-5 mb-4 space-y-1">
                <li>Mengakses informasi pribadi yang kami miliki tentang Anda</li>
                <li>Memperbaiki informasi yang tidak akurat</li>
                <li>Meminta penghapusan data pribadi Anda</li>
                <li>Membatasi atau menolak pemrosesan data tertentu</li>
            </ul>

            <h2 class="text-xl font-bold text-slate-900 mt-8 mb-4">7. Perubahan Kebijakan</h2>
            <p class="mb-4">Kami dapat memperbarui Kebijakan Privasi ini dari waktu ke waktu. Perubahan akan dipublikasikan di halaman ini dengan tanggal berlaku yang diperbarui.</p>

            <h2 class="text-xl font-bold text-slate-900 mt-8 mb-4">8. Kontak</h2>
            <p class="mb-2">Jika Anda memiliki pertanyaan tentang Kebijakan Privasi ini, silakan hubungi kami di:</p>
            <p class="font-medium">Email: admin@supervisi.yourdomain.my.id</p>
        </div>

        <div class="mt-12 pt-8 border-t border-slate-200 text-center">
            <a href="{{ route('docs.index') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">&larr; Kembali ke Dokumentasi</a>
        </div>
    </div>
</div>
@endsection
