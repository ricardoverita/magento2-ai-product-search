<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;
use Throwable;

class SearchAIConfig
{
    private const DEFAULT_MAX_QUERY = 1000;
    private const DEFAULT_MAX_RESPONSE_BYTES = 262144;

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly EncryptorInterface $encryptor
    ) {
    }

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(Path::ENABLED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getAssistantName(?int $storeId = null): string
    {
        return $this->string(Path::ASSISTANT_NAME, $storeId, 'AI Assistant');
    }

    public function getWelcomeMessage(?int $storeId = null): string
    {
        return $this->string(
            Path::WELCOME_MESSAGE,
            $storeId,
            "Hi! Tell me what you're looking for and I'll help you find it."
        );
    }

    public function getInputPlaceholder(?int $storeId = null): string
    {
        return $this->string(Path::INPUT_PLACEHOLDER, $storeId, 'Describe what you are looking for…');
    }

    /** @return string[] */
    public function getLocations(?int $storeId = null): array
    {
        $locations = array_filter(array_map('trim', explode(',', $this->string(Path::LOCATIONS, $storeId))));
        return array_values(array_intersect(['homepage', 'product', 'category'], array_unique($locations)));
    }

    public function getMaxHistoryMessages(?int $storeId = null): int
    {
        return $this->boundedInt(Path::MAX_HISTORY, 6, 0, 20, $storeId);
    }

    public function getProvider(?int $storeId = null): string
    {
        $provider = $this->string(Path::PROVIDER, $storeId, ProviderOptions::OPENAI);
        return in_array($provider, ProviderOptions::PROVIDERS, true) ? $provider : '';
    }

    public function getModel(?int $storeId = null): string
    {
        return match ($this->getProvider($storeId)) {
            ProviderOptions::OPENAI => $this->string(Path::OPENAI_MODEL, $storeId, ProviderOptions::OPENAI_MODEL),
            ProviderOptions::ANTHROPIC => $this->string(
                Path::ANTHROPIC_MODEL,
                $storeId,
                ProviderOptions::ANTHROPIC_MODEL
            ),
            ProviderOptions::GEMINI => $this->string(Path::GEMINI_MODEL, $storeId, ProviderOptions::GEMINI_MODEL),
            ProviderOptions::CUSTOM => $this->string(Path::CUSTOM_MODEL, $storeId),
            default => '',
        };
    }

    public function getApiKey(?int $storeId = null): string
    {
        $path = match ($this->getProvider($storeId)) {
            ProviderOptions::OPENAI => Path::OPENAI_API_KEY,
            ProviderOptions::ANTHROPIC => Path::ANTHROPIC_API_KEY,
            ProviderOptions::GEMINI => Path::GEMINI_API_KEY,
            ProviderOptions::CUSTOM => Path::CUSTOM_API_KEY,
            default => '',
        };
        if ($path === '') {
            return '';
        }

        $value = $this->string($path, $storeId);
        if ($value === '') {
            return '';
        }

        try {
            return (string) $this->encryptor->decrypt($value);
        } catch (Throwable) {
            return '';
        }
    }

    public function getCustomBaseUrl(?int $storeId = null): string
    {
        return rtrim($this->string(Path::CUSTOM_BASE_URL, $storeId), '/');
    }

    public function getCustomEndpoint(?int $storeId = null): string
    {
        return '/' . ltrim($this->string(Path::CUSTOM_ENDPOINT, $storeId, '/v1/chat/completions'), '/');
    }

    public function getCustomAuthHeader(?int $storeId = null): string
    {
        return $this->string(Path::CUSTOM_AUTH_HEADER, $storeId, 'Authorization');
    }

    public function getCustomAuthPrefix(?int $storeId = null): string
    {
        return $this->string(Path::CUSTOM_AUTH_PREFIX, $storeId, 'Bearer');
    }

    public function getCandidateLimit(?int $storeId = null): int
    {
        return $this->boundedInt(Path::CANDIDATE_LIMIT, 8, 3, 20, $storeId);
    }

    public function shouldUseShortDescription(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            Path::USE_SHORT_DESCRIPTION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function shouldIncludeDescription(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(Path::INCLUDE_DESCRIPTION, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /** @return string[] */
    public function getAdditionalAttributes(?int $storeId = null): array
    {
        $values = array_filter(array_map('trim', explode(',', $this->string(Path::ADDITIONAL_ATTRIBUTES, $storeId))));
        $valid = array_filter($values, static fn (string $code): bool => (bool) preg_match('/^[a-z][a-z0-9_]{0,63}$/', $code));
        return array_values(array_unique($valid));
    }

    public function shouldIncludeOutOfStock(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(Path::INCLUDE_OUT_OF_STOCK, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getRequestsPerMinute(?int $storeId = null): int
    {
        return $this->boundedInt(Path::REQUESTS_PER_MINUTE, 10, 1, 120, $storeId);
    }

    public function getMaxQueryLength(?int $storeId = null): int
    {
        return $this->boundedInt(Path::MAX_QUERY_LENGTH, self::DEFAULT_MAX_QUERY, 100, 4000, $storeId);
    }

    public function getProviderTimeout(?int $storeId = null): int
    {
        return $this->boundedInt(Path::PROVIDER_TIMEOUT, 15, 3, 60, $storeId);
    }

    public function getMaxResponseBytes(?int $storeId = null): int
    {
        return $this->boundedInt(
            Path::MAX_RESPONSE_BYTES,
            self::DEFAULT_MAX_RESPONSE_BYTES,
            16384,
            1048576,
            $storeId
        );
    }

    public function getSystemGuidance(?int $storeId = null): string
    {
        return mb_substr($this->string(Path::SYSTEM_GUIDANCE, $storeId), 0, 4000);
    }

    public function isProviderReady(?int $storeId = null): bool
    {
        if ($this->getProvider($storeId) === ProviderOptions::CUSTOM) {
            return $this->getModel($storeId) !== ''
                && $this->getCustomBaseUrl($storeId) !== '';
        }

        return $this->getProvider($storeId) !== ''
            && $this->getModel($storeId) !== ''
            && $this->getApiKey($storeId) !== '';
    }

    private function string(string $path, ?int $storeId = null, string $default = ''): string
    {
        $value = $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
        return is_scalar($value) && trim((string) $value) !== '' ? trim((string) $value) : $default;
    }

    private function boundedInt(string $path, int $default, int $minimum, int $maximum, ?int $storeId): int
    {
        $value = (int) $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
        if ($value === 0) {
            $value = $default;
        }
        return max($minimum, min($maximum, $value));
    }
}
