<?php

declare(strict_types=1);

namespace Plume\Console\Commands;

use Illuminate\Console\Command;
use Plume\Console\Concerns\ResolvesXClient;
use Plume\Console\Concerns\SupportsJsonOutput;

class PostCommand extends Command
{
    use ResolvesXClient;
    use SupportsJsonOutput;

    /** @var string */
    protected $signature = 'plume:post {--text= : Post text} {--reply-to= : Tweet ID to reply to} {--quote= : Tweet ID to quote} {--format=table}';

    /** @var string */
    protected $description = 'Create a new post';

    public function handle(): int
    {
        $client = $this->resolveClient();

        if (is_int($client)) {
            return $client;
        }

        $text = $this->option('text');

        if (! is_string($text) || $text === '') {
            $this->error('The --text option is required.');

            return self::FAILURE;
        }

        /** @var array<string, mixed> $options */
        $options = [];

        $replyTo = $this->option('reply-to');

        if (is_string($replyTo) && $replyTo !== '') {
            $options['reply'] = ['in_reply_to_tweet_id' => $replyTo];
        }

        $quote = $this->option('quote');

        if (is_string($quote) && $quote !== '') {
            $options['quote_tweet_id'] = $quote;
        }

        try {
            $post = $client->createPost($text, $options);
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        if ($this->outputJson($post)) {
            return self::SUCCESS;
        }

        $this->info("Post created successfully. ID: {$post->id}");
        $this->line("Text: {$post->text}");

        return self::SUCCESS;
    }
}
