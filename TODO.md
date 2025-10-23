# Supervisi Digital - TODO

- [x] t1-init: Inisialisasi proyek Laravel "supervisi-digital" dan setup Git
- [x] t2-db-env: Konfigurasi PostgreSQL dan .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD), timezone=Asia/Jakarta, locale=id
- [x] t3-deps: Tambah dependensi
  - laravel/socialite (Google OAuth)
  - google/apiclient
  - maatwebsite/excel
  - spatie/laravel-permission
  - laravel/sanctum (opsional)
  - tailwindcss + laravel-vite-plugin
  - Model/tabel: `schools`, `users`, `school_user` (role per sekolah)
  - Supervisor bisa multi-sekolah; guru hanya satu sekolah
  - Scoping permission per `school_id` (teams)
- [x] t5-migrations: Migrasi & model inti
  - `schools`, `users`, `school_user`, `schedules`, `submissions`, `notes`, `evaluations`, `files`, `activity_logs`
- [x] t6-oauth: Google OAuth
  - Scopes: openid, email, profile, drive.file, drive.metadata.readonly
  - Simpan refresh token user
  - Catatan: pastikan Authorized redirect URI di Google Console = `${APP_URL}/auth/google/callback` (contoh: `http://127.0.0.1:8000/auth/google/callback`), lalu `php artisan config:clear`
- [ ] t7-drive: Integrasi Google Drive
  - Root: "SUPERVISI DIGITAL"
  - Subfolder by tanggal: "DD-MM-YYYY"
  - Upload RPP/video; share folder + file ke supervisor (read/comment)
  - Catatan: partial progress; hanya upload RPP/video yang berhasil
- [ ] t8-ui: Blade UI modern (Tailwind)
  - Layout, navbar, sidebar, komponen tabel/kartu, form, toast
- [ ] t9-teacher: Fitur Guru
  - Lihat jadwal, unggah RPP/video ke Drive, lihat catatan & penilaian
- [ ] t10-supervisor: Fitur Supervisor
- [ ] t11-excel: Bulk Excel
  - Template export (jadwal, penilaian) + formula
  - Import (queue + validasi)
- [ ] t12-security: Keamanan & otorisasi
  - Policies/gates, scoping per sekolah, rate limit, validation rules
- [ ] t13-notify: Notifikasi in-app (toast/cards)
- [ ] t14-audit: Activity log (status, catatan, penilaian)
- [ ] t15-tests: Unit/Feature/Integration (mock OAuth/Drive)
- [ ] t16-deploy: Deployment (env prod, queue worker, storage, SSL, backup)
- [ ] t17-excel-rubric: Ekstraksi rubrik dari Form Supervisi.xlsx → tabel/JSON
- [ ] t18-file-validation: Validasi RPP (PDF/DOCX), Video (MP4); cek durasi ~30 menit 1080p via Drive metadata
- [ ] t19-inapp-notify-ui: UI notifikasi (toast, indikator di dashboard/profile)
- [x] t20-todo-md: Simpan dan update TODO.md secara berkala

## Catatan Kebijakan
- Login Google: semua domain diizinkan (Google Cloud/testing mode; email di-whitelist).
- File constraints:
  - RPP: PDF/DOCX; ukuran wajar (mis. ≤ 20 MB, dapat disesuaikan).
  - Video: MP4; durasi ≤ ~30 menit 1080p (cek `videoMediaMetadata.durationMillis`).
- Supervisor multi-sekolah; Guru hanya satu sekolah (enforced di `school_user` & UI selector).

## Progress tambahan
- [x] t25-rubric-docs: Dokumentasi rubrik (lihat `docs/rubrik.md`)
- [x] t26-oauth-routes-stubs: Routes OAuth Google + stub controller & GoogleDriveService
- [x] t28-seed-roles: Seed roles dasar (teacher, supervisor) dengan Spatie Permission