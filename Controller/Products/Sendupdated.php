<?php

namespace Autocompleteplus\Autosuggest\Controller\Products;
/**
 * Sendupdated
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

    protected $response;

    /**
     * Sendupdated constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Autocompleteplus\Autosuggest\Helper\Product\Xml\Generator $xmlGenerator
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Autocompleteplus\Autosuggest\Helper\Product\Xml\Generator $xmlGenerator,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->xmlGenerator = $xmlGenerator;
        $this->date = $date;
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
        $count = $this->getRequest()->getParam('count', 100);
        $storeId = $this->getRequest()->getParam('store_id', 1);
        $from = $this->getRequest()->getParam('from');
        $to = $this->getRequest()->getParam('to', false);
        $page = $this->getRequest()->getParam('page', 1);
        $send_oos = $this->getRequest()->getParam('send_oos', false);

        if (intval($send_oos) == 1) {
            $send_oos = true;
        }
        $catalogXml = $this->xmlGenerator
            ->renderUpdatesCatalogXml($count, $storeId, $from, $to, $page, $send_oos);

        $this->response->setHeader('Content-type', 'text/xml');
        $this->response->setBody($catalogXml);
    }
}
