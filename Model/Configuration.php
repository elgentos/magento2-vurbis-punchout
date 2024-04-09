<?php

declare(strict_types=1);

namespace Vurbis\Punchout\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

class Configuration
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function getApiUrl(): string
    {
        $url = $this->getApiSetting('api_url');
        if (!$url) {
            throw new LocalizedException(__('API URL is not configured.'));
        }

        return $url;
    }

    public function getSupplierId(): string
    {
        $supplier_id = $this->getApiSetting('supplier_id');
        if (!$supplier_id) {
            throw new LocalizedException(__('SUPPLIER ID is not configured.'));
        }

        return $supplier_id;
    }

    public function getUseOriginalCustomerAccount(): bool
    {
        return (bool) $this->getApiSetting('use_original_customer_account');
    }

    public function isEnabled(): bool
    {
        return (bool) $this->getApiSetting('enable');
    }

    public function sendFullModuleList(): bool
    {
        return (bool) $this->getApiSetting('send_full_module_list');
    }

    public function isHyvaOrHeadless(): bool
    {
        return (bool) $this->getApiSetting('is_hyva_or_headless');
    }

    private function getApiSetting(string $name): string
    {
        return trim(
            $this->scopeConfig->getValue(
                'vurbis_punchout/api/' . $name,
                ScopeInterface::SCOPE_WEBSITE
            ) ?? ''
        );
    }
}
