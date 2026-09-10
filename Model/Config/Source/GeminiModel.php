<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Vera\SearchAI\Model\Config\ProviderOptions;

class GeminiModel implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [['value' => ProviderOptions::GEMINI_MODEL, 'label' => __('Gemini 3.8 Flash (economical)')]];
    }
}
