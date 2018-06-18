<?php

namespace Autocompleteplus\Autosuggest\Controller\Products;

use Magento\Store\Model\ScopeInterface;

class Changeserp extends \Autocompleteplus\Autosuggest\Controller\Products
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheTypeList;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->cacheTypeList = $cacheTypeList;
        parent::__construct($context);
    }

    public function execute()
    {
        $request = $this->getRequest();
        $serpReq = $request->getParam('new_serp', 0);
        $storeId = $request->getParam('store_id', 0);
        $result = $this->resultJsonFactory->create();

        $storeUrl = $this->scopeConfig->getValue(
            'web/unsecure/base_url',
            ScopeInterface::SCOPE_STORE
        );
        
        switch ($serpReq) {
            case 'status':
                $status = $this->helper->getSearchLayered($storeId);
                return $result->setData([ 'current_status' => $status ]);
                break;
            default:
                $this->helper->setSearchLayered(boolval($serpReq), 'stores', $storeId);
                $status = $this->helper->getSearchLayered($storeId);
                break;
        }

        $resp = [
            'request_state' => $serpReq,
            'new_state' => $status,
            'site_url' => $storeUrl,
            'status' => $status
        ];

        $this->cacheTypeList->cleanType('config');

        return $result->setData($resp);
    }
}
