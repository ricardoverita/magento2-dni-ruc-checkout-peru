<?php

namespace Vera\DniRucCheckout\Plugin\Checkout;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Vera\DniRucCheckout\Model\ReceiptDataManagement;
use Vera\DniRucCheckout\Model\QuoteResolver;

class GuestPaymentInformationManagementPlugin
{
    private QuoteResolver $quoteResolver;

    private ReceiptDataManagement $receiptDataManagement;

    public function __construct(
        QuoteResolver $quoteResolver,
        ReceiptDataManagement $receiptDataManagement
    ) {
        $this->quoteResolver = $quoteResolver;
        $this->receiptDataManagement = $receiptDataManagement;
    }

    public function beforeSavePaymentInformation(
        $subject,
        string $cartId,
        PaymentInterface $paymentMethod,
        ?AddressInterface $billingAddress = null
    ): array {
        $this->saveReceiptData($cartId, $billingAddress);

        return [$cartId, $paymentMethod, $billingAddress];
    }

    public function beforeSavePaymentInformationAndPlaceOrder(
        $subject,
        string $cartId,
        string $email,
        PaymentInterface $paymentMethod,
        ?AddressInterface $billingAddress = null
    ): array {
        $this->saveReceiptData($cartId, $billingAddress);

        return [$cartId, $email, $paymentMethod, $billingAddress];
    }

    private function saveReceiptData(string $cartId, ?AddressInterface $billingAddress): void
    {
        $quote = $this->quoteResolver->getGuestQuote($cartId);
        $this->receiptDataManagement->saveFromBillingAddress($quote, $billingAddress);
    }
}
