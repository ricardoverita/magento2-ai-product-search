<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Config;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\UrlInterface;

class PublicConfigProvider
{
    public function __construct(
        private readonly SearchAIConfig $config,
        private readonly UrlInterface $urlBuilder,
        private readonly ResolverInterface $localeResolver
    ) {
    }

    /** @return array<string, mixed> */
    public function get(?int $storeId = null): array
    {
        return [
            'endpoint' => $this->urlBuilder->getUrl('searchai/chat/send'),
            'assistantName' => $this->config->getAssistantName($storeId),
            'welcomeMessage' => $this->config->getWelcomeMessage($storeId),
            'inputPlaceholder' => $this->config->getInputPlaceholder($storeId),
            'maxHistory' => $this->config->getMaxHistoryMessages($storeId),
            'maxQueryLength' => $this->config->getMaxQueryLength($storeId),
            'storageKey' => 'vera_searchai_' . (string) ($storeId ?? 0),
            'locale' => $this->localeResolver->getLocale(),
            'labels' => [
                'send' => (string) __('Send'),
                'close' => (string) __('Close assistant'),
                'retry' => (string) __('Try again'),
                'unavailable' => (string) __('The assistant is temporarily unavailable. Please try again.'),
                'rateLimited' => (string) __('Too many requests. Please wait a moment and try again.'),
            ],
        ];
    }
}
