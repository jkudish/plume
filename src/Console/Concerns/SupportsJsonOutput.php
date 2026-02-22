<?php

declare(strict_types=1);

namespace Plume\Console\Concerns;

trait SupportsJsonOutput
{
    /**
     * Output data as JSON (pretty-printed) or return false if not JSON format.
     *
     * Only use in commands that define a {--format=table} option.
     */
    protected function outputJson(mixed $data): bool
    {
        if ($this->option('format') !== 'json') {
            return false;
        }

        $this->line((string) json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return true;
    }

    /**
     * Check if the current output format is JSON.
     */
    protected function isJsonFormat(): bool
    {
        return $this->option('format') === 'json';
    }
}
