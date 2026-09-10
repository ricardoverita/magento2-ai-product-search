<?php
declare(strict_types=1);
namespace Vera\SearchAI\Test\Unit\Model\Chat;
use PHPUnit\Framework\TestCase;
use Vera\SearchAI\Model\Chat\PromptBuilder;
use Vera\SearchAI\Model\Config\SearchAIConfig;
final class PromptBuilderTest extends TestCase
{
    public function testMerchantGuidanceCannotReplaceSecurityRules(): void
    {
        $config = $this->createMock(SearchAIConfig::class);
        $config->method('getSystemGuidance')->willReturn('Promote sale items.');
        $request = (new PromptBuilder($config))->build('shoes', [], [['id' => 10]], 'es_ES', 2);
        self::assertStringContainsString('Promote sale items.', $request->getSystemPrompt());
        self::assertStringContainsString('Only recommend product IDs from CATALOG_CANDIDATES.', $request->getSystemPrompt());
        self::assertSame(2, $request->getStoreId());
    }
}
