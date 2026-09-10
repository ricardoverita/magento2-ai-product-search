<?php
declare(strict_types=1);
namespace Vera\SearchAI\Test\Unit\Model\Chat;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Vera\SearchAI\Model\Chat\RateLimiter;
use Vera\SearchAI\Model\Config\SearchAIConfig;
final class RateLimiterTest extends TestCase
{
    public function testRejectsRequestAfterConfiguredWindowLimit(): void
    {
        $stored = '';
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('load')->willReturnCallback(fn (): string => $stored);
        $cache->method('save')->willReturnCallback(function (string $data) use (&$stored): bool {
            $stored = $data;
            return true;
        });
        $lock = $this->createMock(LockManagerInterface::class);
        $lock->method('lock')->willReturn(true);
        $clock = $this->createMock(DateTime::class);
        $clock->method('gmtTimestamp')->willReturn(100);
        $config = $this->createMock(SearchAIConfig::class);
        $config->method('getRequestsPerMinute')->willReturn(1);
        $limiter = new RateLimiter($cache, $lock, $clock, $config, new NullLogger());
        self::assertTrue($limiter->consume(1, 'session', 'fingerprint')->isAllowed());
        self::assertFalse($limiter->consume(1, 'session', 'fingerprint')->isAllowed());
    }
}
