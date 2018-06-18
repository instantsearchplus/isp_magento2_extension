<?php

namespace Autocompleteplus\Autosuggest\Controller\Adminhtml\Install;

use \Magento\Store\Model\ScopeInterface;

class Run extends \Autocompleteplus\Autosuggest\Controller\Adminhtml\Install
{
    /**
     * Get product version
     *
     * @return string
     */
    public function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }

    public function execute()
    {

        $result = $this->resultRedirectFactory->create();
        $params =
            [
                'site'       => $this->scopeConfig->getValue(
                    'web/unsecure/base_url',
                    ScopeInterface::SCOPE_STORE
                ),
                'email'      => $this->scopeConfig->getValue(
                    'trans_email/ident_support/email',
                    ScopeInterface::SCOPE_STORE
                ),
                'f'          => $this->helper->getVersion(),
                'multistore' => json_encode($this->helper->getMultiStoreData()),
            ];

        $apiRequest = $this->api;
        $apiRequest->setUrl($apiRequest->getApiEndpoint() . '/install');
        $apiRequest->setRequestType(\Zend_Http_Client::POST);
        $response = $apiRequest->buildRequest($params);

        if ($responseData = json_decode($response->getBody())) {
            if (!$responseData->uuid || strlen($responseData->uuid) > 50) {
                $this->api->sendError('Could not get license string.');
                $this->messageManager->addError(__('Something went wrong when trying to install InstantSearch+'));
                $result->setPath('adminhtml/dashboard/index');
                return $result;
            }

            $this->api->setApiUUID($responseData->uuid);
            $this->api->setApiAuthenticationKey($responseData->authentication_key);
            $this->messageManager->addSuccess(__('InstantSearch+ successfully installed!'));
        } else {
            $this->messageManager->addError(__('Something went wrong when trying to install InstantSearch+'));
        }
        $result->setPath('adminhtml/dashboard/index');

        return $result;
    }
}
