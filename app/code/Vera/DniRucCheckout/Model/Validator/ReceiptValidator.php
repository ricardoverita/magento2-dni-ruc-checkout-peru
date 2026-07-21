<?php

namespace Vera\DniRucCheckout\Model\Validator;

use Magento\Framework\Exception\LocalizedException;
use Vera\DniRucCheckout\Model\Config;

class ReceiptValidator
{
    private RucValidator $rucValidator;

    private Config $config;

    public function __construct(
        RucValidator $rucValidator,
        Config $config
    ) {
        $this->rucValidator = $rucValidator;
        $this->config = $config;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string|null>
     * @throws LocalizedException
     */
    public function validate(array $data, ?int $storeId = null): array
    {
        $receiptType = $this->normalize($data['receipt_type'] ?? null);
        $taxIdType = $this->normalize($data['tax_id_type'] ?? null);
        $taxId = $this->normalizeNumber($data['tax_id'] ?? null);
        $companyName = $this->normalizeText($data['company_name'] ?? null);
        $fiscalAddress = $this->normalizeText($data['fiscal_address'] ?? null);

        if (!in_array($receiptType, ['boleta', 'factura'], true)) {
            throw new LocalizedException(__('Please select a valid receipt type.'));
        }

        if ($receiptType === 'boleta') {
            if ($taxIdType !== 'dni') {
                throw new LocalizedException(__('Boleta requires DNI as the document type.'));
            }

            if ($companyName !== null || $fiscalAddress !== null) {
                throw new LocalizedException(__('Company name and fiscal address are only valid for Factura.'));
            }

            if ($taxId === null && $this->config->isDniRequired($storeId)) {
                throw new LocalizedException(__('DNI is required for Boleta.'));
            }

            if ($taxId !== null && (!preg_match('/\A\d{8}\z/D', $taxId) || preg_match('/\A0+\z/D', $taxId))) {
                throw new LocalizedException(__('DNI must contain exactly 8 digits and cannot be all zeros.'));
            }

            return [
                'receipt_type' => $receiptType,
                'tax_id_type' => $taxIdType,
                'tax_id' => $taxId,
                'company_name' => null,
                'fiscal_address' => null,
            ];
        }

        if ($taxIdType !== 'ruc') {
            throw new LocalizedException(__('Factura requires RUC as the document type.'));
        }

        if ($taxId === null) {
            throw new LocalizedException(__('RUC is required for Factura.'));
        }

        if (!$this->rucValidator->isValid($taxId)) {
            throw new LocalizedException(__('Please enter a valid RUC, including its check digit.'));
        }

        if ($companyName === null) {
            throw new LocalizedException(__('Company name is required for Factura.'));
        }

        if ($fiscalAddress === null) {
            throw new LocalizedException(__('Fiscal address is required for Factura.'));
        }

        if (mb_strlen($companyName) > 255) {
            throw new LocalizedException(__('Company name cannot exceed 255 characters.'));
        }

        if (mb_strlen($fiscalAddress) > 255) {
            throw new LocalizedException(__('Fiscal address cannot exceed 255 characters.'));
        }

        return [
            'receipt_type' => $receiptType,
            'tax_id_type' => $taxIdType,
            'tax_id' => $taxId,
            'company_name' => $companyName,
            'fiscal_address' => $fiscalAddress,
        ];
    }

    private function normalize($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : strtolower($value);
    }

    private function normalizeNumber($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function normalizeText($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = preg_replace('/\s+/u', ' ', trim((string) $value));

        return $value === '' ? null : $value;
    }
}
