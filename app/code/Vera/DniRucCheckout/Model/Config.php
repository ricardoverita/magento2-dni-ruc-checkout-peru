<?php

namespace Vera\DniRucCheckout\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const XML_PATH_ENABLED = 'vera_dni_ruc_checkout/general/enabled';
    private const XML_PATH_DNI_REQUIRED = 'vera_dni_ruc_checkout/general/dni_required';

    private ScopeConfigInterface $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isDniRequired(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_DNI_REQUIRED, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
