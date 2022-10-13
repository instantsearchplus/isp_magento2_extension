<?php

namespace Autocompleteplus\Autosuggest\Controller\Products;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Vers
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category Mage
 *
 * @package   Instantsearchplus
 * @author    Fast Simon <info@instantsearchplus.com>
 * @copyright Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */
class Vers extends \Autocompleteplus\Autosuggest\Controller\Products
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollection;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Data
     */
    protected $helper;

    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Api
     */
    protected $apiHelper;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadataInterface;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Module\ModuleList
     */
    protected $moduleList;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Autocompleteplus\Autosuggest\Helper\Api $apiHelper,
        \Magento\Framework\App\ProductMetadataInterface $productMetadataInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\Module\ModuleList $moduleList,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->productCollection = $productCollectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        $this->apiHelper = $apiHelper;
        $this->productMetadataInterface = $productMetadataInterface;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManagerInterface;
        $this->moduleList = $moduleList;
        $this->moduleManager = $moduleManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $getModules = $this->getRequest()->getParam('modules', false);

        $mageVersion = $this->helper->getMagentoVersion();
        $moduleVers = $this->helper->getVersion();
        $mageEdition = $this->productMetadataInterface->getEdition();
        $uuid = $this->apiHelper->getApiUUID();
        $siteUrl = $this->helper->getStoreUrl();
        $storeId = $this->storeManager->getStore()->getId();
        $modules = $this->moduleList->getAll();
        $installedModules = [];

        $productCollection = $this->productCollection->create();
        $numProducts = $productCollection
            ->addStoreFilter($storeId)
            ->getSize();

        if ($getModules) {
            $installedModules = array_filter(
                $modules, function ($name) {
                $isMagentoModule = (substr($name, 0, 7) == 'Magento');
                $isEnabled = $this->moduleManager->isEnabled($name);
                $isOutputEnabled = $this->moduleManager->isOutputEnabled($name);
                return !$isMagentoModule && $isEnabled && $isOutputEnabled;
            }, \ARRAY_FILTER_USE_KEY
            );
        }

        $responseData = [
            'mage' => $mageVersion,
            'ext' => $moduleVers,
            'num_of_products' => $numProducts,
            'edition' => $mageEdition,
            'uuid' => $uuid,
            'site_url' => $siteUrl,
            'store_id' => $storeId,
            'modules' => $installedModules,
            'miniform_change' => $this->helper->canUseMiniFormRewrite(),
            'layered' => $this->helper->canUseSearchLayered(),
            'serp_slug' => $this->helper->getSerpSlug($storeId),
            'smart_nav_native' => $this->helper->getSmartNavigationNative($storeId),
            'show_out_of_stock' => $this->helper->getDisplayOutOfStock(),
            'basic_enabled' => $this->helper->getBasicEnabled($storeId),
            'search_engine' => $this->helper->getSearchEngine($storeId),
            'flat_products_enabled' => $this->helper->getFlatCatalogUsage($storeId),
            'smn_v2_enabled' => $this->helper->getSmnV2($storeId),
            'serp_v2_enabled' => $this->helper->getSerpV2($storeId),
        ];

        $singleStoreData = $this->helper->getSingleStoreEnabled();
        if ($singleStoreData) {
            $responseData['single_store_enabled'] = $singleStoreData;
        }

        $result = $this->resultJsonFactory->create();
        return $result->setData($responseData);
    }
}
