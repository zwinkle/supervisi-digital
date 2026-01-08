# Dokumentasi Alur Sistem & Arsitektur Berbasis Role (Detail Teknis)

Dokumen ini mendetailkan alur kerja sistem hingga level kode, mencakup **Function**, **Conditional Logic**, dan **Class Dependency**.

---

## 1. Role: ADMINISTRATOR

Administrator memiliki kontrol penuh terhadap data master (Pengguna dan Sekolah).

### A. Manajemen Pengguna (CRUD & Invite)
**Controller:** `App\Http\Controllers\Admin\InvitationController` (Create) & `UserController` (Read/Update/Delete)

| Fitur | Function Utama | Detail Logika & Conditional | Method Terkait / Trigger |
| :--- | :--- | :--- | :--- |
| **Kirim Undangan** | `store(Request $request)` | **1. Validasi Role:** <br> - `if role == supervisor`: Wajib pilih 1 sekolah (`supervisor_school_id`). <br> - `if role == teacher`: Wajib sekolah + Tipe Guru (Mapel/Kelas). <br> **2. Generate Token:** `Str::random(40)` membuat token acak. <br> **3. Signed URL:** `URL::temporarySignedRoute(...)` membuat link yang aman dari manipulasi, expired dalam 7 hari. <br> **4. Simpan DB:** `Invitation::create([...])`. | - `App\Mail\InviteMail` (Class Email)<br>- `Illuminate\Support\Str`<br>- `Illuminate\Support\Facades\URL` |
| **List User** | `index(Request $request)` | **1. Filter Pencarian:** `if ($search)`: Query kompleks `where` nama OR email OR NIP OR relasi sekolah (`whereHas`). <br> **2. Eager Loading:** `User::with(['schools'])` untuk mencegah N+1 Query problem saat menampilkan nama sekolah di tabel. | - `App\Models\User` (Relation: schools)<br>- `Illuminate\Database\Eloquent\Builder` |
| **Simpan User Baru** | `store(Request $request)` | **1. Hashing:** `Hash::make($password)` untuk keamanan. <br> **2. Conditional Role Logic:** <br> - `if role == supervisor`: Loop array sekolah -> `attach($id, ['role'=>'supervisor'])`. <br> - `if role == teacher`: Set atribut `teacher_type`, `subject`, `class_name` -> `attach($id, ['role'=>'teacher'])`. | - `App\Support\TeacherOptions` (Enum Mapel/Kelas)<br>- `Illuminate\Support\Facades\Hash` |
| **Hapus User** | `destroy(User $user)` | **Database Transaction (`DB::transaction`):** <br> Menjamin integritas data. Semua step berikut harus sukses, atau semua batal (rollback): <br> 1. `Invitation::where('invited_by')...delete()` <br> 2. `File::where('owner_user_id')...delete()` <br> 3. `$user->schools()->detach()` (Hapus relasi sekolah) <br> 4. `Schedule::where(...)->delete()` (Hapus jadwal) <br> 5. `$user->delete()` | - `Illuminate\Support\Facades\DB`<br>- `App\Models\File`<br>- `App\Models\Schedule` |

### B. Manajemen Sekolah
**Controller:** `App\Http\Controllers\Admin\SchoolController`

| Fitur | Function Utama | Detail Logika & Conditional | Method Terkait / Trigger |
| :--- | :--- | :--- | :--- |
| **Tambah Sekolah** | `store(Request $request)` | Validasi input standar. Memanggil `School::create($validated)`. Model `School` secara otomatis akan men-generate UUID melalui method `booted()` (`static::creating`). | - `App\Models\School`<br>- `Illuminate\Support\Str::uuid()` |

---

## 2. Role: SUPERVISOR

Supervisor bertanggung jawab atas manajemen jadwal suvervisi dan melakukan penilaian.

### A. Manajemen Jadwal
**Controller:** `App\Http\Controllers\Supervisor\ScheduleController`

| Fitur | Function Utama | Detail Logika & Conditional | Method Terkait / Trigger |
| :--- | :--- | :--- | :--- |
| **Buat Jadwal** | `store(Request $request)` | **1. Validasi Otoritas:** Cek apakah sekolah yang dipilih ada di daftar sekolah binaan supervisor (`$user->schools()->wherePivot(...)`). <br> **2. Cek Guru:** Cek apakah guru terdaftar di sekolah tersebut (Query table `school_user`). <br> **3. Cek Duplikasi:** `Schedule::where(...)` cek apakah Supervisor SUDAH punya jadwal dengan judul sama di tanggal sama. Mencegah double click. | - `Illuminate\Validation\Rule`<br>- `App\Models\Schedule` |
| **Tandai Selesai** | `conduct(Schedule $sched)` | **1. Security:** `if ($sched->supervisor_id !== $user->id) abort(403)`. <br> **2. Update:** Set `$schedule->conducted_at = now()`. | - `App\Models\Schedule` |

### B. Proses Penilaian (Assessment)

#### Metode 1: Penilaian Digital (Form Web)
**Controller:** `App\Http\Controllers\EvaluationController`

