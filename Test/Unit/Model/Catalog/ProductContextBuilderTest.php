<?php
declare(strict_types=1);
namespace Vera\SearchAI\Test\Unit\Model\Catalog;
use PHPUnit\Framework\TestCase;
use Vera\SearchAI\Model\Catalog\ProductContextBuilder;
use Vera\SearchAI\Model\Config\SearchAIConfig;
use Vera\SearchAI\Model\Data\ProductCandidate;
final class ProductContextBuilderTest extends TestCase
{
    public function testStripsHtmlAndOmitsFullDescriptionByDefault(): void
    {
        $config = $this->createMock(SearchAIConfig::class);
        $config->method('shouldUseShortDescription')->willReturn(true);
        $config->method('shouldIncludeDescription')->willReturn(false);
        $candidate = new ProductCandidate(
            10, 'SKU', 'Name', '<p>Hello<script>bad()</script></p>' . str_repeat('x', 600),
            'secret', [], [], 1.0, '$1', 'USD', true, '/p', '/i'
        );
        $context = (new ProductContextBuilder($config))->build([$candidate], 1);
        self::assertStringNotContainsString('bad()', $context[0]['short_description']);
        self::assertLessThanOrEqual(500, mb_strlen($context[0]['short_description']));
        self::assertArrayNotHasKey('description', $context[0]);
    }
}
