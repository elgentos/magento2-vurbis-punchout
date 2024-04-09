<?php

declare(strict_types=1);

namespace Vurbis\Punchout\Plugin;

use Magento\WebsiteRestriction\Model\Restrictor;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;

/**
 * WebsiteRestrictor Plugin
 */
class WebsiteRestrictor
{
    /**
     * @return mixed|void
     */
    public function aroundRestrict(
        Restrictor $subject, // @phpstan-ignore-line
        callable $proceed,
        RequestInterface $request,
        $response,
        $isCustomerLoggedIn
    ) {
        /** @var Http $request */
        if ($request->getControllerModule() != 'Vurbis_Punchout') {
            return $proceed($request, $response, $isCustomerLoggedIn);
        }
    }
}
