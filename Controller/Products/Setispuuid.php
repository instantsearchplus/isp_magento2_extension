<?php

namespace Autocompleteplus\Autosuggest\Controller\Products;

use Magento\Store\Model\ScopeInterface;

class Setispuuid extends \Autocompleteplus\Autosuggest\Controller\Products
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
        $this->apiHelper->updateUUID();
        return $this->getResponse();
    }
}
