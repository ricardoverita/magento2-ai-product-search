<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Chat;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;
use Throwable;
use Vera\SearchAI\Model\Config\SearchAIConfig;

class RateLimiter
{
    private const WINDOW_SECONDS = 60;
    private const CACHE_TTL = 120;

    public function __construct(
        private readonly CacheInterface $cache,
        private readonly LockManagerInterface $lockManager,
        private readonly DateTime $dateTime,
        private readonly SearchAIConfig $config,
        private readonly LoggerInterface $logger
    ) {
    }

    public function consume(
        int $storeId,
        string $sessionId,
        string $fingerprint
    ): RateLimitResult {
        $identity = hash('sha256', $storeId . '|' . $sessionId . '|' . $fingerprint);
        $cacheKey = 'vera_searchai_rate_' . $identity;
        $lockKey = 'vera_searchai_rate_lock_' . $identity;
        $locked = false;

        try {
            $locked = $this->lockManager->lock($lockKey, 3);
            if (!$locked) {
                return new RateLimitResult(false, 1);
            }

            $now = $this->dateTime->gmtTimestamp();
            $data = json_decode((string) $this->cache->load($cacheKey), true);
            if (!is_array($data) || !isset($data['started'], $data['count'])
                || $now - (int) $data['started'] >= self::WINDOW_SECONDS) {
                $data = ['started' => $now, 'count' => 0];
            }

            $limit = $this->config->getRequestsPerMinute($storeId);
            if ((int) $data['count'] >= $limit) {
                return new RateLimitResult(
                    false,
                    max(1, self::WINDOW_SECONDS - ($now - (int) $data['started']))
                );
            }

            $data['count'] = (int) $data['count'] + 1;
            $this->cache->save(
                json_encode($data, JSON_THROW_ON_ERROR),
                $cacheKey,
                [],
                self::CACHE_TTL
            );
            return new RateLimitResult(true);
        } catch (Throwable $exception) {
            $this->logger->warning('SearchAI rate limiter unavailable.', [
                'exception' => $exception::class,
            ]);
            return new RateLimitResult(true);
        } finally {
            if ($locked) {
                $this->lockManager->unlock($lockKey);
            }
        }
    }
}
