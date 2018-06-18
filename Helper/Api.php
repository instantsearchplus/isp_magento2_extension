<?php
/**
 * Api File
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

namespace Autocompleteplus\Autosuggest\Helper;

use Magento\Framework\HTTP\ZendClientFactory;
use \Magento\Store\Model\ScopeInterface;

/**
 * Api
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
class Api extends \Magento\Framework\App\Helper\AbstractHelper
{
    const API_AUTHENTICATION_KEY = 'autosuggest/api/authentication_key';
    const API_UUID = 'autosuggest/api/uuid';
    const API_ENDPOINT = 'autosuggest/api/endpoint';
    const API_ENDPOINT_URL = 'https://acp-magento.appspot.com';

    protected $curlFactory;
    protected $curlUrl;
    protected $requestType = \Zend_Http_Client::POST;
    protected $scopeConfig;
    protected $httpClientFactory;
    protected $resourceConfig;
    protected $storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        ZendClientFactory $httpClientFactory,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
    ) {
        $this->curlFactory = $curlFactory;
        $this->httpClientFactory = $httpClientFactory;
        $this->resourceConfig = $resourceConfig;
        $this->storeManager = $storeManagerInterface;
        parent::__construct($context);
    }

    public function getApiAuthenticationKey()
    {
        return $this->scopeConfig->getValue(self::API_AUTHENTICATION_KEY);
    }

    public function getApiUUID()
    {
        return $this->scopeConfig->getValue(self::API_UUID);
    }

    public function getApiEndpoint()
    {
        return self::API_ENDPOINT_URL;
    }

    public function setApiAuthenticationKey($authKey)
    {
        $this->resourceConfig->saveConfig(
            self::API_AUTHENTICATION_KEY,
            $authKey,
            'default',
            0
        );
    }

    public function setApiUUID($UUID)
    {
        if ($this->validateUUID($UUID)) {
            $this->resourceConfig->saveConfig(
                self::API_UUID,
                $UUID,
                'default',
                0
            );
        } else {
            throw new \Magento\Framework\Exception('Tried setting invalid UUID value for InstantSearch+.');
        }
    }

    public function validateUUID($UUID)
    {
        if (strlen($UUID) == 36
            && substr_count($UUID, '-') == 4
        ) {
            return true;
        }

        return false;
    }

    public function setUrl($url)
    {
        $this->curlUrl = $url;
    }

    public function getUrl()
    {
        return $this->curlUrl;
    }

    public function getRequestType()
    {
        return $this->requestType;
    }

    public function setRequestType($type)
    {
        return $this->requestType = $type;
    }
    
    public function buildRequest($requestData = [] , $timeout=2)
    {                    
        /** @var \Magento\Framework\HTTP\ZendClient $client */
        $client = $this->httpClientFactory->create();
        $responseBody = [];

        $client->setAdapter('\Zend_Http_Client_Adapter_Curl');
        $client->setUri($this->getUrl());
        /**
         * fix for localhost without ssl cert
         * 'verifypeer'    => false,
         * 'verifyhost'    => false
         */
        $client->setConfig(
            [
                'timeout'   => $timeout
            ]
        );

        if ($this->getRequestType() == \Zend_Http_Client::POST) {
            $client->setParameterPost($requestData);
        } else {
            $client->setParameterGet($requestData);
        }

        $client->setMethod($this->getRequestType());

        return $client->request();
    }

    public function sendError($message)
    {
        $siteUrl = $this->scopeConfig->getValue(
            'web/unsecure/base_url',
            ScopeInterface::SCOPE_STORE
        );
        $email = $this->scopeConfig->getValue(
            'trans_email/ident_support/email',
            ScopeInterface::SCOPE_STORE
        );
        $this->setUrl($this->getApiEndpoint() . '/install_error');
        $response = $this->buildRequest([
            'site'  => $siteUrl,
            'msg'   => $message,
            'email' => $email,
            'multistore'    => $this->helper->getMultiStoreJson(),
            'f' => $this->helper->getVersion()
        ]);
        return $response;
    }

    public function fetchProductListingData()
    {           
        $this->setUrl($this->getApiEndpoint() . '/ma_load_search_page');
        
        try {
            $response = $this->buildRequest(
                [
                'isp_platform' => 'magento',
                'r' => '002'
                ]
            );
            $responseData = json_decode($response->getBody());
            if ($responseData->html) {
                return $responseData->html;
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }

    public function updateUUID()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $siteUrl = $this->scopeConfig->getValue(
            'web/unsecure/base_url',
            ScopeInterface::SCOPE_STORE
        );

        $this->setUrl($this->getApiEndpoint() . '/update_uuid');
        $response = $this->buildRequest([
            'store_id'  =>  $storeId,
            'site_url'  =>  $siteUrl
        ]);
        $responseData = json_decode($response->getBody());
        if ($this->validateUUID($responseData->uuid)) {
            $this->setApiUUID($responseData->uuid);
        }
    }
}
