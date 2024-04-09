<?php

declare(strict_types=1);

namespace Vurbis\Punchout\Api;

use Magento\Framework\Api\CustomAttributesDataInterface;

interface ApiUpdateResponseInterface extends CustomAttributesDataInterface
{
    public const RESULT = 'result';

    public const FIELD = 'field';

    public const ERROR = 'error';

    public function getId(): ?string;

    public function setId(string $id = null): static;

    public function setField(string $field = null): static;
}
