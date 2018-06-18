<?php

namespace Autocompleteplus\Autosuggest\Controller\Products;

class Getispuuid extends \Autocompleteplus\Autosuggest\Controller\Products
{
    protected $apiHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Autocompleteplus\Autosuggest\Helper\Api $apiHelper
    ) {
        $this->apiHelper = $apiHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $response = $this->getResponse();
        $response->setBody($this->apiHelper->getApiUUID());
        return $response->sendResponse();
    }
}
