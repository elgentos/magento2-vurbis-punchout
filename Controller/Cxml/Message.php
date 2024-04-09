<?php

declare(strict_types=1);

namespace Vurbis\Punchout\Controller\Cxml;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Rest\Request as ApiRequest;
use Vurbis\Punchout\Model\Configuration;
use Vurbis\Punchout\Model\Punchout;

/**
 * Message Controller
 */
class Message extends Action
{
    public function __construct(
        Context $context,
        protected Configuration $configuration,
        protected Punchout $punchout,
        protected CustomerSession $session,
        protected ApiRequest $request
    ) {
        parent::__construct($context);
    }

    /**
     * Add Message script
     * @SuppressWarnings(PHPMD.ElseExpression)
     *
     * @return Raw|(Raw&ResultInterface)
     * @throws LocalizedException
     */
    public function execute()
    {
        $sessionId = $this->session->getPunchoutSession();
        if (empty($sessionId)) {
            $response = "not a punchout session";
        } else {
            $apiUrl   = $this->configuration->getApiUrl();
            $post     = json_decode($this->request->getContent(), true);
            $url      = $apiUrl . '/punchout/message/' . $sessionId . '?format=magento2-cart&cartId=' . $post['cartId'];
            $response = $this->punchout->post($url, $post, 'json', 'text');
        }

        /** @var Raw $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);

        return $result
            ->setHeader(
                'Cache-Control',
                'no-store, no-cache, must-revalidate, max-age=0',
                true
            )
            ->setHeader('Content-Type', 'application/json')
            ->setContents($response);
    }
}
