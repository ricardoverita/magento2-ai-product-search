<?php
declare(strict_types=1);
namespace Vera\SearchAI\Test\Unit\Model\Ai;
use PHPUnit\Framework\TestCase;
use Vera\SearchAI\Model\Ai\Exception\ProviderException;
use Vera\SearchAI\Model\Ai\StructuredResponseDecoder;
final class StructuredResponseDecoderTest extends TestCase
{
    public function testDecodesJsonAndDeduplicatesIds(): void
    {
        $result = (new StructuredResponseDecoder())->decode('{"answer":"Found","product_ids":[10,20,10]}');
        self::assertSame('Found', $result->getAnswer());
        self::assertSame([10, 20], $result->getProductIds());
    }
    public function testRejectsUnexpectedShape(): void
    {
        $this->expectException(ProviderException::class);
        (new StructuredResponseDecoder())->decode('{"answer":[],"product_ids":"10"}');
    }
}
