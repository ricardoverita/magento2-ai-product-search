<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Chat;

use Magento\Framework\Locale\ResolverInterface;
use Vera\SearchAI\Api\ProductRetrieverInterface;
use Vera\SearchAI\Model\Ai\Exception\ProviderConfigurationException;
use Vera\SearchAI\Model\Ai\ProviderPool;
use Vera\SearchAI\Model\Catalog\ProductCardMapper;
use Vera\SearchAI\Model\Catalog\ProductContextBuilder;
use Vera\SearchAI\Model\Chat\Exception\RateLimitExceededException;
use Vera\SearchAI\Model\Config\SearchAIConfig;
use Vera\SearchAI\Model\Data\StorefrontResponse;

class ChatService
{
    public function __construct(
        private readonly SearchAIConfig $config,
        private readonly RequestValidator $requestValidator,
        private readonly RateLimiter $rateLimiter,
        private readonly NetworkFingerprint $networkFingerprint,
        private readonly ProductRetrieverInterface $productRetriever,
        private readonly ProductContextBuilder $contextBuilder,
        private readonly PromptBuilder $promptBuilder,
        private readonly ProviderPool $providerPool,
        private readonly CandidateValidator $candidateValidator,
        private readonly ProductCardMapper $productCardMapper,
        private readonly ResolverInterface $localeResolver
    ) {
    }

    public function execute(string $message, mixed $history, int $storeId, string $sessionId): StorefrontResponse
    {
        if (!$this->config->isEnabled($storeId) || !$this->config->isProviderReady($storeId)) {
            throw new ProviderConfigurationException('SearchAI is not configured.');
        }

        $validatedHistory = $this->requestValidator->validate($message, $history, $storeId);
        $rate = $this->rateLimiter->consume(
            $storeId,
            $sessionId,
            $this->networkFingerprint->create()
        );
        if (!$rate->isAllowed()) {
            throw new RateLimitExceededException((string) $rate->getRetryAfter());
        }

        $candidates = $this->productRetriever->retrieve(
            $message,
            $storeId,
            $this->config->getCandidateLimit($storeId)
        );
        if ($candidates === []) {
            return new StorefrontResponse(
                (string) __("I couldn't find a matching product. Try a different description."),
                []
            );
        }

        $request = $this->promptBuilder->build(
            $message,
            $validatedHistory,
            $this->contextBuilder->build($candidates, $storeId),
            $this->localeResolver->getLocale(),
            $storeId
        );
        $response = $this->providerPool->get($this->config->getProvider($storeId))->chat($request);
        $validCandidates = $this->candidateValidator->validate($response, $candidates);
        $cards = array_map(
            fn ($candidate) => $this->productCardMapper->map($candidate),
            $validCandidates
        );

        return new StorefrontResponse($response->getAnswer(), $cards);
    }
}
