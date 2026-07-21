<?php

namespace Vera\DniRucCheckout\Plugin\Checkout;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Vera\DniRucCheckout\Model\ReceiptDataManagement;

class PaymentInformationManagementPlugin
{
    private CartRepositoryInterface $cartRepository;

    private ReceiptDataManagement $receiptDataManagement;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        ReceiptDataManagement $receiptDataManagement
    ) {
        $this->cartRepository = $cartRepository;
        $this->receiptDataManagement = $receiptDataManagement;
    }

    public function beforeSavePaymentInformation(
        $subject,
        int $cartId,
        PaymentInterface $paymentMethod,
        ?AddressInterface $billingAddress = null
    ): array {
        $this->saveReceiptData($cartId, $billingAddress);

        return [$cartId, $paymentMethod, $billingAddress];
    }

    public function beforeSavePaymentInformationAndPlaceOrder(
        $subject,
        int $cartId,
        PaymentInterface $paymentMethod,
        ?AddressInterface $billingAddress = null
    ): array {
        $this->saveReceiptData($cartId, $billingAddress);

        return [$cartId, $paymentMethod, $billingAddress];
    }

    private function saveReceiptData(int $cartId, ?AddressInterface $billingAddress): void
    {
        $quote = $this->cartRepository->getActive($cartId);
        $this->receiptDataManagement->saveFromBillingAddress($quote, $billingAddress);
    }
}
