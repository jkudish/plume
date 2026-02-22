<?php

declare(strict_types=1);

namespace Plume\Console\Commands;

use Illuminate\Console\Command;
use Plume\Console\Concerns\ResolvesXClient;
use Plume\Console\Concerns\SupportsJsonOutput;

class UploadCommand extends Command
{
    use ResolvesXClient;
    use SupportsJsonOutput;

    /** @var string */
    protected $signature = 'plume:upload {file : Path to the media file} {--alt-text= : Alt text for the media} {--format=table}';

    /** @var string */
    protected $description = 'Upload media to X';

    public function handle(): int
    {
        $client = $this->resolveClient();

        if (is_int($client)) {
            return $client;
        }

        /** @var string $file */
        $file = $this->argument('file');

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        $mimeType = mime_content_type($file);

        if ($mimeType === false) {
            $this->error("Could not determine MIME type for: {$file}");

            return self::FAILURE;
        }

        try {
            $response = $client->uploadMedia($file, $mimeType);

            $altText = $this->option('alt-text');
            if (is_string($altText) && $altText !== '') {
                $client->setMediaMetadata($response['media_id'], $altText);
            }
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        if ($this->outputJson($response)) {
            return self::SUCCESS;
        }

        $this->info('Media uploaded successfully.');
        $this->table(['Field', 'Value'], [
            ['Media ID', $response['media_id']],
        ]);

        return self::SUCCESS;
    }
}
