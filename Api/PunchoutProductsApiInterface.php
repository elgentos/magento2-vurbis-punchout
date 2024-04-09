<?php

declare(strict_types=1);

namespace Vurbis\Punchout\Api;

interface PunchoutProductsApiInterface
{
    /**
     * Run
     *
     * @param string $skus
     *
     * @return \Vurbis\Punchout\Api\ApiProductsResponseInterface[]
     * @api
     */
    public function run($skus);
}
