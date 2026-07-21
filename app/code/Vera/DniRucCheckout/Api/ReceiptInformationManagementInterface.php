<?php

namespace Vera\DniRucCheckout\Api;

use Vera\DniRucCheckout\Api\Data\ReceiptDataInterface;

interface ReceiptInformationManagementInterface
{
    public function saveGuest(
        string $cartId,
        string $receiptType,
        string $taxIdType,
        ?string $taxId = null,
        ?string $companyName = null,
        ?string $fiscalAddress = null
    ): void;

    public function getGuest(string $cartId): ReceiptDataInterface;

    public function saveMine(
        string $receiptType,
        string $taxIdType,
        ?string $taxId = null,
        ?string $companyName = null,
        ?string $fiscalAddress = null
    ): void;

    public function getMine(): ReceiptDataInterface;
}
