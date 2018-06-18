<?php

namespace Autocompleteplus\Autosuggest\Controller\Adminhtml;

abstract class Redirect extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    protected $resultForwardFactory;
    protected $resultRedirectFactory;
    protected $scopeConfig;
    protected $helper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Autocompleteplus\Autosuggest\Helper\Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
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
            'Autocompleteplus_Autosuggest::instantsearch'
        )->_addBreadcrumb(
            __('InstantSearch'),
            __('Redirect')
        );
        return $this;
    }
}
