<?php
/**
 * NotInstalled File
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

namespace Autocompleteplus\Autosuggest\Model\System\Message;

/**
 * NotInstalled
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
class NotInstalled implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * Store manager object
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    protected $helper;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Autocompleteplus\Autosuggest\Helper\Api $helper
     */
    public function __construct(
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Autocompleteplus\Autosuggest\Helper\Api $helper
    ) {
        $this->_authorization = $authorization;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->helper = $helper;
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return md5('AUTOSUGGEST_NOTINSTALLED_NOTIFICATION');
    }

    public function checkEndpoint()
    {
        return is_null($this->helper->getApiEndpoint()) ||
            (strlen($this->helper->getApiEndpoint()) < 30);
    }

    public function checkUUID()
    {
        return is_null($this->helper->getApiUUID()) ||
        (strlen($this->helper->getApiUUID()) < 30);
    }

    public function checkAuthenticationKey()
    {
        return is_null($this->helper->getApiAuthenticationKey()) ||
        (strlen($this->helper->getApiAuthenticationKey()) < 30);
    }

    /**
     * Get URL to the admin instantsearch configuration page
     *
     * @return string
     */
    public function getLink()
    {
        return $this->urlBuilder->getUrl('adminhtml/system_config/edit/section/autosuggest');
    }

    public function getInstallUrl()
    {
        return $this->urlBuilder->getUrl('autosuggest/install/run');
    }

    public function getIgnoreNotificationUrl()
    {
        return $this->urlBuilder->getUrl('autosuggest/notification/ignoreNotInstalled');
    }

    /**
     * Check whether notification is displayed
     * @return bool
     */
    public function isDisplayed()
    {
        return ($this->_authorization->isAllowed(
            'Autocompleteplus_Autosuggest::autosuggest'
        ) && ($this->checkAuthenticationKey() ||
            $this->checkEndpoint() ||
            $this->checkUUID()));
    }

    /**
     * Build message text
     * Determine which notification and data to display
     *
     * @return string
     */
    public function getText()
    {
        $messageDetails = '';
        $messageDetails .= '<strong>';
        $messageDetails .= __('There appears to be an issue with your InstantSearch+ configuration! ');
        $messageDetails .= '</strong><p>';
        $messageDetails .= __('Please make sure your settings are correct <a href="%1">here</a>',
            $this->getLink());
        $messageDetails .= '</p><p>';
        $messageDetails .= __('You could also try installing it <a href="%1">here</a>',
            $this->getInstallUrl());
        $messageDetails .= '</p>';

        return $messageDetails;
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL;
    }
}
