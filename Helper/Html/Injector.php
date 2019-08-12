<?php
/**
 * Injector.php File
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
namespace Autocompleteplus\Autosuggest\Helper\Html;

use Magento\Framework\App;
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
 * @copyright 2017 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */
class Injector extends \Magento\Framework\App\Helper\AbstractHelper
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
    protected $catalogSession;
    protected $product;
    protected $productModel;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Injector constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Autocompleteplus\Autosuggest\Helper\Data $helper
     * @param \Autocompleteplus\Autosuggest\Helper\Api $apiHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Checkout\Model\Cart $cart
     * @param CookieManagerInterface $cookieManager
     * @param DeploymentConfig $deploymentConfig
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Catalog\Model\Product $productModel
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Autocompleteplus\Autosuggest\Helper\Api $apiHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Cart $cart,
        CookieManagerInterface $cookieManager,
        DeploymentConfig $deploymentConfig,
        \Magento\Customer\Model\Session $session,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Catalog\Model\Product $productModel
    ) {
        $this->helper = $helper;
        $this->apiHelper = $apiHelper;
        $this->registry = $registry;
        $this->cart = $cart;
        $this->deploymentConfig = $deploymentConfig;
        $this->cookieManager = $cookieManager;
        $this->customerSession = $session;
        $this->catalogSession = $catalogSession;
        $this->_isScopePrivate = true;
        $this->_storeManager = $context->getStoreManager();
        $this->productModel = $productModel;
    }

    public function getCurrentProduct()
    {
        if ($this->catalogSession->getIspProductSku() && !$this->product) {
            $this->product = $this->productModel
                ->loadByAttribute(
                    'sku',
                    $this->catalogSession->getIspProductSku(),
                    'product_url'
                );
        }
        return $this->product;
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
        $parameters = $this->getParameters();

        return self::AUTOCOMPLETE_JS_URL.'?'.http_build_query($parameters, '', '&');
    }

    /**
     * Method getAdditionalParameters returns dynamic script vars
     *
     * @return array
     */
    public function getAdditionalParameters()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $parameters = [
            'store' => $storeId,
            'is_admin_user' =>  0,
            'sessionID' =>  $this->getSessionId(),
            'is_user_logged_in'=> ($this->isLoggedInUser() ? '1' : '0'),
            'QuoteID'   =>  $this->cart->getQuote()->getId(),
        ];

        if ($this->customerSession->isLoggedIn()) {
            $parameters['customer_group_id'] = $this->customerSession
                ->getCustomerGroupId();
        }

        if ($this->getCurrentProduct()) {
            $parameters = array_merge(
                $parameters,
                [
                    'product_url' => $this->getCurrentProduct()->getProductUrl(),
                    'product_sku' => $this->getCurrentProduct()->getSku(),
                    'product_id' => $this->getCurrentProduct()->getId(),
                ]
            );
        }

        $this->catalogSession->unsIspProductSku();

        return $parameters;
    }

    /**
     * Method getParameters return query string parameters
     *
     * @return array
     */
    public function getParameters()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $parameters = [
            'mage_v' => $this->helper->getMagentoVersion(),
            'ext_v' => $this->helper->getVersion(),
            'store' => $storeId,
            'UUID' => $this->apiHelper->getApiUUID(),
            'serp_slug' => $this->helper->getSerpSlug($storeId),
            'm2' => true
        ];

        return $parameters;
    }
}
