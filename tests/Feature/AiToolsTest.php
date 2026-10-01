<?php

declare(strict_types=1);

use Illuminate\JsonSchema\JsonSchema;
use Laravel\Ai\AiServiceProvider;
use Laravel\Ai\AnonymousAgent;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Responses\Data\ToolCall;
use Laravel\Ai\Tools\Request;
use Plume\Ai\Tools\PlumeFetchTweetTool;
use Plume\Ai\Tools\PlumePostTweetTool;
use Plume\Data\Post;
use Plume\Facades\X;

beforeEach(function (): void {
    if (! interface_exists(Tool::class)) {
        $this->markTestSkipped('Laravel AI is optional and not installed on the Laravel 11 CI jobs.');
    }
});

it('resolves all tagged tools and serializes their schemas with the AI SDK', function (): void {
    $tools = iterator_to_array(app()->tagged('ai-tools'));

    expect($tools)->toHaveCount(15);

    foreach ($tools as $tool) {
        expect($tool)->toBeInstanceOf(Tool::class)
            ->and($tool->description())->not->toBeEmpty();

        $schema = JsonSchema::object(fn ($schema) => $tool->schema($schema))->toArray();

        expect($schema['type'])->toBe('object');
    }

    $schema = JsonSchema::object(fn ($schema) => (new PlumePostTweetTool)->schema($schema))->toArray();

    expect($schema['required'])->toBe(['text'])
        ->and($schema['properties']['text']['type'])->toBe('string')
        ->and($schema['properties']['reply_to']['type'])->toBe('string');
});

it('executes a Plume tool through the AI SDK generation loop', function (array $arguments, array $options): void {
    $this->app->register(AiServiceProvider::class);
    $fake = X::fake()->shouldReturn('createPost', new Post(id: '847', text: 'SDK integration'));

    AnonymousAgent::fake([
        new ToolCall('call_plume', 'PlumePostTweetTool', $arguments),
        'Posted successfully.',
    ])->preventStrayPrompts();

    $response = (new AnonymousAgent('Post to X.', [], [new PlumePostTweetTool]))
        ->prompt('Post SDK integration.', provider: 'openai');

    $fake->assertCalledTimes('createPost', 1);
    $fake->assertCalled('createPost', fn (array $args): bool => $args === ['SDK integration', $options]);

    expect($response->text)->toBe('Posted successfully.')
        ->and($response->toolResults)->toHaveCount(1)
        ->and(json_decode($response->toolResults[0]->result, true, flags: JSON_THROW_ON_ERROR))
        ->toBe(['id' => '847', 'text' => 'SDK integration']);
})->with([
    'standalone post' => [['text' => 'SDK integration'], []],
    'reply' => [['text' => 'SDK integration', 'reply_to' => '293'], ['reply' => ['in_reply_to_tweet_id' => '293']]],
]);

it('returns API errors as tool output', function (): void {
    $fake = X::fake()->shouldThrow('getPost', new RuntimeException('API unavailable'));

    $result = (new PlumeFetchTweetTool)->handle(new Request(['tweet_id' => '629']));

    expect($result)->toBe('Error fetching tweet 629: API unavailable');
    $fake->assertCalled('getPost', fn (array $args): bool => $args[0] === '629');
});
