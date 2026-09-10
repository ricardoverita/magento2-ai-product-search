<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Vera\SearchAI\Model\Config\ProviderOptions;

class AnthropicModel implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [['value' => ProviderOptions::ANTHROPIC_MODEL, 'label' => __('Claude Fable 5 (economical)')]];
    }
}
