<?php

declare(strict_types=1);

namespace Vurbis\Punchout\Api;

interface PunchoutApiInterface
{
    /**
     * @api
     */
    public function run(string $id, mixed $props): array;
}
