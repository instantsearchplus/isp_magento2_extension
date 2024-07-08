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

use Laminas\Http\Client;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Helper\Context;
use Laminas\Http\Client\Adapter\Curl;
use \Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

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
    const API_ENDPOINT_URL = 'https://sync.fastsimon.com';
    const API_ENDPOINT_URL_UNSECURE = 'http://sync.fastsimon.com';

    protected $curlUrl;
    protected $requestType = \Laminas\Http\Request::METHOD_POST;
    protected $resourceConfig;
    protected $storeManager;

    /**
     * @param Context $context
     * @param Config $resourceConfig
     * @param StoreManagerInterface $storeManagerInterface
     */
    public function __construct(
        Context               $context,
        Config                $resourceConfig,
        StoreManagerInterface $storeManagerInterface
    )
    {
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

    public function getApiEndpointUnsecure()
    {
        return self::API_ENDPOINT_URL_UNSECURE;
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
            throw new \Magento\Framework\Exception('Tried setting invalid UUID value for Fast Simon. UUID=' . json_encode($UUID));
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

    public function buildRequest($requestData = [], $timeout = 30)
    {
        $client = new Client();

        $client->setUri($this->getUrl());
        /**
         * fix for localhost without ssl cert
         * 'verifypeer'    => false,
         * 'verifyhost'    => false
         */
        $client->setOptions([
            'useragent' => Client::class,
            'adapter' => Curl::class,
            'timeout' => $timeout
        ]);

        $client->setAdapter(Curl::class);

        if ($this->getRequestType() == \Laminas\Http\Request::METHOD_POST) {
            $client->setParameterPost($requestData);
        } else {
            $client->setParameterGet($requestData);
        }

        $client->setMethod($this->getRequestType());

        return $client->send();
    }

    public function getSerpCustomValues($uuid, $storeId) {
        $response = $this->getCustomValuesFromFAstSimon($uuid, $storeId);
        return $response;
    }

    private function getCustomValuesFromFAstSimon($uuid, $storeId) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://dashboard.fastsimon.com/api/serving/magento_update_fields',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; rv:21.0) Gecko/20100101 Firefox/21.0',
        ));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Store-ID: ' . $storeId,
                'UUID: ' . $uuid,
                'Content-Type: application/json',
                'Content-Length: 0')
        );

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
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

        $this->setUrl($this->getApiEndpoint() . '/magento_error');
        $response = $this->buildRequest(
            [
                'site' => $siteUrl,
                'msg' => $message,
                'email' => $email
            ]
        );

        return $response;
    }

    public function fetchProductListingData($secure=true)
    {
        $endPoint = $this->getApiEndpoint();
        if (!$secure) {
            $endPoint = $this->getApiEndpointUnsecure();
        }
        $this->setUrl($endPoint . '/ma_load_search_page');
        $params = [
            'isp_platform' => 'magento',
            'r' => '002',
            'uuid' => $this->getApiUUID(),
            'store_id' => $this->storeManager->getStore()->getId(),
            'm2' => 1
        ];
        try {
            $response = $this->buildRequest($params);
            $responseData = json_decode($response->getBody());
            if ($responseData->html) {
                return $responseData->html;
            }
        } catch (\Exception $e) {
            if ($secure)
                return $this->fetchProductListingData(false);
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
        $response = $this->buildRequest(
            [
            'store_id'  =>  $storeId,
            'site_url'  =>  $siteUrl
            ]
        );
        $responseData = json_decode($response->getBody());
        if ($this->validateUUID($responseData->uuid)) {
            $this->setApiUUID($responseData->uuid);
        }
    }

    public function updateSiteGroup($newAuthKey, $newUuid)
    {
        $uuid = $this->getApiUUID();
        $authKey = $this->getApiAuthenticationKey();
        $storeId = $this->storeManager->getStore()->getId();
        $siteUrl = $this->scopeConfig->getValue(
            'web/unsecure/base_url',
            ScopeInterface::SCOPE_STORE
        );

        if (empty($newAuthKey) or empty($newUuid)) {
            $this->setUrl($this->getApiEndpoint() . '/update_site_group_credentials');
            $response = $this->buildRequest(
                [
                    'store_id' => $storeId,
                    'site_url' => $siteUrl,
                    'uuid' => $uuid,
                    'authKey' => $authKey
                ]
            );
            $responseData = json_decode($response->getBody());

            if ($responseData->status == 'success') {
                $newAuthKey = $responseData->authKey;
                $newUuid = $responseData->uuid;
            }
            else {
                return ['status' => 'error', 'message' => $responseData->message];
            }
        }

        if (!empty($newAuthKey) and !empty($newUuid)) {
            $this->setApiUUID($newUuid);
            $this->setApiAuthenticationKey($newAuthKey);

            return ['status' => 'success', 'new_uuid' => $newUuid];
        }
        else {
            return ['status' => 'error', 'message' =>  'Tried setting invalid UUID or auth key value for Fast Simon.'];
        }
    }

    /**
     * post_without_wait send http call and close the connection without waiting for response
     *
     * @param $url
     * @param array  $params
     * @param string $type
     *
     * @return void
     */
    public function post_without_wait($url, $params = [], $type = 'POST', $post_params = [])
    {
        foreach ($params as $key => &$val) {
            if (is_array($val)) {
                $val = implode(',', $val);
            }
            $post_params[] = $key.'='.urlencode($val);
        }

        $post_string = implode('&', $post_params);
        $parts=parse_url($url);
        $fp = fsockopen(
            $parts['host'],
            isset($parts['port'])? $parts['port'] : 80,
            $errno,
            $errstr,
            30
        );

        // Data goes in the path for a GET request
        if ('GET' == $type) {
            $parts['path'] .= '?'.$post_string;
        }

        $out = "$type ".$parts['path']." HTTP/1.1\r\n";
        $out.= "Host: ".$parts['host']."\r\n";

        if ($type == 'POST') {
            $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
            $out.= "Content-Length: ".strlen($post_string)."\r\n";
        }

        $out.= "Connection: Close\r\n\r\n";
        // Data goes in the request body for a POST request
        if ('POST' == $type && isset($post_string)) {
            $out.= $post_string;
        }

        fwrite($fp, $out);
        fclose($fp);
    }
}
