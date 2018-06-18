<?php
/**
 * Injector File
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
 * @copyright 2014 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

namespace Autocompleteplus\Autosuggest\Block\Html;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * Injector
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
 * @copyright 2014 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */
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
    protected $customerSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Autocompleteplus\Autosuggest\Helper\Api $apiHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Cart $cart,
        CookieManagerInterface $cookieManager,
        DeploymentConfig $deploymentConfig,
        \Magento\Customer\Model\Session $session,
        array $data
    ) {
        $this->helper = $helper;
        $this->apiHelper = $apiHelper;
        $this->registry = $registry;
        $this->cart = $cart;
        $this->deploymentConfig = $deploymentConfig;
        $this->cookieManager = $cookieManager;
        $this->customerSession = $session;
        $this->_isScopePrivate = true;
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
        return md5(
            $this->cookieManager->getCookie(
                self::SESSION_COOKIE_NAME
            ).$this->deploymentConfig->get(
                \Magento\Framework\Encryption\Encryptor::PARAM_CRYPT_KEY
            )
        );
    }

    /**
     * IsLoggedInUser cheks if user is logged in
     *
     * @return mixed
     */
    public function isLoggedInUser()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * GetSrc return script url with params
     *
     * @return string
     */
    public function getSrc()
    {       
        $parameters = [
            'mage_v' => $this->helper->getMagentoVersion(),
            'ext_v' => $this->helper->getVersion(),
            'store' => $this->_storeManager->getStore()->getId(),
            'UUID' => $this->apiHelper->getApiUUID(),
            'is_admin_user' =>  0,
            'sessionID' =>  $this->getSessionId(),
            'is_user_logged_in'=> $this->isLoggedInUser(),
            'QuoteID'   =>  $this->cart->getQuote()->getId(),
        ];

        if ($this->customerSession->isLoggedIn()) {
            $parameters['customer_group_id'] = $this->customerSession
                ->getCustomerGroupId();
        }

        if ($this->getCurrentProduct()) {
            $parameters = array_merge(
                $parameters, [
                    'product_url' => $this->getCurrentProduct()->getProductUrl(),
                    'product_sku' => $this->getCurrentProduct()->getSku(),
                    'product_id' => $this->getCurrentProduct()->getId(),
                ]
            );
        }

        return self::AUTOCOMPLETE_JS_URL.'?'.http_build_query($parameters, '', '&');
    }
}
