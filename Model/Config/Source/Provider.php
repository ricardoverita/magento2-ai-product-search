<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Vera\SearchAI\Model\Config\ProviderOptions;

class Provider implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => ProviderOptions::OPENAI, 'label' => __('OpenAI')],
            ['value' => ProviderOptions::ANTHROPIC, 'label' => __('Anthropic')],
            ['value' => ProviderOptions::GEMINI, 'label' => __('Google Gemini')],
            ['value' => ProviderOptions::CUSTOM, 'label' => __('Custom / OpenAI-compatible')],
        ];
    }
}
