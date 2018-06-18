<?php

namespace Autocompleteplus\Autosuggest\Controller\Products;

class Updatesitemap extends \Autocompleteplus\Autosuggest\Controller\Products
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
        //$collection = $this->categoryFactory->create()->getCollection();
        //$collection->addFieldToSelect('*');
        // TODO: Implement execute() method.
    }
}
