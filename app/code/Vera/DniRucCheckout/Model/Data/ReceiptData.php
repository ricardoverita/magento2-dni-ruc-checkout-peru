<?php

namespace Vera\DniRucCheckout\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use Vera\DniRucCheckout\Api\Data\ReceiptDataInterface;

class ReceiptData extends AbstractExtensibleObject implements ReceiptDataInterface
{
    public function getReceiptType(): ?string
    {
        return $this->_get(ReceiptDataInterface::RECEIPT_TYPE);
    }

    public function getTaxIdType(): ?string
    {
        return $this->_get(ReceiptDataInterface::TAX_ID_TYPE);
    }

    public function getTaxId(): ?string
    {
        return $this->_get(ReceiptDataInterface::TAX_ID);
    }

    public function getCompanyName(): ?string
    {
        return $this->_get(ReceiptDataInterface::COMPANY_NAME);
    }

    public function getFiscalAddress(): ?string
    {
        return $this->_get(ReceiptDataInterface::FISCAL_ADDRESS);
    }
}
