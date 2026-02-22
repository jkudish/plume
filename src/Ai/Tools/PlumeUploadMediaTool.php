<?php

declare(strict_types=1);

namespace Plume\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Plume\Facades\X;
use Throwable;

class PlumeUploadMediaTool implements Tool
{
    public static function toolId(): string
    {
        return 'plume:upload-media';
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): string
    {
        return 'Upload media (image, video, or GIF) for use in tweets.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): string
    {
        /** @var string $filePath */
        $filePath = (string) $request->string('file_path');
        /** @var string $mediaType */
        $mediaType = (string) $request->string('media_type');

        try {
            $result = X::uploadMedia($filePath, $mediaType);

            return json_encode($result, JSON_PRETTY_PRINT) ?: 'No data.';
        } catch (Throwable $e) {
            return "Error uploading media: {$e->getMessage()}";
        }
    }

    /**
     * Get the tool's schema definition.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'file_path' => $schema
                ->string()
                ->description('The local file path of the media to upload.')
                ->required(),
            'media_type' => $schema
                ->string()
                ->description('The MIME type of the media (e.g., image/jpeg, image/png, video/mp4, image/gif).')
                ->required(),
        ];
    }
}
