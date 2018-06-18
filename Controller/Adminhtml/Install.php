<?php

namespace Autocompleteplus\Autosuggest\Controller\Adminhtml;

abstract class Install extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    protected $resultForwardFactory;
    protected $resultRedirectFactory;
    protected $scopeConfig;
    protected $resourceConfig;
    protected $helper;
    protected $productMetadata;
    protected $api;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Autocompleteplus\Autosuggest\Helper\Api $api
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->scopeConfig = $scopeConfig;
        $this->resourceConfig = $resourceConfig;
        $this->helper = $helper;
        $this->productMetadata = $productMetadata;
        $this->api = $api;
        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Autocompleteplus_Autosuggest::instantsearch_configuration');
    }

    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Autocompleteplus_Autosuggest::instantsearch_configuration'
        )->_addBreadcrumb(
            __('InstantSearch'),
            __('Configuration')
        );
        return $this;
    }
}
