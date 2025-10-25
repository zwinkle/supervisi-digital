<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Schedule;
use App\Models\Submission;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SubmissionController extends Controller
{
    public function showForm(Schedule $schedule)
    {
        $user = Auth::user();
        // Basic authorization: only the assigned teacher or supervisor can access
        if ($user->id !== $schedule->teacher_id && $user->id !== $schedule->supervisor_id) {
            abort(403);
        }
        $schedule->loadMissing(['submission.rppFile','submission.videoFile','submission.asesmenFile','submission.administrasiFile']);
        return view('submissions.upload', compact('schedule'));
    }

    public function status(Request $request, Schedule $schedule)
    {
        $user = Auth::user();
        if ($user->id !== $schedule->teacher_id && $user->id !== $schedule->supervisor_id) {
            abort(403);
        }
        $schedule->loadMissing(['submission.rppFile','submission.videoFile','submission.asesmenFile','submission.administrasiFile']);
        $submission = $schedule->submission;
        $data = [
            'rpp' => null,
            'video' => null,
            'asesmen' => null,
            'administrasi' => null,
        ];
        $drive = null;
        try {
            if ($submission && $submission->rppFile) {
                $rpp = $submission->rppFile;
                // If size or link missing, refresh from Drive
                if (empty($rpp->web_view_link) || empty($rpp->extra['size'])) {
                    $drive = new GoogleDriveService($user->google_access_token, $user->google_refresh_token);
                    $g = $drive->getFile($rpp->google_file_id, 'id, name, webViewLink, webContentLink, mimeType, size');
                    $rpp->web_view_link = $g->webViewLink ?? $rpp->web_view_link;
                    $rpp->web_content_link = $g->webContentLink ?? $rpp->web_content_link;
                    $extra = $rpp->extra ?? [];
                    if (isset($g->size)) { $extra['size'] = (int)$g->size; }
                    $rpp->extra = $extra;
                    $rpp->save();
                }
                $data['rpp'] = [
                    'name' => $rpp->name,
                    'size' => $rpp->extra['size'] ?? null,
                    'pageCount' => $rpp->extra['pageCount'] ?? null,
                    'webViewLink' => $rpp->web_view_link,
                ];
            }
            if ($submission && $submission->asesmenFile) {
                $asesmen = $submission->asesmenFile;
                if (empty($asesmen->web_view_link) || empty($asesmen->extra['size'])) {
                    $drive = $drive ?? new GoogleDriveService($user->google_access_token, $user->google_refresh_token);
                    $g = $drive->getFile($asesmen->google_file_id, 'id, name, webViewLink, webContentLink, mimeType, size');
                    $asesmen->web_view_link = $g->webViewLink ?? $asesmen->web_view_link;
                    $asesmen->web_content_link = $g->webContentLink ?? $asesmen->web_content_link;
                    $extra = $asesmen->extra ?? [];
                    if (isset($g->size)) { $extra['size'] = (int) $g->size; }
                    $asesmen->extra = $extra;
                    $asesmen->save();
                }
                $data['asesmen'] = [
                    'name' => $asesmen->name,
                    'size' => $asesmen->extra['size'] ?? null,
                    'pageCount' => $asesmen->extra['pageCount'] ?? null,
                    'webViewLink' => $asesmen->web_view_link,
                ];
            }
            if ($submission && $submission->videoFile) {
                $vid = $submission->videoFile;
                if (empty($vid->extra['videoMediaMetadata']) || empty($vid->web_view_link) || empty($vid->extra['size'])) {
                    $drive = $drive ?? new GoogleDriveService($user->google_access_token, $user->google_refresh_token);
                    $g = $drive->getFile($vid->google_file_id, 'id, name, webViewLink, webContentLink, mimeType, size, videoMediaMetadata');
                    $vid->web_view_link = $g->webViewLink ?? $vid->web_view_link;
                    $vid->web_content_link = $g->webContentLink ?? $vid->web_content_link;
                    $extra = $vid->extra ?? [];
                    if (isset($g->size)) { $extra['size'] = (int)$g->size; }
                    if (isset($g->videoMediaMetadata)) { $extra['videoMediaMetadata'] = $g->videoMediaMetadata; }
                    $vid->extra = $extra;
                    $vid->save();
                }
                $data['video'] = [
                    'name' => $vid->name,
                    'size' => $vid->extra['size'] ?? null,
                    'durationMillis' => $vid->extra['videoMediaMetadata']['durationMillis'] ?? null,
                    'webViewLink' => $vid->web_view_link,
                ];
            }
            if ($submission && $submission->administrasiFile) {
                $administrasi = $submission->administrasiFile;
                if (empty($administrasi->web_view_link) || empty($administrasi->extra['size'])) {
                    $drive = $drive ?? new GoogleDriveService($user->google_access_token, $user->google_refresh_token);
                    $g = $drive->getFile($administrasi->google_file_id, 'id, name, webViewLink, webContentLink, mimeType, size');
                    $administrasi->web_view_link = $g->webViewLink ?? $administrasi->web_view_link;
                    $administrasi->web_content_link = $g->webContentLink ?? $administrasi->web_content_link;
                    $extra = $administrasi->extra ?? [];
                    if (isset($g->size)) { $extra['size'] = (int) $g->size; }
                    $administrasi->extra = $extra;
                    $administrasi->save();
                }
                $data['administrasi'] = [
                    'name' => $administrasi->name,
                    'size' => $administrasi->extra['size'] ?? null,
                    'pageCount' => $administrasi->extra['pageCount'] ?? null,
                    'webViewLink' => $administrasi->web_view_link,
                ];
            }
        } catch (\Throwable $e) {
            // ignore errors; return whatever we have
        }
        return response()->json($data);
    }

    public function store(Request $request, Schedule $schedule)
    {
        $user = Auth::user();
        if ($user->id !== $schedule->teacher_id) {
            abort(403);
        }

        // Pre-check low-level PHP upload errors for clearer UI messages
        $rppFile = $request->file('rpp');
        $videoFile = $request->file('video');
        $asesmenFile = $request->file('asesmen');
        $administrasiFile = $request->file('administrasi');
        $errorMap = function (?int $code) {
            switch ($code) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    return 'Ukuran file melebihi batas sistem. Perbesar batas server (upload_max_filesize/post_max_size) atau perkecil file.';
                case UPLOAD_ERR_PARTIAL:
                    return 'Unggahan file terputus. Coba ulangi unggah.';
                case UPLOAD_ERR_NO_FILE:
                    return 'Tidak ada file yang diunggah.';
                case UPLOAD_ERR_NO_TMP_DIR:
                    return 'Server tidak memiliki folder sementara untuk unggahan.';
                case UPLOAD_ERR_CANT_WRITE:
                    return 'Server gagal menulis file unggahan ke disk.';
                case UPLOAD_ERR_EXTENSION:
                    return 'Unggahan dibatalkan oleh ekstensi PHP.';
                default:
                    return null;
            }
        };
        $preErrors = [];
        if ($rppFile && $rppFile->getError()) {
            $msg = $errorMap($rppFile->getError());
            if ($msg) { $preErrors['rpp'] = $msg.' (Maks RPP: 20MB)'; }
        }
        if ($videoFile && $videoFile->getError()) {
            $msg = $errorMap($videoFile->getError());
            if ($msg) { $preErrors['video'] = $msg.' (Maks Video: ~500MB, format MP4)'; }
        }
        if ($asesmenFile && $asesmenFile->getError()) {
            $msg = $errorMap($asesmenFile->getError());
            if ($msg) { $preErrors['asesmen'] = $msg.' (Maks Asesmen: 20MB)'; }
        }
        if ($administrasiFile && $administrasiFile->getError()) {
            $msg = $errorMap($administrasiFile->getError());
            if ($msg) { $preErrors['administrasi'] = $msg.' (Maks Administrasi: 20MB)'; }
        }
        if (!empty($preErrors)) {
            return back()->withErrors($preErrors)->withInput();
        }

        $validated = $request->validate([
            'rpp' => ['sometimes', 'file', 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'max:20480'], // 20MB
            'video' => ['sometimes', 'file', 'mimetypes:video/mp4', 'max:512000'], // ~500MB cap
            'asesmen' => ['sometimes', 'file', 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'max:20480'],
            'administrasi' => ['sometimes', 'file', 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'max:20480'],
            'video_duration_ms' => ['nullable','integer','min:0'],
        ], [
            'rpp.required' => 'Harap unggah berkas RPP (PDF/DOC/DOCX). Maksimal 20MB.',
            'rpp.file' => 'RPP harus berupa file yang valid.',
            'rpp.mimetypes' => 'Format RPP harus PDF/DOC/DOCX.',
            'rpp.max' => 'Ukuran RPP melebihi 20MB.',
            'rpp.uploaded' => 'Gagal mengunggah RPP. Periksa ukuran file dan batas server (upload_max_filesize/post_max_size).',

            'video.required' => 'Harap unggah berkas video (MP4).',
            'video.file' => 'Video harus berupa file yang valid.',
            'video.mimetypes' => 'Format video harus MP4.',
            'video.max' => 'Ukuran video melebihi ~500MB.',
            'video.uploaded' => 'Gagal mengunggah video. Periksa ukuran file dan batas server (upload_max_filesize/post_max_size).',

            'asesmen.file' => 'Asesmen harus berupa file yang valid.',
            'asesmen.mimetypes' => 'Format Asesmen harus PDF/DOC/DOCX.',
            'asesmen.max' => 'Ukuran Asesmen melebihi 20MB.',
            'asesmen.uploaded' => 'Gagal mengunggah Asesmen. Periksa ukuran file dan batas server (upload_max_filesize/post_max_size).',

            'administrasi.file' => 'Administrasi harus berupa file yang valid.',
            'administrasi.mimetypes' => 'Format Administrasi harus PDF/DOC/DOCX.',
            'administrasi.max' => 'Ukuran Administrasi melebihi 20MB.',
            'administrasi.uploaded' => 'Gagal mengunggah Administrasi. Periksa ukuran file dan batas server (upload_max_filesize/post_max_size).',
        ]);

        // Ensure at least one file is provided
        if (!($request->hasFile('rpp') || $request->hasFile('video') || $request->hasFile('asesmen') || $request->hasFile('administrasi')))
        {
            return back()->withErrors(['upload' => 'Pilih minimal satu berkas untuk diunggah (RPP, Video, Asesmen, atau Administrasi).'])->withInput();
        }

        // Ensure tokens exist
        if (!$user->google_access_token) {
            return back()->withErrors(['google' => 'Token Google tidak tersedia. Silakan login ulang dengan Google.']);
        }

        try {
            $drive = new GoogleDriveService($user->google_access_token, $user->google_refresh_token);

            // Ensure folder structure: SUPERVISI DIGITAL / "<Title> - <dd-mm-YYYY>"
            $rootId = $drive->ensureRootFolder();
            $dateStr = $schedule->date->format('d-m-Y');
            $rawTitle = $schedule->title ?: 'Sesi Supervisi';
            $cleanTitle = preg_replace('/[\\\\\/]/', '-', $rawTitle); // replace slashes
            $cleanTitle = trim(preg_replace('/\s+/', ' ', $cleanTitle)); // collapse whitespace
            $folderName = $cleanTitle." - ".$dateStr;
            $dateFolderId = $drive->ensureChildFolder($rootId, $folderName);

            // Upsert submission container first
            $submission = Submission::firstOrCreate(
                ['schedule_id' => $schedule->id, 'teacher_id' => $user->id],
                ['submitted_at' => now()]
            );

            // Upload RPP if provided
            $rppRecord = null;
            $asesmenRecord = null;
            $administrasiRecord = null;
            if ($request->hasFile('rpp')) {
                $rppFile = $request->file('rpp');
                $rppContents = file_get_contents($rppFile->getRealPath());
                $rppMeta = $drive->uploadFile($dateFolderId, $rppFile->getClientOriginalName(), $rppFile->getMimeType(), $rppContents);
                // Derive page count for PDF if possible (simple heuristic)
                $pageCount = null;
                if (strtolower($rppFile->getClientOriginalExtension()) === 'pdf') {
                    $matches = [];
                    preg_match_all('/\/Type\s*\/Page(?!s)/', $rppContents, $matches);
                    if (!empty($matches[0])) { $pageCount = count($matches[0]); }
                }
                $rppRecord = File::create([
                    'owner_user_id' => $user->id,
                    'schedule_id' => $schedule->id,
                    'google_file_id' => $rppMeta['id'],
                    'name' => $rppMeta['name'],
                    'mime' => $rppMeta['mime'],
                    'web_view_link' => $rppMeta['webViewLink'] ?? null,
                    'web_content_link' => $rppMeta['webContentLink'] ?? null,
                    'folder_id' => $dateFolderId,
                    'extra' => [
                        'size' => $rppMeta['size'] ?? null,
                        'pageCount' => $pageCount,
                    ],
                ]);
                // Keep reference to previous RPP to delete after success
                $oldRpp = $submission->rppFile;
                $submission->rpp_file_id = $rppRecord->id;
                // After pointer update, remove old Drive file and DB record
                if ($oldRpp) {
                    try { $drive->deleteFile($oldRpp->google_file_id); } catch (\Throwable $e) { Log::warning('Failed to delete old RPP from Drive', ['error' => $e->getMessage()]); }
                    try { $oldRpp->delete(); } catch (\Throwable $e) { Log::warning('Failed to delete old RPP DB record', ['error' => $e->getMessage()]); }
                }
            }

            if ($request->hasFile('asesmen')) {
                $asesmenFile = $request->file('asesmen');
                $asesmenContents = file_get_contents($asesmenFile->getRealPath());
                $asesmenMeta = $drive->uploadFile($dateFolderId, $asesmenFile->getClientOriginalName(), $asesmenFile->getMimeType(), $asesmenContents);
                $pageCount = null;
                if (strtolower($asesmenFile->getClientOriginalExtension()) === 'pdf') {
                    $matches = [];
                    preg_match_all('/\/Type\s*\/Page(?!s)/', $asesmenContents, $matches);
                    if (!empty($matches[0])) { $pageCount = count($matches[0]); }
                }
                $asesmenRecord = File::create([
                    'owner_user_id' => $user->id,
                    'schedule_id' => $schedule->id,
                    'google_file_id' => $asesmenMeta['id'],
                    'name' => $asesmenMeta['name'],
                    'mime' => $asesmenMeta['mime'],
                    'web_view_link' => $asesmenMeta['webViewLink'] ?? null,
                    'web_content_link' => $asesmenMeta['webContentLink'] ?? null,
                    'folder_id' => $dateFolderId,
                    'extra' => [
                        'size' => $asesmenMeta['size'] ?? null,
                        'pageCount' => $pageCount,
                    ],
                ]);
                $oldAsesmen = $submission->asesmenFile;
                $submission->asesmen_file_id = $asesmenRecord->id;
                if ($oldAsesmen) {
                    try { $drive->deleteFile($oldAsesmen->google_file_id); } catch (\Throwable $e) { Log::warning('Failed to delete old Asesmen from Drive', ['error' => $e->getMessage()]); }
                    try { $oldAsesmen->delete(); } catch (\Throwable $e) { Log::warning('Failed to delete old Asesmen DB record', ['error' => $e->getMessage()]); }
                }
            }

            if ($request->hasFile('administrasi')) {
                $administrasiFile = $request->file('administrasi');
                $administrasiContents = file_get_contents($administrasiFile->getRealPath());
                $administrasiMeta = $drive->uploadFile($dateFolderId, $administrasiFile->getClientOriginalName(), $administrasiFile->getMimeType(), $administrasiContents);
                $pageCount = null;
                if (strtolower($administrasiFile->getClientOriginalExtension()) === 'pdf') {
                    $matches = [];
                    preg_match_all('/\/Type\s*\/Page(?!s)/', $administrasiContents, $matches);
                    if (!empty($matches[0])) { $pageCount = count($matches[0]); }
                }
                $administrasiRecord = File::create([
                    'owner_user_id' => $user->id,
                    'schedule_id' => $schedule->id,
                    'google_file_id' => $administrasiMeta['id'],
                    'name' => $administrasiMeta['name'],
                    'mime' => $administrasiMeta['mime'],
                    'web_view_link' => $administrasiMeta['webViewLink'] ?? null,
                    'web_content_link' => $administrasiMeta['webContentLink'] ?? null,
                    'folder_id' => $dateFolderId,
                    'extra' => [
                        'size' => $administrasiMeta['size'] ?? null,
                        'pageCount' => $pageCount,
                    ],
                ]);
                $oldAdministrasi = $submission->administrasiFile;
                $submission->administrasi_file_id = $administrasiRecord->id;
                if ($oldAdministrasi) {
                    try { $drive->deleteFile($oldAdministrasi->google_file_id); } catch (\Throwable $e) { Log::warning('Failed to delete old Administrasi from Drive', ['error' => $e->getMessage()]); }
                    try { $oldAdministrasi->delete(); } catch (\Throwable $e) { Log::warning('Failed to delete old Administrasi DB record', ['error' => $e->getMessage()]); }
                }
            }

            // Upload Video
            $videoRecord = null;
            if ($request->hasFile('video')) {
                $videoFile = $request->file('video');
                $videoContents = file_get_contents($videoFile->getRealPath());
                $videoMeta = $drive->uploadFile($dateFolderId, $videoFile->getClientOriginalName(), $videoFile->getMimeType(), $videoContents);

            // Validate duration ~30 minutes max
                $durationMs = 0;
                if (!empty($videoMeta['videoMediaMetadata']) && isset($videoMeta['videoMediaMetadata']['durationMillis'])) {
                    $durationMs = (int) $videoMeta['videoMediaMetadata']['durationMillis'];
                }
                // Fallback: use client-provided duration if Drive metadata is missing
                if ($durationMs <= 0) {
                    $clientDur = (int) $request->input('video_duration_ms', 0);
                    if ($clientDur > 0) { $durationMs = $clientDur; }
                }
                $maxDurationMs = 30 * 60 * 1000; // 30 minutes
                if ($durationMs > 0 && $durationMs > $maxDurationMs) {
                // Delete file if too long
                try {
                    $drive->getClient(); // ensure client exists
                    $driveFileService = new \Google\Service\Drive($drive->getClient());
                    $driveFileService->files->delete($videoMeta['id']);
                } catch (\Throwable $e) {
                    Log::warning('Failed to delete too-long video from Drive', ['error' => $e->getMessage()]);
                }
                    return back()->withErrors(['video' => 'Durasi video melebihi 30 menit. Mohon unggah video yang lebih pendek.'])->withInput();
                }

                // Prepare video metadata payload (include duration if available)
                $videoVm = $videoMeta['videoMediaMetadata'] ?? [];
                if ($durationMs > 0) { $videoVm['durationMillis'] = $durationMs; }
                $videoRecord = File::create([
                    'owner_user_id' => $user->id,
                    'schedule_id' => $schedule->id,
                    'google_file_id' => $videoMeta['id'],
                    'name' => $videoMeta['name'],
                    'mime' => $videoMeta['mime'],
                    'web_view_link' => $videoMeta['webViewLink'] ?? null,
                    'web_content_link' => $videoMeta['webContentLink'] ?? null,
                    'folder_id' => $dateFolderId,
                    'extra' => [
                        'size' => $videoMeta['size'] ?? null,
                        'videoMediaMetadata' => $videoVm ?: null
                    ],
                ]);
                // Keep reference to previous Video to delete after success
                $oldVideo = $submission->videoFile;
                $submission->video_file_id = $videoRecord->id;
                // After pointer update, remove old Drive file and DB record
                if ($oldVideo) {
                    try { $drive->deleteFile($oldVideo->google_file_id); } catch (\Throwable $e) { Log::warning('Failed to delete old Video from Drive', ['error' => $e->getMessage()]); }
                    try { $oldVideo->delete(); } catch (\Throwable $e) { Log::warning('Failed to delete old Video DB record', ['error' => $e->getMessage()]); }
                }
            }

            // Share folder & files to supervisor as commenter
            try {
                $drive->shareWith($dateFolderId, $schedule->supervisor->email, 'commenter');
                if ($rppRecord) $drive->shareWith($rppRecord->google_file_id, $schedule->supervisor->email, 'commenter');
                if ($videoRecord) $drive->shareWith($videoRecord->google_file_id, $schedule->supervisor->email, 'commenter');
                if ($asesmenRecord) $drive->shareWith($asesmenRecord->google_file_id, $schedule->supervisor->email, 'commenter');
                if ($administrasiRecord) $drive->shareWith($administrasiRecord->google_file_id, $schedule->supervisor->email, 'commenter');
            } catch (\Throwable $e) {
                Log::warning('Failed to share drive files with supervisor', ['error' => $e->getMessage()]);
            }

            $submission->submitted_at = $submission->submitted_at ?: now();
            $submission->save();
            // Attempt to mark schedule completed if all evaluations are present
            try {
                $schedule->checkAndMarkCompleted();
            } catch (\Throwable $e) {
                // ignore; not critical for upload
            }

            // Optionally persist refreshed tokens
            $newToken = $drive->getClient()->getAccessToken();
            if (!empty($newToken['access_token'])) {
                $user->google_access_token = $newToken['access_token'];
                if (!empty($newToken['expires_in'])) {
                    $user->google_token_expires_at = now()->addSeconds((int)$newToken['expires_in']);
                }
                $user->save();
            }

            return redirect()->route('guru.submissions.show', $schedule)->with('success', 'Berkas berhasil diunggah.');
        } catch (\Throwable $e) {
            Log::error('Submission upload error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors(['upload' => 'Gagal mengunggah berkas. Coba lagi.'])->withInput();
        }
        }

    public function deleteFile(Request $request, Schedule $schedule, string $kind)
    {
        $user = Auth::user();
        if ($user->id !== $schedule->teacher_id) {
            abort(403);
        }
        $submission = $schedule->submission;
        if (!$submission) return back()->with('success', 'Tidak ada berkas untuk dihapus.');
        if (!in_array($kind, ['rpp','video','asesmen','administrasi'], true)) abort(404);
        try {
            $drive = new GoogleDriveService($user->google_access_token, $user->google_refresh_token);
            if ($kind === 'rpp' && $submission->rppFile) {
                $file = $submission->rppFile;
                // Delete from Drive
                try { $drive->deleteFile($file->google_file_id); } catch (\Throwable $e) { Log::warning('Failed to delete RPP from Drive', ['error' => $e->getMessage()]); }
                $file->delete();
                $submission->rpp_file_id = null;
            }
            if ($kind === 'video' && $submission->videoFile) {
                $file = $submission->videoFile;
                try { $drive->deleteFile($file->google_file_id); } catch (\Throwable $e) { Log::warning('Failed to delete Video from Drive', ['error' => $e->getMessage()]); }
                $file->delete();
                $submission->video_file_id = null;
            }
            if ($kind === 'asesmen' && $submission->asesmenFile) {
                $file = $submission->asesmenFile;
                try { $drive->deleteFile($file->google_file_id); } catch (\Throwable $e) { Log::warning('Failed to delete Asesmen from Drive', ['error' => $e->getMessage()]); }
                $file->delete();
                $submission->asesmen_file_id = null;
            }
            if ($kind === 'administrasi' && $submission->administrasiFile) {
                $file = $submission->administrasiFile;
                try { $drive->deleteFile($file->google_file_id); } catch (\Throwable $e) { Log::warning('Failed to delete Administrasi from Drive', ['error' => $e->getMessage()]); }
                $file->delete();
                $submission->administrasi_file_id = null;
            }
            $submission->save();
        } catch (\Throwable $e) {
            Log::warning('deleteFile error: '.$e->getMessage());
        }
        return back()->with('success', 'Berkas dihapus.');
    }
}
