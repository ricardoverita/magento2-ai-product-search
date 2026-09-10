<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;

class AttributeCodes extends Value
{
    public function beforeSave(): self
    {
        $codes = array_filter(array_map('trim', explode(',', (string) $this->getValue())));
        foreach ($codes as $code) {
            if (!preg_match('/^[a-z][a-z0-9_]{0,63}$/', $code)) {
                throw new LocalizedException(__('Invalid product attribute code: %1', $code));
            }
        }
        $this->setValue(implode(',', array_unique($codes)));
        return parent::beforeSave();
    }
}
