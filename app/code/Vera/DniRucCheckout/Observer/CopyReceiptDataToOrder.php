<?php

namespace Vera\DniRucCheckout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Quote\Model\Quote;
use Vera\DniRucCheckout\Model\ReceiptDataManagement;

class CopyReceiptDataToOrder implements ObserverInterface
{
    private ReceiptDataManagement $receiptDataManagement;

    public function __construct(ReceiptDataManagement $receiptDataManagement)
    {
        $this->receiptDataManagement = $receiptDataManagement;
    }

    public function execute(Observer $observer): void
    {
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();

        if (!$quote instanceof Quote || !$order instanceof Order) {
            return;
        }

        $this->receiptDataManagement->copyToOrder($quote, $order);
    }
}
