<?php
declare(strict_types=1);

namespace Vera\SearchAI\Block;

use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Page\Config as PageConfig;
use Vera\SearchAI\Model\Config\PublicConfigProvider;
use Vera\SearchAI\Model\Config\SearchAIConfig;

class Widget extends Template
{
    public function __construct(
        Context $context,
        private readonly SearchAIConfig $config,
        private readonly PublicConfigProvider $publicConfigProvider,
        private readonly FormKey $formKey,
        private readonly Json $json,
        private readonly PageConfig $pageConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function canRender(?string $location = null): bool
    {
        $location ??= (string) $this->getData('location');
        $storeId = (int) $this->_storeManager->getStore()->getId();
        return $this->config->isEnabled($storeId)
            && $this->config->isProviderReady($storeId)
            && in_array($location, $this->config->getLocations($storeId), true);
    }

    public function getMageInitJson(): string
    {
        $storeId = (int) $this->_storeManager->getStore()->getId();
        $configuration = $this->publicConfigProvider->get($storeId);
        $configuration['formKey'] = $this->formKey->getFormKey();
        return $this->json->serialize(['Vera_SearchAI/js/widget' => $configuration]);
    }

    protected function _prepareLayout(): self
    {
        if ($this->canRender()) {
            $this->pageConfig->addPageAsset('Vera_SearchAI::css/searchai.css');
        }
        parent::_prepareLayout();
        return $this;
    }

    protected function _toHtml(): string
    {
        return $this->canRender() ? parent::_toHtml() : '';
    }
}
