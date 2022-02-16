<?php

namespace Autocompleteplus\Autosuggest\Controller\Layered;

class Setdropdownv2 extends \Autocompleteplus\Autosuggest\Controller\Layered
{
    public function execute()
    {
        $request = $this->getRequest();
        $authKey = $request->getParam('authentication_key');
        $uuid = $request->getParam('uuid');
        $scopeId = $request->getParam('store_id', 1);
        $drV2 = $request->getParam('v2_enabled', 'false');
        $result = $this->resultJsonFactory->create();

        if (!$this->isValid($uuid, $authKey)) {
            $response = [
                'status' => 'error: Authentication failed'
            ];
            $result->setData($response);
            return $result;
        }

        if (!$this->isStoreExists($scopeId)) {
            $response = [
                'status' => 'error: Store does not exists'
            ];
            $result->setData($response);
            return $result;
        }

        $this->helper->setDropdownV2($drV2, $scopeId);
        $this->clearCache();

        $response = [
            'new_state' => $drV2,
            'status' => 'ok',
        ];

        $result->setData($response);
        return $result;
    }
}
