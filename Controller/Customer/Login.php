<?php

declare(strict_types=1);

// phpcs:disable Generic.Metrics.CyclomaticComplexity

namespace Vurbis\Punchout\Controller\Customer;

use GuzzleHttp\Exception\GuzzleException;
use Laminas\Http\Request;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ObjectManager\Environment\Developer;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\State;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\UrlFactory;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Vurbis\Punchout\Model\Configuration;
use Vurbis\Punchout\Model\Punchout;

class Login extends Action
{
    private UrlInterface $url;

    public function __construct(
        $context,
        private readonly Session $session,
        private readonly Configuration $configuration,
        private readonly Punchout $punchout,
        private readonly CustomerRepository $customerRepository,
        private readonly CookieManagerInterface $cookieManager,
        private readonly CookieMetadataFactory $cookieMetadataFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly State $state,
        private readonly UrlFactory $urlFactory,
    ) {
        $this->url = $this->urlFactory->create();
        parent::__construct($context);
    }

    /**
     * @return Redirect
     * @throws GuzzleException
     * @throws LocalizedException
     */
    public function execute(): Redirect
    {
        try {
            $resultRedirect = $this->resultRedirectFactory->create();
            // log out customer
            if ($this->session->isLoggedIn()) {
                $lastCustomerId = $this->session->getId();
                $this->session->logout()->setLastCustomerId($lastCustomerId);
            }

            $this->session->setPunchoutIsOci(false);

            /** @var Http $req */
            $req             = $this->getRequest();
            $punchoutSession = $req->getParam('session');
            $username        = $req->getParam('username');
            $password        = $req->getParam('password');
            if (!isset($password)) {
                $password = $req->getParam('pass');
            }

            $apiUrl        = $this->configuration->getApiUrl();
            $authenticated = false;
            if (!$punchoutSession) {
                $supplier_id = $this->configuration->getSupplierId();
                $url         = $apiUrl . '/punchout/' . $supplier_id . '/login';
                $res         = $this->punchout->post(
                    $url,
                    [
                        'query' => $req->getParams(),
                        'body' => $req->getContent(),
                    ]
                );
                if (!isset($res->id)) {
                    throw new LocalizedException(
                        __('Username and password could not be found in Vurbis marketplace.')
                    );
                }

                $punchoutSession      = $res->id;
                $username             = $res->username; // @phpstan-ignore-line
                $password             = $res->password; // @phpstan-ignore-line
                $authenticated        = true;
                $is_clean_customer_id = false;
                if (isset($res->config)) {
                    if (isset($res->config->clean_customer_id)) {
                        $is_clean_customer_id = $res->config->clean_customer_id;
                    }
                }

                $this->session->setPunchoutCleanCustomerId($is_clean_customer_id);
                $this->session->setData(true);
            }

            if (!$punchoutSession) {
                throw new LocalizedException(__('Punchout session is required.'));
            }

            if (!$username) {
                throw new LocalizedException(__('Username is required.'));
            }

            if (!$password) {
                throw new LocalizedException(__('Password is required.'));
            }

            if (!$authenticated) {
                $authRes = $this->punchout->post(
                    $apiUrl . "/punchout/authenticate",
                    [
                        'username' => $username,
                        'password' => $password,
                        'session' => $punchoutSession,
                    ]
                );
                if (!$authRes->authenticated) {  // @phpstan-ignore-line
                    throw new LocalizedException(__('Authentication failed.'));
                }
            }

            if (
                $this->state->getMode() === Developer::MODE
                && $req->getParam('test')
            ) {
                $username = $req->getParam('test');
            }

            if (
                (
                    $this->state->getMode() === Developer::MODE
                    && $req->getParam('use_original')
                )
                ||
                $this->configuration->getUseOriginalCustomerAccount()
            ) {
                $username = $req->getParam('username');
            }

            try {
                $customer = $this->customerRepository->get(
                    $username,
                    $this->storeManager->getWebsite()->getId()
                );
            } catch (NoSuchEntityException $e) {
                throw new LocalizedException(__('Failed to login.'));
            }

            $this->_eventManager->dispatch(
                'customer_data_object_login',
                ['customer' => $customer]
            );

            $this->session->setCustomerDataAsLoggedIn($customer);
            $this->session->regenerateId();

            if ($this->cookieManager->getCookie('mage-cache-sessid')) {
                $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                $metadata->setPath('/');
                $this->cookieManager->deleteCookie(
                    'mage-cache-sessid',
                    $metadata
                );
            }

            // Add punchout session ID to customer session
            $this->session->setPunchoutSession($punchoutSession);

            // Fake request method to trigger version update for private content
            $this->_request->setMethod(Request::METHOD_POST); // @phpstan-ignore-line
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        }

        $defaultUrl = $this->url->getUrl('/', ['_secure' => true]);
        $resultRedirect->setUrl($defaultUrl);

        return $resultRedirect;
    }
}
