<?php

namespace Autocompleteplus\Autosuggest\Controller\Products;

class Getstores extends \Autocompleteplus\Autosuggest\Controller\Products
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Data
     */
    protected $helper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Autocompleteplus\Autosuggest\Helper\Data $helper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        $responseArr = $this->helper->getMultiStoreData();
        $result = $this->resultJsonFactory->create();
        return $result->setData($responseArr);
    }
}
