<?php

namespace Autocompleteplus\Autosuggest\Controller\Products;

use Magento\Store\Model\ScopeInterface;

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
        $siteUrl = $this->scopeConfig->getValue(
            'web/unsecure/base_url',
            ScopeInterface::SCOPE_STORE
        );
        $storeId = $this->storeManager->getStore()->getId();
        $modules = $this->moduleList->getAll();
        $installedModules = [];

        $productCollection = $this->productCollection->create();
        $numProducts = $productCollection
            ->addStoreFilter($storeId)
            ->getSize();

        if ($getModules) {
            $installedModules = array_filter($modules, function ($name) {
                $isMagentoModule = (substr($name, 0, 7) == 'Magento');
                $isEnabled = $this->moduleManager->isEnabled($name);
                $isOutputEnabled = $this->moduleManager->isOutputEnabled($name);
                return !$isMagentoModule && $isEnabled && $isOutputEnabled;
            }, \ARRAY_FILTER_USE_KEY);
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
        ];

        $result = $this->resultJsonFactory->create();
        return $result->setData($responseData);
    }
}
