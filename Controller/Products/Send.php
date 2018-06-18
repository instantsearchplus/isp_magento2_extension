<?php

namespace Autocompleteplus\Autosuggest\Controller\Products;

class Send extends \Autocompleteplus\Autosuggest\Controller\Products
{
    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Product\Xml\Generator
     */
    protected $xmlGenerator;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Autocompleteplus\Autosuggest\Helper\Product\Xml\Generator $xmlGenerator
    ) {
        $this->xmlGenerator = $xmlGenerator;
        parent::__construct($context);
    }

    public function execute()
    {
        $offset = $this->getRequest()->getParam('offset', 0);
        $count = $this->getRequest()->getParam('count', 100);
        $storeId = $this->getRequest()->getParam('store', 1);
        $orders = $this->getRequest()->getParam('orders', false);
        $interval = $this->getRequest()->getParam('month_interval', 12);

        $catalogXml = $this->xmlGenerator->renderCatalogXml($offset, $count, $storeId, $orders, $interval);

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\App\ResponseInterface|\Magento\Framework\App\Response\Http $response */
        $response = $om->get('Magento\Framework\App\ResponseInterface');

        $response->setHeader('Content-type', 'text/xml');
        $response->setBody($catalogXml);
    }
}