| Fitur | Function Utama | Detail Logika & Conditional | Method Terkait / Trigger |
| :--- | :--- | :--- | :--- |
| **Buka Form** | `show(...)` | **1. Prasyarat:** `if (!$schedule->hasSubmissionFor($type))`: Redirect back dgn error jika guru belum upload file yang diminta. <br> **2. Load Struktur:** Panggil `self::structureFor($type)` untuk dapat array pertanyaan statis. | - `Schedule::hasSubmissionFor()`<br>- `self::structureFor()` |
| **Simpan Nilai** | `store(...)` | **1. Flattening:** Mengubah array multidimensi `A1[a1_1]` jadi `A1.a1_1` (dot notation). <br> **2. Kalkulasi:** Panggil `computeTotals()`. <br> **3. Persistance:** `Evaluation::updateOrCreate(...)` (Simpan JSON breakdown). <br> **4. Trigger Selesai:** Panggil `$schedule->checkAndMarkCompleted()` di dalam blok `try-catch`. | - `computeTotals()` (Private logic)<br>- `Schedule::checkAndMarkCompleted()` |
| **Hitung Nilai** | `computeTotals(...)` | **Logic `if type == pembelajaran`:** <br> - Hitung jumlah item bernilai `true` (Ya). <br> - Rumus: `(Count(Ya) / TotalItem) * 100`. <br> **Logic `else` (RPP/Asesmen):** <br> - Hitung total skor (1-4). <br> - Rumus: `(TotalSkor / (TotalItem * 4)) * 100`. | - Helper Function (Internal) |

#### Metode 2: Penilaian Manual (Upload Scan)
**Controller:** `App\Http\Controllers\Supervisor\ScheduleController`

| Fitur | Function Utama | Detail Logika & Conditional | Method Terkait / Trigger |
| :--- | :--- | :--- | :--- |
| **Upload Scan** | `uploadEvaluation` | **1. Sanitisasi:** Ganti koma (`,`) jadi titik (`.`) pada input skor. <br> **2. Hapus Lama:** `if (exists($oldFile)) Storage::delete(...)`. <br> **3. Simpan Baru:** `store('evaluation_files', 'public')`. <br> **4. Update DB:** Set `evaluation_method` = `'upload'` dan simpan path file. | - `Illuminate\Support\Facades\Storage`<br>- `App\Models\Schedule` |

---

## 3. Role: GURU

Guru berfokus pada melengkapi administrasi.

### A. Upload Dokumen (Submission)
**Controller:** `App\Http\Controllers\SubmissionController`

Fitur paling kompleks karena melibatkan integrasi API eksternal.

| Fitur | Function Utama | Detail Logika & Conditional | Method Terkait / Trigger |
| :--- | :--- | :--- | :--- |
| **Upload Berkas** | `store(...)` | **1. Token Check:** `if (!$user->google_access_token)` return error. <br> **2. Init Service:** `new GoogleDriveService(...)`. Cek `isTokenValid()`. Jika false, panggil `getRefreshedToken()` dan update user DB. <br> **3. Folder Logic:** <br> - `ensureRootFolder()`: Cek folder "Supervisi Digital", buat jika belum ada. <br> - `ensureDateFolder()`: Buat folder anak "[Judul] - [Tanggal]". <br> **4. Upload Loop:** Loop setiap file -> `drive->uploadFile()`. <br> **5. Save Metadata:** Simpan `google_file_id`, `web_view_link` ke tabel `files`. <br> **6. Sharing:** `drive->shareWith(email_supervisor, 'commenter')`. | - `App\Services\GoogleDriveService`<br>- `App\Models\Submission`<br>- `App\Models\File` |
| **Cek Status** | `status(...)` | **Polling AJAX:** <br> - Loop dokumen yang sudah ada. <br> - `if` link preview kosong atau size 0: Panggil `drive->getFile(metadata)` untuk update info terbaru dari Google. <br> - `try-catch`: Bungkus semua request API agar tidak crash jika koneksi Google timeout. | - `GoogleDriveService->getFile()` |

### B. Lihat Hasil
**Controller:** `App\Http\Controllers\Guru\ScheduleController`

| Fitur | Function Utama | Detail Logika & Conditional | Method Terkait / Trigger |
| :--- | :--- | :--- | :--- |
| **Export PDF** | `export(...)` | **1. DOMPDF Setup:** Set option `isRemoteEnabled = false` (security). <br> **2. Rendering:** `view('exports.schedule_evaluation')->render()`. <br> **3. Output:** `dompdf->stream()`. | - `Dompdf\Dompdf`<br>- `Dompdf\Options` |

---

## 4. Sistem Inti (Shared)

### A. Autentikasi
**Controller:** `App\Http\Controllers\Auth\AuthController` & `InviteController`

| Fitur | Function Utama | Detail Logika & Conditional | Method Terkait / Trigger |
| :--- | :--- | :--- | :--- |
| **Login** | `login(...)` | **1. Attempt:** `Auth::attempt(...)` cek hash. <br> **2. Regenerate:** `$request->session()->regenerate()` (Penting: Mencegah Session Fixation attack). <br> **3. Redirect:** `dashboardFor($user)` switch case berdasarkan `is_admin`, role supervisor/guru. | - `Illuminate\Support\Facades\Auth` |
| **Terima Undangan** | `store(...)` (InviteController) | **Database Transaction:** <br> 1. `User::firstOrNew(['email' => ...])`. <br> 2. Set password hash. <br> 3. `schools()->detach()` lalu `attach()` ulang sesuai data undangan. <br> 4. `Invitation::delete()` atau `used_at = now()`. <br> 5. `Auth::login($user)` (Auto login setelah set password). | - `App\Models\Invitation`<br>- `App\Events\Verified` |
