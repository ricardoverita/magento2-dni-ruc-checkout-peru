<?php

namespace Vera\DniRucCheckout\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMaskFactory;

class QuoteResolver
{
    private CartRepositoryInterface $cartRepository;

    private QuoteIdMaskFactory $quoteIdMaskFactory;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->cartRepository = $cartRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    public function getGuestQuote(string $maskedCartId): Quote
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($maskedCartId, 'masked_id');
        $quoteId = (int) $quoteIdMask->getQuoteId();

        if (!$quoteId) {
            throw new NoSuchEntityException(__('The requested cart does not exist.'));
        }

        return $this->cartRepository->getActive($quoteId);
    }

    public function getCustomerQuote(int $customerId): Quote
    {
        if (!$customerId) {
            throw new NoSuchEntityException(__('The active customer cart does not exist.'));
        }

        return $this->cartRepository->getActiveForCustomer($customerId);
    }
}
