<?php

namespace Autocompleteplus\Autosuggest\Controller\Result;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\ScopeInterface;


class Index extends \Magento\CatalogSearch\Controller\Result\Index
{
    /**
     * @var QueryFactory
     */
    protected $_queryFactory;

    /**
     * Catalog Layer Resolver
     *
     * @var Resolver
     */
    protected $layerResolver;

    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Data
     */
    protected $helper;
    
    /**
     * @param Context $context
     * @param Session $catalogSession
     * @param StoreManagerInterface $storeManager
     * @param QueryFactory $queryFactory
     * @param Resolver $layerResolver
     */
    public function __construct(
        Context $context,
        Session $catalogSession,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        Resolver $layerResolver,
        \Autocompleteplus\Autosuggest\Helper\Data $helper
    ) {
        $this->_storeManager = $storeManager;
        $this->_catalogSession = $catalogSession;
        $this->_queryFactory = $queryFactory;
        $this->layerResolver = $layerResolver;
        $this->helper = $helper;
        parent::__construct(
            $context,
            $catalogSession,
            $storeManager,
            $queryFactory,
            $layerResolver
        );
    }
    
    public function execute()
    {
        $this->layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);
        /* @var $query \Magento\Search\Model\Query */
        $query = $this->_queryFactory->get();

        $query->setStoreId($this->_storeManager->getStore()->getId());

        $laeyeredEnabled = $this->helper->canUseSearchLayered();
        
        if ($laeyeredEnabled == '1') {
            $this->_view->loadLayout();
            $this->_view->renderLayout();
        } else {
            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
        }
    }
}
