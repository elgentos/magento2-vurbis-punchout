<?php

declare(strict_types=1);

namespace Vurbis\Punchout\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteRepository;
use Vurbis\Punchout\Model\Configuration;

class CartObserver implements ObserverInterface
{
    public function __construct(
        private readonly QuoteRepository $quoteRepository,
        private readonly Session $session,
        private readonly Configuration $configuration
    ) {
    }

    public function execute(Observer $observer): void
    {
        if (
            !$this->configuration->isHyvaOrHeadless() &&
            $this->session->getPunchoutIsOci() &&
            $this->session->getPunchoutSession() &&
            $this->session->getPunchoutCleanCustomerId()
        ) {
            $cart  = $observer->getData('cart');
            $quote = $this->quoteRepository->get($cart->getQuote()->getId());
            $quote->setCustomerId(null)->save(); // @phpstan-ignore-line
        }
    }
}
