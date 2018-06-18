<?php

namespace Autocompleteplus\Autosuggest\Controller\Products;

class Checksum extends \Autocompleteplus\Autosuggest\Controller\Products
{
    protected $productFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->productFactory = $productFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        // TODO: Implement execute() method.
    }
}
