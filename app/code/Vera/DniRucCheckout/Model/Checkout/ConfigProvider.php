<?php

namespace Vera\DniRucCheckout\Model\Checkout;

use Magento\Checkout\Model\ConfigProviderInterface;
use Vera\DniRucCheckout\Model\Config;

class ConfigProvider implements ConfigProviderInterface
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getConfig(): array
    {
        return [
            'veraDniRucCheckout' => [
                'enabled' => $this->config->isEnabled(),
                'dniRequired' => $this->config->isDniRequired(),
            ],
        ];
    }
}
