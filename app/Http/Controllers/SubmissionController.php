<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Schedule;
use App\Models\Submission;
use App\Models\SubmissionDocument;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubmissionController extends Controller
{
    /**
    /**
     * Menampilkan halaman upload dokumen.
     * Halaman ini digunakan guru untuk mengunggah RPP, video, dan dokumen pendukung lainnya.
     */
    public function showForm(Schedule $schedule)
    {
        $user = Auth::user();
        // Validasi akses: Pastikan hanya pemilik jadwal (guru) atau supervisor terkait yang bisa akses
        if ($user->id !== $schedule->teacher_id && $user->id !== $schedule->supervisor_id) {
            abort(403);
        }
        // Eager load relasi yang diperlukan untuk meminimalisir query database berulang
        $schedule->loadMissing(['submission.documents.file','submission.videoFile']);
        return view('submissions.upload', compact('schedule'));
    }

    /**
     * Endpoint API status kelengkapan dokumen.
     * Endpoint ini dipanggil via AJAX untuk update progress bar dan status file secara real-time.
     */
    public function status(Request $request, Schedule $schedule)
    {
        $user = Auth::user();
        if ($user->id !== $schedule->teacher_id && $user->id !== $schedule->supervisor_id) {
            abort(403);
        }

        $schedule->loadMissing(['submission.documents.file','submission.videoFile']);
        $submission = $schedule->submission;

        // Daftar kategori dokumen yang harus dilengkapi
        $categories = SubmissionDocument::ALLOWED_CATEGORIES;
        $documentsData = [];
        foreach ($categories as $category) {
            $documentsData[$category] = [];
        }

        // Menyiapkan data batas maksimal upload per kategori
        $limits = [];
        foreach ($categories as $category) {
            $limits[$category] = [
                'max' => SubmissionDocument::MAX_PER_CATEGORY,
                'current' => $submission ? $submission->documents->where('category', $category)->count() : 0,
            ];
        }

        $data = [
            'documents' => $documentsData,
            'video' => null,
            'limits' => $limits,
        ];

        $drive = null;

        // Blok Try-Catch Besar untuk interaksi Google Drive
        // Karena koneksi ke Google API bisa gagal sewaktu-waktu (token expired, network error),
        // kita bungkus agar tidak membuat halaman error 500, tapi tetap mengembalikan data yang bisa ditampilkan.
        try {
            if ($submission) {
                // Loop setiap kategori dokumen untuk mengambil detail file
                foreach ($categories as $category) {
                    $docs = $submission->documents->where('category', $category)->values();
                    $data['documents'][$category] = $docs->map(function ($doc) use (&$drive, $user) {
                        $file = $doc->file;
                        if (!$file) {
                            return null;
                        }

                        // Jika file tersimpan di Google Drive tapi link view-nya belum ada di database lokal
                        // Maka kita perlu request ke API Google Drive untuk minta link terbaru & ukuran file
                        if ($file->google_file_id && (empty($file->web_view_link) || empty($file->extra['size']))) {
                            if ($user->google_access_token) {
                                try {
                                    // Inisialisasi Service Google Drive (koneksi ke API)
                                    $drive = $drive ?? new GoogleDriveService($user->google_access_token, $user->google_refresh_token);
                                    
                                    // Cek apakah token masih valid, jika tidak coba refresh
                                    if (!$drive->isTokenValid()) {
                                        $newToken = $drive->getRefreshedToken();
                                        if ($newToken) {
                                            // Token berhasil diperbarui, simpan ke database user
                                            $user->google_access_token = $newToken['access_token'];
                                            if (!empty($newToken['expires_in'])) {
                                                $user->google_token_expires_at = now()->addSeconds((int) $newToken['expires_in']);
                                            }
                                            if (!empty($newToken['refresh_token'])) {
                                                $user->google_refresh_token = $newToken['refresh_token'];
                                            }
                                            $user->save();
                                        } else {
                                            // Gagal refresh token, skip file ini
                                            Log::warning('Gagal refresh token saat ambil info file', ['file_id' => $file->google_file_id]);
                                            return null; 
                                        }
                                    }
                                    
                                    // Panggil API Google untuk ambil metadata file (Link View, Ukuran, dll)
                                    $remote = $drive->getFile($file->google_file_id, 'id, name, webViewLink, webContentLink, mimeType, size');
                                    $file->web_view_link = $remote->webViewLink ?? $file->web_view_link;
                                    $file->web_content_link = $remote->webContentLink ?? $file->web_content_link;
                                    $extra = $file->extra ?? [];
                                    if (isset($remote->size)) {
                                        $extra['size'] = (int) $remote->size;
                                    }
                                    $file->extra = $extra;
                                    $file->save(); // Update cache lokal
                                } catch (\Throwable $e) {
                                    Log::warning('Gagal ambil info file dari Drive', ['error' => $e->getMessage(), 'file_id' => $file->google_file_id]);
                                    // Lanjut ke file berikutnya meski yang ini gagal
                                }
                            }
                        }

                        // Kembalikan data file yang sudah bersih untuk JSON response
                        return [
                            'id' => $doc->id,
                            'name' => $file->name,
                            'size' => $file->extra['size'] ?? null,
                            'pageCount' => $file->extra['pageCount'] ?? null,
                            'webViewLink' => $file->web_view_link,
                        ];
                    })->filter()->values()->all();
                }

                // Proses khusus untuk Video Pembelajaran
                if ($submission->videoFile) {
                    $vid = $submission->videoFile;
                    // Logika serupa: Cek metadata video di Drive jika belum lengkap
                    if (empty($vid->extra['is_external_link']) && (empty($vid->extra['videoMediaMetadata']) || empty($vid->web_view_link) || empty($vid->extra['size']))) {
                        if ($user->google_access_token) {
                            try {
                                $drive = $drive ?? new GoogleDriveService($user->google_access_token, $user->google_refresh_token);
                                
                                // Cek dan refresh token jika perlu
                                if (!$drive->isTokenValid()) {
                                    $newToken = $drive->getRefreshedToken();
                                    if ($newToken) {
                                        $user->google_access_token = $newToken['access_token'];
                                        if (!empty($newToken['expires_in'])) {
                                            $user->google_token_expires_at = now()->addSeconds((int) $newToken['expires_in']);
                                        }
                                        if (!empty($newToken['refresh_token'])) {
                                            $user->google_refresh_token = $newToken['refresh_token'];
                                        }
                                        $user->save();
                                        
                                        // Coba ambil metadata video lagi setelah token baru didapat
                                        $g = $drive->getFile($vid->google_file_id, 'id, name, webViewLink, webContentLink, mimeType, size, videoMediaMetadata');
                                        $vid->web_view_link = $g->webViewLink ?? $vid->web_view_link;
                                        $vid->web_content_link = $g->webContentLink ?? $vid->web_content_link;
                                        $extra = $vid->extra ?? [];
                                        if (isset($g->size)) { $extra['size'] = (int)$g->size; }
                                        if (isset($g->videoMediaMetadata)) { $extra['videoMediaMetadata'] = $g->videoMediaMetadata; }
                                        $vid->extra = $extra;
                                        $vid->save();
                                    } else {
                                        Log::warning('Gagal refresh token untuk video info', ['file_id' => $vid->google_file_id]);
                                    }
                                } else {
                                    // Token valid, langsung ambil metadata
                                    $g = $drive->getFile($vid->google_file_id, 'id, name, webViewLink, webContentLink, mimeType, size, videoMediaMetadata');
                                    $vid->web_view_link = $g->webViewLink ?? $vid->web_view_link;
                                    $vid->web_content_link = $g->webContentLink ?? $vid->web_content_link;
                                    $extra = $vid->extra ?? [];
                                    if (isset($g->size)) { $extra['size'] = (int)$g->size; }
                                    if (isset($g->videoMediaMetadata)) { $extra['videoMediaMetadata'] = $g->videoMediaMetadata; }
                                    $vid->extra = $extra;
                                    $vid->save();
                                }
                            } catch (\Throwable $e) {
                                Log::warning('Gagal ambil info video dari Drive', ['error' => $e->getMessage(), 'file_id' => $vid->google_file_id]);
                            }
                        }
                    }
                    $durationMs = $vid->extra['videoMediaMetadata']['durationMillis'] ?? null;
                    $data['video'] = [
                        'name' => $vid->name,
                        'size' => $vid->extra['size'] ?? null,
                        'durationMillis' => $durationMs ? (int) $durationMs : null,
                        'webViewLink' => $vid->web_view_link,
                        'isExternal' => !empty($vid->extra['is_external_link']),
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Abaikan error global di blok ini agar return JSON tetap jalan
        }

        return response()->json($data);
    }



    /**
     * Menangani proses upload file atau penyimpanan link video.
     * Method ini akan mengupload file fisik ke Google Drive dan menyimpan metadatanya ke database.
     */
    public function store(Request $request, Schedule $schedule)
    {
        $user = Auth::user();
        if ($user->id !== $schedule->teacher_id) {
            abort(403);
        }

        // Ambil input file dari request.
        // Kita normalisasi input agar selalu berbentuk array, untuk menghandle multiple file upload.
        $rawInputs = [
            'rpp' => $request->file('rpp', []),
            'asesmen' => $request->file('asesmen', []),
            'administrasi' => $request->file('administrasi', []),
        ];

        // Normalisasi file upload: Pastikan semua kategori berisi array of file, kosong jika tidak ada.
        $normalizedFiles = [];
        foreach ($rawInputs as $category => $files) {
            if (!$files) {
                $normalizedFiles[$category] = [];
                continue;
            }
            if (!is_array($files)) {
                $files = [$files];
            }
            $normalizedFiles[$category] = array_values(array_filter($files));
        }

        $labels = [
            'rpp' => 'RPP',
            'asesmen' => 'Asesmen',
            'administrasi' => 'Administrasi',
        ];

        // Pengecekan Error Upload PHP Native (misal: file terlalu besar melebihi batas php.ini)
        $errorMap = function (?int $code) {
            switch ($code) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    return 'Ukuran file terlalu besar. Harap perbesar batas upload sever atau kompres file Anda.';
                case UPLOAD_ERR_PARTIAL:
                    return 'Proses upload terputus. Silakan coba lagi.';
                case UPLOAD_ERR_NO_FILE:
                    return 'Tidak ada file yang dipilih untuk diupload.';
                case UPLOAD_ERR_NO_TMP_DIR:
                    return 'Server error: Folder temporary tidak ditemukan.';
                case UPLOAD_ERR_CANT_WRITE:
                    return 'Server error: Gagal menulis file ke disk.';
                case UPLOAD_ERR_EXTENSION:
                    return 'Upload dibatalkan oleh ekstensi PHP.';
                default:
                    return null;
            }
        };

        // Cek error awal sebelum validasi Laravel
        $preErrors = [];
        foreach ($normalizedFiles as $category => $files) {
            foreach ($files as $file) {
                if ($file && $file->getError()) {
                    $msg = $errorMap($file->getError());
                    if ($msg) {
                        $preErrors[$category] = $msg;
                        break;
                    }
                }
            }
        }
        if (!empty($preErrors)) {
            return back()->withErrors($preErrors)->withInput();
        }

        // Validasi Laravel untuk tipe file dan ukuran (max 20MB)
        $validated = $request->validate([
            'rpp' => ['nullable', 'array'],
            'rpp.*' => ['file', 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'max:20480'],
            'asesmen' => ['nullable', 'array'],
            'asesmen.*' => ['file', 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'max:20480'],
            'administrasi' => ['nullable', 'array'],
            'administrasi.*' => ['file', 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'max:20480'],
            'video_link' => ['nullable', 'url', 'max:500'],
        ], [
            'rpp.*.mimetypes' => 'Format RPP harus PDF/DOC/DOCX.',
            'rpp.*.max' => 'Ukuran RPP melebihi 20MB.',
            'asesmen.*.mimetypes' => 'Format Asesmen harus PDF/DOC/DOCX.',
            'asesmen.*.max' => 'Ukuran Asesmen melebihi 20MB.',
            'administrasi.*.mimetypes' => 'Format Administrasi harus PDF/DOC/DOCX.',
            'administrasi.*.max' => 'Ukuran Administrasi melebihi 20MB.',
            'video_link.url' => 'Link video harus berupa URL yang valid (YouTube atau Google Drive).',
            'video_link.max' => 'Link video terlalu panjang.',
        ]);

        // Menggabungkan semua file yang lolos validasi ke satu list antrian
        $incomingFiles = [];
        foreach ($normalizedFiles as $category => $files) {
            foreach ($files as $file) {
                $incomingFiles[] = ['category' => $category, 'file' => $file];
            }
        }

        if (empty($incomingFiles) && !$request->filled('video_link')) {
            return back()->withErrors(['upload' => 'Pilih minimal satu berkas untuk diunggah (RPP, Asesmen, Administrasi, atau tautan Video).'])->withInput();
        }

        // Buat record submission jika belum ada
        $submission = Submission::firstOrCreate(
            ['schedule_id' => $schedule->id, 'teacher_id' => $user->id],
            ['submitted_at' => now()]
        );

        // Cek Quota: Mencegah user mengupload lebih dari batas maksimum per kategori
        $submission->loadMissing('documents');
        $existingPerCategory = $submission->documents->groupBy('category')->map->count();
        $incomingPerCategory = [];
        foreach ($incomingFiles as $payload) {
            $incomingPerCategory[$payload['category']] = ($incomingPerCategory[$payload['category']] ?? 0) + 1;
        }

        foreach (SubmissionDocument::ALLOWED_CATEGORIES as $category) {
            $existing = $existingPerCategory[$category] ?? 0;
            $incoming = $incomingPerCategory[$category] ?? 0;
            if ($existing + $incoming > SubmissionDocument::MAX_PER_CATEGORY) {
                $message = 'Batas unggahan untuk ' . ($labels[$category] ?? ucfirst($category)) . ' tercapai (maksimal ' . SubmissionDocument::MAX_PER_CATEGORY . ' berkas).';
                return back()->withErrors(['upload' => $message])->withInput();
            }
        }

        // Cek Token Google: Jika ada file fisik yg mau diupload, user wajib punya token Google yang valid
        $needsDrive = !empty($incomingFiles);
        if ($needsDrive && !$user->google_access_token) {
            return back()->withErrors(['google' => 'Token Google tidak tersedia. Silakan login ulang dengan Google.']);
        }

        try {
            $drive = null;
            $dateFolderId = null;
            $tokenRefreshed = false;

            if ($needsDrive) {
                try {
                    // Inisialisasi koneksi ke Google Drive
                    $drive = new GoogleDriveService($user->google_access_token, $user->google_refresh_token);
                    
                    // Cek validitas token dan refresh otomatis jika expired
                    if (!$drive->isTokenValid()) {
                        $newToken = $drive->getRefreshedToken();
                        if ($newToken) {
                            $user->google_access_token = $newToken['access_token'];
                            if (!empty($newToken['expires_in'])) {
                                $user->google_token_expires_at = now()->addSeconds((int) $newToken['expires_in']);
                            }
                            if (!empty($newToken['refresh_token'])) {
                                $user->google_refresh_token = $newToken['refresh_token'];
                            }
                            $user->save();
                            $tokenRefreshed = true;
                        } else {
                            // Jika refresh gagal (misal akses dicabut user), minta login ulang
                            return back()->withErrors(['google' => 'Token Google telah expired dan tidak dapat diperbarui. Silakan logout dari Google di halaman profil dan login kembali.'])->withInput();
                        }
                    }
                    
                    // Siapkan folder tujuan di Google Drive
                    // Format nama folder: "[Nama Jadwal] - [Tanggal]"
                    $rootId = $drive->ensureRootFolder();
                    $dateStr = $schedule->date->format('d-m-Y');
                    $rawTitle = $schedule->title ?: 'Sesi Supervisi';
                    $cleanTitle = preg_replace('/[\\\\\/]/', '-', $rawTitle);
                    $cleanTitle = trim(preg_replace('/\s+/', ' ', $cleanTitle));
                    $folderName = $cleanTitle . ' - ' . $dateStr;
                    $dateFolderId = $drive->ensureChildFolder($rootId, $folderName);
                } catch (\Throwable $e) {
                    Log::error('Failed to initialize Drive service', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                    return back()->withErrors(['upload' => 'Gagal menghubungkan ke Google Drive. Token mungkin sudah expired. Silakan logout dari Google di halaman profil dan login kembali.'])->withInput();
                }
            }

            $newFileRecords = [];

            // Proses Upload Setiap File ke Google Drive
            foreach ($incomingFiles as $payload) {
                if (!$drive) {
                    return back()->withErrors(['upload' => 'Gagal menginisialisasi Google Drive.'])->withInput();
                }

                /** @var \Illuminate\Http\UploadedFile $docFile */
                $docFile = $payload['file'];
                $category = $payload['category'];
                
                // Baca konten file dan upload ke Drive
                $contents = file_get_contents($docFile->getRealPath());
                $meta = $drive->uploadFile($dateFolderId, $docFile->getClientOriginalName(), $docFile->getMimeType(), $contents);
                
                // Hitung jumlah halaman jika PDF (untuk analisa cepat dosen)
                $pageCount = null;
                if (strtolower($docFile->getClientOriginalExtension()) === 'pdf') {
                    $matches = [];
                    preg_match_all('/\/Type\s*\/Page(?!s)/', $contents, $matches);
                    if (!empty($matches[0])) {
                        $pageCount = count($matches[0]);
                    }
                }

                // Simpan metadata file ke database lokal (tabel 'files')
                $fileRecord = File::create([
                    'owner_user_id' => $user->id,
                    'schedule_id' => $schedule->id,
                    'google_file_id' => $meta['id'],
                    'name' => $meta['name'],
                    'mime' => $meta['mime'],
                    'web_view_link' => $meta['webViewLink'] ?? null,
                    'web_content_link' => $meta['webContentLink'] ?? null,
                    'folder_id' => $dateFolderId,
                    'extra' => [
                        'size' => $meta['size'] ?? null,
                        'pageCount' => $pageCount,
                    ],
                ]);

                // Hubungkan file dengan submission dan kategorinya (RPP/Asesmen/dll)
                SubmissionDocument::create([
                    'submission_id' => $submission->id,
                    'file_id' => $fileRecord->id,
                    'category' => $category,
                ]);

                $newFileRecords[] = $fileRecord;
            }

            // Proses Link Video (juga disimpan sebagai 'File' tapi tipe khusus)
            $videoRecord = null;
            $oldVideo = null;
            if ($request->filled('video_link')) {
                $videoLink = trim($request->input('video_link'));
                $youtubeMatches = [];
                // Deteksi apakah link YouTube atau Google Drive
                $isYoutube = preg_match('/(?:(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11}))/i', $videoLink, $youtubeMatches);
                $isGoogleDrive = preg_match('/(?:https?:\/\/)?(?:www\.)?drive\.google\.com/i', $videoLink);

                if (!$isYoutube && !$isGoogleDrive) {
                    return back()->withErrors(['video_link' => 'Link video harus dari YouTube atau Google Drive.'])->withInput();
                }

                $videoName = 'Video Pembelajaran';
                // Jika YouTube, coba ambil Judul Video via oEmbed
                if ($isYoutube && !empty($youtubeMatches[1])) {
                    $videoId = $youtubeMatches[1];
                    $videoName = 'YouTube: ' . $videoId;
                    try {
                        $oembedUrl = 'https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v=' . $videoId . '&format=json';
                        $response = @file_get_contents($oembedUrl);
                        if ($response) {
                            $data = json_decode($response, true);
                            if (!empty($data['title'])) {
                                $videoName = $data['title'];
                            }
                        }
                    } catch (\Throwable $e) {
                        Log::info('Failed to fetch YouTube title', ['error' => $e->getMessage()]);
                    }
                } elseif ($isGoogleDrive) {
                    $driveMatches = [];
                    if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $videoLink, $driveMatches)) {
                        $videoName = 'Google Drive: ' . substr($driveMatches[1], 0, 10) . '...';
                    } elseif (preg_match('/[?&]id=([a-zA-Z0-9_-]+)/', $videoLink, $driveMatches)) {
                        $videoName = 'Google Drive: ' . substr($driveMatches[1], 0, 10) . '...';
                    } else {
                        $videoName = 'Google Drive Video';
                    }
                }

                // Buat record file untuk video
                $videoRecord = File::create([
                    'owner_user_id' => $user->id,
                    'schedule_id' => $schedule->id,
                    'google_file_id' => null, // null karena ini external link, bukan upload kita
                    'name' => $videoName,
                    'mime' => 'video/link',
                    'web_view_link' => $videoLink,
                    'web_content_link' => $videoLink,
                    'folder_id' => null,
                    'extra' => [
                        'is_external_link' => true,
                        'link_type' => $isYoutube ? 'youtube' : 'google_drive',
                        'original_url' => $videoLink,
                    ],
                ]);

                // Hapus video lama jika ada (agar tidak menumpuk)
                $oldVideo = $submission->videoFile;
                $submission->video_file_id = $videoRecord->id;
                if ($oldVideo) {
                    if ($oldVideo->google_file_id && empty($oldVideo->extra['is_external_link'])) {
                        // Jika video lama adalah file yang DIUPLOAD ke Drive (bukan link), hapus dari Drive juga
                        if (!$drive && $user->google_access_token) {
                            try {
                                $drive = new GoogleDriveService($user->google_access_token, $user->google_refresh_token);
                            } catch (\Throwable $e) {
                                Log::error('Failed to initialize Drive for old video deletion', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                            }
                        }
                        if ($drive) {
                            try {
                                $drive->deleteFile($oldVideo->google_file_id);
                            } catch (\Throwable $e) {
                                Log::error('Failed to delete old Video from Drive', ['error' => $e->getMessage(), 'file_id' => $oldVideo->google_file_id]);
                            }
                        }
                    }
                    try {
                        $oldVideo->delete();
                    } catch (\Throwable $e) {
                        Log::error('Failed to delete old Video DB record', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                    }
                }
            }

            // Sharing Folder: Berikan akses 'Commenter' ke email Supervisor
            // Agar supervisor bisa melihat file tanpa harus login akun teacher
            if ($drive && $dateFolderId) {
                try {
                    $drive->shareWith($dateFolderId, $schedule->supervisor->email, 'commenter');
                    foreach ($newFileRecords as $fileRecord) {
                        $drive->shareWith($fileRecord->google_file_id, $schedule->supervisor->email, 'commenter');
                    }
                } catch (\Throwable $e) {
                    Log::warning('Failed to share drive files with supervisor', ['error' => $e->getMessage()]);
                }
            }

            $submission->submitted_at = $submission->submitted_at ?: now();
            $submission->save();

            // Coba auto-complete jadwal jika semua syarat terpenuhi
            try {
                $schedule->checkAndMarkCompleted();
            } catch (\Throwable $e) {
                // ignore
            }

            if ($drive) {
                // Update token jika ada perubahan otomatis dari library Google Client
                if (!$tokenRefreshed) {
                    $newToken = $drive->getClient()->getAccessToken();
                    if (!empty($newToken['access_token'])) {
                        $user->google_access_token = $newToken['access_token'];
                        if (!empty($newToken['expires_in'])) {
                            $user->google_token_expires_at = now()->addSeconds((int) $newToken['expires_in']);
                        }
                        $user->save();
                    }
                }
            }

            $warningMessage = null;
            if ($oldVideo && $oldVideo->google_file_id && empty($oldVideo->extra['is_external_link']) && !$drive) {
                $warningMessage = 'Berkas berhasil diunggah, namun video lama mungkin masih ada di Google Drive. Silakan hapus manual jika diperlukan.';
            }

            return redirect()->route('guru.submissions.show', $schedule)->with('success', $warningMessage ?: 'Berkas berhasil diunggah.');
        } catch (\Throwable $e) {
            Log::error('Submission upload error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors(['upload' => 'Gagal mengunggah berkas. Coba lagi.'])->withInput();
        }
    }


        /**
     * Menghapus video (link) yang sudah disimpan.
     * Jika video tersimpan sebagai file di Drive (upload), juga akan dihapus dari Drive.
     */
    public function deleteFile(Request $request, Schedule $schedule, string $kind)
    {
        $user = Auth::user();
        if ($user->id !== $schedule->teacher_id) {
            abort(403);
        }

        if ($kind !== 'video') {
            abort(404);
        }

        $submission = $schedule->submission;
        if (!$submission || !$submission->videoFile) {
            return back()->with('success', 'Tidak ada video untuk dihapus.');
        }

        $file = $submission->videoFile;
        $drive = null;

        // Jika file ada di Google Drive (bukan external link), coba hapus juga dari Drive
        if ($file->google_file_id && empty($file->extra['is_external_link']) && $user->google_access_token) {
            try {
                $drive = new GoogleDriveService($user->google_access_token, $user->google_refresh_token);
                
                // Cek token validitas sebelum delete
                if (!$drive->isTokenValid()) {
                    $newToken = $drive->getRefreshedToken();
                    if ($newToken) {
                        $user->google_access_token = $newToken['access_token'];
                        if (!empty($newToken['expires_in'])) {
                            $user->google_token_expires_at = now()->addSeconds((int) $newToken['expires_in']);
                        }
                        if (!empty($newToken['refresh_token'])) {
                            $user->google_refresh_token = $newToken['refresh_token'];
                        }
                        $user->save();
                    } else {
                        Log::warning('Gagal refresh token saat hapus video, file tetap ada di Drive', ['file_id' => $file->google_file_id]);
                        $drive = null;
                    }
                }
                
                if ($drive) {
                    try {
                        $drive->deleteFile($file->google_file_id);
                    } catch (\Throwable $e) {
                        Log::warning('Gagal menghapus video dari Drive', ['error' => $e->getMessage(), 'file_id' => $file->google_file_id]);
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Gagal inisialisasi Drive saat hapus video', ['error' => $e->getMessage()]);
            }
        }

        try {
            $file->delete();
        } catch (\Throwable $e) {
            Log::error('Gagal menghapus record video dari DB', ['error' => $e->getMessage()]);
        }
        $submission->video_file_id = null;
        $submission->save();

        return back()->with('success', 'Video berhasil dihapus.');
    }

    /**
     * Menghapus dokumen (RPP/Asesmen/dll) yang sudah diupload.
     * File akan dihapus dari Google Drive dan record database juga dihapus.
     */
    public function deleteDocument(Request $request, Schedule $schedule, SubmissionDocument $document)
    {
        $user = Auth::user();
        if ($user->id !== $schedule->teacher_id) {
            abort(403);
        }

        $submission = $schedule->submission;
        if (!$submission || $document->submission_id !== $submission->id) {
            abort(404);
        }

        $file = $document->file;
        $drive = null;

        // Hapus fisik file di Google Drive
        if ($file && $file->google_file_id && $user->google_access_token) {
            try {
                $drive = new GoogleDriveService($user->google_access_token, $user->google_refresh_token);
                
                if (!$drive->isTokenValid()) {
                    $newToken = $drive->getRefreshedToken();
                    if ($newToken) {
                        $user->google_access_token = $newToken['access_token'];
                        if (!empty($newToken['expires_in'])) {
                            $user->google_token_expires_at = now()->addSeconds((int) $newToken['expires_in']);
                        }
                        if (!empty($newToken['refresh_token'])) {
                            $user->google_refresh_token = $newToken['refresh_token'];
                        }
                        $user->save();
                    } else {
                        Log::warning('Gagal refresh token saat hapus dokumen, file tetap di Drive', ['file_id' => $file->google_file_id]);
                        $drive = null;
                    }
                }
                
                if ($drive) {
                    try {
                        $drive->deleteFile($file->google_file_id);
                    } catch (\Throwable $e) {
                        Log::warning('Gagal menghapus dokumen dari Drive', ['error' => $e->getMessage(), 'file_id' => $file->google_file_id]);
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Gagal inisialisasi Drive saat hapus dokumen', ['error' => $e->getMessage()]);
                $drive = null;
            }
        }

        // Hapus record di database
        if ($file) {
            try {
                $file->delete();
            } catch (\Throwable $e) {
                Log::warning('Gagal menghapus dokumen DB record', ['error' => $e->getMessage()]);
            }
        }

        $document->delete();

        return back()->with('success', 'Berkas berhasil dihapus.');
    }



}
