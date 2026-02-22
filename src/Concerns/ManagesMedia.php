<?php

declare(strict_types=1);

namespace Plume\Concerns;

trait ManagesMedia
{
    /**
     * @return array{media_id: string}
     */
    public function uploadMedia(string $filePath, string $mediaType, ?string $mediaCategory = null): array
    {
        if (! is_file($filePath) || ! is_readable($filePath)) {
            throw new \RuntimeException("File does not exist or is not readable: {$filePath}");
        }

        $contents = file_get_contents($filePath);

        if ($contents === false) {
            throw new \RuntimeException("Failed to read file: {$filePath}");
        }

        $data = [
            'media_data' => base64_encode($contents),
            'media_type' => $mediaType,
        ];

        if ($mediaCategory !== null) {
            $data['media_category'] = $mediaCategory;
        }

        /** @var array{media_id: string} $response */
        $response = $this->http->post('/2/media/upload', $data);

        return $response;
    }

    /**
     * @return array{media_id: string}
     */
    public function initChunkedUpload(int $totalBytes, string $mediaType, ?string $mediaCategory = null): array
    {
        $data = [
            'total_bytes' => $totalBytes,
            'media_type' => $mediaType,
        ];

        if ($mediaCategory !== null) {
            $data['media_category'] = $mediaCategory;
        }

        /** @var array{media_id: string} $response */
        $response = $this->http->post('/2/media/upload/initialize', $data);

        return $response;
    }

    public function appendChunk(string $mediaId, int $segmentIndex, string $chunkData): void
    {
        $this->http->post("/2/media/upload/{$mediaId}/append", [
            'segment_index' => $segmentIndex,
            'media_data' => base64_encode($chunkData),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function finalizeUpload(string $mediaId): array
    {
        return $this->http->post("/2/media/upload/{$mediaId}/finalize");
    }

    /**
     * @return array<string, mixed>
     */
    public function uploadStatus(string $mediaId): array
    {
        return $this->http->get('/2/media/upload', ['media_id' => $mediaId]);
    }

    public function setMediaMetadata(string $mediaId, ?string $altText = null): void
    {
        $data = ['id' => $mediaId];

        if ($altText !== null) {
            $data['metadata'] = ['alt_text' => $altText];
        }

        $this->http->post('/2/media/metadata', $data);
    }
}
