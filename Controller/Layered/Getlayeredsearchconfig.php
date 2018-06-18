<?php

namespace Autocompleteplus\Autosuggest\Controller\Layered;

class Getlayeredsearchconfig extends \Autocompleteplus\Autosuggest\Controller\Layered
{
    public function execute()
    {
        $request = $this->getRequest();
        $authKey = $request->getParam('authentication_key');
        $uuid = $request->getParam('uuid');
        $scopeId = $request->getParam('store_id', 1);
        $result = $this->resultJsonFactory->create();

        if (!$this->isValid($uuid, $authKey)) {
            $response = [
                'status' => 'error: Authentication failed'
            ];
            $result->setData($response);
            return $result;
        }

        $currentState = $this->helper->getSearchLayered($scopeId);

        $response = [
            'current_state' => $currentState
        ];

        $result->setData($response);
        return $result;
    }
}
