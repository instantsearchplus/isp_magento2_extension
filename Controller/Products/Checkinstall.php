<?php

namespace Autocompleteplus\Autosuggest\Controller\Products;

class Checkinstall extends \Autocompleteplus\Autosuggest\Controller\Products
{
    protected $helper;
    protected $resultJsonFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();
        $result->setData([
            'isInstalled'   =>  $this->helper->isInstalled()
        ]);
        return $result;
    }
}
