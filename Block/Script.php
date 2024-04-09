<?php

declare(strict_types=1);

namespace Vurbis\Punchout\Block;

use Magento\Framework\View\Element\Template;
use Vurbis\Punchout\Model\Configuration;

class Script extends Template
{
    public function __construct(
        Template\Context $context,
        private readonly Configuration $configuration,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getScriptUrl(): string
    {
        return $this->_urlBuilder->getUrl('punchout/cxml/script');
    }

    public function _toHtml(): string
    {
        if (!$this->configuration->isEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}
