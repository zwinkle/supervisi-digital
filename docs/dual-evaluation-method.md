# Fitur Dual Evaluation Method

## Deskripsi
Fitur ini memberikan 2 opsi kepada supervisor untuk melakukan penilaian supervisi terhadap guru:

1. **Penilaian Manual** - Menggunakan form penilaian dengan radio button dan checkbox untuk setiap aspek (RPP, Pembelajaran, Asesmen)
2. **Upload File Hasil Supervisi** - Upload file hasil supervisi yang sudah jadi (PDF, DOC, DOCX) yang mencakup seluruh penilaian

## Cara Penggunaan

### Untuk Supervisor

1. Buka halaman **Jadwal Pengawas** (`/supervisor/schedules`)
2. Klik tombol **Penilaian** pada jadwal yang ingin dinilai
3. Pilih metode evaluasi:
   - **Penilaian Manual**: Sistem akan menampilkan card untuk setiap aspek penilaian (RPP, Pembelajaran, Asesmen). Klik "Lihat / Nilai" untuk menilai setiap aspek.
   - **Upload Hasil Supervisi**: Upload file hasil supervisi (PDF/DOC/DOCX, max 10MB) yang mencakup seluruh penilaian

#### Upload File Hasil Supervisi
1. Pilih opsi "Upload Hasil Supervisi"
2. Form upload akan muncul
3. Klik "Choose File" dan pilih file hasil supervisi (PDF, DOC, atau DOCX)
4. Klik tombol "Upload File"
5. File akan tersimpan dan jadwal akan otomatis ditandai sebagai "Dinilai"
6. Supervisor dapat mengganti file kapan saja dengan mengupload file baru

#### Download File Hasil Supervisi
- Jika supervisor sudah mengupload file, tombol "Unduh Hasil Supervisi" akan muncul di header halaman
- Klik tombol tersebut untuk mengunduh file yang telah diupload

### Untuk Guru

1. Buka halaman **Jadwal Guru** (`/guru/schedules`)
2. Pada jadwal yang sudah dinilai dengan metode upload:
   - Tombol "Ekspor Laporan" akan diganti dengan "Unduh Hasil Supervisi" (tombol hijau)
3. Klik tombol "Unduh Hasil Supervisi" untuk mengunduh file hasil supervisi yang diupload oleh supervisor
4. Jika penilaian menggunakan metode manual, guru tetap dapat mengklik "Ekspor Laporan" untuk mendapatkan PDF yang di-generate sistem

## Teknis

### Database
Tabel `schedules` ditambahkan 2 kolom baru:
- `uploaded_evaluation_file` (nullable string) - Path file yang diupload
- `evaluation_method` (string, default: 'manual') - Metode evaluasi: 'manual' atau 'upload'

### File Storage
- File disimpan di: `storage/app/public/evaluation_files/`
- Symlink dibuat di: `public/storage/`
- Format file yang diterima: PDF, DOC, DOCX
- Ukuran maksimal: 10MB

### Routes

#### Supervisor
- `GET /supervisor/schedules/{schedule}/assessment` - Halaman pilih metode evaluasi
- `POST /supervisor/schedules/{schedule}/upload-evaluation` - Upload file hasil supervisi
- `GET /supervisor/schedules/{schedule}/download-evaluation` - Download file hasil supervisi
- `POST /supervisor/schedules/{schedule}/update-method` - Update metode evaluasi (via AJAX)

#### Guru
- `GET /guru/schedules/{schedule}/download-evaluation` - Download file hasil supervisi

### Controller Methods

#### ScheduleController (Supervisor)
```php
public function uploadEvaluation(Request $request, Schedule $schedule)
public function downloadEvaluation(Request $request, Schedule $schedule)
public function updateMethod(Request $request, Schedule $schedule)
```

#### ScheduleController (Guru)
```php
public function downloadEvaluation(Request $request, Schedule $schedule)
```

## Keamanan
- Validasi ownership: Hanya supervisor yang membuat jadwal yang bisa upload/download
- Validasi ownership: Hanya guru yang terkait jadwal yang bisa download
- Validasi file: Format (pdf, doc, docx) dan ukuran (max 10MB)
- File lama otomatis dihapus saat upload file baru

## Catatan
- Jika supervisor mengganti metode dari 'manual' ke 'upload' setelah sudah menilai manual, penilaian manual tetap tersimpan di database namun tidak akan ditampilkan di export PDF
- Jika supervisor mengganti dari 'upload' ke 'manual', file upload tetap tersimpan namun tidak akan ditampilkan ke guru
- Guru hanya bisa melihat hasil sesuai metode yang dipilih supervisor terakhir kali
