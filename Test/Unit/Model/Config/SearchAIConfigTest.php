<?php
declare(strict_types=1);
namespace Vera\SearchAI\Test\Unit\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use PHPUnit\Framework\TestCase;
use Vera\SearchAI\Model\Config\Path;
use Vera\SearchAI\Model\Config\SearchAIConfig;
final class SearchAIConfigTest extends TestCase
{
    public function testBoundsCandidateLimitAndKeepsDescriptionOff(): void
    {
        $scope = $this->createMock(ScopeConfigInterface::class);
        $scope->method('getValue')->willReturnCallback(
            fn (string $path): mixed => $path === Path::CANDIDATE_LIMIT ? '500' : null
        );
        $scope->method('isSetFlag')->willReturn(false);
        $config = new SearchAIConfig($scope, $this->createMock(EncryptorInterface::class));
        self::assertSame(20, $config->getCandidateLimit());
        self::assertFalse($config->shouldIncludeDescription());
    }
}
