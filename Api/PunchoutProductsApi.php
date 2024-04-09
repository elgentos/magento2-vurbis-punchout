<?php

declare(strict_types=1);

namespace Vurbis\Punchout\Api;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Encryption\EncryptorInterface;
use Vurbis\Punchout\Model\Configuration;

/**
 * PunchoutProductsApi Api
 */
class PunchoutProductsApi
{
    public const CONNECTION_NAME = 'vurbis';

    public function __construct(
        protected CustomerRepositoryInterface $customerRepository,
        protected CustomerRegistry $customerRegistry,
        protected EncryptorInterface $encryptor,
        protected ResourceConnection $resource,
        protected Configuration $configuration
    ) {
    }

    public function getTablename(string $tableName): string
    {
        return $this->resource->getTableName($tableName);
    }

    /**
     * @api
     */
    public function run(string $skus): array
    {
        $results = [];

        $catalog  = $this->getTablename('catalog_product_entity');
        $relation = $this->getTablename('catalog_product_relation');

        $row = 'entity_id';
        if (
            $this->resource->getConnection(self::CONNECTION_NAME)->tableColumnExists(
                $catalog,
                'row_id'
            )
        ) {
            $row = 'row_id';
        }

        $skus = explode(';', $skus);
        $connection = $this->resource->getConnection(self::CONNECTION_NAME);

        foreach ($skus as $childSku) {
            $subSelect = $connection->select()
                ->from(['child' => $catalog], 'entity_id')
                ->where('child.sku = ?', $childSku);

            $relationSelect = $connection->select()
                ->from(['relationship' => $relation], 'parent_id')
                ->where('relationship.child_id = ?', new \Zend_Db_Expr($subSelect));

            $mainSelect = $connection->select()
                ->from(['parent' => $catalog])
                ->where('parent.' . $row . ' IN (?)', new \Zend_Db_Expr($relationSelect))
                ->where('CONCAT(parent.sku, "%") LIKE ?', $childSku);

            $product = $connection->fetchRow($mainSelect);

            $results[] = ['sku' => $childSku, 'parent' => $product];
        }

        return $results;
    }
}
