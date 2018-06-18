<?php

namespace Autocompleteplus\Autosuggest\Block\Html;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Stdlib\CookieManagerInterface;

class Injector extends \Magento\Framework\View\Element\Template
{
    const AUTOCOMPLETE_JS_URL = 'https://acp-magento.appspot.com/js/acp-magento.js';
    const SESSION_COOKIE_NAME = 'PHPSESSID';

    protected $helper;
    protected $apiHelper;
    protected $registry;
    protected $cart;
    protected $deploymentConfig;
    protected $cookieManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Autocompleteplus\Autosuggest\Helper\Api $apiHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Cart $cart,
        CookieManagerInterface $cookieManager,
        DeploymentConfig $deploymentConfig,
        array $data)
    {
        $this->helper = $helper;
        $this->apiHelper = $apiHelper;
        $this->registry = $registry;
        $this->cart = $cart;
        $this->deploymentConfig = $deploymentConfig;
        $this->cookieManager = $cookieManager;
        parent::__construct($context, $data);
    }

    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    public function isEnabled()
    {
        return $this->helper->getEnabled();
    }

    public function getSessionId()
    {
        return md5($this->cookieManager->getCookie(self::SESSION_COOKIE_NAME).$this->deploymentConfig->get(\Magento\Framework\Encryption\Encryptor::PARAM_CRYPT_KEY));
    }
    
    public function isLoggedInUser(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        return $customerSession->isLoggedIn();
    }
    
    public function getSrc()
    {
        $parameters = array(
            'mage_v' => \Magento\Framework\AppInterface::VERSION,
            'ext_v' => $this->helper->getVersion(),
            'store' => $this->_storeManager->getStore()->getId(),
            'UUID' => $this->apiHelper->getApiUUID(),
            'is_admin_user' =>  0,
            'sessionID' =>  $this->getSessionId(),
            'QuoteID'   =>  $this->cart->getQuote()->getId(),
            'is_user_logged_in'=> $this->isLoggedInUser()
        );

        if ($this->getCurrentProduct()) {
            $parameters = array_merge($parameters, array(
                'product_url' => $this->getCurrentProduct()->getProductUrl(),
                'product_sku' => $this->getCurrentProduct()->getSku(),
                'product_id' => $this->getCurrentProduct()->getId(),
            ));
        }

        return self::AUTOCOMPLETE_JS_URL.'?'.http_build_query($parameters, '', '&');
    }
}