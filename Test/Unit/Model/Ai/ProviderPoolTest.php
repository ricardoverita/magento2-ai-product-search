<?php
declare(strict_types=1);
namespace Vera\SearchAI\Test\Unit\Model\Ai;
use PHPUnit\Framework\TestCase;
use Vera\SearchAI\Api\AiProviderInterface;
use Vera\SearchAI\Model\Ai\Exception\ProviderConfigurationException;
use Vera\SearchAI\Model\Ai\ProviderPool;
use Vera\SearchAI\Model\Data\ChatRequest;
use Vera\SearchAI\Model\Data\ChatResponse;
final class ProviderPoolTest extends TestCase
{
    public function testReturnsRegisteredProvider(): void
    {
        $provider = new class implements AiProviderInterface {
            public function chat(ChatRequest $request): ChatResponse { return new ChatResponse('ok', []); }
        };
        self::assertSame($provider, (new ProviderPool(['custom' => $provider]))->get('custom'));
    }
    public function testRejectsUnknownProvider(): void
    {
        $this->expectException(ProviderConfigurationException::class);
        (new ProviderPool())->get('missing');
    }
}
