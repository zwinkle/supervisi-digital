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
                // Only refresh from Drive if it's not an external link
                if (empty($vid->extra['is_external_link']) && (empty($vid->extra['videoMediaMetadata']) || empty($vid->web_view_link) || empty($vid->extra['size']))) {
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
            'video_link' => ['nullable', 'url', 'max:500'],
            'asesmen' => ['sometimes', 'file', 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'max:20480'],
            'administrasi' => ['sometimes', 'file', 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'max:20480'],
        ], [
            'rpp.required' => 'Harap unggah berkas RPP (PDF/DOC/DOCX). Maksimal 20MB.',
            'rpp.file' => 'RPP harus berupa file yang valid.',
            'rpp.mimetypes' => 'Format RPP harus PDF/DOC/DOCX.',
            'rpp.max' => 'Ukuran RPP melebihi 20MB.',
            'rpp.uploaded' => 'Gagal mengunggah RPP. Periksa ukuran file dan batas server (upload_max_filesize/post_max_size).',

            'video_link.url' => 'Link video harus berupa URL yang valid (YouTube atau Google Drive).',
            'video_link.max' => 'Link video terlalu panjang.',

            'asesmen.file' => 'Asesmen harus berupa file yang valid.',
            'asesmen.mimetypes' => 'Format Asesmen harus PDF/DOC/DOCX.',
            'asesmen.max' => 'Ukuran Asesmen melebihi 20MB.',
            'asesmen.uploaded' => 'Gagal mengunggah Asesmen. Periksa ukuran file dan batas server (upload_max_filesize/post_max_size).',

            'administrasi.file' => 'Administrasi harus berupa file yang valid.',
            'administrasi.mimetypes' => 'Format Administrasi harus PDF/DOC/DOCX.',
            'administrasi.max' => 'Ukuran Administrasi melebihi 20MB.',
            'administrasi.uploaded' => 'Gagal mengunggah Administrasi. Periksa ukuran file dan batas server (upload_max_filesize/post_max_size).',
        ]);

        // Ensure at least one file or video link is provided
        if (!($request->hasFile('rpp') || $request->filled('video_link') || $request->hasFile('asesmen') || $request->hasFile('administrasi')))
        {
            return back()->withErrors(['upload' => 'Pilih minimal satu berkas untuk diunggah (RPP, Video Link, Asesmen, atau Administrasi).'])->withInput();
        }

        // Check if we need Google Drive (only if uploading files, not just video link)
        $needsDrive = $request->hasFile('rpp') || $request->hasFile('asesmen') || $request->hasFile('administrasi');
        
        // Ensure tokens exist only if we need Drive
        if ($needsDrive && !$user->google_access_token) {
            return back()->withErrors(['google' => 'Token Google tidak tersedia. Silakan login ulang dengan Google.']);
        }

        try {
            $drive = null;
            $dateFolderId = null;
            
            // Only create Drive service and folder if we have files to upload
            if ($needsDrive) {
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
                } catch (\Throwable $e) {
                    Log::error('Failed to initialize Drive service', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                    return back()->withErrors(['upload' => 'Gagal menghubungkan ke Google Drive. Token mungkin sudah expired. Silakan logout dan login kembali.'])->withInput();
                }
            }

            // Upsert submission container first
            $submission = Submission::firstOrCreate(
                ['schedule_id' => $schedule->id, 'teacher_id' => $user->id],
                ['submitted_at' => now()]
            );

            // Upload RPP if provided
            $rppRecord = null;
            $asesmenRecord = null;
            $administrasiRecord = null;
            $oldVideo = null; // Track old video for warning message
            
            if ($request->hasFile('rpp')) {
                if (!$drive) {
                    return back()->withErrors(['rpp' => 'Gagal menginisialisasi Google Drive.'])->withInput();
                }
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
                if (!$drive) {
                    return back()->withErrors(['asesmen' => 'Gagal menginisialisasi Google Drive.'])->withInput();
                }
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
                if (!$drive) {
                    return back()->withErrors(['administrasi' => 'Gagal menginisialisasi Google Drive.'])->withInput();
                }
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

            // Handle Video Link
            $videoRecord = null;
            if ($request->filled('video_link')) {
                $videoLink = trim($request->input('video_link'));
                
                // Validate that it's YouTube or Google Drive link
                // Support various YouTube formats: youtube.com, youtu.be, with or without www/https
                $youtubeMatches = [];
                $isYoutube = preg_match('/(?:(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11}))/i', $videoLink, $youtubeMatches);
                $isGoogleDrive = preg_match('/(?:https?:\/\/)?(?:www\.)?drive\.google\.com/i', $videoLink);
                
                if (!$isYoutube && !$isGoogleDrive) {
                    return back()->withErrors(['video_link' => 'Link video harus dari YouTube atau Google Drive.'])->withInput();
                }
                
                // Extract video name from URL
                $videoName = 'Video Pembelajaran';
                $videoId = null;
                
                if ($isYoutube && !empty($youtubeMatches[1])) {
                    $videoId = $youtubeMatches[1];
                    $videoName = 'YouTube: ' . $videoId;
                    
                    // Try to get video title from YouTube oEmbed API (no API key needed)
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
                        // Fallback to video ID if API fails
                        Log::info('Failed to fetch YouTube title', ['error' => $e->getMessage()]);
                    }
                } elseif ($isGoogleDrive) {
                    // Extract file ID from Google Drive URL
                    $driveMatches = [];
                    if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $videoLink, $driveMatches)) {
                        $videoName = 'Google Drive: ' . substr($driveMatches[1], 0, 10) . '...';
                    } elseif (preg_match('/[?&]id=([a-zA-Z0-9_-]+)/', $videoLink, $driveMatches)) {
                        $videoName = 'Google Drive: ' . substr($driveMatches[1], 0, 10) . '...';
                    } else {
                        $videoName = 'Google Drive Video';
                    }
                }
                
                $videoRecord = File::create([
                    'owner_user_id' => $user->id,
                    'schedule_id' => $schedule->id,
                    'google_file_id' => null, // No Google file ID for external links
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
                
                // Keep reference to previous Video to delete after success
                $oldVideo = $submission->videoFile;
                $submission->video_file_id = $videoRecord->id;
                
                // After pointer update, remove old video record
                if ($oldVideo) {
                    // Only delete from Drive if it's not an external link
                    if ($oldVideo->google_file_id && empty($oldVideo->extra['is_external_link'])) {
                        // Initialize Drive service if not already done (for video link only uploads)
                        if (!$drive && $user->google_access_token) {
                            try {
                                $drive = new GoogleDriveService($user->google_access_token, $user->google_refresh_token);
                                Log::info('Initialized Drive service for old video deletion', ['old_video_id' => $oldVideo->google_file_id]);
                            } catch (\Throwable $e) {
                                Log::error('Failed to initialize Drive for old video deletion', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                            }
                        }
                        if ($drive) {
                            try { 
                                $drive->deleteFile($oldVideo->google_file_id);
                                Log::info('Successfully deleted old video from Drive', ['file_id' => $oldVideo->google_file_id]);
                            } catch (\Throwable $e) { 
                                Log::error('Failed to delete old Video from Drive', ['error' => $e->getMessage(), 'file_id' => $oldVideo->google_file_id, 'trace' => $e->getTraceAsString()]);
                            }
                        } else {
                            Log::warning('Drive service not available for old video deletion', ['old_video_id' => $oldVideo->google_file_id]);
                        }
                    }
                    try { 
                        $oldVideo->delete();
                        Log::info('Successfully deleted old video DB record', ['id' => $oldVideo->id]);
                    } catch (\Throwable $e) { 
                        Log::error('Failed to delete old Video DB record', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                    }
                }
            }

            // Share folder & files to supervisor as commenter (skip external video links)
            // Only share if we have Drive service initialized
            if ($drive && $dateFolderId) {
                try {
                    $drive->shareWith($dateFolderId, $schedule->supervisor->email, 'commenter');
                    if ($rppRecord) $drive->shareWith($rppRecord->google_file_id, $schedule->supervisor->email, 'commenter');
                    if ($videoRecord && $videoRecord->google_file_id) $drive->shareWith($videoRecord->google_file_id, $schedule->supervisor->email, 'commenter');
                    if ($asesmenRecord) $drive->shareWith($asesmenRecord->google_file_id, $schedule->supervisor->email, 'commenter');
                    if ($administrasiRecord) $drive->shareWith($administrasiRecord->google_file_id, $schedule->supervisor->email, 'commenter');
                } catch (\Throwable $e) {
                    Log::warning('Failed to share drive files with supervisor', ['error' => $e->getMessage()]);
                }
            }

            $submission->submitted_at = $submission->submitted_at ?: now();
            $submission->save();
            // Attempt to mark schedule completed if all evaluations are present
            try {
                $schedule->checkAndMarkCompleted();
            } catch (\Throwable $e) {
                // ignore; not critical for upload
            }

            // Optionally persist refreshed tokens (only if Drive was used)
            if ($drive) {
                $newToken = $drive->getClient()->getAccessToken();
                if (!empty($newToken['access_token'])) {
                    $user->google_access_token = $newToken['access_token'];
                    if (!empty($newToken['expires_in'])) {
                        $user->google_token_expires_at = now()->addSeconds((int)$newToken['expires_in']);
                    }
                    $user->save();
                }
            }

            // Check if there were any warnings about old video deletion
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
            $drive = null;
            $needsDrive = false;
            
            // Check if we need Drive service
            if ($kind === 'rpp' && $submission->rppFile && $submission->rppFile->google_file_id) {
                $needsDrive = true;
            } elseif ($kind === 'video' && $submission->videoFile && $submission->videoFile->google_file_id && empty($submission->videoFile->extra['is_external_link'])) {
                $needsDrive = true;
            } elseif ($kind === 'asesmen' && $submission->asesmenFile && $submission->asesmenFile->google_file_id) {
                $needsDrive = true;
            } elseif ($kind === 'administrasi' && $submission->administrasiFile && $submission->administrasiFile->google_file_id) {
                $needsDrive = true;
            }
            
            // Initialize Drive only if needed
            if ($needsDrive && $user->google_access_token) {
                try {
                    $drive = new GoogleDriveService($user->google_access_token, $user->google_refresh_token);
                } catch (\Throwable $e) {
                    Log::error('Failed to initialize Drive service for delete', ['error' => $e->getMessage()]);
                    // Continue without Drive - will only delete from database
                    $drive = null;
                }
            }
            
            if ($kind === 'rpp' && $submission->rppFile) {
                $file = $submission->rppFile;
                // Delete from Drive
                if ($drive && $file->google_file_id) {
                    try { $drive->deleteFile($file->google_file_id); } catch (\Throwable $e) { Log::warning('Failed to delete RPP from Drive', ['error' => $e->getMessage()]); }
                }
                $file->delete();
                $submission->rpp_file_id = null;
            }
            
            if ($kind === 'video' && $submission->videoFile) {
                $file = $submission->videoFile;
                // Only delete from Drive if it's not an external link
                if ($drive && $file->google_file_id && empty($file->extra['is_external_link'])) {
                    try { $drive->deleteFile($file->google_file_id); } catch (\Throwable $e) { Log::warning('Failed to delete Video from Drive', ['error' => $e->getMessage()]); }
                }
                $file->delete();
                $submission->video_file_id = null;
            }
            
            if ($kind === 'asesmen' && $submission->asesmenFile) {
                $file = $submission->asesmenFile;
                if ($drive && $file->google_file_id) {
                    try { $drive->deleteFile($file->google_file_id); } catch (\Throwable $e) { Log::warning('Failed to delete Asesmen from Drive', ['error' => $e->getMessage()]); }
                }
                $file->delete();
                $submission->asesmen_file_id = null;
            }
            
            if ($kind === 'administrasi' && $submission->administrasiFile) {
                $file = $submission->administrasiFile;
                if ($drive && $file->google_file_id) {
                    try { $drive->deleteFile($file->google_file_id); } catch (\Throwable $e) { Log::warning('Failed to delete Administrasi from Drive', ['error' => $e->getMessage()]); }
                }
                $file->delete();
                $submission->administrasi_file_id = null;
            }
            
            // PENTING: Save submission setelah update foreign key
            $submission->save();
            
            return back()->with('success', 'Berkas berhasil dihapus.');
        } catch (\Throwable $e) {
            Log::error('deleteFile error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors(['delete' => 'Gagal menghapus berkas. Silakan coba lagi.']);
        }
    }
}
