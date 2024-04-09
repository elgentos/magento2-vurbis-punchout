<?php

declare(strict_types=1);

namespace Vurbis\Punchout\Plugin;

use Closure;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\CsrfValidator;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;

/**
 * CsrfValidatorSkip Plugin
 */
class CsrfValidatorSkip
{
    /**
     * @return mixed|void
     */
    public function aroundValidate(
        CsrfValidator $subject,
        Closure $proceed,
        RequestInterface $request,
        ActionInterface $action
    ) {
        /** @var Http $request */
        if ($request->getControllerModule() != 'Vurbis_Punchout') {
            return $proceed($request, $action);
        }
    }
}
