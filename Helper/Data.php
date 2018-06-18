<?php
/**
 * Data File
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
 * @copyright 2014 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

namespace Autocompleteplus\Autosuggest\Helper;

use \Magento\Store\Model\ScopeInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Data
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
 * @copyright 2014 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Store\Model\StoresConfig
     */
    protected $storesConfig;

    /**
     * @var Api
     */
    protected $api;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;

    /**
     * @var \Autocompleteplus\Autosuggest\Model\ResourceModel\Batch\CollectionFactory
     */
    protected $batchCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productModel;

    /**
     * @var \Autocompleteplus\Autosuggest\Model\Batch
     */
    protected $batchModel;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Autocompleteplus\Autosuggest\Model\ResourceModel\Batch\Collection
     */
    protected $batchCollection;
    
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetaData;

    const ENABLED = 'autosuggest/general/enabled';
    const PRODUCT_ATTRIBUTES = 'autosuggest/product/attributes';
    const PRODUCT_IMAGE_FIELD = 'autosuggest/product/image_field';
    const XML_PATH_SEARCH_LAYERED = 'autosuggest/search/layered';
    const XML_FORM_URL_CONFIG = 'autosuggest/search/miniform_change';
    const MODULE_NAME = 'Autocompleteplus_Autosuggest';
    const XML_PATH_API_ENDPOINT = 'autosuggest/api/endpoint';
    const XML_PATH_DASHBOARD_ENDPOINT = 'autosuggest/dashboard/endpoint';
    const XML_PATH_DASHBOARD_PARAMS = 'autosuggest/dashboard/params';
    const SCOPE_CONFIG_STORES = 'stores';
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\StoresConfig $storesConfig,
        \Autocompleteplus\Autosuggest\Helper\Api $api,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Autocompleteplus\Autosuggest\Model\ResourceModel\Batch\CollectionFactory $batchCollectionFactory,
        \Magento\Catalog\Model\Product $productModel,
        \Autocompleteplus\Autosuggest\Model\Batch $batchModel,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\ProductMetadataInterface $productMetaData
    ) {
        $this->moduleList = $moduleList;
        $this->storeManager = $storeManager;
        $this->storesConfig = $storesConfig;
        $this->api = $api;
        $this->resourceConfig = $resourceConfig;
        $this->batchCollectionFactory = $batchCollectionFactory;
        $this->productModel = $productModel;
        $this->batchModel = $batchModel;
        $this->date = $date;
        $this->productMetaData = $productMetaData;
        parent::__construct($context);
    }

    public function getVersion()
    {
        return $this->moduleList
            ->getOne(self::MODULE_NAME)['setup_version'];
    }
    
    public function getMagentoVersion()
    {
        return $this->productMetaData->getVersion();
    }

    public function getEnabled()
    {
        return $this->scopeConfig->getValue(self::ENABLED);
    }

    public function canUseProductAttributes()
    {
        return $this->scopeConfig->getValue(self::PRODUCT_ATTRIBUTES);
    }

    public function canUseSearchLayered()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SEARCH_LAYERED,
            self::SCOPE_CONFIG_STORES
        );
    }

    public function canUseMiniFormRewrite()
    {
        return $this->scopeConfig->getValue(
            self::XML_FORM_URL_CONFIG,
            self::SCOPE_CONFIG_STORES
        );
    }

    public function getDashboardParams()
    {
        $uuid = $this->api->getApiUUID();
        $authKey = $this->api->getApiAuthenticationKey();
        if (!$uuid || !$authKey) {
            return 'login';
        }

        return sprintf($this->scopeConfig->getValue(self::XML_PATH_DASHBOARD_PARAMS), $uuid, $authKey);
    }

    public function getDashboardEndpoint()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_DASHBOARD_ENDPOINT);
    }

    public function setProductAttributes($enabled)
    {
        $this->resourceConfig->saveConfig(
            self::PRODUCT_ATTRIBUTES,
            $enabled,
            'default',
            0
        );
    }

    public function getSearchLayered($scopeId = 0)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SEARCH_LAYERED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $scopeId
        );
    }

    public function setMiniFormRewrite($enabled, $scope = 'default', $scopeId = 0)
    {
        $this->resourceConfig->saveConfig(
            self::XML_FORM_URL_CONFIG,
            intval($enabled),
            $scope,
            $scopeId
        );
        return $this;
    }
    
    public function setSearchLayered($enabled, $scope = 'default', $scopeId = 0)
    {
        $this->resourceConfig->saveConfig(
            self::XML_PATH_SEARCH_LAYERED,
            intval($enabled),
            $scope,
            $scopeId
        );
        return $this;
    }

    public function isInstalled()
    {
        return !!(strlen($this->getApiUUID()) > 0);
    }

    public function getBatchCollection()
    {
        if (!$this->batchCollection) {
            $batchCollection = $this->batchCollectionFactory->create();
            $this->batchCollection = $batchCollection;
        }

        return $this->batchCollection;
    }

    public function getMultiStoreData()
    {
        $websites = $this->storeManager->getWebsites();
        $multistoreData = [];
        $storeData = [];

        $mageVersion = $this->getMagentoVersion(); //will return the magento version
        $extVersion = $this->getVersion();
        $version = [
            'mage' => $mageVersion,
            'ext' => $extVersion
        ];
        $useStoreCode = $this->scopeConfig->getValue(
            'web/url/use_store',
            ScopeInterface::SCOPE_STORE
        );
        $url = $this->scopeConfig->getValue(
            'web/unsecure/base_url',
            ScopeInterface::SCOPE_STORE
        );
        $storeMail = $this->scopeConfig->getValue(
            'trans_email/ident_support/email',
            ScopeInterface::SCOPE_STORE
        );
        $storesArr = [];
        foreach ($websites as $website) {
            $stores = $website->getStores();
            foreach ($stores as $store) {
                $storesArr[$store->getId()] = $store->getData();
            }
        }
        if (count($storesArr) == 1) {
            try {
                $dataArr = [
                    'stores' => array_pop($storesArr),
                    'version' => $version,
                ];
            } catch (\Exception $e) {
                $dataArr = [
                    'stores' => $storeData,
                    'version' => $version,
                ];
            }
            $dataArr['site'] = $url;
            $dataArr['email'] = $storeMail;
        } else {
            $storeUrls = $this->storesConfig
                ->getStoresConfigByPath('web/unsecure/base_url');
            $locales = $this->storesConfig
                ->getStoresConfigByPath('general/locale/code');
            foreach ($storesArr as $key => $value) {
                if (!$value['is_active']) {
                    continue;
                }

                $storeComplete = $value;
                if (array_key_exists($key, $locales)) {
                    $storeComplete['lang'] = $locales[$key];
                } else {
                    $storeComplete['lang'] = $locales[0];
                }

                if (array_key_exists($key, $storeUrls)) {
                    $storeComplete['url'] = $storeUrls[$key];
                } else {
                    $storeComplete['url'] = $storeUrls[0];
                }

                if ($useStoreCode) {
                    $storeComplete['url'] = $storeUrls[0].$value['code'];
                }

                $storeData[] = $storeComplete;
            }
            $dataArr = [
                'stores' => $storeData,
                'version' => $version,
            ];
            $dataArr['site'] = $url;
            $dataArr['email'] = $storeMail;
        }
        return $dataArr;
    }

    public function writeProductDeletion($sku, $productId, $storeId, $product = null)
    {
        $dt = $this->date->gmtTimestamp();
        try {
            if (!$product) {
                $product = $this->productModel->load($productId);
            }

            $productStores = ($storeId == 0 && method_exists($product, 'getStoreIds')) ?
                 $product->getStoreIds() : [$storeId];
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $productStores = [$storeId];
        }

        if ($sku == null) {
            $sku = 'dummy_sku';
        }

        foreach ($productStores as $productStore) {
            $batchCollection = $this->getBatchCollection();
            $batchCollection->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('store_id', $storeId)
                ->setPageSize(1);

            if ($batchCollection->getSize() > 0) {
                $batch = $batchCollection->getFirstItem();
                $batch->setUpdateDate($dt)
                    ->setAction('remove')
                    ->setProductId($productId)
                    ->setStoreId($productStore)
                    ->save();
            } else {
                $batch = $this->batchModel;
                $batch->setUpdateDate($dt)
                    ->setAction('remove')
                    ->setProductId($productId)
                    ->setStoreId($productStore)
                    ->setSku($sku)
                    ->save();
            }
        }
    }
}
