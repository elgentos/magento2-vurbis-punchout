<?php

declare(strict_types=1);

namespace Vurbis\Punchout\Cron;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class RemoveGeneratedCustomers
{
    public function __construct(
        private readonly CustomerCollectionFactory $customerCollectionFactory,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly TimezoneInterface $timezone,
        private readonly Registry $registry,
    ) {
    }

    /**
     * @throws LocalizedException
     */
    public function execute()
    {
        $dateBeforeAWeek = $this->timezone->date()->modify('-7 days')->format('Y-m-d H:i:s');

        $customers = $this->customerCollectionFactory->create();
        $customers->addAttributeToFilter('firstname', ['eq' => 'Punchout']);
        $customers->addAttributeToFilter('lastname', ['eq' => 'OCI']);
        $customers->addAttributeToFilter('created_at', ['to' => $dateBeforeAWeek]);

        if ($customers->getSize()) {
            // Allow area to delete customers
            $this->registry->register('isSecureArea', true);
            $this->deleteCustomers($customers);
        }
    }

    /**
     * @throws LocalizedException
     */
    private function deleteCustomers(CustomerCollection $customers): void
    {
        foreach ($customers as $customer) {
            try {
                $this->customerRepository->deleteById($customer->getId());
            } catch (\Exception $e) {
                throw new LocalizedException(__('Unable to delete customer through Vurbis cron: %1', $e->getMessage()));
            }
        }
    }
}
