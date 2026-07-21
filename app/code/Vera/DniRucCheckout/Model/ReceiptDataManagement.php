<?php

namespace Vera\DniRucCheckout\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Vera\DniRucCheckout\Api\Data\ReceiptDataInterface;
use Vera\DniRucCheckout\Model\Data\ReceiptDataFactory;
use Vera\DniRucCheckout\Model\Validator\ReceiptValidator;

class ReceiptDataManagement
{
    private const QUOTE_FIELDS = [
        'receipt_type' => 'vera_receipt_type',
        'tax_id_type' => 'vera_tax_id_type',
        'tax_id' => 'vera_tax_id',
        'company_name' => 'vera_company_name',
        'fiscal_address' => 'vera_fiscal_address',
    ];

    private CartRepositoryInterface $cartRepository;

    private ReceiptValidator $validator;

    private ReceiptDataFactory $receiptDataFactory;

    private Config $config;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        ReceiptValidator $validator,
        ReceiptDataFactory $receiptDataFactory,
        Config $config
    ) {
        $this->cartRepository = $cartRepository;
        $this->validator = $validator;
        $this->receiptDataFactory = $receiptDataFactory;
        $this->config = $config;
    }

    /**
     * @param array<string, mixed> $data
     * @throws LocalizedException
     */
    public function saveQuote(Quote $quote, array $data): void
    {
        if (!$this->config->isEnabled((int) $quote->getStoreId())) {
            return;
        }

        $normalized = $this->validator->validate($data, (int) $quote->getStoreId());

        foreach (self::QUOTE_FIELDS as $source => $target) {
            $quote->setData($target, $normalized[$source]);
        }

        $this->cartRepository->save($quote);
    }

    /**
     * @throws LocalizedException
     */
    public function saveFromBillingAddress(Quote $quote, ?AddressInterface $billingAddress): void
    {
        if ($billingAddress === null || $billingAddress->getExtensionAttributes() === null) {
            return;
        }

        $extensionAttributes = $billingAddress->getExtensionAttributes();
        $data = [
            'receipt_type' => $extensionAttributes->getReceiptType(),
            'tax_id_type' => $extensionAttributes->getTaxIdType(),
            'tax_id' => $extensionAttributes->getTaxId(),
            'company_name' => $extensionAttributes->getCompanyName(),
            'fiscal_address' => $extensionAttributes->getFiscalAddress(),
        ];

        if (!$this->hasAnyValue($data)) {
            return;
        }

        $this->saveQuote($quote, $data);
    }

    /**
     * @throws LocalizedException
     */
    public function copyToOrder(Quote $quote, Order $order): void
    {
        if (!$this->config->isEnabled((int) $quote->getStoreId())) {
            return;
        }

        $normalized = $this->validator->validate(
            $this->getQuoteData($quote),
            (int) $quote->getStoreId()
        );

        foreach (self::QUOTE_FIELDS as $source => $target) {
            $order->setData($target, $normalized[$source]);
        }
    }

    public function getQuoteData(CartInterface $quote): array
    {
        $data = [];

        foreach (self::QUOTE_FIELDS as $source => $target) {
            $data[$source] = $quote->getData($target);
        }

        return $data;
    }

    public function toDataObject(CartInterface $quote): ReceiptDataInterface
    {
        return $this->receiptDataFactory->create([
            'data' => $this->getQuoteData($quote),
        ]);
    }

    private function hasAnyValue(array $data): bool
    {
        foreach ($data as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return true;
            }
        }

        return false;
    }
}
