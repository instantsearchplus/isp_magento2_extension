<?php

namespace Autocompleteplus\Autosuggest\Controller\Categories;

class Send extends \Autocompleteplus\Autosuggest\Controller\Categories
{
    protected $storeManager;
    protected $jsonGenerator;
    protected $resultJsonFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Autocompleteplus\Autosuggest\Helper\Category\Json\Generator $jsonGenerator,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->jsonGenerator = $jsonGenerator;
        $this->storeManager = $storeManagerInterface;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    public function execute()
    {
        $store = $this->getRequest()->getParam('store', $this->getStoreId());
        $responseData = $this->jsonGenerator->loadTree($store);
        $result = $this->resultJsonFactory->create();
        return $result->setData($responseData);
    }
}
