<?php

namespace Autocompleteplus\Autosuggest\Controller\Layered;

class Setlayeredsearchon extends \Autocompleteplus\Autosuggest\Controller\Layered
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
            $response = array(
                'status' => 'error: Authentication failed'
            );
            $result->setData($response);
            return $result;
        }
        
        $this->helper->setSearchLayered(true, $scope, $scopeId);
        $this->clearCache();

        $response = array(
            'new_state' => 1,
            'status' => 'ok',
        );

        $result->setData($response);
        return $result;
    }
}