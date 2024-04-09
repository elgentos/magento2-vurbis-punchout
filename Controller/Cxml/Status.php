<?php

declare(strict_types=1);

namespace Vurbis\Punchout\Controller\Cxml;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Vurbis\Punchout\Model\Configuration;
use Magento\Framework\Module\FullModuleList;

/**
 * Status Controller
 */
class Status extends Action
{
    public function __construct(
        Context $context,
        private readonly Configuration $configuration,
        private readonly FullModuleList $fullModuleList
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            // Config
            $apiUrl = $this->configuration->getApiUrl();
            $supplier_id = $this->configuration->getSupplierId();
            $enabled = $this->configuration->isEnabled();

            // Other plugins
            $installedModules = $this->getAllModulesWithVersions();

            // Vurbis_Punchout
            $isInstalled = isset($installedModules['Vurbis_Punchout']);
            $version = $isInstalled ? $installedModules['Vurbis_Punchout'] : null;

            $system_status = [
                'module' => 'Vurbis_Punchout',
                'isInstalled' => $isInstalled,
                'isActive' => $enabled,
                'version' => $version,
                'punchoutConfig' => [
                    'apiUrl' => $apiUrl,
                    'supplierId' => $supplier_id
                ]
            ];

            if ($this->configuration->sendFullModuleList()) {
                $system_status['modulesList'] = $installedModules;
            }

            /** @var Raw $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);

            return $result->setHeader('Content-Type', 'application/json')->setContents(json_encode($system_status));
        } catch (LocalizedException $e) {
            throw new LocalizedException(__('Failed to get status.'));
        }
    }

    /**
     * Get list of all installed modules and their versions
     *
     * @return array
     */
    private function getAllModulesWithVersions()
    {
        $allModules = [];
        foreach ($this->fullModuleList->getAll() as $moduleName => $moduleInfo) {
            $allModules[$moduleName] = $moduleInfo['setup_version'];
        }

        return $allModules;
    }
}
