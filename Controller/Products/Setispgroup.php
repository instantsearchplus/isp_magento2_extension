<?php

namespace Autocompleteplus\Autosuggest\Controller\Products;

use Magento\Store\Model\ScopeInterface;

class Setispgroup extends \Autocompleteplus\Autosuggest\Controller\Products
{
    protected $apiHelper;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Autocompleteplus\Autosuggest\Helper\Api $apiHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->apiHelper = $apiHelper;
        $this->resultJsonFactory = $resultJsonFactory;

        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $authKey = $this->getRequest()->getParam('authentication_key');
        $newAuthKey = $this->getRequest()->getParam('new_authentication_key');
        $newUuid = $this->getRequest()->getParam('new_uuid');

        if ($this->apiHelper->getApiAuthenticationKey() != $authKey) {
            $responseData = ['status' => 'error: Authentication failed'];
        }
        else{
            $responseData = $this->apiHelper->updateSiteGroup($newAuthKey, $newUuid);
        }

        return $result->setData($responseData);
    }
}
