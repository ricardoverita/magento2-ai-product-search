<?php
declare(strict_types=1);
namespace Vera\SearchAI\Model\Ai;
use Vera\SearchAI\Api\AiProviderInterface;
use Vera\SearchAI\Model\Ai\Exception\ProviderConfigurationException;
class ProviderPool
{
    public function __construct(private readonly array $providers = [])
    {
        foreach ($providers as $provider) {
            if (!$provider instanceof AiProviderInterface) {
                throw new ProviderConfigurationException('Invalid AI provider registration.');
            }
        }
    }
    public function get(string $code): AiProviderInterface
    {
        if (!isset($this->providers[$code])) {
            throw new ProviderConfigurationException('The configured AI provider is unavailable.');
        }
        return $this->providers[$code];
    }
}
