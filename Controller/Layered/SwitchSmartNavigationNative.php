<?php
/**
 * SwitchSmartNavigationNative.php
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
 * @copyright 2018 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

namespace Autocompleteplus\Autosuggest\Controller\Layered;

class SwitchSmartNavigationNative extends \Autocompleteplus\Autosuggest\Controller\Layered
{
    public function execute()
    {
        $request = $this->getRequest();
        $authKey = $request->getParam('authentication_key');
        $uuid = $request->getParam('uuid');
        $scope = $request->getParam('scope', 'stores');
        $scopeId = $request->getParam('store_id');
        $result = $this->resultJsonFactory->create();
        $state = $request->getParam('state');

        if (!$this->isValid($uuid, $authKey)) {
            $response = [
                'status' => 'error: Authentication failed'
            ];
            $result->setData($response);
            return $result;
        }

        if (!$scopeId) {
            $response = [
                'status' => 'error: No store id specified'
            ];
            $result->setData($response);
            return $result;
        }

        if (!in_array($state, ['on', 'off'])) {
            $response = ['status' => 'error: '.'Wrong state'];
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

        if ($state === 'on') {
            $this->helper->setSmartNavigationNative(true, $scope, $scopeId);
        } else {
            $this->helper->setSmartNavigationNative(false, $scope, $scopeId);
        }
        $this->clearCache();

        $response = [
            'new_state' => $state,
            'status' => 'ok',
        ];

        $result->setData($response);
        return $result;
    }
}
