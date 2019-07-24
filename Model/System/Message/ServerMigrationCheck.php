<?php
/**
 * ServerMigrationCheck.php
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

namespace Autocompleteplus\Autosuggest\Model\System\Message;

use \Magento\Store\Model\ScopeInterface;

class ServerMigrationCheck implements \Magento\Framework\Notification\MessageInterface
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

    protected $request;

    protected $oldDomainIsReachable;

    protected $api;

    protected $notificationText;

    protected $urlMismatchDetected;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Autocompleteplus\Autosuggest\Helper\Api $helper
     */
    public function __construct(
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Autocompleteplus\Autosuggest\Helper\Api $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Autocompleteplus\Autosuggest\Helper\Api $api
    ) {
        $this->_authorization = $authorization;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->helper = $helper;
        $this->request = $request;
        $this->api = $api;
        $this->urlMismatchDetected = false;
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return md5('AUTOSUGGEST_SERVER_MIGRATION_NOTIFICATION');
    }

    protected function _checkSiteUrl()
    {
        $params =
            [
                'site'       => $this->scopeConfig->getValue(
                    'web/unsecure/base_url',
                    ScopeInterface::SCOPE_STORE
                ),
                'uuid'      => $this->helper->getApiUUID(),
                'auth_key'          => $this->helper->getApiAuthenticationKey(),
                'multistore' => json_encode($this->helper->getMultiStoreData()),
            ];

        $apiRequest = $this->api;
        $apiRequest->setUrl($apiRequest->getApiEndpoint() . '/ma_check_migration');
        $apiRequest->setRequestType(\Zend_Http_Client::POST);
        $response = $apiRequest->buildRequest($params);

        if ($responseData = json_decode($response->getBody())) {
            if ($responseData->success && $responseData->recommendation) {
                $this->notificationText = '<strong>';
                $this->notificationText .= __('InstantSearch+ has detected URL mismatch!');
                $this->notificationText .= '</strong>';
                $this->notificationText .= '<p>';
                $this->notificationText .= __('We recommend: ');
                $this->notificationText .= __($responseData->recommendation);
                $this->notificationText .= '</p>';
                $this->urlMismatchDetected = true;
            }
        }
        return $this->urlMismatchDetected;
    }

    /**
     * Check whether notification is displayed
     * @return bool
     */
    public function isDisplayed()
    {
        if ($this->request->getParam('isAjax') === 'true') {
            return ($this->_authorization->isAllowed(
                'Autocompleteplus_Autosuggest::autosuggest'
            ) && ($this->checkSiteUrl()));
        } else {
            return true;
        }
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
        if ($this->oldDomainIsReachable) {
            $messageDetails .= '<strong>';
            $messageDetails .= __('There appears to be an issue with your InstantSearch+ configuration! ');
            $messageDetails .= '</strong><p>';
            $messageDetails .= __(
                'Please make sure your settings are correct <a href="%1">here</a>',
                $this->getLink()
            );
            $messageDetails .= '</p><p>';
            $messageDetails .= __(
                'You could also try installing it <a href="%1">here</a>',
                $this->getInstallUrl()
            );
            $messageDetails .= '</p>';
        } else {
            $messageDetails .= '<strong>';
            $messageDetails .= __('There appears to be an issue with your InstantSearch+ configuration! ');
            $messageDetails .= '</strong><p>';
            $messageDetails .= __(
                'Please make sure your settings are correct <a href="%1">here</a>',
                $this->getLink()
            );
            $messageDetails .= '</p><p>';
            $messageDetails .= __(
                'You could also try installing it <a href="%1">here</a>',
                $this->getInstallUrl()
            );
            $messageDetails .= '</p>';
        }
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
