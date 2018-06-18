<?php

namespace Autocompleteplus\Autosuggest\Controller\Products;

use Magento\Store\Model\ScopeInterface;

class Getnumofproducts extends \Autocompleteplus\Autosuggest\Controller\Products
{
    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Catalog\Report
     */
    protected $catalogReport;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Api
     */
    protected $apiHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Getnumofproducts constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Autocompleteplus\Autosuggest\Helper\Catalog\Report $catalogReport
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Autocompleteplus\Autosuggest\Helper\Api $apiHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Autocompleteplus\Autosuggest\Helper\Catalog\Report $catalogReport,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Autocompleteplus\Autosuggest\Helper\Api $apiHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->catalogReport = $catalogReport;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->apiHelper = $apiHelper;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function execute()
    {
        $storeId = $this->getRequest()->getParam('store_id', false);
        if (!$storeId) {
            $storeId = $this->getRequest()->getParam('store', false);
        }

        $this->catalogReport->setStoreId($storeId);

        $storeUrl = $this->scopeConfig->getValue(
            'web/unsecure/base_url',
            ScopeInterface::SCOPE_STORE
        );

        $responseArr = [
            'num_of_products' => $this->catalogReport->getEnabledProductsCount(),
            'num_of_disabled_products' => $this->catalogReport->getDisabledProductsCount(),
            'num_of_searchable_products' => $this->catalogReport->getSearchableProductsCount(),
            'num_of_searchable_products2' => $this->catalogReport->getSecondarySearchableProductsCount(),
            'uuid' => $this->apiHelper->getApiUUID(),
            'site_url' => $storeUrl,
            'store_id' => $this->catalogReport->getCurrentStoreId()
        ];

        $result = $this->resultJsonFactory->create();
        return $result->setData($responseArr);
    }
}
