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
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetaData;

    protected $_storeTime;

    protected $regionsCollection;

    const ENABLED = 'autosuggest/general/enabled';
    const PRODUCT_ATTRIBUTES = 'autosuggest/product/attributes';
    const STOCK_SOURCE = 'autosuggest/product/stock_source';
    const PRODUCT_IMAGE_FIELD = 'autosuggest/product/image_field';
    const XML_PATH_SEARCH_LAYERED = 'autosuggest/search/layered';
    const XML_PATH_COUNTRY_CODE = 'general/store_information/country_id';
    const XML_PATH_REGION_CODE = 'general/store_information/region_id';
    const XML_PATH_SERP_SLUG = 'autosuggest/search/slug';
    const XML_FORM_URL_CONFIG = 'autosuggest/search/miniform_change';
    const XML_BASIC_ENABLED_CONFIG = 'autosuggest/search/basic_enabled';
    const XML_SMART_NAVIGATION_CONFIG = 'autosuggest/search/smart_navigation';
    const MODULE_NAME = 'Autocompleteplus_Autosuggest';
    const XML_PATH_API_ENDPOINT = 'autosuggest/api/endpoint';
    const XML_PATH_DASHBOARD_ENDPOINT = 'autosuggest/dashboard/endpoint';
    const XML_PATH_DASHBOARD_PARAMS = 'autosuggest/dashboard/params';
    const XML_PATH_SHOW_OOS = 'cataloginventory/options/show_out_of_stock';
    const XML_MANAGE_STOCK = 'cataloginventory/item_options/manage_stock';
    const XML_PATH_SEARCH_ENGINE = 'catalog/search/engine';
    const XML_PATH_FLAT_CATALOG = 'catalog/frontend/flat_catalog_product';
    const XML_PATH_SINGLE_STORE = 'autosuggest/install/single_store';
    const XML_PATH_SINGLE_STORE_ID = 'autosuggest/install/single_store_id';
    const XML_PATH_DROPDOWN_V2 = 'autosuggest/dropdown/v2';
    const XML_PATH_SERP_V2 = 'autocompleteplus/serp/v2';
    const XML_PATH_SMN_V2 = 'autocompleteplus/smn/v2';
    const XML_PATH_CUSTOM_VALUES = 'autocompleteplus/serp/custom_values';
    const SCOPE_CONFIG_STORES = 'stores';

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\StoresConfig $storesConfig,
        \Autocompleteplus\Autosuggest\Helper\Api $api,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Catalog\Model\Product $productModel,
        \Autocompleteplus\Autosuggest\Model\Batch $batchModel,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\ProductMetadataInterface $productMetaData,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Directory\Model\Region $regionsCollection
    ) {
        $this->moduleList = $moduleList;
        $this->storeManager = $storeManager;
        $this->storesConfig = $storesConfig;
        $this->api = $api;
        $this->resourceConfig = $resourceConfig;
        $this->productModel = $productModel;
        $this->batchModel = $batchModel;
        $this->date = $date;
        $this->productMetaData = $productMetaData;
        $this->regionsCollection = $regionsCollection;
        $this->_storeTime = $timezone;
        parent::__construct($context);
    }

    public function getStoreUrl()
    {
        return $this->scopeConfig->getValue(
            'web/unsecure/base_url',
            ScopeInterface::SCOPE_STORE
        );
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

    public function useQtyAsStockSource()
    {
        return $this->scopeConfig->getValue(self::STOCK_SOURCE);
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

    public function getTimezone($storeId = 0)
    {
        return $this->_storeTime->getConfigTimezone('store', $storeId);
    }

    public function getSearchLayered($scopeId = 0)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SEARCH_LAYERED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scopeId
        );
    }

    public function getSearchEngine($scopeId = 0)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SEARCH_ENGINE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scopeId
        );
    }

    public function getFlatCatalogUsage($scopeId = 0)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FLAT_CATALOG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scopeId
        );
    }

    public function getSingleStoreEnabled($scopeId = 0)
    {
        $singleStoreEnabled = $this->scopeConfig->getValue(
            self::XML_PATH_SINGLE_STORE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scopeId
        );

        if (filter_var($singleStoreEnabled, FILTER_VALIDATE_BOOLEAN)) {
            $singleStoreId = $this->scopeConfig->getValue(
                self::XML_PATH_SINGLE_STORE_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $scopeId
            );
            return sprintf('%s:%s', $singleStoreEnabled, $singleStoreId);
        }
        return false;
    }

    public function getSmartNavigationNative($scopeId = 0)
    {
        return $this->scopeConfig->getValue(
            self::XML_SMART_NAVIGATION_CONFIG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scopeId
        );
    }

    public function getBasicEnabled($scopeId = 0)
    {
        return $this->scopeConfig->getValue(
            self::XML_BASIC_ENABLED_CONFIG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scopeId
        );
    }

    public function getDisplayOutOfStock()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SHOW_OOS,
            self::SCOPE_CONFIG_STORES
        );
    }

    public function getDropdownV2($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DROPDOWN_V2,
            'stores',
            $storeId
        );
    }

    public function setDropdownV2($val, $storeId)
    {
        if ($val == 'false' || $val == '0') {
            $val = 0;
        } else {
            $val = 1;
        }
        return $this->resourceConfig->saveConfig(
            self::XML_PATH_DROPDOWN_V2,
            $val,
            'stores',
            $storeId
        );
    }

    public function getSerpV2($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SERP_V2,
            'stores',
            $storeId
        );
    }

    public function setSerpV2($val, $storeId)
    {
        if ($val == 'false' || $val == '0') {
            $val = 0;
        } else {
            $val = 1;
        }
        return $this->resourceConfig->saveConfig(
            self::XML_PATH_SERP_V2,
            $val,
            'stores',
            $storeId
        );
    }

    public function getSmnV2($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SMN_V2,
            'stores',
            $storeId
        );
    }

    public function setSmnV2($val, $storeId)
    {
        if ($val == 'false' || $val == '0') {
            $val = 0;
        } else {
            $val = 1;
        }
        return $this->resourceConfig->saveConfig(
            self::XML_PATH_SMN_V2,
            $val,
            'stores',
            $storeId
        );
    }

    public function getSerpCustomValues($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CUSTOM_VALUES,
            'stores',
            $storeId
        );
    }

    public function setSerpCustomValues($storeId)
    {
        $uuid = $this->api->getApiUUID();
        $val = $this->api->getSerpCustomValues($uuid, $storeId);
        return $this->resourceConfig->saveConfig(
            self::XML_PATH_CUSTOM_VALUES,
            $val,
            'stores',
            $storeId
        );
    }

    public function getManageStock()
    {
        return $this->scopeConfig->getValue(
            self::XML_MANAGE_STOCK,
            self::SCOPE_CONFIG_STORES
        );
    }

    public function getStoreInformation($store_id)
    {
        $country_code = $this->getCountryCode($store_id);
        $address = $this->getRegionCodeById($store_id);
        $store_info = array();

        if ($country_code) {
            $store_info['country_code'] = $country_code;
        }

        if ($address) {
            $store_info['address'] = rawurlencode($address);
        }

        return $store_info;
    }

    public function getCountryCode($store_id = 0)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_COUNTRY_CODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    public function getRegionCodeById($store_id)
    {
        $region_id = $this->scopeConfig->getValue(
            self::XML_PATH_REGION_CODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store_id
        );
        $regionData = $this->regionsCollection
            ->load($region_id);
        return $regionData->getName();
    }

    public function getSerpSlug($scopeId = 0)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SERP_SLUG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scopeId
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

    public function setSmartNavigationNative($enabled, $scope = 'default', $scopeId = 0)
    {
        $this->resourceConfig->saveConfig(
            self::XML_SMART_NAVIGATION_CONFIG,
            intval($enabled),
            $scope,
            $scopeId
        );
        return $this;
    }

    public function setBasicEnabled($enabled, $scope = 'default', $scopeId = 0)
    {
        $this->resourceConfig->saveConfig(
            self::XML_BASIC_ENABLED_CONFIG,
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


    public function setSerpSlug($slug, $scope = 'default', $scopeId = 0)
    {
        $this->resourceConfig->saveConfig(
            self::XML_PATH_SERP_SLUG,
            $slug,
            $scope,
            $scopeId
        );
        return $this;
    }

    public function unsetSerpSlug($scope = 'default', $scopeId = 0)
    {
        $this->resourceConfig->deleteConfig(
            self::XML_PATH_SERP_SLUG,
            $scope,
            $scopeId
        );
        return $this;
    }

    public function isInstalled()
    {
        return !!(strlen($this->getApiUUID()) > 0);
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

        $singleStoreData = $this->getSingleStoreEnabled();
        if (!$singleStoreData) {
            $singleStoreEnabled = false;
            $singleStoreId = null;
        } else {
            $singleStoreData = explode(':', $singleStoreData);
            $singleStoreEnabled = filter_var($singleStoreData[0], FILTER_VALIDATE_BOOLEAN);
            $singleStoreId = $singleStoreData[1];
        }

        $storesArr = [];
        foreach ($websites as $website) {
            $stores = $website->getStores();
            foreach ($stores as $store) {
                if (!$singleStoreEnabled) {
                    $storesArr[$store->getId()] = $store->getData();
                } elseif (intval($singleStoreId) == intval($store->getId())) {
                    $storesArr[$store->getId()] = $store->getData();
                }
            }
        }
        if (count($storesArr) == 1) {
            try {
                $store_data = array_pop($storesArr);
                $dataArr = [
                    'stores' => $store_data,
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
}
