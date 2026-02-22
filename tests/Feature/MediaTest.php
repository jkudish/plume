<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Plume\Http\XHttpClient;
use Plume\XApiClient;

function mediaClient(): XApiClient
{
    return new XApiClient(new XHttpClient(
        baseUrl: 'https://api.x.com',
        timeout: 30,
        bearerToken: 'test-bearer',
    ));
}

it('initializes chunked upload', function (): void {
    Http::fake([
        'api.x.com/2/media/upload/initialize' => Http::response([
            'media_id' => '12345',
        ]),
    ]);

    $result = mediaClient()->initChunkedUpload(1024000, 'video/mp4', 'tweet_video');

    expect($result['media_id'])->toBe('12345');

    Http::assertSent(fn ($r) => $r['total_bytes'] === 1024000 && $r['media_type'] === 'video/mp4');
});

it('appends a chunk', function (): void {
    Http::fake([
        'api.x.com/2/media/upload/12345/append' => Http::response([]),
    ]);

    mediaClient()->appendChunk('12345', 0, 'chunk-data');

    Http::assertSent(fn ($r) => $r['segment_index'] === 0);
});

it('finalizes upload', function (): void {
    Http::fake([
        'api.x.com/2/media/upload/12345/finalize' => Http::response([
            'media_id' => '12345',
            'processing_info' => ['state' => 'pending'],
        ]),
    ]);

    $result = mediaClient()->finalizeUpload('12345');

    expect($result['media_id'])->toBe('12345');
});

it('checks upload status', function (): void {
    Http::fake([
        'api.x.com/2/media/upload*' => Http::response([
            'media_id' => '12345',
            'processing_info' => ['state' => 'succeeded'],
        ]),
    ]);

    $result = mediaClient()->uploadStatus('12345');

    expect($result['processing_info']['state'])->toBe('succeeded');
});

it('sets media metadata', function (): void {
    Http::fake([
        'api.x.com/2/media/metadata' => Http::response([]),
    ]);

    mediaClient()->setMediaMetadata('12345', 'A descriptive alt text');

    Http::assertSent(fn ($r) => $r['id'] === '12345' && $r['metadata']['alt_text'] === 'A descriptive alt text');
});
