<?php

namespace Vera\DniRucCheckout\Block\Adminhtml\Order;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Magento\Backend\Block\Template;

class ReceiptData extends Template
{
    private Registry $registry;

    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
    }

    public function getOrder(): ?Order
    {
        $order = $this->registry->registry('current_order');

        return $order instanceof Order ? $order : null;
    }

    public function hasReceiptData(): bool
    {
        $order = $this->getOrder();

        if (!$order) {
            return false;
        }

        return (bool) ($order->getData('vera_receipt_type')
            || $order->getData('vera_tax_id_type')
            || $order->getData('vera_tax_id')
            || $order->getData('vera_company_name')
            || $order->getData('vera_fiscal_address'));
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    public function getReceiptRows(): array
    {
        $order = $this->getOrder();

        if (!$order) {
            return [];
        }

        $rows = [];
        $fields = [
            'vera_receipt_type' => __('Receipt type'),
            'vera_tax_id_type' => __('Document type'),
            'vera_tax_id' => __('DNI or RUC'),
        ];

        if ($order->getData('vera_receipt_type') === 'factura') {
            $fields['vera_company_name'] = __('Company name');
            $fields['vera_fiscal_address'] = __('Fiscal address');
        }

        foreach ($fields as $field => $label) {
            $value = trim((string) $order->getData($field));

            if ($value !== '') {
                $rows[] = [
                    'label' => (string) $label,
                    'value' => $value,
                ];
            }
        }

        return $rows;
    }

    public function getReceiptTypeLabel(string $value): string
    {
        return $value === 'factura' ? (string) __('Factura') : (string) __('Boleta');
    }

    public function getTaxIdTypeLabel(string $value): string
    {
        return $value === 'ruc' ? (string) __('RUC') : (string) __('DNI');
    }
}
