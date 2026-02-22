<?php

declare(strict_types=1);

namespace Plume\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Plume\Facades\X;
use Throwable;

class PlumeFollowTool implements Tool
{
    public static function toolId(): string
    {
        return 'plume:follow';
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): string
    {
        return 'Follow a user by their user ID.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): string
    {
        /** @var string $targetUserId */
        $targetUserId = (string) $request->string('target_user_id');

        $userId = (string) $request->string('user_id');
        if ($userId === '') {
            $userId = (string) config('x.user_id', '');
        }
        if ($userId === '') {
            return 'No user ID configured. Set X_USER_ID in your .env or pass user_id parameter.';
        }

        try {
            X::follow($userId, $targetUserId);

            return "User {$targetUserId} followed successfully.";
        } catch (Throwable $e) {
            return "Error following user {$targetUserId}: {$e->getMessage()}";
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
            'target_user_id' => $schema
                ->string()
                ->description('The user ID of the account to follow.')
                ->required(),
        ];
    }
}
