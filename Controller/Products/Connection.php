<?php

namespace Autocompleteplus\Autosuggest\Controller\Products;

class Connection extends \Autocompleteplus\Autosuggest\Controller\Products
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Eventually want this to be JSON
     * @return int|void
     */
    public function execute()
    {
        $this->getResponse()->setBody(1);
        return $this->getResponse()->sendResponse();
    }
}
