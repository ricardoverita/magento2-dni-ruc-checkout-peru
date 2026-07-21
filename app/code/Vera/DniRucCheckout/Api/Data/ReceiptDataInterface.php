<?php

namespace Vera\DniRucCheckout\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface ReceiptDataInterface extends ExtensibleDataInterface
{
    public const RECEIPT_TYPE = 'receipt_type';
    public const TAX_ID_TYPE = 'tax_id_type';
    public const TAX_ID = 'tax_id';
    public const COMPANY_NAME = 'company_name';
    public const FISCAL_ADDRESS = 'fiscal_address';

    public function getReceiptType(): ?string;

    public function getTaxIdType(): ?string;

    public function getTaxId(): ?string;

    public function getCompanyName(): ?string;

    public function getFiscalAddress(): ?string;
}
