<?php

declare(strict_types=1);

namespace Vurbis\Punchout\Api;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Vurbis\Punchout\Model\Configuration;

class PunchoutApi implements PunchoutApiInterface
{
    protected const CONNECTION_NAME = 'vurbis';

    public function __construct(
        protected CustomerRepositoryInterface $customerRepository,
        protected CustomerRegistry $customerRegistry,
        protected EncryptorInterface $encryptor,
        protected ResourceConnection $resource,
        protected CartRepositoryInterface $quoteRepository,
        protected OrderRepositoryInterface $orderRepository,
        protected Configuration $configuration
    ) {
    }

    /**
     * @api
     */
    public function run(
        string $id,
        mixed $props
    ): array {
        $results = [];

        $props = json_decode(json_encode($props), true);

        foreach ($props as $prop) {
            $table    = $prop['table'];
            $field    = $prop['field'];
            $value    = $prop['value'];
            $kind     = $prop['kind'];
            $selector = 'entity_id';
            if (isset($prop['selector'])) {
                $selector = $prop['selector'];
            }

            try {
                if ($field == 'password') {
                    $customer       = $this->customerRepository->getById($id);
                    $customerSecure = $this->customerRegistry->retrieveSecureData($id);
                    $customerSecure->setRpToken(null);
                    $customerSecure->setRpTokenCreatedAt(null);
                    $customerSecure->setPasswordHash($this->encryptor->getHash($value, true));
                    $this->customerRepository->save($customer);
                    $results[] = [
                        'result' => true,
                        'field' => $field,
                        'error' => ''
                    ];
                } elseif ($kind == 'entity_field') {
                    if (!isset($table)) {
                        $table = 'customer/entity';
                    }

                    $write        = $this->resource->getConnection(self::CONNECTION_NAME);
                    $table        = $this->resource->getTableName($table);
                    $data         = [];
                    $data[$field] = $value;
                    $key          = $selector . ' = ?';
                    $write->update($table, $data, [$key => $id]);
                    $results[] = [
                        'result' => true,
                        'field' => $field,
                        'error' => 'updated table: ' . $table
                    ];
                } elseif ($kind == 'attribute') {
                    if ($table == 'quote') {
                        $entity = $this->quoteRepository->get($id);
                    } elseif ($table == 'order') {
                        $entity = $this->orderRepository->get($id);
                    } else {
                        $results[] = [
                            'result' => false,
                            'error' => 'Table ' . $table . ' for attribute is not supported',
                            'field' => $field
                        ];
                        continue;
                    }

                    $extensionAttributes = $entity->getExtensionAttributes();
                    $extensionAttributes->setData($field, $value); // @phpstan-ignore-line
                    $entity->setExtensionAttributes($extensionAttributes);
                    $entity->save(); // @phpstan-ignore-line
                    $results[] = [
                        'result' => true,
                        'field' => $field,
                        'error' => 'updated table: ' . $table
                    ];
                } else {
                    $results[] = [
                        'result' => false,
                        'error' => 'Kind ' . $kind . ' is not supported',
                        'field' => $field
                    ];
                }
            } catch (Exception $e) {
                $results[] = [
                    'result' => false,
                    'error' => 'exception: ' . $e->getMessage(),
                    'field' => $field
                ];
            }
        }

        return $results;
    }
}
