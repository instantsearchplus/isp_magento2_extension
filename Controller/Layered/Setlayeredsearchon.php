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
        $mini_form_url_instantsearchplus = $request->getParam('mini_form_url_instantsearchplus', '0');

        if (!$this->isValid($uuid, $authKey)) {
            $response = [
                'status' => 'error: Authentication failed'
            ];
            $result->setData($response);
            return $result;
        }
        
        $this->helper->setSearchLayered(true, $scope, $scopeId);
        if ($mini_form_url_instantsearchplus === '1') {
            $this->helper->setMiniFormRewrite(true, $scope, $scopeId);
        } else {
            $this->helper->setMiniFormRewrite(false, $scope, $scopeId);
        }
        
        $this->clearCache();

        $response = [
            'new_state' => 1,
            'status' => 'ok',
        ];

        $result->setData($response);
        return $result;
    }
}
