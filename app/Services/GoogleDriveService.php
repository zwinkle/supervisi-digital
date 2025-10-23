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
        $this->client->setAccessToken(is_array($accessToken) ? $accessToken : ['access_token' => $accessToken]);
        if ($refreshToken) {
            $token = $this->client->getAccessToken() ?: [];
            $token['refresh_token'] = $refreshToken;
            $this->client->setAccessToken($token);
        }
        // Refresh token if expired and refresh token is available
        if ($refreshToken && $this->client->isAccessTokenExpired()) {
            $newToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
            // Ensure refresh_token persists on some Google libs responses
            if (!isset($newToken['refresh_token'])) {
                $newToken['refresh_token'] = $refreshToken;
            }
            $this->client->setAccessToken($newToken);
        }
        $this->drive = new GoogleDrive($this->client);
    }

    public function getClient(): GoogleClient
    {
        return $this->client;
    }

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
