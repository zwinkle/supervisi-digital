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
    public function showForm(Schedule $schedule)
    {
        $user = Auth::user();
        // Basic authorization: only the assigned teacher or supervisor can access
        if ($user->id !== $schedule->teacher_id && $user->id !== $schedule->supervisor_id) {
            abort(403);
        }
        $schedule->loadMissing(['submission.documents.file','submission.videoFile']);
        return view('submissions.upload', compact('schedule'));
    }

        public function status(Request $request, Schedule $schedule)
    {
        $user = Auth::user();
        if ($user->id !== $schedule->teacher_id && $user->id !== $schedule->supervisor_id) {
            abort(403);
        }

        $schedule->loadMissing(['submission.documents.file','submission.videoFile']);
        $submission = $schedule->submission;

        $categories = SubmissionDocument::ALLOWED_CATEGORIES;
        $documentsData = [];
        foreach ($categories as $category) {
            $documentsData[$category] = [];
        }

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

        try {
            if ($submission) {
                foreach ($categories as $category) {
                    $docs = $submission->documents->where('category', $category)->values();
                    $data['documents'][$category] = $docs->map(function ($doc) use (&$drive, $user) {
                        $file = $doc->file;
                        if (!$file) {
                            return null;
                        }

                        if ($file->google_file_id && (empty($file->web_view_link) || empty($file->extra['size']))) {
                            if ($user->google_access_token) {
                                $drive = $drive ?? new GoogleDriveService($user->google_access_token, $user->google_refresh_token);
                                $remote = $drive->getFile($file->google_file_id, 'id, name, webViewLink, webContentLink, mimeType, size');
                                $file->web_view_link = $remote->webViewLink ?? $file->web_view_link;
                                $file->web_content_link = $remote->webContentLink ?? $file->web_content_link;
                                $extra = $file->extra ?? [];
                                if (isset($remote->size)) {
                                    $extra['size'] = (int) $remote->size;
                                }
                                $file->extra = $extra;
                                $file->save();
                            }
                        }

                        return [
                            'id' => $doc->id,
                            'name' => $file->name,
                            'size' => $file->extra['size'] ?? null,
                            'pageCount' => $file->extra['pageCount'] ?? null,
                            'webViewLink' => $file->web_view_link,
                        ];
                    })->filter()->values()->all();
                }

                if ($submission->videoFile) {
                    $vid = $submission->videoFile;
                    if (empty($vid->extra['is_external_link']) && (empty($vid->extra['videoMediaMetadata']) || empty($vid->web_view_link) || empty($vid->extra['size']))) {
                        if ($user->google_access_token) {
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

        $rawInputs = [
            'rpp' => $request->file('rpp', []),
            'asesmen' => $request->file('asesmen', []),
            'administrasi' => $request->file('administrasi', []),
        ];

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

        $incomingFiles = [];
        foreach ($normalizedFiles as $category => $files) {
            foreach ($files as $file) {
                $incomingFiles[] = ['category' => $category, 'file' => $file];
            }
        }

        if (empty($incomingFiles) && !$request->filled('video_link')) {
            return back()->withErrors(['upload' => 'Pilih minimal satu berkas untuk diunggah (RPP, Asesmen, Administrasi, atau tautan Video).'])->withInput();
        }

        $submission = Submission::firstOrCreate(
            ['schedule_id' => $schedule->id, 'teacher_id' => $user->id],
            ['submitted_at' => now()]
        );

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

        $needsDrive = !empty($incomingFiles);
        if ($needsDrive && !$user->google_access_token) {
            return back()->withErrors(['google' => 'Token Google tidak tersedia. Silakan login ulang dengan Google.']);
        }

        try {
            $drive = null;
            $dateFolderId = null;

            if ($needsDrive) {
                try {
                    $drive = new GoogleDriveService($user->google_access_token, $user->google_refresh_token);
                    $rootId = $drive->ensureRootFolder();
                    $dateStr = $schedule->date->format('d-m-Y');
                    $rawTitle = $schedule->title ?: 'Sesi Supervisi';
                    $cleanTitle = preg_replace('/[\\\\\/]/', '-', $rawTitle);
                    $cleanTitle = trim(preg_replace('/\s+/', ' ', $cleanTitle));
                    $folderName = $cleanTitle . ' - ' . $dateStr;
                    $dateFolderId = $drive->ensureChildFolder($rootId, $folderName);
                } catch (\Throwable $e) {
                    Log::error('Failed to initialize Drive service', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                    return back()->withErrors(['upload' => 'Gagal menghubungkan ke Google Drive. Token mungkin sudah expired. Silakan logout dan login kembali.'])->withInput();
                }
            }

            $newFileRecords = [];

            foreach ($incomingFiles as $payload) {
                if (!$drive) {
                    return back()->withErrors(['upload' => 'Gagal menginisialisasi Google Drive.'])->withInput();
                }

                /** @var \Illuminate\Http\UploadedFile $docFile */
                $docFile = $payload['file'];
                $category = $payload['category'];
                $contents = file_get_contents($docFile->getRealPath());
                $meta = $drive->uploadFile($dateFolderId, $docFile->getClientOriginalName(), $docFile->getMimeType(), $contents);
                $pageCount = null;
                if (strtolower($docFile->getClientOriginalExtension()) === 'pdf') {
                    $matches = [];
                    preg_match_all('/\/Type\s*\/Page(?!s)/', $contents, $matches);
                    if (!empty($matches[0])) {
                        $pageCount = count($matches[0]);
                    }
                }

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

                SubmissionDocument::create([
                    'submission_id' => $submission->id,
                    'file_id' => $fileRecord->id,
                    'category' => $category,
                ]);

                $newFileRecords[] = $fileRecord;
            }

            $videoRecord = null;
            $oldVideo = null;
            if ($request->filled('video_link')) {
                $videoLink = trim($request->input('video_link'));
                $youtubeMatches = [];
                $isYoutube = preg_match('/(?:(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11}))/i', $videoLink, $youtubeMatches);
                $isGoogleDrive = preg_match('/(?:https?:\/\/)?(?:www\.)?drive\.google\.com/i', $videoLink);

                if (!$isYoutube && !$isGoogleDrive) {
                    return back()->withErrors(['video_link' => 'Link video harus dari YouTube atau Google Drive.'])->withInput();
                }

                $videoName = 'Video Pembelajaran';
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

                $videoRecord = File::create([
                    'owner_user_id' => $user->id,
                    'schedule_id' => $schedule->id,
                    'google_file_id' => null,
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

                $oldVideo = $submission->videoFile;
                $submission->video_file_id = $videoRecord->id;
                if ($oldVideo) {
                    if ($oldVideo->google_file_id && empty($oldVideo->extra['is_external_link'])) {
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

            try {
                $schedule->checkAndMarkCompleted();
            } catch (\Throwable $e) {
                // ignore
            }

            if ($drive) {
                $newToken = $drive->getClient()->getAccessToken();
                if (!empty($newToken['access_token'])) {
                    $user->google_access_token = $newToken['access_token'];
                    if (!empty($newToken['expires_in'])) {
                        $user->google_token_expires_at = now()->addSeconds((int) $newToken['expires_in']);
                    }
                    $user->save();
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

        if ($file->google_file_id && empty($file->extra['is_external_link']) && $user->google_access_token) {
            try {
                $drive = new GoogleDriveService($user->google_access_token, $user->google_refresh_token);
            } catch (\Throwable $e) {
                Log::error('Failed to initialize Drive service for video delete', ['error' => $e->getMessage()]);
            }
            if ($drive) {
                try {
                    $drive->deleteFile($file->google_file_id);
                } catch (\Throwable $e) {
                    Log::warning('Failed to delete Video from Drive', ['error' => $e->getMessage()]);
                }
            }
        }

        try {
            $file->delete();
        } catch (\Throwable $e) {
            Log::error('Failed to delete Video DB record', ['error' => $e->getMessage()]);
        }
        $submission->video_file_id = null;
        $submission->save();

        return back()->with('success', 'Video berhasil dihapus.');
    }

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

        if ($file && $file->google_file_id && $user->google_access_token) {
            try {
                $drive = new GoogleDriveService($user->google_access_token, $user->google_refresh_token);
            } catch (\Throwable $e) {
                Log::error('Failed to initialize Drive service for document delete', ['error' => $e->getMessage()]);
                $drive = null;
            }
            if ($drive) {
                try {
                    $drive->deleteFile($file->google_file_id);
                } catch (\Throwable $e) {
                    Log::warning('Failed to delete document from Drive', ['error' => $e->getMessage(), 'file_id' => $file->google_file_id]);
                }
            }
        }

        if ($file) {
            try {
                $file->delete();
            } catch (\Throwable $e) {
                Log::warning('Failed to delete document DB record', ['error' => $e->getMessage()]);
            }
        }

        $document->delete();

        return back()->with('success', 'Berkas berhasil dihapus.');
    }



}
