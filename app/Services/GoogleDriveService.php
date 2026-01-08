<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Config;

class GoogleDriveService
{
    protected GoogleClient $client;
    protected GoogleDrive $drive;

    /**
     * Constructor.
     * Menginisialisasi Google Client dengan config dan token.
     * Menangani renewal token jika refresh token tersedia.
     */

    public function __construct(string $accessToken, ?string $refreshToken = null)
    {
        $this->client = new GoogleClient();
        // Configure client ID/secret and redirect for token refresh
        $this->client->setClientId(Config::get('services.google.client_id'));
        $this->client->setClientSecret(Config::get('services.google.client_secret'));
        $this->client->setRedirectUri(Config::get('services.google.redirect'));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        // Set initial access token, including refresh_token if available
        $tokenPayload = $this->normaliseAccessToken($accessToken);
        $this->applyAccessToken($tokenPayload);
        if ($refreshToken) {
            $token = $this->client->getAccessToken() ?: [];
            $token['refresh_token'] = $refreshToken;
            $this->applyAccessToken($token);
        }
        // Always try to refresh token if refresh token is available
        if ($refreshToken) {
            try {
                if ($this->client->isAccessTokenExpired()) {
                    $newToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                    // Ensure refresh_token persists on some Google libs responses
                    if (!isset($newToken['refresh_token'])) {
                        $newToken['refresh_token'] = $refreshToken;
                    }
                    $this->applyAccessToken($newToken);
                }
            } catch (\Exception $e) {
                // If refresh fails, log the error but continue with current token
                \Log::warning('Google token refresh failed: ' . $e->getMessage());
            }
        }
        $this->drive = new GoogleDrive($this->client);
    }

    /**
     * Normalisasi format access token (string raw, JSON string, atau array).
     * Memastikan format sesuai standar Google Client library.
     */
    protected function normaliseAccessToken($accessToken)
    {
        if (is_array($accessToken)) {
            return $accessToken;
        }

        if (is_string($accessToken)) {
            $trimmed = trim($accessToken);
            if ($trimmed === '') {
                return ['access_token' => ''];
            }

            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && isset($decoded['access_token'])) {
                return $decoded;
            }

            return [
                'access_token' => $trimmed,
                'token_type' => 'Bearer',
            ];
        }

