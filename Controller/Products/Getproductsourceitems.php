<?php

namespace Autocompleteplus\Autosuggest\Controller\Products;

use Magento\Store\Model\ScopeInterface;

class Getproductsourceitems extends \Autocompleteplus\Autosuggest\Controller\Products
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Product\Xml\Generator
     */
    protected $xmlGenerator;

    /**
     * @var \Magento\InventoryApi\Api\GetSourceItemsBySkuInterface
     */
    protected $getSourceItemsBySkuInterface;

    public function __construct(
        \Magento\Framework\App\Action\Context                      $context,
        \Magento\Framework\Controller\Result\JsonFactory           $resultJsonFactory,
        \Autocompleteplus\Autosuggest\Helper\Product\Xml\Generator $xmlGenerator,
        \Magento\InventoryApi\Api\GetSourceItemsBySkuInterface     $getSourceItemsBySkuInterface
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->xmlGenerator = $xmlGenerator;
        $this->getSourceItemsBySkuInterface = $getSourceItemsBySkuInterface;

        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $productId = $this->getRequest()->getParam('product_id');
        $storeId = $this->getRequest()->getParam('store', 1);

        $product = $this->xmlGenerator->loadProductById($productId, $storeId);

        $responseData = [];

        try {
            foreach ($this->getSourceItemsBySkuInterface->execute($product->getSku()) as $sourceItem) {
                $status = $sourceItem->getStatus();
                $sourceCode = $sourceItem->getSourceCode();
                $quantity = $sourceItem->getQuantity();

                $responseData[] = ["status" => $status, "source_code" => $sourceCode, "quantity" => $quantity];
            }
        } catch (\Exception $e) {
            $responseData[] = $e->getTraceAsString();
        }


        return $result->setData($responseData);
    }
}
