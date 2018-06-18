<?php

namespace Autocompleteplus\Autosuggest\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\ScopeInterface;

class UpgradeData implements UpgradeDataInterface
{
    protected $resourceConfig;
    protected $scopeConfig;
    protected $helper;
    protected $api;

    public function _construct(
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Autocompleteplus\Autosuggest\Helper\Api $api
    ) {
        $this->resourceConfig = $resourceConfig;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->api = $api;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        switch ($context->getVersion()) {
            // Always will be previous version of upgrade - not the "upgraded" version (ie: 1.0.0 -> 1.0.1 will result
            // in this switch statement receiving 1.0.0, and not 1.0.1).
            case '4.0.7':
                break;
        }
        $setup->endSetup();
    }
}
