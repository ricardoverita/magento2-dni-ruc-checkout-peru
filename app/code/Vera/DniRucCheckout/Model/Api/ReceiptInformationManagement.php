<?php

namespace Vera\DniRucCheckout\Model\Api;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\AuthorizationException;
use Vera\DniRucCheckout\Api\Data\ReceiptDataInterface;
use Vera\DniRucCheckout\Api\ReceiptInformationManagementInterface;
use Vera\DniRucCheckout\Model\ReceiptDataManagement;
use Vera\DniRucCheckout\Model\QuoteResolver;

class ReceiptInformationManagement implements ReceiptInformationManagementInterface
{
    private QuoteResolver $quoteResolver;

    private ReceiptDataManagement $receiptDataManagement;

    private UserContextInterface $userContext;

    public function __construct(
        QuoteResolver $quoteResolver,
        ReceiptDataManagement $receiptDataManagement,
        UserContextInterface $userContext
    ) {
        $this->quoteResolver = $quoteResolver;
        $this->receiptDataManagement = $receiptDataManagement;
        $this->userContext = $userContext;
    }

    public function saveGuest(
        string $cartId,
        string $receiptType,
        string $taxIdType,
        ?string $taxId = null,
        ?string $companyName = null,
        ?string $fiscalAddress = null
    ): void {
        $this->receiptDataManagement->saveQuote(
            $this->quoteResolver->getGuestQuote($cartId),
            [
                'receipt_type' => $receiptType,
                'tax_id_type' => $taxIdType,
                'tax_id' => $taxId,
                'company_name' => $companyName,
                'fiscal_address' => $fiscalAddress,
            ]
        );
    }

    public function getGuest(string $cartId): ReceiptDataInterface
    {
        return $this->receiptDataManagement->toDataObject($this->quoteResolver->getGuestQuote($cartId));
    }

    public function saveMine(
        string $receiptType,
        string $taxIdType,
        ?string $taxId = null,
        ?string $companyName = null,
        ?string $fiscalAddress = null
    ): void {
        $this->assertCustomerContext();
        $this->receiptDataManagement->saveQuote(
            $this->quoteResolver->getCustomerQuote((int) $this->userContext->getUserId()),
            [
                'receipt_type' => $receiptType,
                'tax_id_type' => $taxIdType,
                'tax_id' => $taxId,
                'company_name' => $companyName,
                'fiscal_address' => $fiscalAddress,
            ]
        );
    }

    public function getMine(): ReceiptDataInterface
    {
        $this->assertCustomerContext();

        return $this->receiptDataManagement->toDataObject(
            $this->quoteResolver->getCustomerQuote((int) $this->userContext->getUserId())
        );
    }

    private function assertCustomerContext(): void
    {
        if ($this->userContext->getUserType() !== UserContextInterface::USER_TYPE_CUSTOMER) {
            throw new AuthorizationException(__('Customer authentication is required.'));
        }
    }
}
