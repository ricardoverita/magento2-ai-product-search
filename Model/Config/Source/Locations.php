<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Locations implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'homepage', 'label' => __('Homepage')],
            ['value' => 'product', 'label' => __('Product pages')],
            ['value' => 'category', 'label' => __('Category pages')],
        ];
    }
}
