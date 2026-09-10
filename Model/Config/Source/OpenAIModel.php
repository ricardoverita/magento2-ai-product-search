<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Vera\SearchAI\Model\Config\ProviderOptions;

class OpenAIModel implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [['value' => ProviderOptions::OPENAI_MODEL, 'label' => __('GPT-5.6 Luna (economical)')]];
    }
}
