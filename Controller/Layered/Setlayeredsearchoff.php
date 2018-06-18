<?php

namespace Autocompleteplus\Autosuggest\Controller\Layered;

class Setlayeredsearchoff extends \Autocompleteplus\Autosuggest\Controller\Layered
{
    public function execute()
    {
        $request = $this->getRequest();
        $authKey = $request->getParam('authentication_key');
        $uuid = $request->getParam('uuid');
        $scope = $request->getParam('scope', 'stores');
        $scopeId = $request->getParam('store_id', 1);
        $result = $this->resultJsonFactory->create();

        if (!$this->isValid($uuid, $authKey)) {
            $response = [
                'status' => 'error: Authentication failed'
            ];
            $result->setData($response);
            return $result;
        }

        $this->helper->setSearchLayered(false, $scope, $scopeId);
        $this->helper->setMiniFormRewrite(false, $scope, $scopeId);
        $this->clearCache();

        $response = [
            'new_state' => 0,
            'status' => 'ok',
        ];

        $result->setData($response);
        return $result;
    }
}
