<?php

declare(strict_types=1);

namespace Vurbis\Punchout\Api;

use Magento\Framework\Api\CustomAttributesDataInterface;

/**
 * ApiProductsResponseInterface Api
 */
interface ApiProductsResponseInterface extends CustomAttributesDataInterface
{
    public const SKU = 'sku';

    public const PARENT_SKUS = 'parentSkus';

    public const KIND = 'kind';

    public function getSku(): ?string;

    public function setSku(string $sku): static;

    public function getKind(): ?string;

    public function setKind(string $kind): static;

    public function getParentSkus(): ?string;

    public function setParentSkus(string $parentSkus): static;
}
