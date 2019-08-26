<?php
/**
 * ConfigProvider.php
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
 * @copyright 2019 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

namespace Autocompleteplus\Autosuggest\Model\Plugin\Html;

use Magento\Framework\App;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Stdlib\CookieManagerInterface;
/**
 * Class ConfigProvider
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
 * @copyright Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */
class ConfigProvider
{

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Catalog\Model\Session $catalogSession
     */
    protected $catalogSession;

    protected $helper;
    protected $cart;
    protected $deploymentConfig;
    protected $cookieManager;
    protected $customerSession;

    const SESSION_COOKIE_NAME = 'PHPSESSID';

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Session $catalogSession,
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Magento\Checkout\Model\Cart $cart,
        CookieManagerInterface $cookieManager,
        DeploymentConfig $deploymentConfig,
        \Magento\Customer\Model\Session $session
    ) {
        $this->storeManager = $storeManager;
        $this->catalogSession = $catalogSession;
        $this->helper = $helper;
        $this->cart = $cart;
        $this->deploymentConfig = $deploymentConfig;
        $this->cookieManager = $cookieManager;
        $this->customerSession = $session;
        $this->catalogSession = $catalogSession;
    }

    public function afterGetConfig($subject, $result)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $result['isp_store_id'] = $storeId;

        if ($this->catalogSession->getIspProductId()) {
            $result['isp_product_id'] = $this->catalogSession->getIspProductId();
            $this->catalogSession->unsIspProductId();
        }

        if ($this->helper->getSerpSlug($storeId)) {
            $result['isp_serp_slug'] = $this->helper->getSerpSlug($storeId);
        }

        $result['sessionID'] = $this->getSessionId();
        $result['is_user_logged_in'] = ($this->isLoggedInUser() ? '1' : '0');
        $result['QuoteID'] = $this->cart->getQuote()->getId();
        $result['is_admin_user'] = 0;

        if ($this->customerSession->isLoggedIn()) {
            $result['customer_group_id'] = $this->customerSession
                ->getCustomerGroupId();
        }

        return $result;
    }

    public function getSessionId()
    {
        return hash(
            'sha256',
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
}
