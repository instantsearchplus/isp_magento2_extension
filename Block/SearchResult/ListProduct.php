<?php

namespace Autocompleteplus\Autosuggest\Block\SearchResult;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class ListProduct extends Template
{
    protected $helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $response;
    protected $storeManager;
    protected $formKey;

    public function __construct(
        Context $context,
        \Magento\Framework\App\Response\Http $response,
        \Autocompleteplus\Autosuggest\Helper\Api $helper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = []
    )
    {
        $this->storeManager = $storeManagerInterface;
        $this->request = $request;
        $this->response = $response;
        $this->helper = $helper;
        $this->formKey = $formKey;
        parent::__construct($context, $data);
    }

    public function getSearchQuery()
    {
        return $this->request->getParam('q');
    }

    public function getApiUUID()
    {
        return $this->helper->getApiUUID();
    }

    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getSearchResults()
    {
        return $this->helper->fetchProductListingData();
    }
}