<?php

namespace Autocompleteplus\Autosuggest\Controller\Products;

class Sendupdated extends \Autocompleteplus\Autosuggest\Controller\Products
{
    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Product\Xml\Generator
     */
    protected $xmlGenerator;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Autocompleteplus\Autosuggest\Helper\Product\Xml\Generator $xmlGenerator,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->xmlGenerator = $xmlGenerator;
        $this->date = $date;
        parent::__construct($context);
    }

    public function execute()
    {
        $count = $this->getRequest()->getParam('count', 100);
        $storeId = $this->getRequest()->getParam('store_id', 1);
        $from = $this->getRequest()->getParam('from');
        $to = $this->getRequest()->getParam('to', $this->date->gmtTimestamp());

        $catalogXml = $this->xmlGenerator->renderUpdatesCatalogXml($count, $storeId, $from, $to);

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\App\ResponseInterface|\Magento\Framework\App\Response\Http $response */
        $response = $om->get('Magento\Framework\App\ResponseInterface');

        $response->setHeader('Content-type', 'text/xml');
        $response->setBody($catalogXml);
    }
}
