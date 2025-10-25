# Supervisi Digital - Platform Monitoring Guru

Supervisi Digital adalah aplikasi internal untuk mempermudah proses supervisi akademik guru, koordinasi supervisor, serta pengelolaan dokumen penunjang seperti RPP, asesmen, dan video pembelajaran. Proyek ini dibangun menggunakan **Laravel 10** dengan fokus pada pengalaman mobile-first dan integrasi Google Drive untuk penyimpanan berkas.

## Fitur Utama

- **Manajemen Pengguna & Peran**: Admin dapat mengundang/kelola akun Guru, Supervisor, dan Admin dengan pemetaan sekolah.
- **Jenis Guru & Penugasan**: Guru wajib memilih jenis (Mata Pelajaran / Kelas) beserta detail penugasannya.
- **Supervisi Multi Sekolah**: Supervisor dapat diawasi pada lebih dari satu sekolah melalui antarmuka checkbox.
- **Jadwal & Evaluasi**: Jadwal supervisi lengkap dengan status, evaluasi (RPP, Pembelajaran, Asesmen), serta ekspor PDF/Excel.
- **Unggah Dokumen Terintegrasi**: Guru mengunggah berkas ke Google Drive organisasi (RPP, asesmen, administrasi, video) dengan pemantauan status real-time.
- **Tampilan Responsif**: Halaman utama (dashboard, tabel, kartu) dioptimalkan untuk akses mobile.

## Teknologi

- Laravel 10
- PHP 8.2+
- PostgreSQL / MySQL
- Tailwind CSS & Alpine.js (via Blade Components)
- Google Drive API
- PHPUnit untuk pengujian

## Persyaratan

- PHP >= 8.2 dengan ekstensi yang dibutuhkan oleh Laravel
- Composer
- Node.js & NPM (opsional untuk build frontend)
- Database (PostgreSQL disarankan)
- Kredensial Google API (OAuth Client + Drive API enabled)

## Instalasi

```bash
git clone <repo>
cd supervisi-digital
cp .env.example .env
composer install
php artisan key:generate
```

Konfigurasi `.env` yang penting:

```
APP_URL=https://supervisi.example.com
DB_CONNECTION=pgsql
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI=${APP_URL}/auth/google/callback
```

Jalankan migrasi dan seeding dasar (opsional):

```bash
php artisan migrate
php artisan db:seed
```

Jika menggunakan Vite:

```bash
npm install
npm run dev
```

## Perintah Penting

```bash
php artisan test         # Menjalankan seluruh unit & feature test
php artisan migrate      # Menerapkan perubahan skema
php artisan queue:work   # Menjalankan antrean (jika diaktifkan)
```

## Struktur Direktori

- `app/Http/Controllers` – logika aplikasi (Admin, Supervisor, Profile, Auth)
- `app/Support/TeacherOptions.php` – daftar jenis guru, mata pelajaran, kelas
- `resources/views` – tampilan Blade responsif
- `database/migrations` – migrasi database termasuk penambahan kolom `teacher_type`
- `app/Services/GoogleDriveService.php` – integrasi unggah/penghapusan berkas Drive

## Kontribusi

1. Fork repositori & buat branch fitur: `git checkout -b fitur-baru`
2. Pastikan seluruh tes lulus: `php artisan test`
3. Ajukan pull request dengan deskripsi jelas

## Lisensi

Proyek ini dirilis di bawah lisensi **MIT**.

---

Untuk pertanyaan internal atau permintaan fitur, hubungi tim pengembang Supervisi Digital melalui kanal resmi organisasi.