        throw new \InvalidArgumentException('Unsupported access token format');
    }

    /**
     * Menerapkan access token ke Google Client.
     * Menangani fallback jika format token tidak valid.
     */
    protected function applyAccessToken($token): void
    {
        // Ensure token is in the correct format
        if (is_string($token)) {
            // If it's a JSON string, decode it first
            $decoded = json_decode($token, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $token = $decoded;
            } else {
                // If it's a plain access token string, wrap it in array
                $token = [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ];
            }
        }
        
        // Ensure required fields exist
        if (is_array($token) && !isset($token['token_type'])) {
            $token['token_type'] = 'Bearer';
        }
        
        try {
            $this->client->setAccessToken($token);
        } catch (\InvalidArgumentException $e) {
            // Last resort: try as JSON string
            if (is_array($token)) {
                try {
                    $this->client->setAccessToken(json_encode($token, JSON_THROW_ON_ERROR));
                    return;
                } catch (\Throwable $jsonError) {
                    // If JSON encoding also fails, throw original error
                }
            }
            throw $e;
        }
    }

    public function getClient(): GoogleClient
    {
        return $this->client;
    }

    /**
     * Mengecek validitas token saat init.
     */
    public function isTokenValid(): bool
    {
        return !$this->client->isAccessTokenExpired();
    }

    /**
     * Mencoba refresh token jika expired.
     * Mengembalikan array token baru jika berhasil, atau null jika gagal.
     */
    public function getRefreshedToken(): ?array
    {
        $currentToken = $this->client->getAccessToken();
        $refreshToken = $currentToken['refresh_token'] ?? null;
        
        if (!$refreshToken) {
            return null;
        }

        try {
            if ($this->client->isAccessTokenExpired()) {
                $newToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                if (!isset($newToken['refresh_token'])) {
                    $newToken['refresh_token'] = $refreshToken;
                }
                $this->applyAccessToken($newToken);
                return $newToken;
            }
            return $currentToken;
        } catch (\Exception $e) {
            \Log::error('Failed to refresh Google token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Memastikan folder root "SUPERVISI DIGITAL" ada di Google Drive user.
     * Jika belum ada, akan dibuatkan.
     * @return string ID Folder Google Drive
     */
    public function ensureRootFolder(): string
    {
        // Ensure folder named "SUPERVISI DIGITAL" exists in user's root; return folderId
        $name = 'SUPERVISI DIGITAL';
        $existing = $this->drive->files->listFiles([
            'q' => sprintf("name='%s' and mimeType='application/vnd.google-apps.folder' and 'root' in parents and trashed=false", addslashes($name)),
            'fields' => 'files(id,name)'
        ]);
        if (!empty($existing->files)) {
            return $existing->files[0]->id;
        }
        $file = new DriveFile([
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => ['root']
        ]);
        $created = $this->drive->files->create($file, ['fields' => 'id']);
        return $created->id;
    }

    /**
     * Membuat folder tanggal (format DD-MM-YYYY) di dalam root folder.
     */
    public function ensureDateFolder(string $rootFolderId, string $dateDdMmYyyy): string
    {
        $q = sprintf("name='%s' and mimeType='application/vnd.google-apps.folder' and '%s' in parents and trashed=false", addslashes($dateDdMmYyyy), $rootFolderId);
        $existing = $this->drive->files->listFiles(['q' => $q, 'fields' => 'files(id,name)']);
        if (!empty($existing->files)) {
            return $existing->files[0]->id;
        }
        $file = new DriveFile([
            'name' => $dateDdMmYyyy,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$rootFolderId]
        ]);
        $created = $this->drive->files->create($file, ['fields' => 'id']);
        return $created->id;
    }

    /**
     * Membuat sub-folder generik (misal nama guru) di dalam parent folder.
     */
    public function ensureChildFolder(string $parentFolderId, string $name): string
    {
        $q = sprintf("name='%s' and mimeType='application/vnd.google-apps.folder' and '%s' in parents and trashed=false", addslashes($name), $parentFolderId);
        $existing = $this->drive->files->listFiles(['q' => $q, 'fields' => 'files(id,name)']);
        if (!empty($existing->files)) {
            return $existing->files[0]->id;
        }
        $file = new DriveFile([
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$parentFolderId]
        ]);
        $created = $this->drive->files->create($file, ['fields' => 'id']);
        return $created->id;
    }

    /**
     * Mengupload file ke folder Google Drive tertentu.
     * Mengembalikan metadata file termasuk link preview.
     */
    public function uploadFile(string $folderId, string $name, string $mimeType, string $contents): array
    {
        $file = new DriveFile([
            'name' => $name,
            'parents' => [$folderId],
        ]);
        $created = $this->drive->files->create($file, [
            'data' => $contents,
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
            'fields' => 'id, name, webViewLink, webContentLink, mimeType'
        ]);
        // Enable webViewLink generation
        $created = $this->drive->files->get($created->id, ['fields' => 'id, name, webViewLink, webContentLink, mimeType, size, videoMediaMetadata']);
        return [
            'id' => $created->id,
            'name' => $created->name,
            'mime' => $created->mimeType,
            'webViewLink' => $created->webViewLink ?? null,
            'webContentLink' => $created->webContentLink ?? null,
            'size' => isset($created->size) ? (int)$created->size : null,
            'videoMediaMetadata' => $created->videoMediaMetadata ?? null,
        ];
    }

    /**
     * Membagikan file/folder ke email tertentu (misal ke admin).
     */
    public function shareWith(string $fileOrFolderId, string $email, string $role = 'reader'): void
    {
        $permission = new \Google\Service\Drive\Permission([
            'type' => 'user',
            'role' => $role, // reader | commenter | writer
            'emailAddress' => $email,
        ]);
        $this->drive->permissions->create($fileOrFolderId, $permission, ['sendNotificationEmail' => false]);
    }

    public function getFile(string $fileId, string $fields = 'id, name, mimeType, webViewLink, videoMediaMetadata'): \Google\Service\Drive\DriveFile
    {
        return $this->drive->files->get($fileId, ['fields' => $fields]);
    }

    public function deleteFile(string $fileId): void
    {
        $this->drive->files->delete($fileId);
    }
}
