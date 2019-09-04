<?php

namespace Autocompleteplus\Autosuggest\Controller\Products;

use Magento\Store\Model\ScopeInterface;

class Getpriceindex extends \Autocompleteplus\Autosuggest\Controller\Products
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
     *
     * @param \Magento\Framework\App\Action\Context               $context
     * @param \Autocompleteplus\Autosuggest\Helper\Catalog\Report $catalogReport
     * @param \Magento\Framework\Controller\Result\JsonFactory    $resultJsonFactory
     * @param \Autocompleteplus\Autosuggest\Helper\Api            $apiHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface  $scopeConfig
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
        $result = $this->resultJsonFactory->create();
        $storeId = $this->getRequest()->getParam('store', 1);
        $customer_group = $this->getRequest()->getParam('customer_group', 0);
        $count = $this->getRequest()->getParam('count', 1000);
        $offset = $this->getRequest()->getParam('offset', 0);
        $product_id = $this->getRequest()->getParam('id', 0);
        $authKey = $this->getRequest()->getParam('authentication_key');
        $uuid = $this->getRequest()->getParam('uuid');

        if (!$this->isValid($uuid, $authKey)) {
            $response = [
                'status' => 'error: Authentication failed'
            ];
            $result->setData($response);
            return $result;
        }


        $responseArr = $this->catalogReport->getPricesFromIndex(
            $storeId,
            $customer_group,
            $count,
            $offset,
            $product_id
        );
        return $result->setData($responseArr);
    }

    public function isValid($uuid, $authKey)
    {
        if ($this->apiHelper->getApiUUID() == $uuid
            && $this->apiHelper->getApiAuthenticationKey() == $authKey
        ) {
            return true;
        }

        return false;
    }
}
