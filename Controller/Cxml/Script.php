<?php

declare(strict_types=1);

namespace Vurbis\Punchout\Controller\Cxml;

use GuzzleHttp\Exception\GuzzleException;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Vurbis\Punchout\Model\Configuration;
use Vurbis\Punchout\Model\Punchout;

class Script extends Action
{
    public function __construct(
        Context $context,
        protected Configuration $configuration,
        protected Punchout $punchout,
        protected UrlInterface $urlInterface,
        protected CustomerSession $session,
        protected CheckoutSession $checkoutSession
    ) {
        parent::__construct($context);
    }

    /**
     * Add script tag to header
     *
     * @return Raw|(Raw&ResultInterface)
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws GuzzleException
     */
    public function execute()
    {
        $sessionId = $this->session->getPunchoutSession();
        if (empty($sessionId)) {
            $response = "function initPunchout(){}//" . time();
        } else {
            $apiUrl   = $this->configuration->getApiUrl();
            $cart     = $this->checkoutSession->getQuote();
            $url      = $apiUrl . '/punchout/files/' . $sessionId . '/magento2.js?proxy=true&cart=' . $cart->getId();
            $response = $this->punchout->get($url);
        }

        /** @var Raw $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);

        return $result
            ->setHeader(
                'Cache-Control',
                'no-store, no-cache, must-revalidate, max-age=0',
                true
            )
            ->setHeader('Content-Type', 'application/javascript;charset=UTF-8')
            ->setContents($response);
    }
}
