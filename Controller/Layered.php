<?php

namespace Autocompleteplus\Autosuggest\Controller;

abstract class Layered extends \Magento\Framework\App\Action\Action
{
    protected $helper;
    protected $apiHelper;
    protected $resultJsonFactory;
    protected $cacheTypeList;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Autocompleteplus\Autosuggest\Helper\Api $apiHelper,
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
    ) {
        $this->apiHelper = $apiHelper;
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cacheTypeList = $cacheTypeList;
        parent::__construct($context);
    }

    public function isValid($uuid, $authKey)
    {
        if ($this->apiHelper->getApiUUID() == $uuid &&
            $this->apiHelper->getApiAuthenticationKey() == $authKey) {
            return true;
        }
        return false;
    }

    public function clearCache()
    {
        $this->cacheTypeList->cleanType('config');
    }
}
