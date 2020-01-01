<?php

namespace Autocompleteplus\Autosuggest\Controller\Products;

/**
 * Send
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category Mage
 *
 * @package   Instantsearchplus
 * @author    Fast Simon <info@instantsearchplus.com>
 * @copyright 2017 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */
class Send extends \Autocompleteplus\Autosuggest\Controller\Products
{
    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Product\Xml\Generator
     */
    protected $xmlGenerator;

    protected $response;

    /**
     * Send constructor.
     *
     * @param \Magento\Framework\App\Action\Context                      $context
     * @param \Autocompleteplus\Autosuggest\Helper\Product\Xml\Generator $xmlGenerator
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Autocompleteplus\Autosuggest\Helper\Product\Xml\Generator $xmlGenerator
    ) {
        $this->xmlGenerator = $xmlGenerator;
        $this->response = $context->getResponse();
        parent::__construct($context);
    }

    /**
     * Method execute
     *
     * @return void
     */
    public function execute()
    {
        $offset = $this->getRequest()->getParam('offset', 0);
        $count = $this->getRequest()->getParam('count', 100);
        $storeId = $this->getRequest()->getParam('store', 1);
        $orders = $this->getRequest()->getParam('orders', false);
        $interval = $this->getRequest()->getParam('month_interval', 12);
        $this->xmlGenerator->checkCachedAttrValues($storeId);
        $catalogXml = $this->xmlGenerator
            ->renderCatalogXml($offset, $count, $storeId, $orders, $interval);

        $this->response->setHeader('Content-type', 'text/xml');
        $this->response->setBody($catalogXml);
    }
}
