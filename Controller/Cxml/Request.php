<?php

declare(strict_types=1);

namespace Vurbis\Punchout\Controller\Cxml;

use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\UrlInterface;
use Vurbis\Punchout\Model\Configuration;
use Vurbis\Punchout\Model\Punchout;

class Request extends Action
{
    public function __construct(
        Context $context,
        protected readonly Configuration $configuration,
        protected readonly Punchout $punchout,
        protected readonly UrlInterface $urlInterface,
        protected readonly File $fileSystem
    ) {
        parent::__construct($context);
    }

    /**
     * Request action
     *
     * @return Raw|(Raw&ResultInterface)
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws GuzzleException
     */
    public function execute()
    {
        $apiUrl   = $this->configuration->getApiUrl();
        $url      = $this->urlInterface->getCurrentUrl();
        $path     = explode('/punchout/cxml', $url)[1];
        $path     = str_replace('/setup', '/request', $path);
        $url      = $apiUrl . '/punchout' . $path;
        $body     = $this->fileSystem->fileGetContents('php://input');
        $response = $this->punchout->post($url, $body, 'xml', 'xml');

        /** @var Raw $result */
        $result   = $this->resultFactory->create(ResultFactory::TYPE_RAW);

        return $result->setHeader(
            'Content-Type',
            'text/xml'
        )->setContents($response);
    }
}
